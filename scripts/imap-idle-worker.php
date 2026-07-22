<?php
/**
 * IMAP IDLE Worker - Real-time new mail monitor
 * Run: php C:\xampp\htdocs\roundcube\scripts\imap-idle-worker.php
 * Runs as a background process, uses IMAP IDLE for instant new mail detection.
 */

$projectRoot = dirname(__DIR__);
$settingsDir = $projectRoot . '/storage/app/webmail';
$statusFile = $projectRoot . '/public/webmail/data/_data_/_default_/cache/imap-idle-status.json';
$logFile = $projectRoot . '/public/webmail/data/_data_/_default_/logs/imap-idle-worker.log';

set_time_limit(0);

function log_msg($msg) {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND);
}

function read_imap_line($sock, $timeout = 10) {
    $line = '';
    $start = time();
    while (true) {
        if (time() - $start > $timeout) return false;
        $char = @fgetc($sock);
        if ($char === false || $char === '') return false;
        if ($char === "\n") return rtrim($line, "\r");
        $line .= $char;
    }
}

function read_imap_until_tag($sock, $tag) {
    $response = '';
    while (true) {
        $line = read_imap_line($sock);
        if ($line === false) return false;
        $response .= $line . "\n";
        if (strpos($line, $tag . ' ') === 0) return $response;
    }
}

function extract_uidnext($response) {
    if (preg_match('/UIDNEXT\s+(\d+)/i', $response, $m)) return (int)$m[1];
    return 0;
}

$reconnect_wait = 5;

while (true) {
    try {
        $settingsFile = $settingsDir . '/sm_imap_' . md5('admin@alphaspacepro.online') . '.json';
        if (!is_file($settingsFile)) {
            log_msg("Settings file not found: $settingsFile, waiting...");
            sleep(30);
            continue;
        }

        $settings = json_decode(file_get_contents($settingsFile), true);
        if (!$settings || empty($settings['imap_host'])) {
            log_msg("Invalid settings, waiting...");
            sleep(30);
            continue;
        }

        $host = $settings['imap_host'];
        $port = (int)$settings['imap_port'];
        $username = 'admin@alphaspacepro.online';
        $password = $settings['password'];
        $encryption = $settings['imap_encryption'] ?? 'ssl';

        log_msg("Connecting to $host:$port...");
        $scheme = ($encryption === 'ssl') ? 'ssl://' : '';
        $sock = @fsockopen($scheme . $host, $port, $errno, $errstr, 30);
        if (!$sock) {
            throw new Exception("Connection failed: $errstr ($errno)");
        }

        $greeting = read_imap_line($sock);
        log_msg("Greeting: $greeting");

        $tag = 'A001';
        $pass_quoted = str_replace(['\\', '"'], ['\\\\', '\\"'], $password);
        fwrite($sock, "$tag LOGIN \"$username\" \"$pass_quoted\"\r\n");
        $resp = read_imap_until_tag($sock, $tag);
        if (strpos($resp, $tag . ' OK') === false) {
            throw new Exception("LOGIN failed: " . substr($resp, 0, 200));
        }
        log_msg("Authenticated");

        $tag = 'A002';
        fwrite($sock, "$tag SELECT INBOX\r\n");
        $resp = read_imap_until_tag($sock, $tag);
        $uidNext = extract_uidnext($resp);
        log_msg("INBOX selected, UIDNEXT=$uidNext");

        $idleTag = 'A003';
        fwrite($sock, "$idleTag IDLE\r\n");

        $cont = read_imap_line($sock);
        if ($cont === false || strpos($cont, '+') !== 0) {
            throw new Exception("IDLE not accepted: $cont");
        }
        log_msg("IDLE started");

        stream_set_timeout($sock, 300);
        $idleStart = time();
        $lastExists = 0;

        while (true) {
            $line = read_imap_line($sock, 300);
            if ($line === false) {
                throw new Exception("Connection timed out during IDLE");
            }
            log_msg("IDLE: $line");

            if (preg_match('/^\*\s+(\d+)\s+EXISTS/i', $line, $m)) {
                $exists = (int)$m[1];
                if ($exists > $lastExists) {
                    $lastExists = $exists;
                    log_msg("New mail detected! EXISTS=$exists");

                    fwrite($sock, "DONE\r\n");
                    $doneResp = read_imap_until_tag($sock, $idleTag);
                    log_msg("IDLE ended: " . substr($doneResp, 0, 100));

                    $fetchTag = 'A004';
                    fwrite($sock, "$fetchTag FETCH $exists (UID FLAGS BODY.PEEK[HEADER.FIELDS (FROM SUBJECT DATE)])\r\n");
                    $fetchResp = read_imap_until_tag($sock, $fetchTag);

                    $subject = '';
                    $from = '';
                    $msgUid = 0;
                    if (preg_match('/UID\s+(\d+)/i', $fetchResp, $m)) $msgUid = (int)$m[1];
                    if (preg_match('/^Subject:\s*(.+)$/im', $fetchResp, $m)) $subject = trim($m[1]);
                    if (preg_match('/^From:\s*(.+)$/im', $fetchResp, $m)) $from = trim($m[1]);

                    $status = [
                        'has_new' => true,
                        'timestamp' => time(),
                        'uid' => $msgUid,
                        'subject' => mb_substr($subject, 0, 200),
                        'from' => mb_substr($from, 0, 200),
                        'exists' => $exists
                    ];
                    file_put_contents($statusFile, json_encode($status));
                    log_msg("Status file updated: " . json_encode($status));

                    $idleTag = 'A005';
                    fwrite($sock, "$idleTag IDLE\r\n");
                    $cont = read_imap_line($sock);
                    if ($cont === false || strpos($cont, '+') !== 0) {
                        throw new Exception("IDLE re-enter failed: $cont");
                    }
                    $idleStart = time();
                    log_msg("IDLE restarted");
                }
            }

            if (preg_match('/^\*\s+(\d+)\s+RECENT/i', $line, $m)) {
                log_msg("RECENT: {$m[1]} new messages");
            }

            if (time() - $idleStart >= 1740) {
                log_msg("Renewing IDLE (28 min)");
                fwrite($sock, "DONE\r\n");
                read_imap_until_tag($sock, $idleTag);
                $idleTag = 'A006';
                fwrite($sock, "$idleTag IDLE\r\n");
                $cont = read_imap_line($sock);
                if ($cont === false || strpos($cont, '+') !== 0) {
                    throw new Exception("IDLE renew failed: $cont");
                }
                $idleStart = time();
                log_msg("IDLE renewed");
            }
        }

    } catch (Exception $e) {
        log_msg("ERROR: " . $e->getMessage());
        if (isset($sock) && $sock) @fclose($sock);
    }

    log_msg("Reconnecting in {$reconnect_wait}s...");
    sleep($reconnect_wait);
    $reconnect_wait = min($reconnect_wait + 5, 60);
}

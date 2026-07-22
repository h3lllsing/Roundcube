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
$apiToken = getenv('NOTIFICATION_API_TOKEN') ?: 'dev-secret-token-change-in-production';
$appUrl = getenv('APP_URL') ?: 'http://localhost';

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

function send_notification($email, $subject, $from, $accountId) {
    global $apiToken, $appUrl;
    $payload = json_encode([
        'email' => $email,
        'subject' => mb_substr($subject, 0, 500),
        'from' => mb_substr($from, 0, 500),
        'account_id' => $accountId,
        'token' => $apiToken,
    ]);
    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\nContent-Length: " . strlen($payload),
            'content' => $payload,
            'timeout' => 10,
        ]
    ];
    $context = stream_context_create($opts);
    $result = @file_get_contents($appUrl . '/new-mail-notification', false, $context);
    log_msg("Notification API result: " . ($result ?: 'failed'));
}

function get_accounts($settingsDir) {
    $accounts = [];
    $files = glob($settingsDir . '/sm_imap_*.json');
    foreach ($files as $f) {
        $data = json_decode(file_get_contents($f), true);
        if ($data && !empty($data['imap_host'])) {
            $email = $data['email'] ?? $data['username'] ?? '';
            $accounts[] = [
                'email' => $email,
                'host' => $data['imap_host'],
                'port' => (int)($data['imap_port'] ?? 993),
                'username' => $data['username'] ?? $email,
                'password' => $data['password'] ?? '',
                'encryption' => $data['imap_encryption'] ?? 'ssl',
                'uidnext' => 0,
                'last_exists' => 0,
            ];
        }
    }
    return $accounts;
}

$reconnect_wait = 5;

while (true) {
    $accounts = get_accounts($settingsDir);
    if (empty($accounts)) {
        log_msg("No accounts found in $settingsDir, waiting...");
        sleep(30);
        continue;
    }

    foreach ($accounts as $acct) {
        try {
            $host = $acct['host'];
            $port = $acct['port'];
            $username = $acct['username'];
            $password = $acct['password'];
            $encryption = $acct['encryption'];
            $email = $acct['email'];

            log_msg("Connecting $email to $host:$port...");
            $scheme = ($encryption === 'ssl') ? 'ssl://' : '';
            $sock = @fsockopen($scheme . $host, $port, $errno, $errstr, 30);
            if (!$sock) {
                log_msg("Connection failed for $email: $errstr ($errno)");
                continue;
            }

            $greeting = read_imap_line($sock);
            log_msg("$email greeting: $greeting");

            $tag = 'A001';
            $pass_quoted = str_replace(['\\', '"'], ['\\\\', '\\"'], $password);
            fwrite($sock, "$tag LOGIN \"$username\" \"$pass_quoted\"\r\n");
            $resp = read_imap_until_tag($sock, $tag);
            if (strpos($resp, $tag . ' OK') === false) {
                log_msg("LOGIN failed for $email: " . substr($resp, 0, 200));
                @fclose($sock);
                continue;
            }
            log_msg("$email authenticated");

            $tag = 'A002';
            fwrite($sock, "$tag SELECT INBOX\r\n");
            $resp = read_imap_until_tag($sock, $tag);
            $uidNext = extract_uidnext($resp);
            log_msg("$email INBOX selected, UIDNEXT=$uidNext");

            preg_match('/^\*\s+(\d+)\s+EXISTS/im', $resp, $existsMatch);
            $lastExists = $existsMatch ? (int)$existsMatch[1] : 0;
            log_msg("$email initial EXISTS=$lastExists");

            $idleTag = 'A003';
            fwrite($sock, "$idleTag IDLE\r\n");
            $cont = read_imap_line($sock);
            if ($cont === false || strpos($cont, '+') !== 0) {
                log_msg("IDLE not accepted for $email: $cont");
                @fclose($sock);
                continue;
            }
            log_msg("$email IDLE started");

            stream_set_timeout($sock, 300);
            $idleStart = time();

            while (true) {
                $line = read_imap_line($sock, 300);
                if ($line === false) {
                    throw new Exception("Connection timed out during IDLE for $email");
                }

                if (preg_match('/^\*\s+(\d+)\s+EXISTS/i', $line, $m)) {
                    $exists = (int)$m[1];
                    if ($exists > $lastExists) {
                        $lastExists = $exists;
                        log_msg("New mail detected for $email! EXISTS=$exists");

                        fwrite($sock, "DONE\r\n");
                        $doneResp = read_imap_until_tag($sock, $idleTag);
                        log_msg("IDLE ended for $email");

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
                            'exists' => $exists,
                            'email' => $email,
                        ];
                        file_put_contents($statusFile, json_encode($status));
                        log_msg("Status file updated for $email");

                        send_notification($email, $subject, $from, 0);

                        $idleTag = 'A005';
                        fwrite($sock, "$idleTag IDLE\r\n");
                        $cont = read_imap_line($sock);
                        if ($cont === false || strpos($cont, '+') !== 0) {
                            throw new Exception("IDLE re-enter failed for $email: $cont");
                        }
                        $idleStart = time();
                        log_msg("$email IDLE restarted");
                    }
                }

                if (preg_match('/^\*\s+(\d+)\s+RECENT/i', $line, $m)) {
                    log_msg("$email RECENT: {$m[1]} new messages");
                }

                if (time() - $idleStart >= 1740) {
                    log_msg("Renewing IDLE for $email (28 min)");
                    fwrite($sock, "DONE\r\n");
                    read_imap_until_tag($sock, $idleTag);
                    $idleTag = 'A006';
                    fwrite($sock, "$idleTag IDLE\r\n");
                    $cont = read_imap_line($sock);
                    if ($cont === false || strpos($cont, '+') !== 0) {
                        throw new Exception("IDLE renew failed for $email: $cont");
                    }
                    $idleStart = time();
                    log_msg("$email IDLE renewed");
                }
            }

        } catch (Exception $e) {
            log_msg("ERROR for {$acct['email']}: " . $e->getMessage());
            if (isset($sock) && $sock) @fclose($sock);
        }
    }

    log_msg("All accounts processed, reconnecting in {$reconnect_wait}s...");
    sleep($reconnect_wait);
    $reconnect_wait = min($reconnect_wait + 5, 60);
}

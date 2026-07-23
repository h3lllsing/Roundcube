<?php
/**
 * Parallel IMAP IDLE Worker — monitors all accounts simultaneously via stream_select
 * Run: php C:\roundcube\scripts\imap-idle-worker.php
 * Single process, non-blocking, handles unlimited accounts with low memory (~15MB)
 */

declare(strict_types=1);

$projectRoot = dirname(__DIR__);

// Load .env
$envFile = $projectRoot . '/.env';
if (is_file($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $val] = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val);
            if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                $val = substr($val, 1, -1);
            }
            $_ENV[$key] = $val;
            putenv("$key=$val");
        }
    }
}

$settingsDir  = $projectRoot . '/storage/app/webmail';
$legacyStatusFile = $projectRoot . '/storage/app/webmail/cache/imap-idle-status.json'; // for JS polling
$perAccountDir = $projectRoot . '/storage/app/webmail/cache';                          // per-account status
$logDir       = $projectRoot . '/storage/app/webmail/logs';
$logFile      = $logDir . '/imap-idle-worker.log';
$apiToken     = getenv('NOTIFICATION_API_TOKEN') ?: 'dev-secret-token-change-in-production';
$appUrl       = getenv('APP_URL') ?: 'http://localhost';

foreach ([$logDir, $perAccountDir] as $d) {
    if (!is_dir($d)) mkdir($d, 0777, true);
}

set_time_limit(0);

// ─── Helpers ────────────────────────────────────────────────────

function log_msg(string $msg): void {
    global $logFile;
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
    echo $line;
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

function send_notification(string $email, string $subject, string $from): void {
    global $apiToken, $appUrl;
    $payload = json_encode([
        'email'      => $email,
        'subject'    => mb_substr($subject, 0, 500),
        'from'       => mb_substr($from, 0, 500),
        'account_id' => 0,
        'token'      => $apiToken,
    ]);
    $opts = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/json\r\nContent-Length: " . strlen($payload),
            'content' => $payload,
            'timeout' => 10,
        ]
    ];
    $result = @file_get_contents($appUrl . '/new-mail-notification', false, stream_context_create($opts));
    log_msg("Notification: $email — " . ($result ?: 'HTTP_FAILED'));
}

function sanitize_account_email(array &$data): string {
    return $data['email'] ?? $data['username'] ?? 'unknown@unknown';
}

// ─── Account Scanner ────────────────────────────────────────────

function scan_accounts(string $settingsDir): array {
    $accounts = [];
    foreach (glob($settingsDir . '/sm_imap_*.json') ?: [] as $f) {
        $data = json_decode(file_get_contents($f), true);
        if (!$data || empty($data['imap_host'])) continue;
        $email = sanitize_account_email($data);
        $accounts[$email] = [
            'email'      => $email,
            'host'       => $data['imap_host'],
            'port'       => (int)($data['imap_port'] ?? 993),
            'username'   => $data['username'] ?? $email,
            'password'   => $data['password'] ?? '',
            'encryption' => $data['imap_encryption'] ?? 'ssl',
        ];
    }
    return $accounts;
}

// ─── Connection Lifecycle ───────────────────────────────────────

function imap_connect(array $acct): ?array {
    $host = $acct['host'];
    $port = $acct['port'];
    $scheme = ($acct['encryption'] === 'ssl') ? 'ssl://' : '';

    log_msg("Connecting {$acct['email']} ($host:$port)");

    $sock = @fsockopen($scheme . $host, $port, $errno, $errstr, 15);
    if (!$sock) {
        log_msg("connect FAIL {$acct['email']}: $errstr ($errno)");
        return null;
    }

    // Blocking mode during initial handshake
    stream_set_blocking($sock, true);
    stream_set_timeout($sock, 10);
    $line = imap_read_line($sock);
    if ($line === false || $line === '') {
        log_msg("no greeting {$acct['email']}");
        @fclose($sock);
        return null;
    }

    $pass = str_replace(['\\', '"'], ['\\\\', '\\"'], $acct['password']);
    if (!imap_cmd($sock, 'A001', "LOGIN \"{$acct['username']}\" \"$pass\"")) {
        log_msg("LOGIN FAIL {$acct['email']}");
        @fclose($sock);
        return null;
    }
    log_msg("login OK {$acct['email']}");

    $resp = imap_cmd_raw($sock, 'A002', 'SELECT INBOX');
    if ($resp === false) {
        log_msg("SELECT FAIL {$acct['email']}");
        @fclose($sock);
        return null;
    }

    preg_match('/UIDNEXT\s+(\d+)/i', $resp, $m);
    $uidNext = isset($m[1]) ? (int)$m[1] : 0;
    preg_match('/^\*\s+(\d+)\s+EXISTS/im', $resp, $m);
    $lastExists = isset($m[1]) ? (int)$m[1] : 0;

    log_msg("INBOX {$acct['email']}: UIDNEXT=$uidNext EXISTS=$lastExists");

    if (!imap_idle_enter($sock, 'A003')) {
        log_msg("IDLE enter FAIL {$acct['email']}");
        @fclose($sock);
        return null;
    }

    // Switch to non-blocking mode for polling loop
    stream_set_blocking($sock, false);

    log_msg("IDLE started {$acct['email']}");

    return [
        'email'       => $acct['email'],
        'sock'        => $sock,
        'lastExists'  => $lastExists,
        'uidNext'     => $uidNext,
        'idleTag'     => 'A003',
        'idleStart'   => time(),
        'buf'         => '',
        'reconnects'  => 0,
    ];
}

function imap_read_line($sock): string|false {
    $line = '';
    while (true) {
        $char = @fgetc($sock);
        if ($char === false) return false;
        if ($char === '') return false;
        if ($char === "\n") return rtrim($line, "\r");
        $line .= $char;
    }
}

function imap_cmd($sock, string $tag, string $cmd): bool {
    fwrite($sock, "$tag $cmd\r\n");
    while (true) {
        $line = imap_read_line($sock);
        if ($line === false) return false;
        if (str_starts_with($line, "$tag OK")) return true;
        if (str_starts_with($line, "$tag NO") || str_starts_with($line, "$tag BAD")) return false;
    }
}

function imap_cmd_raw($sock, string $tag, string $cmd): string|false {
    fwrite($sock, "$tag $cmd\r\n");
    $resp = '';
    while (true) {
        $line = imap_read_line($sock);
        if ($line === false) return false;
        $resp .= $line . "\n";
        if (str_starts_with($line, "$tag OK")) return $resp;
        if (str_starts_with($line, "$tag NO") || str_starts_with($line, "$tag BAD")) return false;
    }
}

function imap_idle_enter($sock, string $tag): bool {
    fwrite($sock, "$tag IDLE\r\n");
    $cont = imap_read_line($sock);
    return ($cont !== false && str_starts_with($cont, '+'));
}

// ─── Non-blocking line reader for Windows reliability ──────────

function imap_try_read_line($sock): string|false|null {
    $line = '';
    while (true) {
        $char = @fgetc($sock);
        if ($char === false) return false;       // error / closed
        if ($char === '') return null;            // no data (non-blocking)
        if ($char === "\n") return rtrim($line, "\r");
        $line .= $char;
    }
}

// ─── Main Event Loop ───────────────────────────────────────────

$connections  = [];
$lastScan     = 0;
$healthTick   = 0;
$scanInterval = 15;
$idleTimeout  = 1740;
$tagCounter   = 0;

log_msg("=== WORKER STARTED ===");

function shutdown_cleanup(array &$connections): void {
    log_msg("Shutting down, closing " . count($connections) . " connection(s)...");
    foreach ($connections as $c) {
        @fwrite($c['sock'], "DONE\r\n");
        @fclose($c['sock']);
    }
    log_msg("Worker stopped");
    exit(0);
}

// Windows: Ctrl+C via SetConsoleCtrlHandler workaround
// PHP doesn't natively catch Ctrl+C on Windows, but process kill will close sockets anyway

while (true) {
    $now = time();

    // ── Account re-scan ─────────────────────────────────────
    if ($now - $lastScan >= $scanInterval) {
        $lastScan = $now;
        $desired = scan_accounts($settingsDir);

        // Remove accounts that no longer exist
        foreach (array_keys($connections) as $email) {
            if (!isset($desired[$email])) {
                log_msg("- removing {$email}");
                @fwrite($connections[$email]['sock'], "DONE\r\n");
                @fclose($connections[$email]['sock']);
                unset($connections[$email]);
            }
        }

        // Add / reconnect accounts
        foreach ($desired as $email => $acct) {
            if (isset($connections[$email]) && $connections[$email]['sock'] !== null) continue;
            if ($acct['password'] === '') continue;
            $conn = imap_connect($acct);
            if ($conn) {
                $connections[$email] = $conn;
                log_msg("+ connected {$email}");
            }
        }

        if (empty($connections)) {
            log_msg("No accounts to monitor, sleeping 15s");
            sleep(15);
            continue;
        }
    }

    // ── Health report (every 5 min) ────────────────────────
    if ($now - $healthTick >= 300) {
        $healthTick = $now;
        $emails = array_keys($connections);
        log_msg("STATUS: " . count($emails) . " accounts — " . implode(', ', $emails));
    }

    // ── Poll all connections (non-blocking, works on Windows) ──
    $reconnect_emails = [];

    foreach ($connections as $email => &$c) {
        $maxReads = 50; // prevent infinite loop if server floods
        while ($maxReads-- > 0) {
            $line = imap_try_read_line($c['sock']);
            if ($line === false) {
                // Connection closed/error
                log_msg("disconnected {$email}, will reconnect");
                @fclose($c['sock']);
                $c['sock'] = null;
                $reconnect_emails[] = $email;
                break;
            }
            if ($line === null) {
                // No data available, move to next connection
                break;
            }

            // IDLE untagged EXISTS — new mail
            if (preg_match('/^\*\s+(\d+)\s+EXISTS/i', $line, $m)) {
                $exists = (int)$m[1];
                if ($exists > $c['lastExists']) {
                    $c['lastExists'] = $exists;
                    log_msg("NEW MAIL {$email}: EXISTS=$exists");

                    // Switch to blocking mode for DONE + FETCH
                    stream_set_blocking($c['sock'], true);
                    stream_set_timeout($c['sock'], 15);

                    // Exit IDLE
                    fwrite($c['sock'], "DONE\r\n");
                    $done = false;
                    while (true) {
                        $l = imap_read_line($c['sock']);
                        if ($l === false) break;
                        if (str_starts_with($l, $c['idleTag'] . ' OK')) { $done = true; break; }
                        if (str_starts_with($l, $c['idleTag'] . ' NO') || str_starts_with($l, $c['idleTag'] . ' BAD')) break;
                    }

                    if ($done) {
                        $tagCounter++;
                        $fetchTag = 'F' . $tagCounter;
                        fwrite($c['sock'], "$fetchTag FETCH $exists (UID FLAGS BODY.PEEK[HEADER.FIELDS (FROM SUBJECT DATE)])\r\n");
                        $fetchResp = '';
                        $timeout = 50;
                        while (true) {
                            $l = imap_read_line($c['sock']);
                            if ($l === false) break;
                            $fetchResp .= $l . "\n";
                            if (str_starts_with($l, "$fetchTag OK") || str_starts_with($l, "$fetchTag NO") || str_starts_with($l, "$fetchTag BAD")) break;
                            if (--$timeout <= 0) break;
                        }

                        $subject = '';
                        $from    = '';
                        $msgUid  = 0;
                        if (preg_match('/UID\s+(\d+)/i', $fetchResp, $m)) $msgUid = (int)$m[1];
                        if (preg_match('/^Subject:\s*(.+)$/im', $fetchResp, $m)) $subject = trim($m[1]);
                        if (preg_match('/^From:\s*(.+)$/im', $fetchResp, $m)) $from = trim($m[1]);

                        // Legacy status file (for JS polling via imap-idle-status.php)
                        $legacyStatus = [
                            'has_new'   => true,
                            'timestamp' => time(),
                            'uid'       => $msgUid,
                            'subject'   => mb_substr($subject, 0, 200),
                            'from'      => mb_substr($from, 0, 200),
                            'exists'    => $exists,
                            'email'     => $email,
                        ];
                        file_put_contents($legacyStatusFile, json_encode($legacyStatus));

                        // Per-account status file
                        $safeFile = preg_replace('/[^a-z0-9]/i', '_', $email);
                        file_put_contents("$perAccountDir/imap-new-$safeFile.json", json_encode($legacyStatus));
                        log_msg("NEW {$email}: {$from} — {$subject}");

                        send_notification($email, $subject, $from);
                    }

                    // Re-enter IDLE (blocking mode needed for read)
                    $tagCounter++;
                    $c['idleTag'] = 'I' . $tagCounter;
                    $c['idleStart'] = time();
                    stream_set_timeout($c['sock'], 10);
                    if (!imap_idle_enter($c['sock'], $c['idleTag'])) {
                        log_msg("IDLE re-enter FAIL {$email}");
                        @fclose($c['sock']);
                        $c['sock'] = null;
                        $reconnect_emails[] = $email;
                        break;
                    }
                    // Back to non-blocking
                    stream_set_blocking($c['sock'], false);
                    log_msg("IDLE restarted {$email}");
                }
            }

            // RECENT
            if (preg_match('/^\*\s+(\d+)\s+RECENT/i', $line, $m)) {
                log_msg("RECENT {$email}: {$m[1]} new");
            }
        }
    }
    unset($c);

    // ── Reconnect dropped connections ──────────────────────
    foreach ($reconnect_emails as $email) {
        if (isset($desired[$email])) {
            $conn = imap_connect($desired[$email]);
            if ($conn) $connections[$email] = $conn;
            else unset($connections[$email]);
        } else {
            unset($connections[$email]);
        }
    }

    // ── IDLE renew (every 28 min) ──────────────────────────
    foreach ($connections as $email => &$c) {
        if ($c['sock'] === null) continue;
        if ($now - $c['idleStart'] >= $idleTimeout) {
            log_msg("Renewing IDLE {$email}");
            stream_set_blocking($c['sock'], true);
            stream_set_timeout($c['sock'], 15);
            fwrite($c['sock'], "DONE\r\n");
            $done = false;
            $r_timeout = 30;
            while (true) {
                $l = imap_read_line($c['sock']);
                if ($l === false) break;
                if (str_starts_with($l, $c['idleTag'] . ' OK')) { $done = true; break; }
                if (str_starts_with($l, $c['idleTag'] . ' NO') || str_starts_with($l, $c['idleTag'] . ' BAD')) break;
                if (--$r_timeout <= 0) break;
            }
            if (!$done) {
                log_msg("IDLE renew FAIL {$email}, reconnecting");
                @fclose($c['sock']);
                $c['sock'] = null;
                continue;
            }
            $tagCounter++;
            $c['idleTag'] = 'I' . $tagCounter;
            $c['idleStart'] = time();
            stream_set_timeout($c['sock'], 10);
            if (!imap_idle_enter($c['sock'], $c['idleTag'])) {
                log_msg("IDLE re-enter FAIL after renew {$email}");
                @fclose($c['sock']);
                $c['sock'] = null;
                continue;
            }
            stream_set_blocking($c['sock'], false);
            log_msg("IDLE renewed {$email}");
        }
    }
    unset($c);

    // ── Small sleep to prevent busy-wait ───────────────────
    usleep(200000); // 200ms
}

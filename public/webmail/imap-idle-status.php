<?php
/**
 * 3-Layer New Mail Monitor
 * Layer 1: IMAP IDLE Worker (real-time, persistent)
 * Layer 2: Direct IMAP STATUS (fallback if worker dead)
 * Layer 3: JS polling trigger (15s interval in app.js)
 */
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin && !str_contains($origin, $_SERVER['HTTP_HOST'] ?? '')) {
    http_response_code(403);
    echo json_encode(['error' => 'Forbidden']);
    exit;
}

$cacheDir = dirname(__DIR__, 2) . '/storage/app/webmail/cache';
$heartbeatFile = $cacheDir . '/imap-worker-heartbeat.json';
$statusFile = $cacheDir . '/imap-idle-status.json';

// ── Heartbeat check: is worker alive? ─────────────────────────
$workerAlive = false;
if (is_file($heartbeatFile)) {
    $hb = json_decode(file_get_contents($heartbeatFile), true);
    if ($hb && !empty($hb['alive']) && time() - $hb['timestamp'] < 60) {
        $workerAlive = true;
    }
}

// ── Layer 1: Worker is alive, read its status file ────────────
if ($workerAlive && is_file($statusFile) && time() - filemtime($statusFile) < 180) {
    $content = file_get_contents($statusFile);
    $data = json_decode($content, true);
    if ($data && !empty($data['has_new'])) {
        $response = [
            'has_new' => true,
            'subject' => $data['subject'] ?? '',
            'from' => $data['from'] ?? '',
            'uid' => $data['uid'] ?? 0,
        ];
        $data['has_new'] = false;
        file_put_contents($statusFile, json_encode($data), LOCK_EX);
        echo json_encode($response);
        exit;
    }
    echo json_encode(['has_new' => false]);
    exit;
}

// ── Layer 2: Direct IMAP STATUS (worker dead or no new mail flag) ──
$settingsDir = dirname(__DIR__, 2) . '/storage/app/webmail';
$anyNew = false;
$totalUnseen = 0;
$cachePrefix = $cacheDir . '/imap-last-uidnext-';

foreach (glob($settingsDir . '/sm_imap_*.json') ?: [] as $settingsFile) {
    $settings = json_decode(file_get_contents($settingsFile), true);
    if (!$settings || empty($settings['imap_host'])) continue;

    $email = $settings['email'] ?? md5_file($settingsFile);
    $host = $settings['imap_host'];
    $port = (int)$settings['imap_port'];
    $password = $settings['password'];
    $encryption = $settings['imap_encryption'] ?? 'ssl';
    $scheme = ($encryption === 'ssl') ? 'ssl://' : '';
    $hash = md5($email);

    $cacheFile = $cachePrefix . $hash . '.txt';
    $prevUidNext = (int)@file_get_contents($cacheFile);

    $sock = @fsockopen($scheme . $host, $port, $errno, $errstr, 5);
    if (!$sock) continue;

    fread($sock, 1024);
    $pass_quoted = str_replace(['\\', '"'], ['\\\\', '\\"'], $password);
    fwrite($sock, "a001 LOGIN \"$email\" \"$pass_quoted\"\r\n");
    $resp = '';
    while ($l = fgets($sock)) { $resp .= $l; if (str_contains($l, 'a001 OK')) break; }

    if (str_contains($resp, 'a001 OK')) {
        fwrite($sock, "a002 STATUS INBOX (UIDNEXT UNSEEN MESSAGES)\r\n");
        $resp = '';
        while ($l = fgets($sock)) { $resp .= $l; if (str_contains($l, 'a002 OK')) break; }
        fwrite($sock, "a003 LOGOUT\r\n");

        preg_match('/UIDNEXT\s+(\d+)/i', $resp, $m);
        $currentUidNext = (int)($m[1] ?? 0);
        preg_match('/UNSEEN\s+(\d+)/i', $resp, $m);
        $unseen = (int)($m[1] ?? 0);

        if ($prevUidNext > 0 && $currentUidNext > $prevUidNext) {
            $anyNew = true;
        }
        $totalUnseen += $unseen;

        if ($currentUidNext > 0 && $currentUidNext !== $prevUidNext) {
            file_put_contents($cacheFile, (string)$currentUidNext, LOCK_EX);
        }
    }
    fclose($sock);
}

echo json_encode(['has_new' => $anyNew, 'unseen' => $totalUnseen]);
exit;

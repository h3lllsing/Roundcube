<?php
/**
 * Dual-mode new mail monitor
 * Mode 1: IMAP IDLE Worker (VPS/Windows) — reads worker's status file
 * Mode 2: Direct IMAP STATUS (cPanel) — no worker needed
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

// === Mode 1: IDLE Worker Status File ===
$statusFile = dirname(__DIR__, 2) . '/storage/app/webmail/cache/imap-idle-status.json';
if (is_file($statusFile) && time() - filemtime($statusFile) < 180) {
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
        file_put_contents($statusFile, json_encode($data));
        echo json_encode($response);
        exit;
    }
    echo json_encode(['has_new' => false]);
    exit;
}

// === Mode 2: Direct IMAP STATUS (cPanel, no worker) ===
$settingsDir = dirname(__DIR__, 2) . '/storage/app/webmail';
$anyNew = false;
$totalUnseen = 0;
$cachePrefix = __DIR__ . '/data/_data_/_default_/cache/imap-last-uidnext-';

foreach (glob($settingsDir . '/sm_imap_*.json') as $settingsFile) {
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
    while ($l = fgets($sock)) { $resp .= $l; if (strpos($l, 'a001 OK') !== false) break; }

    if (strpos($resp, 'a001 OK') !== false) {
        fwrite($sock, "a002 STATUS INBOX (UIDNEXT UNSEEN MESSAGES)\r\n");
        $resp = '';
        while ($l = fgets($sock)) { $resp .= $l; if (strpos($l, 'a002 OK') !== false) break; }
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
            file_put_contents($cacheFile, (string)$currentUidNext);
        }
    }
    fclose($sock);
}

echo json_encode(['has_new' => $anyNew, 'unseen' => $totalUnseen]);
exit;

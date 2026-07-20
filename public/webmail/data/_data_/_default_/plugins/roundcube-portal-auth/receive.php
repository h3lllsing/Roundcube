<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$token = $_POST['t'] ?? null;
if (!$token) {
    http_response_code(403);
    echo 'Missing token';
    exit;
}

$scriptName = $_SERVER['SCRIPT_NAME'] ?? '/';
$basePath = rtrim(dirname($scriptName), '/');
$basePath = dirname(dirname($basePath));
$basePath = dirname($basePath);
$resolveUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    . $basePath . '/webmail-auth/resolve?t=' . urlencode($token);

$ch = curl_init($resolveUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER => ['Accept: application/json'],
]);
$res = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http !== 200) {
    http_response_code(403);
    echo 'Unauthorized';
    exit;
}

$data = json_decode($res, true);
if (!$data || empty($data['email'])) {
    http_response_code(403);
    echo 'Invalid response';
    exit;
}

$_ENV['SNAPPYMAIL_INCLUDE_AS_API'] = '1';
require_once __DIR__ . '/../../index.php';

$emailHash = md5($data['email']);
$settingsFile = sys_get_temp_dir() . '/sm_imap_' . $emailHash . '.json';
file_put_contents($settingsFile, json_encode([
    'imap_host' => $data['imap_host'] ?? '',
    'imap_port' => (int)($data['imap_port'] ?? 993),
    'imap_encryption' => $data['imap_encryption'] ?? 'ssl',
    'smtp_host' => $data['smtp_host'] ?? '',
    'smtp_port' => (int)($data['smtp_port'] ?? 587),
    'smtp_encryption' => $data['smtp_encryption'] ?? 'tls',
    'smtp_username' => $data['smtp_username'] ?? $data['email'],
    'smtp_password' => $data['smtp_password'] ?? $data['password'],
    'created_at' => time(),
]), LOCK_EX);

try {
    $ssoHash = \RainLoop\Api::CreateUserSsoHash($data['email'], $data['password']);
    if ($ssoHash) {
        $webmailRoot = dirname(dirname(dirname($scriptName)));
        header('Location: ' . $webmailRoot . '/?sso&hash=' . urlencode($ssoHash));
        exit;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'SSO generation failed: ' . $e->getMessage();
    exit;
}

http_response_code(500);
echo 'SSO generation failed';

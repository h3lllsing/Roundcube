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

$resolveUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost')
    . '/webmail/resolve?t=' . urlencode($token);

$ch = curl_init($resolveUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => true,
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

$_POST['email'] = $data['email'];
$_POST['password'] = $data['password'];

require_once __DIR__ . '/../../index.php';

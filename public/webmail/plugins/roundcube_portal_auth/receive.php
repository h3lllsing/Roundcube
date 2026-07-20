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

$projectRoot = dirname(__DIR__, 4);
require_once $projectRoot . '/vendor/autoload.php';

$app = require_once $projectRoot . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Enums\AccountStatus;
use App\Enums\DomainStatus;
use Illuminate\Support\Facades\DB;
use App\Models\EmailAccount;

$row = DB::table('webmail_tokens')
    ->where('token', $token)
    ->where('used', false)
    ->where('expires_at', '>', now())
    ->first();

if (!$row) {
    http_response_code(403);
    echo 'Invalid or expired token';
    exit;
}

DB::table('webmail_tokens')
    ->where('token', $token)
    ->where('used', false)
    ->update(['used' => true]);

$account = EmailAccount::with('domain')->findOrFail($row->email_account_id);

if ($account->status !== AccountStatus::Active
    || $account->domain->status !== DomainStatus::Active
    || !$account->sync_enabled) {
    http_response_code(403);
    echo 'Account not available';
    exit;
}

$storageDir = $projectRoot . '/storage/app/webmail';
if (!is_dir($storageDir)) {
    mkdir($storageDir, 0700, true);
}

$emailHash = md5($account->email);
$settingsFile = $storageDir . '/sm_imap_' . $emailHash . '.json';

file_put_contents($settingsFile, json_encode([
    'email' => $account->email,
    'password' => $account->password,
    'imap_host' => $account->imap_host,
    'imap_port' => (int)$account->imap_port,
    'imap_encryption' => $account->imap_encryption ?? 'ssl',
    'smtp_host' => $account->smtp_host ?? '',
    'smtp_port' => (int)($account->smtp_port ?? 587),
    'smtp_encryption' => $account->smtp_encryption ?? 'tls',
    'smtp_username' => $account->smtp_username ?? $account->email,
    'smtp_password' => $account->smtp_password ?? $account->password,
    'created_at' => time(),
]), LOCK_EX);

@chmod($settingsFile, 0600);

$_ENV['SNAPPYMAIL_INCLUDE_AS_API'] = '1';
require_once __DIR__ . '/../../index.php';

try {
    $ssoHash = \RainLoop\Api::CreateUserSsoHash($account->email, $account->password);
    if ($ssoHash) {
        $webmailRoot = dirname(dirname(dirname($_SERVER['SCRIPT_NAME'] ?? '/')));
        header('Location: ' . $webmailRoot . '/?sso&hash=' . urlencode($ssoHash));
        exit;
    }
} catch (\Throwable $e) {
    http_response_code(500);
    echo 'SSO generation failed';
    exit;
}

http_response_code(500);
echo 'SSO generation failed';

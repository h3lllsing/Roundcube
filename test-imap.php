<?php
$email = $argv[1] ?? '';
$pass = $argv[2] ?? '';
if (!$email || !$pass) { die("Usage: php test-imap.php email password\n"); }

require __DIR__ . '/app/Services/SmtpAutoDiscover.php';
$disc = new App\Services\SmtpAutoDiscover;
$settings = $disc->discoverAll($email);
echo "Discovered: " . json_encode($settings, JSON_PRETTY_PRINT) . "\n";

if (empty($settings['imap_host'])) { die("Auto-discover failed\n"); }

$host = $settings['imap_host'];
$port = $settings['imap_port'];
$enc = $settings['imap_encryption'] === 'ssl' ? '/ssl' : '/tls';
$mailbox = '{' . $host . ':' . $port . '/imap' . $enc . '}INBOX';
echo "Connecting: $mailbox\n";

$conn = @imap_open($mailbox, $email, $pass, OP_HALFOPEN, 1);
if ($conn) {
    echo "SUCCESS: Connected!\n";
    imap_close($conn);
} else {
    echo "FAILED: " . imap_last_error() . "\n";
}

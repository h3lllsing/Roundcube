<?php

namespace App\Console\Commands;

use App\Models\EmailAccount;
use Illuminate\Console\Command;
use Webklex\PHPIMAP\ClientManager;

class TestEmailConnection extends Command
{
    protected $signature = 'email:test-connection
                          {account : Email account ID or email address}
                          {--type=imap : Connection type: imap or smtp}';

    protected $description = 'Test IMAP or SMTP connection for an email account';

    public function handle(): int
    {
        $identifier = $this->argument('account');
        $type = $this->option('type');

        $account = is_numeric($identifier)
            ? EmailAccount::find($identifier)
            : EmailAccount::where('email', $identifier)->first();

        if (! $account) {
            $this->error("Account not found: {$identifier}");
            return self::FAILURE;
        }

        $this->line("Account: {$account->email}");

        if ($type === 'imap') {
            return $this->testImap($account);
        }

        return $this->testSmtp($account);
    }

    private function testImap(EmailAccount $account): int
    {
        $this->line("Testing IMAP: {$account->imap_host}:{$account->imap_port} ({$account->imap_encryption})");

        $cm = new ClientManager();
        $client = $cm->make([
            'host' => $account->imap_host,
            'port' => (int) $account->imap_port,
            'encryption' => $account->imap_encryption ?: null,
            'validate_cert' => true,
            'username' => $account->email,
            'password' => $account->password,
            'protocol' => 'imap',
            'timeout' => 10,
        ]);

        try {
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $this->info("Connected to INBOX successfully");
            $this->line("Folder: {$folder->name}");
            $client->disconnect();
            $this->info("IMAP test PASSED");
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("IMAP test FAILED: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    private function testSmtp(EmailAccount $account): int
    {
        $host = $account->smtp_host ?: $account->imap_host;
        $port = $account->smtp_port ?: 587;
        $enc = $account->smtp_encryption ?: 'tls';

        $this->line("Testing SMTP: {$host}:{$port} ({$enc})");

        $timeout = 10;

        if ($enc === 'ssl') {
            $fp = @fsockopen('ssl://' . $host, (int) $port, $errno, $errstr, $timeout);
        } else {
            $fp = @fsockopen($host, (int) $port, $errno, $errstr, $timeout);
        }

        if (! $fp) {
            $this->error("SMTP connection FAILED: {$errstr} ({$errno})");
            return self::FAILURE;
        }

        $resp = '';
        $resp .= fgets($fp, 512);
        fwrite($fp, "EHLO test\r\n");

        while ($line = fgets($fp, 512)) {
            $resp .= $line;
            if (substr($line, 3, 1) === ' ') break;
        }

        if ($enc === 'tls' && str_contains($resp, 'STARTTLS')) {
            fwrite($fp, "STARTTLS\r\n");
            $resp .= fgets($fp, 512);

            if (str_contains($resp, '220')) {
                if (! stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    $this->error("TLS handshake failed");
                    fclose($fp);
                    return self::FAILURE;
                }
                fwrite($fp, "EHLO test\r\n");
                while ($line = fgets($fp, 512)) {
                    $resp .= $line;
                    if (substr($line, 3, 1) === ' ') break;
                }
            }
        }

        fclose($fp);

        if (str_contains($resp, '220') || str_contains($resp, '250')) {
            $this->info("SMTP connection successful");
            $this->line("Server: " . trim(explode("\n", $resp)[0]));
            $this->info("SMTP test PASSED");
            return self::SUCCESS;
        }

        $this->warn("SMTP connected but unexpected response: " . trim($resp));
        return self::FAILURE;
    }
}

<?php

namespace App\Services;

use App\Models\EmailAccount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Webklex\PHPIMAP\ClientManager;

class EmailStatService
{
    const CACHE_LOCK_KEY = 'email_stat_batch_fetch';
    const TIMEOUT = 10;

    public function fetchCounts(EmailAccount $account): array
    {
        $cm = new ClientManager();
        $client = $cm->make([
            'host' => $account->imap_host,
            'port' => $account->imap_port,
            'encryption' => $account->imap_encryption ?: null,
            'validate_cert' => true,
            'username' => $account->email,
            'password' => $account->password,
            'protocol' => 'imap',
            'timeout' => static::TIMEOUT,
        ]);

        try {
            $client->connect();

            $inbox = $client->getFolder('INBOX');
            $total = $inbox->messages()->count();
            $unseen = $inbox->messages()->unseen()->count();

            $client->disconnect();

            return [
                'success' => true,
                'total_emails' => $total,
                'unseen_emails' => $unseen,
                'error' => null,
            ];
        } catch (\Exception $e) {
            Log::warning("EmailStat: failed to fetch counts for {$account->email}: {$e->getMessage()}");

            return [
                'success' => false,
                'total_emails' => null,
                'unseen_emails' => null,
                'error' => $e->getMessage(),
            ];
        }
    }

    public function batchFetch(): array
    {
        $lock = Cache::lock(static::CACHE_LOCK_KEY, 30);

        if (!$lock->get()) {
            return ['success' => false, 'error' => 'Another batch fetch is already in progress.'];
        }

        try {
            $accounts = EmailAccount::where('status', 'active')
                ->where('sync_enabled', true)
                ->get();

            $results = [];

            foreach ($accounts as $account) {
                $results[$account->id] = $this->fetchCounts($account);
            }

            return [
                'success' => true,
                'accounts_processed' => $accounts->count(),
                'results' => $results,
                'error' => null,
            ];
        } finally {
            $lock->release();
        }
    }
}

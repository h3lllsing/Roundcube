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
        return $this->tryConnect($account, $account->imap_host, $account->imap_port, $account->imap_encryption);
    }

    public function fetchCountsWithFallback(EmailAccount $account): array
    {
        $result = $this->fetchCounts($account);

        if ($result['success']) {
            return $result;
        }

        $discovered = app(SmtpAutoDiscover::class)->discoverAll($account->email);

        $dnsHost = $discovered['imap_host'] ?? null;
        $dnsPort = $discovered['imap_port'] ?? null;
        $dnsEnc = $discovered['imap_encryption'] ?? null;

        if (! $dnsHost) {
            $this->logFailure($account, $result['error']);
            return $result;
        }

        if ($dnsHost === $account->imap_host && (int) $dnsPort === (int) $account->imap_port && $dnsEnc === $account->imap_encryption) {
            $this->logFailure($account, $result['error']);
            return $result;
        }

        $fallbackResult = $this->tryConnect($account, $dnsHost, $dnsPort, $dnsEnc);

        if ($fallbackResult['success']) {
            $account->forceFill([
                'imap_host' => $dnsHost,
                'imap_port' => (int) $dnsPort,
                'imap_encryption' => $dnsEnc,
            ])->saveQuietly();

            Log::info("EmailStat: DNS fallback applied for {$account->email} → {$dnsHost}:{$dnsPort}");
            activity()
                ->event('imap_dns_fallback')
                ->performedOn($account)
                ->log("IMAP settings auto-corrected via DNS fallback: {$dnsHost}:{$dnsPort} ({$dnsEnc})");

            $fallbackResult['fallback_applied'] = true;
            return $fallbackResult;
        }

        $this->logFailure($account, $fallbackResult['error']);
        $fallbackResult['fallback_tried'] = true;
        return $fallbackResult;
    }

    private function logFailure(EmailAccount $account, ?string $error): void
    {
        activity()
            ->event('imap_fetch_failed')
            ->performedOn($account)
            ->log("IMAP fetch failed for {$account->email}: " . ($error ?: 'Unknown error'));
    }

    private function safeCount(mixed $inbox, EmailAccount $account, string $type): int
    {
        try {
            $messages = $inbox->messages();
            return $type === 'unseen'
                ? $messages->unseen()->count()
                : $messages->count();
        } catch (\Exception $e) {
            Log::warning("EmailStat: {$type} count failed for {$account->email}: {$e->getMessage()}");
            return 0;
        }
    }

    private function tryConnect(EmailAccount $account, string $host, int|string $port, ?string $encryption): array
    {
        $cm = new ClientManager();
        $client = $cm->make([
            'host' => $host,
            'port' => (int) $port,
            'encryption' => $encryption ?: null,
            'validate_cert' => true,
            'username' => $account->email,
            'password' => $account->password,
            'protocol' => 'imap',
            'timeout' => static::TIMEOUT,
        ]);

        try {
            $client->connect();

            $inbox = $client->getFolder('INBOX');
            $total = $this->safeCount($inbox, $account, 'total');
            $unseen = $this->safeCount($inbox, $account, 'unseen');

            $client->disconnect();

            return [
                'success' => true,
                'total_emails' => $total,
                'unseen_emails' => $unseen,
                'error' => null,
                'fallback_applied' => false,
            ];
        } catch (\Exception $e) {
            Log::warning("EmailStat: IMAP failed for {$account->email} at {$host}:{$port}: {$e->getMessage()}");

            return [
                'success' => false,
                'total_emails' => null,
                'unseen_emails' => null,
                'error' => $e->getMessage(),
                'fallback_applied' => false,
            ];
        }
    }

    public function failedAccountsCountLast24h(): int
    {
        return \Spatie\Activitylog\Models\Activity::where('event', 'imap_fetch_failed')
            ->where('created_at', '>=', now()->subDay())
            ->distinct('subject_id')
            ->count('subject_id');
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
                ->assignedToActiveUsers()
                ->get();

            $results = [];

            foreach ($accounts as $account) {
                $results[$account->id] = $this->fetchCountsWithFallback($account);
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

<?php

namespace App\Jobs;

use App\Models\EmailAccount;
use App\Notifications\EmailSyncFailed;
use App\Services\EmailStatService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class EmailSyncJob implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $timeout = 30;
    public $tries = 2;
    public $backoff = [10, 30];

    public function __construct(
        public EmailAccount $emailAccount
    ) {}

    public function handle(EmailStatService $service): void
    {
        $this->emailAccount->refresh();

        $result = $service->fetchCountsWithFallback($this->emailAccount);

        if (! $result['success']) {
            throw new \RuntimeException($result['error'] ?? 'IMAP sync failed');
        }

        $this->emailAccount->forceFill(['last_sync_at' => now()])->save();
    }

    public function failed(\Throwable $exception): void
    {
        $account = $this->emailAccount;

        Log::error("EmailSyncJob permanently failed for {$account->email}: {$exception->getMessage()}");

        $notifiables = $account->assignedUsers()
            ->whereNull('suspended_at')
            ->get();

        if ($notifiables->isNotEmpty()) {
            Notification::send($notifiables, new EmailSyncFailed($account, $exception->getMessage()));
        }
    }
}

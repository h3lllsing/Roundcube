<?php

namespace App\Console\Commands;

use App\Models\ExpiryTracker;
use App\Models\ExpiryTrackerNotification;
use App\Services\RenewalNotificationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class SendEmailReminders extends Command
{
    protected $signature = 'renewals:send-email-reminders {--limit=}';

    protected $description = 'Send renewal email reminders for expiry trackers';

    public function handle(RenewalNotificationService $service): int
    {
        $this->info('Sending renewal email reminders...');

        $limit = $this->option('limit');
        $limit = $limit !== null ? (int) $limit : null;

        $eligible = ExpiryTracker::where('email_notifications_enabled', true)
            ->whereIn('status', ['active', 'pending_renewal'])
            ->count();

        if ($limit !== null) {
            $this->info("Limit: {$limit} email(s) per run.");
        }

        $startTime = now();

        $sent = $service->sendReminders($limit);

        $failed = ExpiryTrackerNotification::where('trigger_source', 'cron')
            ->where('status', 'failed')
            ->where('created_at', '>=', $startTime)
            ->count();

        $skipped = max(0, $eligible - $sent - $failed);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Processed', $eligible],
                ['Sent', $sent],
                ['Skipped', $skipped],
                ['Failed', $failed],
            ]
        );

        logger("renewals:send-email-reminders completed", [
            'eligible' => $eligible,
            'sent' => $sent,
            'skipped' => $skipped,
            'failed' => $failed,
        ]);

        Cache::put('scheduler:last_run', now()->toDateTimeString(), 86400);

        $this->info('Renewal email reminders complete.');

        return Command::SUCCESS;
    }
}

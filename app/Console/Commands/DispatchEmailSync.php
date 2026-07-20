<?php

namespace App\Console\Commands;

use App\Enums\AccountStatus;
use App\Jobs\EmailSyncJob;
use App\Models\EmailAccount;
use Illuminate\Console\Command;

class DispatchEmailSync extends Command
{
    protected $signature = 'email-sync:dispatch';
    protected $description = 'Dispatch queued IMAP sync jobs for all enabled email accounts';

    public function handle(): int
    {
        $count = 0;

        EmailAccount::where('status', AccountStatus::Active)
            ->where('sync_enabled', true)
            ->assignedToActiveUsers()
            ->chunk(100, function ($accounts) use (&$count) {
                foreach ($accounts as $account) {
                    EmailSyncJob::dispatch($account);
                    $count++;
                }
            });

        if ($count === 0) {
            $this->info('No accounts to sync.');
            return self::SUCCESS;
        }

        $this->info("Dispatched {$count} email sync jobs.");

        return self::SUCCESS;
    }
}

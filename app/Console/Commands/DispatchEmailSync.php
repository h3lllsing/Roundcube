<?php

namespace App\Console\Commands;

use App\Jobs\EmailSyncJob;
use App\Models\EmailAccount;
use Illuminate\Console\Command;

class DispatchEmailSync extends Command
{
    protected $signature = 'email-sync:dispatch';
    protected $description = 'Dispatch queued IMAP sync jobs for all enabled email accounts';

    public function handle(): int
    {
        $accounts = EmailAccount::where('status', 'active')
            ->where('sync_enabled', true)
            ->assignedToActiveUsers()
            ->get();

        if ($accounts->isEmpty()) {
            $this->info('No accounts to sync.');
            return self::SUCCESS;
        }

        $this->info("Dispatching {$accounts->count()} email sync jobs...");
        $bar = $this->output->createProgressBar($accounts->count());
        $bar->start();

        foreach ($accounts as $account) {
            EmailSyncJob::dispatch($account);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('All jobs dispatched to queue.');

        return self::SUCCESS;
    }
}

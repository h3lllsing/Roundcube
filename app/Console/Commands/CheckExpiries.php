<?php

namespace App\Console\Commands;

use App\Services\ExpiryNotificationService;
use Illuminate\Console\Command;

class CheckExpiries extends Command
{
    protected $signature = 'expiry:check';
    protected $description = 'Check all service modules for items expiring soon and send notifications';

    public function handle(ExpiryNotificationService $service): int
    {
        $this->info('Checking for expiring items...');

        $sent = $service->check();

        $this->info("Done. {$sent} expiry notification(s) sent.");

        return Command::SUCCESS;
    }
}

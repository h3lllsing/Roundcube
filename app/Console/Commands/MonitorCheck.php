<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MonitorCheck extends Command
{
    protected $signature = 'monitor:check';

    protected $description = 'Ping monitored services';

    public function handle(): int
    {
        $this->info('Monitoring command is not yet configured for this platform.');

        return Command::SUCCESS;
    }
}

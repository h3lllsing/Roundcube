<?php

namespace App\Console\Commands;

use App\Events\MonitorCheckFailed;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\OtherService;
use App\Models\ServiceProvider;
use App\Models\Voip;
use App\Models\Vps;
use App\Services\MonitorService;
use Illuminate\Console\Command;

class MonitorCheck extends Command
{
    protected $signature = 'monitor:check';
    protected $description = 'Ping all services that have a monitoring URL configured';

    /** @var class-string[] */
    private array $models = [
        Domain::class, Hosting::class, Vps::class, Voip::class,
        ServiceProvider::class, DomainEmail::class, OtherService::class, ExpiryTracker::class,
    ];

    public function handle(MonitorService $service): int
    {
        $this->info('Checking monitored services...');

        $total = 0;
        $success = 0;

        foreach ($this->models as $modelClass) {
            $items = $modelClass::whereNotNull('monitoring_url')->get();

            foreach ($items as $item) {
                $total++;
                $result = $service->check($item->monitoring_url);
                $ok = $result['ping']['success'] ? 'OK' : 'FAIL';
                $this->line("  [{$ok}] {$item->monitoring_url} (".class_basename($modelClass)." #{$item->id})");

                if ($result['ping']['success']) {
                    $success++;
                } else {
                    MonitorCheckFailed::dispatch($item, class_basename($modelClass), $result['ping']['error'] ?? 'Unknown error');
                }

                $item->last_ping_at = now();
                $item->save();
            }
        }

        $this->info("Done. {$success}/{$total} services responded successfully.");

        return Command::SUCCESS;
    }
}

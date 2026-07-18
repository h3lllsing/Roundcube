<?php

namespace App\Providers;

use App\Events\MonitorCheckFailed;
use App\Listeners\NotifyMonitorFailure;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MonitorCheckFailed::class => [
            NotifyMonitorFailure::class,
        ],
    ];

    public function boot(): void {}
}

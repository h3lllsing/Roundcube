<?php

namespace App\Providers;

use App\Events\MonitorCheckFailed;
use App\Listeners\AuditEventListener;
use App\Listeners\NotifyMonitorFailure;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Spatie\Activitylog\Models\Activity;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        MonitorCheckFailed::class => [
            NotifyMonitorFailure::class,
        ],
    ];

    public function boot(): void
    {
        Activity::created(fn (Activity $activity) => app(AuditEventListener::class)->created($activity));
    }
}

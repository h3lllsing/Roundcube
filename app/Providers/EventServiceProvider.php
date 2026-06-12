<?php

namespace App\Providers;

use App\Events\ExpiryWarningTriggered;
use App\Events\MonitorCheckFailed;
use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Events\VaultPasswordRevealed;
use App\Listeners\AlertVaultOwner;
use App\Listeners\LogExpiryWarning;
use App\Listeners\NotifyMonitorFailure;
use App\Listeners\SendTaskAssignedNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskCreated::class => [
            SendTaskAssignedNotification::class,
        ],
        TaskUpdated::class => [
            SendTaskAssignedNotification::class,
        ],
        VaultPasswordRevealed::class => [
            AlertVaultOwner::class,
        ],
        ExpiryWarningTriggered::class => [
            LogExpiryWarning::class,
        ],
        MonitorCheckFailed::class => [
            NotifyMonitorFailure::class,
        ],
    ];

    public function boot(): void {}
}

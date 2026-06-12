<?php

namespace App\Listeners;

use App\Events\MonitorCheckFailed;
use App\Models\User;
use App\Notifications\MonitorCheckFailed as MonitorCheckFailedNotification;

class NotifyMonitorFailure
{
    public function handle(MonitorCheckFailed $event): void
    {
        $itemName = $event->item->name ?? $event->item->email ?? $event->item->monitoring_url ?? '#' . $event->item->getKey();
        $admins = User::whereHas('roles', fn($q) => $q->whereIn('slug', ['admin', 'super-admin']))->get();
        foreach ($admins as $admin) {
            $admin->notify(new MonitorCheckFailedNotification(
                type: $event->type,
                error: $event->error,
                itemName: $itemName,
            ));
        }
    }
}

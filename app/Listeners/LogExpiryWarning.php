<?php

namespace App\Listeners;

use App\Events\ExpiryWarningTriggered;

class LogExpiryWarning
{
    public function handle(ExpiryWarningTriggered $event): void
    {
        activity()
            ->causedBy($event->user)
            ->performedOn($event->item)
            ->withProperties([
                'type' => $event->type,
                'days_remaining' => $event->daysRemaining,
            ])
            ->event('expiry_warning')
            ->log('expiry_warning_sent');
    }
}

<?php

namespace App\Listeners;

use App\Events\VaultPasswordRevealed;
use App\Notifications\VaultPasswordRevealed as VaultPasswordRevealedNotification;

class AlertVaultOwner
{
    public function handle(VaultPasswordRevealed $event): void
    {
        $owner = $event->entry->user;
        $causer = $event->causer;

        if (!$owner) {
            return;
        }

        if ($causer && $owner->id === $causer->id) {
            return;
        }

        $owner->notify(new VaultPasswordRevealedNotification(
            serviceName: $event->entry->service_name,
            revealedBy: $causer->name ?? 'System',
        ));
    }
}

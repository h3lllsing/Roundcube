<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class VaultPasswordRevealed extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $serviceName,
        private readonly string $revealedBy,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array<string, mixed> */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'vault_password_revealed',
            'service' => $this->serviceName,
            'revealed_by' => $this->revealedBy,
        ];
    }
}

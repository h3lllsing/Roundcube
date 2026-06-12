<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class MonitorCheckFailed extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $type,
        private readonly string $error,
        private readonly string $itemName,
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
            'type' => 'monitor_check_failed',
            'resource_type' => $this->type,
            'resource_name' => $this->itemName,
            'error' => $this->error,
        ];
    }
}

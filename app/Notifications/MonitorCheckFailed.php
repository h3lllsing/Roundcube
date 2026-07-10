<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'mail'];
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

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[OpsPilot] Service DOWN: {$this->itemName}")
            ->greeting("Service Monitoring Alert")
            ->line("A monitored service is not responding.")
            ->line("**Service:** {$this->itemName}")
            ->line("**Type:** {$this->type}")
            ->line("**Error:** {$this->error}")
            ->action('View Dashboard', url('/dashboard'))
            ->line('The hourly monitoring check will retry automatically.');
    }
}

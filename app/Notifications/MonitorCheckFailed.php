<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class MonitorCheckFailed extends Notification
{
    use Queueable;

    private const ROUTE_MAP = [
        'Hosting' => 'hostings.show',
        'Domain' => 'domains.show',
        'Vps' => 'vps.show',
        'Voip' => 'voip.show',
        'ServiceProvider' => 'service-providers.show',
        'DomainEmail' => 'domain-emails.show',
        'OtherService' => 'other-services.show',
        'ExpiryTracker' => 'expiry-trackers.show',
    ];

    public function __construct(
        private readonly string $type,
        private readonly string $error,
        private readonly string $itemName,
        private readonly ?int $itemId = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'monitor_check_failed',
            'resource_type' => $this->type,
            'resource_name' => $this->itemName,
            'error' => $this->error,
            'item_id' => $this->itemId,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/dashboard');
        if ($this->itemId !== null && isset(self::ROUTE_MAP[$this->type])) {
            try {
                $url = route(self::ROUTE_MAP[$this->type], $this->itemId);
            } catch (\Exception) {
                $url = url('/dashboard');
            }
        }

        return (new MailMessage)
            ->subject("[OpsPilot] {$this->type} DOWN — {$this->itemName}")
            ->greeting("Service Monitoring Alert")
            ->line("A monitored service is not responding.")
            ->line("**Service:** {$this->itemName}")
            ->line("**Resource Type:** {$this->type}")
            ->line("**Error:** {$this->error}")
            ->action('View Resource', $url)
            ->line("You received this because you are an administrator.")
            ->line('The hourly monitoring check will retry automatically.');
    }
}

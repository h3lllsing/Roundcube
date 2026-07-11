<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiringSoon extends Notification
{
    use Queueable;

    private const ROUTE_MAP = [
        'App\Models\Hosting' => 'hostings.show',
        'App\Models\Domain' => 'domains.show',
        'App\Models\Vps' => 'vps.show',
        'App\Models\Voip' => 'voip.show',
        'App\Models\ServiceProvider' => 'service-providers.show',
        'App\Models\DomainEmail' => 'domain-emails.show',
        'App\Models\OtherService' => 'other-services.show',
    ];

    private const HUMAN_LABELS = [
        'App\Models\Hosting' => 'Hosting',
        'App\Models\Domain' => 'Domain',
        'App\Models\Vps' => 'VPS',
        'App\Models\Voip' => 'VoIP',
        'App\Models\ServiceProvider' => 'Service Provider',
        'App\Models\DomainEmail' => 'Domain Email',
        'App\Models\OtherService' => 'Other Service',
    ];

    public function __construct(
        private readonly string $itemType,
        private readonly int $itemId,
        private readonly string $name,
        private readonly string $entityType,
        private readonly ?string $expiryDate,
        private readonly string $threshold,
        private readonly int $daysRemaining,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $urgency = match (true) {
            $this->daysRemaining < 0 => 'expired ' . abs($this->daysRemaining) . ' days ago',
            $this->daysRemaining === 0 => 'expires today',
            $this->daysRemaining === 1 => 'expires tomorrow',
            default => 'expires in ' . $this->daysRemaining . ' days',
        };

        $subject = "[OpsPilot] {$this->entityType} {$urgency} — {$this->name}";

        $routeName = self::ROUTE_MAP[$this->itemType] ?? null;
        $url = url('/');
        if ($routeName) {
            try {
                $url = route($routeName, $this->itemId);
            } catch (\Exception) {
                $url = url('/');
            }
        }

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . $notifiable->name . ',');

        $mail->line("This is a notification regarding **{$this->name}** ({$this->entityType}).");

        if ($this->daysRemaining < 0) {
            $mail->line('This item expired **' . abs($this->daysRemaining) . " day(s) ago** on {$this->expiryDate}.");
        } elseif ($this->daysRemaining === 0) {
            $mail->line("This item **expires today** ({$this->expiryDate}).");
        } else {
            $mail->line("This item will expire in **{$this->daysRemaining} day(s)** on {$this->expiryDate}.");
        }

        $mail->line("**Resource Type:** {$this->entityType}");
        $mail->line("**Status:** active");

        $mail->line("You received this because you are the assigned user for this resource.");

        return $mail->action('View Resource', $url);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'expiring_soon',
            'item_type' => $this->itemType,
            'item_id' => $this->itemId,
            'name' => $this->name,
            'entity_type' => $this->entityType,
            'expiry_date' => $this->expiryDate,
            'threshold' => $this->threshold,
            'days_remaining' => $this->daysRemaining,
        ];
    }
}

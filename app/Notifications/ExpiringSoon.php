<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExpiringSoon extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $itemType,
        private readonly int $itemId,
        private readonly string $name,
        private readonly string $entityType,
        private readonly ?string $expiryDate,
        private readonly string $threshold,
        private readonly int $daysRemaining,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /** @param User $notifiable */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->daysRemaining < 0
            ? "Overdue: {$this->entityType} — {$this->name}"
            : "Expiring Soon: {$this->entityType} — {$this->name}";

        $lines = [
            "This is a notification regarding **{$this->name}** ({$this->entityType}).",
        ];

        if ($this->daysRemaining < 0) {
            $lines[] = 'This item expired **'.abs($this->daysRemaining)." day(s) ago** on {$this->expiryDate}.";
        } elseif ($this->daysRemaining === 0) {
            $lines[] = "This item **expires today** ({$this->expiryDate}).";
        } else {
            $lines[] = "This item will expire in **{$this->daysRemaining} day(s)** on {$this->expiryDate}.";
        }

        $lines[] = 'Please take the necessary action to renew or update the status.';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Hello '.$notifiable->name.',');

        foreach ($lines as $line) {
            $mail->line($line);
        }

        return $mail->action('View Item', url('/'));
    }

    /** @return array<string, mixed> */
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

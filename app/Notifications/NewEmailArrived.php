<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class NewEmailArrived extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $email,
        private readonly string $subject,
        private readonly string $from,
        private readonly int $accountId,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_email',
            'email' => $this->email,
            'subject' => $this->subject,
            'from' => $this->from,
            'account_id' => $this->accountId,
        ];
    }
}

<?php

namespace App\Notifications;

use App\Models\EmailAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailSyncFailed extends Notification
{
    use Queueable;

    public function __construct(
        private readonly EmailAccount $account,
        private readonly string $error,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'email_sync_failed',
            'account_id' => $this->account->id,
            'email' => $this->account->email,
            'error' => $this->error,
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("[Alphaspace] Email Sync Failed — {$this->account->email}")
            ->greeting("Email Sync Alert")
            ->line("IMAP sync failed for email account.")
            ->line("**Account:** {$this->account->email}")
            ->line("**Domain:** {$this->account->domain?->name}")
            ->line("**Error:** {$this->error}")
            ->action('View Account', route('email_accounts.show', $this->account))
            ->line("The sync will retry automatically on the next schedule.");
    }
}

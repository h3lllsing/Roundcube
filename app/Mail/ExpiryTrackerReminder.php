<?php

namespace App\Mail;

use App\Models\ExpiryTracker;
use App\Models\SmtpProfile;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ExpiryTrackerReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ExpiryTracker $tracker,
        public int $daysLeft,
        public string $recipientEmail,
        public ?SmtpProfile $smtpProfile = null,
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->tracker->name;
        $subject = match (true) {
            $this->daysLeft < 0 => "Renewal Reminder: {$title} expired " . abs($this->daysLeft) . ' days ago',
            $this->daysLeft === 0 => "Renewal Reminder: {$title} expires today",
            default => "Renewal Reminder: {$title} expires in {$this->daysLeft} days",
        };

        return new Envelope(
            subject: $subject,
            from: $this->smtpProfile ? new \Illuminate\Mail\Mailables\Address($this->smtpProfile->sender_email, $this->smtpProfile->sender_name) : null,
            replyTo: $this->smtpProfile?->reply_to_email ? [new \Illuminate\Mail\Mailables\Address($this->smtpProfile->reply_to_email)] : [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.expiry-tracker-reminder',
            with: $this->buildViewData(),
        );
    }

    public function buildViewData(): array
    {
        return [
            'title' => $this->tracker->name,
            'expiryDate' => $this->tracker->expiry_date?->format('Y-m-d') ?? 'N/A',
            'daysLeft' => $this->daysLeft,
            'type' => $this->tracker->module?->name ?? 'Renewal',
            'cost' => $this->tracker->cost ? '$' . number_format($this->tracker->cost, 2) : 'N/A',
            'provider' => $this->tracker->serviceProvider?->name ?? 'N/A',
            'assignedUser' => $this->tracker->user?->name ?? 'N/A',
            'portalLink' => $this->tracker->id ? route('expiry-trackers.show', $this->tracker->id) : '#',
        ];
    }

    public function renderPreview(): string
    {
        return $this->render();
    }
}

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

    private const TRACKABLE_LABELS = [
        'App\Models\Hosting' => 'Hosting',
        'App\Models\Domain' => 'Domain',
        'App\Models\Vps' => 'VPS',
        'App\Models\Voip' => 'VoIP',
        'App\Models\ServiceProvider' => 'Service Provider',
        'App\Models\DomainEmail' => 'Domain Email',
        'App\Models\OtherService' => 'Other Service',
    ];

    private const RECIPIENT_REASONS = [
        'assigned_user' => 'You are assigned to this resource.',
        'admin' => 'You receive administrative renewal notifications.',
        'custom' => 'You were added as a notification recipient.',
        'test' => 'This test was requested from your OpsPilot account.',
    ];

    public function __construct(
        public ExpiryTracker $tracker,
        public int $daysLeft,
        public string $recipientEmail,
        public ?SmtpProfile $smtpProfile = null,
        public string $recipientType = 'assigned_user',
        public bool $isTest = false,
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->tracker->name;
        $resourceType = $this->resolveResourceType();
        $urgency = match (true) {
            $this->daysLeft < 0 => 'expired ' . abs($this->daysLeft) . ' days ago',
            $this->daysLeft === 0 => 'expires today',
            $this->daysLeft === 1 => 'expires tomorrow',
            default => 'expires in ' . $this->daysLeft . ' days',
        };

        $subject = $this->isTest
            ? "[OpsPilot][TEST] {$resourceType} {$urgency} — {$title}"
            : "[OpsPilot] {$resourceType} {$urgency} — {$title}";

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
        $trackable = $this->tracker->trackable;
        $resourceType = $this->resolveResourceType();
        $relatedDomain = null;
        $relatedHosting = null;

        if ($trackable) {
            $trackableClass = get_class($trackable);
            if ($trackableClass === 'App\Models\Domain') {
                $relatedHosting = $trackable->hosting?->name;
                $relatedDomain = $trackable->name;
            } elseif ($trackableClass === 'App\Models\Hosting') {
                $relatedDomain = $trackable->domain;
            } elseif ($trackableClass === 'App\Models\DomainEmail') {
                $relatedDomain = $trackable->domain?->name;
            } elseif ($trackableClass === 'App\Models\Vps') {
                $relatedHosting = null;
            }
        }

        $data = [
            'title' => $this->tracker->name,
            'expiryDate' => $this->tracker->expiry_date?->format('Y-m-d'),
            'daysLeft' => $this->daysLeft,
            'resourceType' => $resourceType,
            'cost' => $this->tracker->cost ? '$' . number_format($this->tracker->cost, 2) : null,
            'provider' => $this->tracker->serviceProvider?->name,
            'assignedUser' => $this->tracker->user?->name,
            'status' => $this->tracker->status,
            'portalLink' => $this->tracker->id ? route('expiry-trackers.show', $this->tracker->id) : '#',
            'isTest' => $this->isTest,
            'recipientReason' => self::RECIPIENT_REASONS[$this->recipientType] ?? 'You are a notification recipient.',
            'relatedDomain' => $relatedDomain,
            'relatedHosting' => $relatedHosting,
            'senderEmail' => $this->smtpProfile?->sender_email ?? config('mail.from.address'),
            'senderName' => $this->smtpProfile?->sender_name ?? config('mail.from.name'),
        ];

        return $data;
    }

    public function renderPreview(): string
    {
        return $this->render();
    }

    private function resolveResourceType(): string
    {
        $trackable = $this->tracker->trackable;
        if ($trackable) {
            $label = self::TRACKABLE_LABELS[get_class($trackable)] ?? null;
            if ($label) {
                return $label;
            }
        }
        return $this->tracker->module?->name ?? 'Renewal';
    }
}

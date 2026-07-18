<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\Activitylog\Models\Activity;

class AuditEventNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Activity $activity
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (env('AUDIT_NOTIFY_MAIL', true)) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $action = $this->activity->event;
        $subjectType = $this->activity->subject_type ? class_basename($this->activity->subject_type) : 'Unknown';
        $subjectId = $this->activity->subject_id;
        $causer = $this->activity->causer;

        return (new MailMessage)
            ->subject("Audit Event: {$action}")
            ->markdown('notifications.audit_event', [
                'action' => $action,
                'resource' => "{$subjectType} #{$subjectId}",
                'causer' => ($causer?->getAttribute('name') ?? 'System') . ' (' . ($causer?->getAttribute('email') ?? 'N/A') . ')',
                'timestamp' => $this->activity->created_at->format('Y-m-d H:i:s'),
                'url' => route('audit.index'),
            ]);
    }

    public function toArray(object $notifiable): array
    {
        return [
            'action' => $this->activity->event,
            'description' => $this->activity->description,
            'subject_type' => $this->activity->subject_type,
            'subject_id' => $this->activity->subject_id,
            'causer_name' => $this->activity->causer?->getAttribute('name'),
            'causer_email' => $this->activity->causer?->getAttribute('email'),
            'timestamp' => $this->activity->created_at->toDateTimeString(),
        ];
    }
}

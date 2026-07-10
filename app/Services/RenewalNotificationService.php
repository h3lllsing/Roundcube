<?php

namespace App\Services;

use App\Mail\ExpiryTrackerReminder;
use App\Models\ExpiryTracker;
use App\Models\ExpiryTrackerNotification;
use App\Models\SmtpProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RenewalNotificationService
{
    public function sendReminders(int $limit = null): int
    {
        $lock = Cache::lock('renewal_notifications', 300);
        if (! $lock->get()) {
            return 0;
        }

        try {
            $sent = $this->processReminders($limit);
        } finally {
            $lock->release();
        }

        return $sent;
    }

    private function processReminders(int $limit = null): int
    {
        $sent = 0;

        ExpiryTracker::with(['user', 'module', 'serviceProvider', 'smtpProfile'])
            ->where('email_notifications_enabled', true)
            ->whereIn('status', ['active', 'pending_renewal'])
            ->chunk(200, function ($trackers) use (&$sent, $limit) {
                foreach ($trackers as $tracker) {
                    if ($limit !== null && $sent >= $limit) {
                        return false;
                    }

                    $daysLeft = (int) Carbon::today()->startOfDay()->diffInDays(
                        Carbon::parse($tracker->expiry_date)->startOfDay(),
                        false
                    );

                    $triggerDays = $this->getTriggerDays($tracker);
                    $matchedDay = $this->findMatchingDay($daysLeft, $triggerDays);

                    if ($matchedDay === null) {
                        continue;
                    }

                    $recipients = $this->buildRecipients($tracker);

                    foreach ($recipients as $recipient) {
                        if ($limit !== null && $sent >= $limit) {
                            return false;
                        }

                        if ($this->preventDuplicate($tracker, $matchedDay, $recipient['email'], 'cron')) {
                            continue;
                        }

                        $notification = $this->recordHistory(
                            $tracker,
                            $matchedDay,
                            $recipient['email'],
                            $recipient['type'],
                            'cron',
                            'queued'
                        );

                        try {
                            $this->sendEmail($tracker, $matchedDay, $recipient['email']);
                            $notification->update(['status' => 'sent', 'sent_at' => now()]);
                        } catch (\Exception $e) {
                            $notification->update([
                                'status' => 'failed',
                                'error_message' => $this->sanitizeErrorMessage($e),
                            ]);
                            Log::error('Renewal notification failed', [
                                'tracker_id' => $tracker->id,
                                'recipient' => $recipient['email'],
                                'error' => $e->getMessage(),
                            ]);
                        }

                        $sent++;
                    }

                    if ($sent > 0) {
                        DB::transaction(function () use ($tracker) {
                            $tracker->update([
                                'last_notification_sent_at' => now(),
                                'next_notification_due_at' => $this->computeNextDueDate($tracker),
                            ]);
                        });
                    }
                }
            });

        return $sent;
    }

    public function sendNow(ExpiryTracker $tracker, bool $force = false): int
    {
        $sent = 0;
        $tracker->load(['user', 'module', 'serviceProvider', 'smtpProfile']);

        $daysLeft = (int) Carbon::today()->startOfDay()->diffInDays(
            Carbon::parse($tracker->expiry_date)->startOfDay(),
            false
        );

        $triggerDays = $this->getTriggerDays($tracker);
        $matchedDay = $this->findMatchingDay($daysLeft, $triggerDays) ?? $daysLeft;

        $recipients = $this->buildRecipients($tracker);

        foreach ($recipients as $recipient) {
            if (!$force && $this->preventDuplicate($tracker, $matchedDay, $recipient['email'], 'manual')) {
                continue;
            }

            $notification = $this->recordHistory(
                $tracker,
                $matchedDay,
                $recipient['email'],
                $recipient['type'],
                'manual',
                'queued'
            );

            try {
                $this->sendEmail($tracker, $matchedDay, $recipient['email']);
                $notification->update(['status' => 'sent', 'sent_at' => now()]);
                $sent++;
            } catch (\Exception $e) {
                $notification->update([
                    'status' => 'failed',
                    'error_message' => $this->sanitizeErrorMessage($e),
                ]);
                Log::error('Renewal notification sendNow failed', [
                    'tracker_id' => $tracker->id,
                    'recipient' => $recipient['email'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($sent > 0) {
            DB::transaction(function () use ($tracker) {
                $tracker->update([
                    'last_notification_sent_at' => now(),
                    'next_notification_due_at' => $this->computeNextDueDate($tracker),
                ]);
            });
        }

        return $sent;
    }

    public function sendTest(ExpiryTracker $tracker, User $recipient): void
    {
        $tracker->load(['user', 'module', 'serviceProvider', 'smtpProfile']);

        $daysLeft = (int) Carbon::today()->startOfDay()->diffInDays(
            Carbon::parse($tracker->expiry_date)->startOfDay(),
            false
        );

        $notification = $this->recordHistory(
            $tracker,
            max($daysLeft, 0),
            $recipient->email,
            'test',
            'test',
            'queued'
        );

        try {
            $this->sendEmail($tracker, $daysLeft, $recipient->email);
            $notification->update(['status' => 'sent', 'sent_at' => now()]);
        } catch (\Exception $e) {
            $notification->update([
                'status' => 'failed',
                'error_message' => $this->sanitizeErrorMessage($e),
            ]);
            Log::error('Renewal test email failed', [
                'tracker_id' => $tracker->id,
                'recipient' => $recipient->email,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function previewEmail(ExpiryTracker $tracker): array
    {
        $tracker->load(['user', 'module', 'serviceProvider', 'smtpProfile']);

        $daysLeft = (int) Carbon::today()->startOfDay()->diffInDays(
            Carbon::parse($tracker->expiry_date)->startOfDay(),
            false
        );

        $mailable = $this->buildMailable($tracker, $daysLeft, $tracker->user?->email ?? '');

        $subject = $mailable->envelope()->subject;
        $html = $mailable->renderPreview();
        $profileName = $tracker->smtpProfile?->name ?? 'Default System SMTP';
        $senderEmail = $tracker->smtpProfile?->sender_email ?? config('mail.from.address');
        $senderName = $tracker->smtpProfile?->sender_name ?? config('mail.from.name');

        return compact('subject', 'html', 'profileName', 'senderEmail', 'senderName');
    }

    public function buildMailable(ExpiryTracker $tracker, int $daysLeft, string $recipientEmail): ExpiryTrackerReminder
    {
        return new ExpiryTrackerReminder(
            $tracker,
            $daysLeft,
            $recipientEmail,
            $tracker->smtpProfile,
        );
    }

    public function resolveMailer(?SmtpProfile $profile = null): mixed
    {
        if ($profile && $profile->is_active) {
            $mailerName = 'smtp_profile_' . $profile->id;

            config(["mail.mailers.{$mailerName}" => [
                'transport' => 'smtp',
                'host' => $profile->smtp_host,
                'port' => (int) $profile->smtp_port,
                'encryption' => $profile->smtp_encryption,
                'username' => $profile->smtp_username,
                'password' => $profile->smtp_password,
                'timeout' => 30,
                'stream' => [
                    'ssl' => [
                        'allow_self_signed' => ! env('MAIL_VERIFY_PEER', true),
                        'verify_peer' => env('MAIL_VERIFY_PEER', true),
                        'verify_peer_name' => env('MAIL_VERIFY_PEER_NAME', true),
                    ],
                ],
            ]]);

            return Mail::mailer($mailerName);
        }

        return Mail::mailer(config('mail.default'));
    }

    public function buildRecipients(ExpiryTracker $tracker): array
    {
        $recipients = [];
        $seen = [];

        if ($tracker->notify_assigned_user && $tracker->user?->email) {
            $email = $tracker->user->email;
            $seen[$email] = true;
            $recipients[] = ['email' => $email, 'type' => 'assigned_user'];
        }

        if ($tracker->notify_admins) {
            $adminEmails = User::whereHas('roles', fn ($q) => $q->whereIn('slug', ['super-admin', 'admin']))
                ->pluck('email')->filter()->unique()->toArray();
            foreach ($adminEmails as $email) {
                if (!isset($seen[$email])) {
                    $seen[$email] = true;
                    $recipients[] = ['email' => $email, 'type' => 'admin'];
                }
            }
        }

        if ($tracker->notify_custom_emails) {
            foreach ($tracker->notify_custom_emails as $email) {
                if (filter_var($email, FILTER_VALIDATE_EMAIL) && !isset($seen[$email])) {
                    $seen[$email] = true;
                    $recipients[] = ['email' => $email, 'type' => 'custom'];
                }
            }
        }

        return $recipients;
    }

    public function recordHistory(
        ExpiryTracker $tracker,
        int $reminderDay,
        string $recipientEmail,
        string $recipientType,
        string $triggerSource,
        string $status = 'queued',
    ): ExpiryTrackerNotification {
        return ExpiryTrackerNotification::create([
            'expiry_tracker_id' => $tracker->id,
            'smtp_profile_id' => $tracker->smtp_profile_id,
            'sender_email' => $tracker->smtpProfile?->sender_email ?? config('mail.from.address'),
            'reminder_day' => $reminderDay,
            'recipient_email' => $recipientEmail,
            'recipient_type' => $recipientType,
            'trigger_source' => $triggerSource,
            'status' => $status,
        ]);
    }

    public function preventDuplicate(
        ExpiryTracker $tracker,
        int $reminderDay,
        string $recipientEmail,
        string $triggerSource,
    ): bool {
        return ExpiryTrackerNotification::where('expiry_tracker_id', $tracker->id)
            ->where('reminder_day', $reminderDay)
            ->where('recipient_email', $recipientEmail)
            ->where('trigger_source', $triggerSource)
            ->where('status', 'sent')
            ->exists();
    }

    private function sendEmail(ExpiryTracker $tracker, int $matchedDay, string $email): void
    {
        $mailer = $this->resolveMailer($tracker->smtpProfile);
        $mailable = $this->buildMailable($tracker, $matchedDay, $email);

        $mailer->to($email)->send($mailable);
    }

    private function sanitizeErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();
        $message = preg_replace('/\b(password|username|smtp_password|smtp_username)s?\b[^:]*:[^\n]*/i', '$1: [REDACTED]', $message);
        return mb_substr($message, 0, 1000);
    }

    private function getTriggerDays(ExpiryTracker $tracker): array
    {
        $days = $tracker->notify_days_before ?? config('renewals.notify_days_before');

        if ($tracker->notify_on_expiry_day) {
            $days[] = 0;
        }

        return array_unique(array_map('intval', $days));
    }

    private function findMatchingDay(int $daysLeft, array $triggerDays): ?int
    {
        if ($daysLeft < 0) {
            $minDay = !empty($triggerDays) ? min($triggerDays) : 1;
            return $minDay;
        }

        foreach (array_unique($triggerDays) as $day) {
            if ($daysLeft === (int) $day) {
                return (int) $day;
            }
        }

        return null;
    }

    private function computeNextDueDate(ExpiryTracker $tracker): ?string
    {
        if (!$tracker->expiry_date) {
            return null;
        }

        $triggerDays = $this->getTriggerDays($tracker);
        $today = Carbon::today();

        foreach ($triggerDays as $day) {
            $candidate = Carbon::parse($tracker->expiry_date)->subDays($day);
            if ($candidate->greaterThan($today)) {
                return $candidate->format('Y-m-d');
            }
        }

        return null;
    }

    public function testSmtpProfile(SmtpProfile $profile, User $recipient): void
    {
        $mailer = $this->resolveMailer($profile);

        $dummy = ExpiryTracker::make([
            'name' => 'Test SMTP Profile',
            'expiry_date' => now()->addDays(7),
        ]);

        $mailable = new ExpiryTrackerReminder(
            $dummy,
            7,
            $recipient->email,
            $profile
        );

        $mailer->to($recipient->email)->send($mailable);
    }

    public function getRecipients(ExpiryTracker $tracker): array
    {
        return $this->buildRecipients($tracker);
    }
}

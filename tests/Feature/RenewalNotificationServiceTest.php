<?php

namespace Tests\Feature;

use App\Mail\ExpiryTrackerReminder;
use App\Models\ExpiryTracker;
use App\Models\ExpiryTrackerNotification;
use App\Models\SmtpProfile;
use App\Models\User;
use App\Services\RenewalNotificationService;
use Carbon\Carbon;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RenewalNotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $assignedUser;
    private RenewalNotificationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com']);
        $this->admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->assignedUser = User::factory()->create(['name' => 'Assigned', 'email' => 'assigned@test.com']);

        $this->service = app(RenewalNotificationService::class);
    }

    private function createTracker(array $overrides = []): ExpiryTracker
    {
        return ExpiryTracker::factory()->create(array_merge([
            'user_id' => $this->assignedUser->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_days_before' => [30, 15, 7, 1],
            'notify_on_expiry_day' => false,
            'notify_assigned_user' => true,
            'notify_admins' => false,
            'notify_custom_emails' => null,
        ], $overrides));
    }

    private function createSmtpProfile(array $overrides = []): SmtpProfile
    {
        return SmtpProfile::create(array_merge([
            'name' => 'Test SMTP',
            'sender_name' => 'Test Sender',
            'sender_email' => 'sender@example.com',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'testuser',
            'smtp_password' => 'secret123',
            'is_default' => false,
            'is_active' => true,
            'priority' => 100,
            'created_by' => $this->admin->id,
        ], $overrides));
    }

    public function test_preview_renders_html(): void
    {
        $tracker = $this->createTracker();
        $result = $this->service->previewEmail($tracker);

        $this->assertArrayHasKey('subject', $result);
        $this->assertArrayHasKey('html', $result);
        $this->assertArrayHasKey('profileName', $result);
        $this->assertArrayHasKey('senderEmail', $result);
        $this->assertArrayHasKey('senderName', $result);
        $this->assertStringContainsString('Renewal Reminder', $result['subject']);
        $this->assertStringContainsString('Renewal Reminder', $result['html']);
        $this->assertStringContainsString('Default System SMTP', $result['profileName']);
    }

    public function test_test_email_sends_and_records_history(): void
    {
        Mail::fake();
        $tracker = $this->createTracker();

        $this->service->sendTest($tracker, $this->admin);

        Mail::assertSent(ExpiryTrackerReminder::class, function ($mail) use ($tracker) {
            return $mail->hasTo($this->admin->email)
                && $mail->tracker->id === $tracker->id;
        });

        $this->assertDatabaseHas('expiry_tracker_notifications', [
            'expiry_tracker_id' => $tracker->id,
            'recipient_email' => $this->admin->email,
            'recipient_type' => 'test',
            'trigger_source' => 'test',
            'status' => 'sent',
        ]);
    }

    public function test_send_now_sends_to_configured_recipients(): void
    {
        Mail::fake();
        $tracker = $this->createTracker([
            'notify_assigned_user' => true,
            'notify_admins' => true,
        ]);

        $sent = $this->service->sendNow($tracker);

        $this->assertGreaterThanOrEqual(1, $sent);

        Mail::assertSent(ExpiryTrackerReminder::class, function ($mail) {
            return $mail->hasTo($this->assignedUser->email);
        });
    }

    public function test_history_row_created_on_send(): void
    {
        Mail::fake();
        $tracker = $this->createTracker();

        $this->service->sendNow($tracker);

        $this->assertDatabaseHas('expiry_tracker_notifications', [
            'expiry_tracker_id' => $tracker->id,
            'recipient_email' => $this->assignedUser->email,
            'recipient_type' => 'assigned_user',
            'trigger_source' => 'manual',
            'status' => 'sent',
        ]);
    }

    public function test_duplicate_prevented_for_same_tracker_day_recipient_source(): void
    {
        Mail::fake();
        $tracker = $this->createTracker();

        ExpiryTrackerNotification::create([
            'expiry_tracker_id' => $tracker->id,
            'smtp_profile_id' => null,
            'sender_email' => config('mail.from.address'),
            'reminder_day' => 15,
            'recipient_email' => $this->assignedUser->email,
            'recipient_type' => 'assigned_user',
            'trigger_source' => 'manual',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->assertTrue($this->service->preventDuplicate($tracker, 15, $this->assignedUser->email, 'manual'));

        $this->assertFalse($this->service->preventDuplicate($tracker, 15, $this->assignedUser->email, 'cron'));
    }

    public function test_default_mailer_when_no_smtp_profile(): void
    {
        Mail::fake();
        $tracker = $this->createTracker(['smtp_profile_id' => null]);

        $mailer = $this->service->resolveMailer($tracker->smtpProfile);
        $this->assertNotNull($mailer);

        $this->service->sendNow($tracker);
        Mail::assertSent(ExpiryTrackerReminder::class);
    }

    public function test_smtp_profile_used_when_set(): void
    {
        Mail::fake();
        $profile = $this->createSmtpProfile();
        $tracker = $this->createTracker(['smtp_profile_id' => $profile->id]);

        $mailer = $this->service->resolveMailer($profile);
        $this->assertNotNull($mailer);

        $this->service->sendNow($tracker);
        Mail::assertSent(ExpiryTrackerReminder::class);
    }

    public function test_failed_smtp_recorded_in_history(): void
    {
        Mail::fake();
        Mail::shouldReceive('mailer->to->send')->andThrow(new \Exception('Connection refused'));

        $tracker = $this->createTracker();

        $this->service->sendNow($tracker);

        $this->assertDatabaseHas('expiry_tracker_notifications', [
            'expiry_tracker_id' => $tracker->id,
            'status' => 'failed',
        ]);
    }

    public function test_no_password_leakage_in_error_message(): void
    {
        Mail::fake();
        Mail::shouldReceive('mailer->to->send')->andThrow(
            new \Exception('SMTP connection refused')
        );

        $tracker = $this->createTracker();

        $this->service->sendNow($tracker);

        $record = ExpiryTrackerNotification::where('expiry_tracker_id', $tracker->id)
            ->where('status', 'failed')
            ->first();

        $this->assertNotNull($record);
        $this->assertNotNull($record->error_message);
        $this->assertStringContainsString('SMTP', $record->error_message);
        $this->assertStringNotContainsString('secret123', $record->error_message);
    }

    public function test_build_recipients_returns_assigned_user(): void
    {
        $tracker = $this->createTracker(['notify_assigned_user' => true, 'notify_admins' => false]);
        $recipients = $this->service->buildRecipients($tracker);

        $emails = array_column($recipients, 'email');
        $this->assertContains($this->assignedUser->email, $emails);
    }

    public function test_build_recipients_returns_admins(): void
    {
        $tracker = $this->createTracker(['notify_assigned_user' => false, 'notify_admins' => true]);
        $recipients = $this->service->buildRecipients($tracker);

        $emails = array_column($recipients, 'email');
        $this->assertContains($this->admin->email, $emails);
    }

    public function test_build_recipients_returns_custom_emails(): void
    {
        $tracker = $this->createTracker([
            'notify_assigned_user' => false,
            'notify_admins' => false,
            'notify_custom_emails' => ['custom@example.com'],
        ]);
        $recipients = $this->service->buildRecipients($tracker);

        $emails = array_column($recipients, 'email');
        $this->assertContains('custom@example.com', $emails);
    }

    public function test_prevent_duplicate_returns_true_when_exists(): void
    {
        $tracker = $this->createTracker();
        ExpiryTrackerNotification::create([
            'expiry_tracker_id' => $tracker->id,
            'smtp_profile_id' => null,
            'sender_email' => 'test@test.com',
            'reminder_day' => 7,
            'recipient_email' => 'dup@test.com',
            'recipient_type' => 'custom',
            'trigger_source' => 'manual',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->assertTrue(
            $this->service->preventDuplicate($tracker, 7, 'dup@test.com', 'manual')
        );
    }

    public function test_prevent_duplicate_returns_false_when_not_exists(): void
    {
        $tracker = $this->createTracker();

        $this->assertFalse(
            $this->service->preventDuplicate($tracker, 7, 'nonexistent@test.com', 'manual')
        );
    }

    public function test_resolve_mailer_returns_default_when_no_profile(): void
    {
        $mailer = $this->service->resolveMailer(null);
        $this->assertNotNull($mailer);
    }
}

<?php

namespace Tests\Feature;

use App\Models\ExpiryTracker;
use App\Models\ExpiryTrackerNotification;
use App\Models\SmtpProfile;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RenewalSchedulerCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    private function createTracker(array $overrides = []): ExpiryTracker
    {
        $user = User::factory()->create();

        return ExpiryTracker::factory()->create(array_merge([
            'user_id' => $user->id,
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

    public function test_command_runs(): void
    {
        Mail::fake();
        $this->createTracker();

        $this->artisan('renewals:send-email-reminders')
            ->assertSuccessful()
            ->expectsOutputToContain('Processed')
            ->expectsOutputToContain('Sent')
            ->expectsOutputToContain('Skipped')
            ->expectsOutputToContain('Failed');
    }

    public function test_reminder_queued_and_sent(): void
    {
        Mail::fake();
        $tracker = $this->createTracker();

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $this->assertDatabaseHas('expiry_tracker_notifications', [
            'expiry_tracker_id' => $tracker->id,
            'trigger_source' => 'cron',
            'status' => 'sent',
        ]);
    }

    public function test_duplicate_skipped(): void
    {
        Mail::fake();
        $tracker = $this->createTracker();
        $user = $tracker->user;

        ExpiryTrackerNotification::create([
            'expiry_tracker_id' => $tracker->id,
            'smtp_profile_id' => null,
            'sender_email' => config('mail.from.address'),
            'reminder_day' => 15,
            'recipient_email' => $user->email,
            'recipient_type' => 'assigned_user',
            'trigger_source' => 'cron',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $this->assertEquals(
            1,
            ExpiryTrackerNotification::where('expiry_tracker_id', $tracker->id)->count()
        );
    }

    public function test_limit_honored(): void
    {
        Mail::fake();
        $this->createTracker();
        $this->createTracker();

        $this->artisan('renewals:send-email-reminders', ['--limit' => 1])
            ->assertSuccessful();

        $this->assertEquals(1, ExpiryTrackerNotification::where('status', 'sent')->count());
    }

    public function test_failure_continues_processing(): void
    {
        Mail::fake();
        Mail::shouldReceive('mailer->to->send')
            ->andReturnUsing(function () {
                static $call = 0;
                $call++;
                if ($call === 1) {
                    throw new \Exception('Send failed');
                }
            });

        $this->createTracker();
        $this->createTracker();

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $this->assertEquals(1, ExpiryTrackerNotification::where('status', 'failed')->count());
        $this->assertEquals(1, ExpiryTrackerNotification::where('status', 'sent')->count());
    }

    public function test_summary_correct(): void
    {
        Mail::fake();
        $this->createTracker();
        $this->createTracker();

        $this->artisan('renewals:send-email-reminders')
            ->expectsOutputToContain('Processed')
            ->expectsOutputToContain('Sent')
            ->assertSuccessful();
    }

    public function test_cron_trigger_in_history(): void
    {
        Mail::fake();
        $tracker = $this->createTracker();

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $this->assertDatabaseHas('expiry_tracker_notifications', [
            'expiry_tracker_id' => $tracker->id,
            'trigger_source' => 'cron',
        ]);
    }

    public function test_inactive_smtp_profile_uses_fallback(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['email' => 'admin@test.com']);
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $profile = SmtpProfile::create([
            'name' => 'Inactive SMTP',
            'sender_name' => 'Inactive',
            'sender_email' => 'inactive@example.com',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'user',
            'smtp_password' => 'pass',
            'is_default' => false,
            'is_active' => false,
            'priority' => 100,
            'created_by' => $admin->id,
        ]);

        $tracker = $this->createTracker(['smtp_profile_id' => $profile->id]);

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $this->assertDatabaseHas('expiry_tracker_notifications', [
            'expiry_tracker_id' => $tracker->id,
            'status' => 'sent',
        ]);
    }

    public function test_disabled_tracker_skipped(): void
    {
        Mail::fake();
        $this->createTracker(['email_notifications_enabled' => false]);

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $this->assertDatabaseCount('expiry_tracker_notifications', 0);
    }

    // ── Patch 1.0.1: Recipient Deduplication ────────────────────────

    public function test_dedup_assigned_user_is_also_admin(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'shared@example.com']);
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_days_before' => [15],
            'notify_on_expiry_day' => false,
            'notify_assigned_user' => true,
            'notify_admins' => true,
            'notify_custom_emails' => null,
        ]);

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $this->assertEquals(
            1,
            ExpiryTrackerNotification::where('expiry_tracker_id', $tracker->id)
                ->where('recipient_email', 'shared@example.com')
                ->where('status', 'sent')
                ->count(),
            'Should send exactly one email when assigned user is also an admin (email: shared@example.com)'
        );
    }

    public function test_dedup_duplicate_custom_email(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'user@example.com']);
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_days_before' => [15],
            'notify_on_expiry_day' => false,
            'notify_assigned_user' => false,
            'notify_admins' => false,
            'notify_custom_emails' => ['dup@example.com', 'dup@example.com', 'other@example.com'],
        ]);

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $this->assertEquals(
            1,
            ExpiryTrackerNotification::where('expiry_tracker_id', $tracker->id)
                ->where('recipient_email', 'dup@example.com')
                ->where('status', 'sent')
                ->count(),
            'Should send exactly one email per duplicate custom email address'
        );

        $this->assertEquals(
            1,
            ExpiryTrackerNotification::where('expiry_tracker_id', $tracker->id)
                ->where('recipient_email', 'other@example.com')
                ->where('status', 'sent')
                ->count(),
            'Should still send to non-duplicate custom email'
        );
    }

    public function test_dedup_mixed_sources_all_same_email(): void
    {
        Mail::fake();
        $admin = User::factory()->create(['email' => 'admin@example.com']);
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $admin->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_days_before' => [15],
            'notify_on_expiry_day' => false,
            'notify_assigned_user' => true,
            'notify_admins' => true,
            'notify_custom_emails' => ['admin@example.com'],
        ]);

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $notifications = ExpiryTrackerNotification::where('expiry_tracker_id', $tracker->id)
            ->where('status', 'sent')
            ->get();

        $adminNotifications = $notifications->where('recipient_email', 'admin@example.com');

        $this->assertCount(
            1,
            $adminNotifications,
            'Should send exactly one email when assigned user, admin, and custom email all share the same address "admin@example.com"'
        );

        $allEmails = $notifications->pluck('recipient_email');
        $this->assertEquals(
            $allEmails->unique()->count(),
            $allEmails->count(),
            'All sent emails should be unique — no duplicate addresses'
        );
    }

    public function test_dedup_unique_recipients_not_affected(): void
    {
        Mail::fake();
        $user = User::factory()->create(['email' => 'assigned@example.com']);

        $admin1 = User::factory()->create(['email' => 'admin1@example.com']);
        $admin1->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $admin2 = User::factory()->create(['email' => 'admin2@example.com']);
        $admin2->assignRole(Role::where('slug', 'admin')->firstOrFail());

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_days_before' => [15],
            'notify_on_expiry_day' => false,
            'notify_assigned_user' => true,
            'notify_admins' => true,
            'notify_custom_emails' => ['custom@example.com'],
        ]);

        $this->artisan('renewals:send-email-reminders')->assertSuccessful();

        $sentEmails = ExpiryTrackerNotification::where('expiry_tracker_id', $tracker->id)
            ->where('status', 'sent')
            ->pluck('recipient_email');

        $this->assertContains('assigned@example.com', $sentEmails, 'Assigned user should receive email');
        $this->assertContains('admin1@example.com', $sentEmails, 'Admin 1 should receive email');
        $this->assertContains('admin2@example.com', $sentEmails, 'Admin 2 should receive email');
        $this->assertContains('custom@example.com', $sentEmails, 'Custom email should receive email');
        $this->assertEquals(
            $sentEmails->unique()->count(),
            $sentEmails->count(),
            'All sent emails should be unique — no duplicates'
        );
    }
}

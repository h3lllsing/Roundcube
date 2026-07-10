<?php

namespace Tests\Feature;

use App\Models\ExpiryTracker;
use App\Models\SmtpProfile;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiryTrackerNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com']);
        $this->admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());

        $this->user = User::factory()->create(['name' => 'User', 'email' => 'user@test.com']);
        $this->user->assignRole(Role::where('slug', 'admin')->firstOrFail());
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

    public function test_create_page_shows_notification_section(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('expiry-trackers.create'));
        $response->assertStatus(200);
        $response->assertSee('Enable Email Notifications');
        $response->assertSee('Send From / SMTP Profile');
        $response->assertSee('Notify Before');
        $response->assertSee('Recipients');
    }

    public function test_edit_page_shows_notification_section(): void
    {
        $tracker = ExpiryTracker::factory()->create();
        $this->actingAs($this->admin);
        $response = $this->get(route('expiry-trackers.edit', $tracker->id));
        $response->assertStatus(200);
        $response->assertSee('Enable Email Notifications');
        $response->assertSee('Send From / SMTP Profile');
        $response->assertSee('Notify Before');
        $response->assertSee('Recipients');
    }

    public function test_show_page_shows_notification_status(): void
    {
        $tracker = ExpiryTracker::factory()->create(['email_notifications_enabled' => true]);
        $this->actingAs($this->admin);
        $response = $this->get(route('expiry-trackers.show', $tracker->id));
        $response->assertStatus(200);
        $response->assertSee('Email Notifications');
        $response->assertSee('Enabled');
    }

    public function test_can_save_enabled_notification_settings(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('expiry-trackers.store'), [
            'name' => 'Tracker With Notifications',
            'email_notifications_enabled' => '1',
            'notify_days' => ['30', '7'],
            'notify_on_expiry_day' => '1',
            'notify_assigned_user' => '1',
            'notify_admins' => '0',
        ]);
        $response->assertRedirect(route('expiry-trackers.index'));
        $this->assertDatabaseHas('expiry_trackers', [
            'name' => 'Tracker With Notifications',
            'email_notifications_enabled' => 1,
            'notify_on_expiry_day' => 1,
            'notify_assigned_user' => 1,
            'notify_admins' => 0,
        ]);
        $tracker = ExpiryTracker::where('name', 'Tracker With Notifications')->first();
        $this->assertEquals([30, 7], $tracker->notify_days_before);
        $this->assertNull($tracker->disabled_by);
        $this->assertNull($tracker->disabled_at);
        $this->assertNull($tracker->disable_reason);
    }

    public function test_can_save_custom_emails(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('expiry-trackers.store'), [
            'name' => 'Tracker Custom Emails',
            'email_notifications_enabled' => '1',
            'notify_days' => ['30'],
            'notify_assigned_user' => '0',
            'notify_custom_emails' => ['alice@example.com', 'bob@example.com'],
        ]);
        $response->assertRedirect(route('expiry-trackers.index'));
        $tracker = ExpiryTracker::where('name', 'Tracker Custom Emails')->first();
        $this->assertEquals(['alice@example.com', 'bob@example.com'], $tracker->notify_custom_emails);
    }

    public function test_invalid_custom_email_fails(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('expiry-trackers.store'), [
            'name' => 'Tracker Bad Email',
            'email_notifications_enabled' => '1',
            'notify_days' => ['30'],
            'notify_assigned_user' => '1',
            'notify_custom_emails' => ['not-an-email'],
        ]);
        $response->assertSessionHasErrors('notify_custom_emails.0');
    }

    public function test_inactive_smtp_profile_cannot_be_selected(): void
    {
        $profile = $this->createSmtpProfile(['is_active' => false]);
        $this->actingAs($this->admin);
        $response = $this->post(route('expiry-trackers.store'), [
            'name' => 'Tracker Inactive SMTP',
            'smtp_profile_id' => $profile->id,
        ]);
        $response->assertSessionHasErrors('smtp_profile_id');
    }

    public function test_disabling_notifications_stores_disabled_audit_fields(): void
    {
        $this->actingAs($this->admin);
        $tracker = ExpiryTracker::factory()->create([
            'email_notifications_enabled' => true,
            'notify_days_before' => [30],
            'notify_assigned_user' => true,
            'disabled_by' => null,
            'disabled_at' => null,
            'disable_reason' => null,
        ]);
        $response = $this->put(route('expiry-trackers.update', $tracker->id), [
            'name' => $tracker->name,
            'email_notifications_enabled' => '0',
            'disable_reason' => 'Cancelled',
        ]);
        $response->assertRedirect(route('expiry-trackers.index'));
        $tracker->refresh();
        $this->assertFalse($tracker->email_notifications_enabled);
        $this->assertEquals($this->admin->id, $tracker->disabled_by);
        $this->assertNotNull($tracker->disabled_at);
        $this->assertEquals('Cancelled', $tracker->disable_reason);
    }

    public function test_enabling_notifications_clears_disabled_audit_fields(): void
    {
        $this->actingAs($this->admin);
        $tracker = ExpiryTracker::factory()->create([
            'email_notifications_enabled' => false,
            'notify_days_before' => [30],
            'notify_assigned_user' => true,
            'disabled_by' => $this->admin->id,
            'disabled_at' => now()->subDay(),
            'disable_reason' => 'Manual',
        ]);
        $response = $this->put(route('expiry-trackers.update', $tracker->id), [
            'name' => $tracker->name,
            'email_notifications_enabled' => '1',
            'notify_days' => ['30'],
            'notify_assigned_user' => '1',
        ]);
        $response->assertRedirect(route('expiry-trackers.index'));
        $tracker->refresh();
        $this->assertTrue($tracker->email_notifications_enabled);
        $this->assertNull($tracker->disabled_by);
        $this->assertNull($tracker->disabled_at);
        $this->assertNull($tracker->disable_reason);
    }

    public function test_read_only_user_cannot_update_settings(): void
    {
        $module = \App\Models\Module::where('slug', 'expiry-trackers')->firstOrFail();
        $role = $this->user->roles->first();
        $mrp = \App\Models\ModuleRolePermission::firstOrCreate(
            ['module_id' => $module->id, 'role_id' => $role->id],
            ['can_create' => false, 'can_read' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false]
        );
        $mrp->update(['can_read' => true, 'can_create' => false, 'can_update' => false]);

        $tracker = ExpiryTracker::factory()->create([
            'module_id' => $module->id,
            'user_id' => $this->user->id,
            'email_notifications_enabled' => false,
        ]);
        $this->actingAs($this->user);
        $this->get(route('expiry-trackers.edit', $tracker->id))->assertStatus(403);
        $this->put(route('expiry-trackers.update', $tracker->id), [
            'name' => $tracker->name,
            'email_notifications_enabled' => '0',
        ])->assertStatus(403);
    }

    public function test_show_page_shows_disabled_info_when_disabled(): void
    {
        $tracker = ExpiryTracker::factory()->create([
            'email_notifications_enabled' => false,
            'disabled_by' => $this->admin->id,
            'disabled_at' => now(),
            'disable_reason' => 'Manual',
        ]);
        $this->actingAs($this->admin);
        $response = $this->get(route('expiry-trackers.show', $tracker->id));
        $response->assertStatus(200);
        $response->assertSee('Disabled');
        $response->assertSee('Manual');
    }

    public function test_recipient_required_when_enabled(): void
    {
        $this->actingAs($this->admin);
        $response = $this->post(route('expiry-trackers.store'), [
            'name' => 'No Recipients',
            'email_notifications_enabled' => '1',
            'notify_days' => ['30'],
            'notify_assigned_user' => '0',
            'notify_admins' => '0',
        ]);
        $response->assertSessionHasErrors('notify_assigned_user');
    }
}

<?php

namespace Tests\Feature;

use App\Models\ExpiryTracker;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use Carbon\Carbon;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecipientPreviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();
        $role = Role::where('slug', 'user')->first();
        if ($role) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $module->id, 'role_id' => $role->id],
                ['can_read' => true]
            );
        }
    }

    public function test_assigned_user_shows_display_name(): void
    {
        $user = User::factory()->create(['name' => 'Masood Nasir', 'email' => 'masood@alphatach.com']);
        $role = Role::where('slug', 'user')->first();
        if ($role) $user->assignRole($role);
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_assigned_user' => true,
            'notify_admins' => false,
            'notify_custom_emails' => null,
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertSee('Notification Recipient Preview');
        $response->assertSee('Masood Nasir');
        $response->assertSee('masood@alphatach.com');
        $response->assertSee('Assigned User');
    }

    public function test_admin_recipient_shows_display_name(): void
    {
        $user = User::factory()->create(['name' => 'Regular User', 'email' => 'user@example.com']);
        $role = Role::where('slug', 'user')->first();
        if ($role) $user->assignRole($role);
        $admin = User::factory()->create(['name' => 'Ali Khan', 'email' => 'ali@alphatach.com']);
        $admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_assigned_user' => false,
            'notify_admins' => true,
            'notify_custom_emails' => null,
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertSee('Ali Khan');
        $response->assertSee('ali@alphatach.com');
        $response->assertSee('Administrator');
    }

    public function test_custom_email_without_user_shows_email_only(): void
    {
        $user = User::factory()->create(['name' => 'Regular User', 'email' => 'user@example.com']);
        $role = Role::where('slug', 'user')->first();
        if ($role) $user->assignRole($role);
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_assigned_user' => false,
            'notify_admins' => false,
            'notify_custom_emails' => ['accounts@company.com'],
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertSee('accounts@company.com');
        $response->assertSee('Custom');
        $response->assertDontSee('accounts@company.com</span>');
    }

    public function test_duplicate_email_appears_once(): void
    {
        $user = User::factory()->create(['name' => 'Shared User', 'email' => 'shared@example.com']);
        $user->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_assigned_user' => true,
            'notify_admins' => true,
            'notify_custom_emails' => ['shared@example.com'],
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertSee('shared@example.com');
        $response->assertSee('Notification Recipient Preview');
    }

    public function test_no_recipient_warning_appears(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $role = Role::where('slug', 'user')->first();
        if ($role) $user->assignRole($role);
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_assigned_user' => false,
            'notify_admins' => false,
            'notify_custom_emails' => null,
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertSee('No recipients selected');
        $response->assertSee('Email notifications will not be sent');
    }

    public function test_smtp_profile_and_from_appear(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $role = Role::where('slug', 'user')->first();
        if ($role) $user->assignRole($role);
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_assigned_user' => true,
            'notify_admins' => false,
            'notify_custom_emails' => null,
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertSee('Default System SMTP');
        $response->assertSee('From:');
    }

    public function test_smtp_credentials_not_exposed(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $role = Role::where('slug', 'user')->first();
        if ($role) $user->assignRole($role);
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_assigned_user' => true,
            'notify_admins' => false,
            'notify_custom_emails' => null,
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertDontSee('smtp_password');
        $response->assertDontSee('smtp_username');
        $response->assertDontSee('smtp_host');
        $response->assertDontSee('smtp_port');
    }

    public function test_disabled_notification_shows_message(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $role = Role::where('slug', 'user')->first();
        if ($role) $user->assignRole($role);
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => false,
            'notify_assigned_user' => true,
            'notify_admins' => false,
            'notify_custom_emails' => null,
            'disable_reason' => 'Manual',
            'disabled_at' => now(),
            'disabled_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertSee('Email notifications are disabled');
    }

    public function test_recipient_count_remains_correct(): void
    {
        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com']);
        $role = Role::where('slug', 'user')->first();
        if ($role) $user->assignRole($role);
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_assigned_user' => true,
            'notify_admins' => false,
            'notify_custom_emails' => null,
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertSee('Recipients (1)');
    }

    public function test_show_page_recipients_count_line(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);
        $role = Role::where('slug', 'user')->first();
        if ($role) $user->assignRole($role);
        $module = Module::where('slug', 'expiry-trackers')->firstOrFail();

        $tracker = ExpiryTracker::factory()->create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'expiry_date' => Carbon::today()->addDays(15),
            'status' => 'active',
            'email_notifications_enabled' => true,
            'notify_assigned_user' => true,
            'notify_admins' => false,
            'notify_custom_emails' => null,
        ]);

        $response = $this->actingAs($user)->get(route('expiry-trackers.show', $tracker->id));
        $response->assertOk();
        $response->assertSee('Recipients');
    }
}

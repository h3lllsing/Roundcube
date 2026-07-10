<?php

namespace Tests\Feature;

use App\Models\ExpiryTracker;
use App\Models\SmtpProfile;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SmtpProfileTest extends TestCase
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

    private function createProfile(array $overrides = []): SmtpProfile
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

    public function test_super_admin_can_create_profile(): void
    {
        $this->actingAs($this->admin);

        $this->get(route('smtp-profiles.create'))
            ->assertStatus(200);

        $this->post(route('smtp-profiles.store'), [
            'name' => 'SendGrid',
            'sender_name' => 'Notifications',
            'sender_email' => 'noreply@example.com',
            'smtp_host' => 'smtp.sendgrid.net',
            'smtp_port' => 587,
            'smtp_encryption' => 'tls',
            'smtp_username' => 'apikey',
            'smtp_password' => 'SG.secret',
            'is_default' => false,
            'is_active' => true,
            'priority' => 100,
        ])->assertRedirect(route('smtp-profiles.index'));

        $this->assertDatabaseHas('smtp_profiles', [
            'name' => 'SendGrid',
            'sender_email' => 'noreply@example.com',
            'smtp_host' => 'smtp.sendgrid.net',
        ]);
    }

    public function test_non_super_admin_cannot_manage_profiles(): void
    {
        $profile = $this->createProfile();

        $this->actingAs($this->user);

        $this->get(route('smtp-profiles.index'))->assertForbidden();
        $this->get(route('smtp-profiles.create'))->assertForbidden();
        $this->post(route('smtp-profiles.store'), [
            'name' => 'Test',
            'sender_name' => 'Test',
            'sender_email' => 'test@test.com',
            'smtp_host' => 'smtp.test.com',
            'smtp_port' => 587,
            'smtp_username' => 'user',
            'smtp_password' => 'pass',
        ])->assertForbidden();
        $this->get(route('smtp-profiles.show', $profile))->assertForbidden();
        $this->get(route('smtp-profiles.edit', $profile))->assertForbidden();
        $this->put(route('smtp-profiles.update', $profile), ['name' => 'x'])->assertForbidden();
        $this->delete(route('smtp-profiles.destroy', $profile))->assertForbidden();
        $this->post(route('smtp-profiles.test', $profile))->assertForbidden();
        $this->patch(route('smtp-profiles.set-default', $profile))->assertForbidden();
        $this->patch(route('smtp-profiles.toggle-active', $profile))->assertForbidden();
        $this->post(route('smtp-profiles.duplicate', $profile))->assertForbidden();
    }

    public function test_password_encrypted_at_rest(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $raw = DB::table('smtp_profiles')->where('id', $profile->id)->value('smtp_password');
        $this->assertNotNull($raw);
        $this->assertNotEquals('secret123', $raw);
        $this->assertEquals('secret123', decrypt($raw));

        $this->assertEquals('secret123', $profile->fresh()->smtp_password);
    }

    public function test_password_not_visible_in_edit_form(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $response = $this->get(route('smtp-profiles.edit', $profile));
        $response->assertStatus(200);
        $response->assertDontSee('secret123');
        $response->assertSee('leave blank to keep current');
    }

    public function test_password_not_visible_in_show_page(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $response = $this->get(route('smtp-profiles.show', $profile));
        $response->assertStatus(200);
        $response->assertDontSee('secret123');
        $response->assertSee('Encrypted at rest');
    }

    public function test_password_hidden_from_json(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $array = $profile->toArray();
        $this->assertArrayNotHasKey('smtp_password', $array);
    }

    public function test_only_one_default_profile(): void
    {
        $this->actingAs($this->admin);
        $this->createProfile(['name' => 'First', 'is_default' => true]);
        $this->createProfile(['name' => 'Second', 'is_default' => true]);

        $defaults = SmtpProfile::where('is_default', true)->get();
        $this->assertCount(1, $defaults);
    }

    public function test_set_default_updates_all_profiles(): void
    {
        $this->actingAs($this->admin);
        $p1 = $this->createProfile(['name' => 'P1', 'is_default' => true]);
        $p2 = $this->createProfile(['name' => 'P2', 'is_default' => false]);

        $this->patch(route('smtp-profiles.set-default', $p2))
            ->assertRedirect(route('smtp-profiles.index'));

        $this->assertFalse($p1->fresh()->is_default);
        $this->assertTrue($p2->fresh()->is_default);
    }

    public function test_activate_deactivate_works(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile(['is_active' => true]);

        $this->patch(route('smtp-profiles.toggle-active', $profile))
            ->assertRedirect(route('smtp-profiles.index'));
        $this->assertFalse($profile->fresh()->is_active);

        $this->patch(route('smtp-profiles.toggle-active', $profile))
            ->assertRedirect(route('smtp-profiles.index'));
        $this->assertTrue($profile->fresh()->is_active);
    }

    public function test_delete_blocked_when_in_use(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $tracker = ExpiryTracker::factory()->create([
            'smtp_profile_id' => $profile->id,
            'status' => 'active',
        ]);

        $this->assertTrue($profile->fresh()->isInUse());

        $this->delete(route('smtp-profiles.destroy', $profile))
            ->assertRedirect(route('smtp-profiles.index'));
        $this->assertDatabaseHas('smtp_profiles', ['id' => $profile->id]);
    }

    public function test_delete_works_when_not_in_use(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $this->assertFalse($profile->fresh()->isInUse());

        $this->delete(route('smtp-profiles.destroy', $profile))
            ->assertRedirect(route('smtp-profiles.index'));
        $this->assertSoftDeleted($profile);
    }

    public function test_inactivate_blocked_when_in_use(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile(['is_active' => true]);

        ExpiryTracker::factory()->create([
            'smtp_profile_id' => $profile->id,
            'status' => 'active',
        ]);

        $this->assertTrue($profile->fresh()->isInUse());

        $this->patch(route('smtp-profiles.toggle-active', $profile))
            ->assertRedirect(route('smtp-profiles.index'));
        $this->assertTrue($profile->fresh()->is_active);
    }

    public function test_duplicate_profile_works(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile(['is_default' => true, 'priority' => 50]);

        $this->post(route('smtp-profiles.duplicate', $profile))
            ->assertRedirect(route('smtp-profiles.index'));

        $this->assertDatabaseHas('smtp_profiles', [
            'name' => 'Test SMTP (Copy)',
            'is_default' => false,
        ]);
    }

    public function test_priority_ordering_works(): void
    {
        $this->actingAs($this->admin);
        $this->createProfile(['name' => 'Low Priority', 'priority' => 200]);
        $this->createProfile(['name' => 'High Priority', 'priority' => 50]);
        $this->createProfile(['name' => 'Medium Priority', 'priority' => 100]);

        $response = $this->get(route('smtp-profiles.index'));
        $response->assertStatus(200);

        $profiles = SmtpProfile::orderBy('priority')->orderBy('name')->get();
        $this->assertEquals('High Priority', $profiles[0]->name);
        $this->assertEquals('Medium Priority', $profiles[1]->name);
        $this->assertEquals('Low Priority', $profiles[2]->name);
    }

    public function test_consumer_tables_registry_works(): void
    {
        $tables = SmtpProfile::consumerTables();
        $this->assertArrayHasKey(ExpiryTracker::class, $tables);
        $this->assertEquals('smtp_profile_id', $tables[ExpiryTracker::class]['fk']);
        $this->assertEquals('status', $tables[ExpiryTracker::class]['status_fk']);
        $this->assertEquals(['active', 'pending_renewal'], $tables[ExpiryTracker::class]['active']);
    }

    public function test_usage_count_reflects_active_trackers(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        ExpiryTracker::factory()->create([
            'smtp_profile_id' => $profile->id,
            'status' => 'active',
        ]);
        $this->assertEquals(1, $profile->fresh()->usageCount());

        ExpiryTracker::factory()->create([
            'smtp_profile_id' => $profile->id,
            'status' => 'pending_renewal',
        ]);
        $this->assertEquals(2, $profile->fresh()->usageCount());

        ExpiryTracker::factory()->create([
            'smtp_profile_id' => $profile->id,
            'status' => 'expired',
        ]);
        $this->assertEquals(2, $profile->fresh()->usageCount());
    }

    public function test_test_smtp_records_status(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $this->assertNull($profile->fresh()->last_tested_at);

        $this->post(route('smtp-profiles.test', $profile))
            ->assertRedirect(route('smtp-profiles.show', $profile));

        $profile->refresh();
        $this->assertNotNull($profile->last_tested_at);
        $this->assertEquals('failed', $profile->last_test_status);
        $this->assertNotNull($profile->last_test_error);
    }

    public function test_update_preserves_password_when_left_blank(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile(['smtp_password' => 'original_secret']);

        $this->put(route('smtp-profiles.update', $profile), [
            'name' => 'Updated Name',
            'sender_name' => $profile->sender_name,
            'sender_email' => $profile->sender_email,
            'smtp_host' => $profile->smtp_host,
            'smtp_port' => $profile->smtp_port,
            'smtp_username' => $profile->smtp_username,
            'smtp_password' => '',
            'priority' => $profile->priority,
        ])->assertRedirect(route('smtp-profiles.index'));

        $this->assertEquals('original_secret', $profile->fresh()->smtp_password);
    }

    public function test_index_page_displays_profiles(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $response = $this->get(route('smtp-profiles.index'));
        $response->assertStatus(200);
        $response->assertSee($profile->name);
        $response->assertSee($profile->sender_email);
    }

    public function test_show_page_displays_details(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $response = $this->get(route('smtp-profiles.show', $profile));
        $response->assertStatus(200);
        $response->assertSee($profile->name);
        $response->assertSee($profile->smtp_host);
        $response->assertSee($profile->smtp_username);
        $response->assertSee('Encrypted at rest');
    }

    public function test_activity_logged_on_create(): void
    {
        $this->actingAs($this->admin);

        $this->post(route('smtp-profiles.store'), [
            'name' => 'Activity Test',
            'sender_name' => 'Test',
            'sender_email' => 'test@test.com',
            'smtp_host' => 'smtp.test.com',
            'smtp_port' => 587,
            'smtp_username' => 'user',
            'smtp_password' => 'pass',
        ]);

        $profile = SmtpProfile::where('name', 'Activity Test')->first();
        $this->assertNotNull($profile);

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => SmtpProfile::class,
            'subject_id' => $profile->id,
            'event' => 'created',
        ]);
    }

    public function test_activity_logged_on_test(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $this->post(route('smtp-profiles.test', $profile));

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => SmtpProfile::class,
            'subject_id' => $profile->id,
            'event' => 'tested',
        ]);
    }

    public function test_activity_logged_on_duplicate(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $this->post(route('smtp-profiles.duplicate', $profile));

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => SmtpProfile::class,
            'event' => 'duplicated',
        ]);
    }

    public function test_activity_logged_on_set_default(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $this->patch(route('smtp-profiles.set-default', $profile));

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => SmtpProfile::class,
            'subject_id' => $profile->id,
            'event' => 'updated',
        ]);
    }

    public function test_activity_logged_on_toggle_active(): void
    {
        $this->actingAs($this->admin);
        $profile = $this->createProfile();

        $this->patch(route('smtp-profiles.toggle-active', $profile));

        $this->assertDatabaseHas('activity_log', [
            'subject_type' => SmtpProfile::class,
            'subject_id' => $profile->id,
            'event' => 'updated',
        ]);
    }
}

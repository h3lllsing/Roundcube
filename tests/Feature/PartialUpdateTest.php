<?php

namespace Tests\Feature;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\Domain;
use App\Models\ExpiryTracker;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Hosting;
use App\Models\SmtpProfile;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use App\Models\Webhook;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class PartialUpdateTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->regularUser = User::factory()->create();
    }

    // ─── SMTP Profiles ─────────────────────────────────────────

    public function test_smtp_update_sender_name_only()
    {
        $profile = SmtpProfile::create([
            'name' => 'Test Profile',
            'sender_name' => 'Old Name',
            'sender_email' => 'old@example.com',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'user',
            'smtp_password' => 'existing-encrypted',
            'created_by' => $this->admin->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('smtp-profiles.update', $profile), [
                'name' => 'Test Profile',
                'sender_name' => 'New Sender Name',
                'sender_email' => 'old@example.com',
                'smtp_host' => 'smtp.example.com',
                'smtp_port' => 587,
                'smtp_username' => 'user',
            ])
            ->assertSessionHas('success');

        $fresh = $profile->fresh();
        $this->assertEquals('New Sender Name', $fresh->sender_name);
        $this->assertNotEmpty($fresh->smtp_password);
    }

    public function test_smtp_blank_password_keeps_existing()
    {
        $profile = SmtpProfile::create([
            'name' => 'Test Profile',
            'sender_name' => 'Test',
            'sender_email' => 'test@example.com',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'user',
            'smtp_password' => 'secret-123',
            'created_by' => $this->admin->id,
        ]);

        $originalEncrypted = $profile->getRawOriginal('smtp_password');

        $this->actingAs($this->admin)
            ->put(route('smtp-profiles.update', $profile), [
                'name' => 'Test Profile',
                'sender_name' => 'Test',
                'sender_email' => 'test@example.com',
                'smtp_host' => 'smtp.example.com',
                'smtp_port' => 587,
                'smtp_username' => 'user',
                'smtp_password' => '',
            ])
            ->assertSessionHas('success');

        $this->assertEquals($originalEncrypted, $profile->fresh()->getRawOriginal('smtp_password'));
    }

    // ─── Vault ──────────────────────────────────────────────────

    public function test_vault_update_notes_only()
    {
        $encrypted = Crypt::encryptString('secret123');
        $vault = VaultEntry::factory()->create([
            'description' => 'Original description',
            'encrypted_password' => $encrypted,
        ]);

        $this->actingAs($this->admin)
            ->put(route('vault.update', $vault->id), [
                'service_name' => $vault->service_name,
                'username' => $vault->username,
                'description' => 'Updated description only',
            ])
            ->assertSessionHas('success');

        $fresh = $vault->fresh();
        $this->assertEquals('Updated description only', $fresh->description);
        $this->assertEquals($encrypted, $fresh->getRawOriginal('encrypted_password'));
    }

    public function test_vault_blank_password_keeps_existing()
    {
        $encrypted = Crypt::encryptString('old-password');
        $vault = VaultEntry::factory()->create([
            'encrypted_password' => $encrypted,
        ]);

        $this->actingAs($this->admin)
            ->put(route('vault.update', $vault->id), [
                'service_name' => $vault->service_name,
                'username' => $vault->username,
                'password' => '',
                'password_confirmation' => '',
            ])
            ->assertSessionHas('success');

        $this->assertEquals($encrypted, $vault->fresh()->getRawOriginal('encrypted_password'));
    }

    // ─── Expiry Trackers ────────────────────────────────────────

    public function test_expiry_tracker_update_status_only()
    {
        $tracker = ExpiryTracker::factory()->create([
            'status' => 'active',
            'notify_days_before' => [7, 30],
        ]);

        $this->actingAs($this->admin)
            ->put(route('expiry-trackers.update', $tracker->id), [
                'name' => $tracker->name,
                'expiry_date' => $tracker->expiry_date?->format('Y-m-d'),
                'status' => 'expired',
            ])
            ->assertSessionHas('success');

        $fresh = $tracker->fresh();
        $this->assertEquals('expired', $fresh->status);
        $this->assertEquals([7, 30], $fresh->notify_days_before);
    }

    public function test_expiry_tracker_notification_recipients_unchanged()
    {
        $tracker = ExpiryTracker::factory()->create([
            'email_notifications_enabled' => true,
            'notify_days_before' => [7],
        ]);

        $this->actingAs($this->admin)
            ->put(route('expiry-trackers.update', $tracker->id), [
                'name' => $tracker->name,
                'expiry_date' => $tracker->expiry_date?->format('Y-m-d'),
                'status' => 'active',
            ])
            ->assertSessionHas('success');

        $fresh = $tracker->fresh();
        $this->assertTrue($fresh->email_notifications_enabled);
        $this->assertEquals([7], $fresh->notify_days_before);
    }

    // ─── Tasks ──────────────────────────────────────────────────

    public function test_task_update_status_only()
    {
        $task = Task::factory()->create([
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin)
            ->put(route('tasks.update', $task->id), [
                'title' => $task->title,
                'status' => 'completed',
            ])
            ->assertSessionHas('success');

        $fresh = $task->fresh();
        $this->assertEquals('completed', $fresh->status);
    }

    public function test_task_priority_unchanged_when_not_submitted()
    {
        $task = Task::factory()->create([
            'status' => 'pending',
            'priority' => 'high',
        ]);

        $this->actingAs($this->admin)
            ->put(route('tasks.update', $task->id), [
                'title' => $task->title,
                'status' => 'in_progress',
            ])
            ->assertSessionHas('success');

        $this->assertEquals('high', $task->fresh()->priority);
    }

    // ─── Assets ─────────────────────────────────────────────────

    public function test_asset_update_notes_only()
    {
        $category = AssetCategory::factory()->create();
        $type = AssetType::factory()->create(['category_id' => $category->id]);
        $asset = Asset::factory()->create([
            'category_id' => $category->id,
            'type_id' => $type->id,
            'description' => 'Original',
            'condition' => 'good',
        ]);

        $this->actingAs($this->admin)
            ->put(route('assets.update', $asset->id), [
                'asset_tag' => $asset->asset_tag,
                'category_id' => $asset->category_id,
                'type_id' => $asset->type_id,
                'description' => 'Updated notes only',
            ])
            ->assertSessionHas('success');

        $fresh = $asset->fresh();
        $this->assertEquals('Updated notes only', $fresh->description);
        $this->assertEquals('good', $fresh->condition);
    }

    // ─── Infrastructure: Domain ─────────────────────────────────

    public function test_domain_update_one_field_only()
    {
        $domain = Domain::factory()->create([
            'name' => 'example.com',
            'status' => 'active',
        ]);

        $this->actingAs($this->admin)
            ->put(route('domains.update', $domain->id), [
                'name' => 'example.com',
                'status' => 'expired',
            ])
            ->assertSessionHas('success');

        $fresh = $domain->fresh();
        $this->assertEquals('expired', $fresh->status);
    }

    public function test_domain_dns_servers_preserved_when_omitted()
    {
        $domain = Domain::factory()->create([
            'name' => 'dns-test.com',
            'dns_servers' => ['ns1.original.com', 'ns2.original.com'],
        ]);

        $this->actingAs($this->admin)
            ->put(route('domains.update', $domain->id), [
                'name' => 'dns-test.com',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(['ns1.original.com', 'ns2.original.com'], $domain->fresh()->dns_servers);
    }

    // ─── Infrastructure: Hosting ────────────────────────────────

    public function test_hosting_update_one_field_only()
    {
        $hosting = Hosting::factory()->create([
            'name' => 'My Hosting',
            'plan' => 'Basic',
        ]);

        $this->actingAs($this->admin)
            ->put(route('hostings.update', $hosting->id), [
                'name' => 'My Hosting',
                'plan' => 'Enterprise',
            ])
            ->assertSessionHas('success');

        $fresh = $hosting->fresh();
        $this->assertEquals('Enterprise', $fresh->plan);
    }

    // ─── Infrastructure: VPS ────────────────────────────────────

    public function test_vps_update_one_field_only()
    {
        $vps = Vps::factory()->create([
            'name' => 'My VPS',
            'plan' => 's-1vcpu-1gb',
        ]);

        $this->actingAs($this->admin)
            ->put(route('vps.update', $vps->id), [
                'name' => 'My VPS',
                'plan' => 's-4vcpu-8gb',
            ])
            ->assertSessionHas('success');

        $fresh = $vps->fresh();
        $this->assertEquals('s-4vcpu-8gb', $fresh->plan);
    }

    // ─── Super Admin Access ─────────────────────────────────────

    public function test_super_admin_can_edit_domain()
    {
        $domain = Domain::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('domains.edit', $domain->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_hosting()
    {
        $hosting = Hosting::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('hostings.edit', $hosting->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_vps()
    {
        $vps = Vps::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('vps.edit', $vps->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_voip()
    {
        $voip = Voip::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('voip.edit', $voip->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_expiry_tracker()
    {
        $tracker = ExpiryTracker::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('expiry-trackers.edit', $tracker->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_task()
    {
        $task = Task::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('tasks.edit', $task->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_smtp_profile()
    {
        $profile = SmtpProfile::create([
            'name' => 'Test',
            'sender_name' => 'Test',
            'sender_email' => 'test@example.com',
            'smtp_host' => 'smtp.example.com',
            'smtp_port' => 587,
            'smtp_username' => 'user',
            'smtp_password' => 'pass',
            'created_by' => $this->admin->id,
        ]);
        $this->actingAs($this->admin)
            ->get(route('smtp-profiles.edit', $profile->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_vault()
    {
        $vault = VaultEntry::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('vault.edit', $vault->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_asset()
    {
        $asset = Asset::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('assets.edit', $asset->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_webhook()
    {
        $webhook = Webhook::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('webhooks.edit', $webhook->id))
            ->assertOk();
    }

    public function test_super_admin_can_edit_user()
    {
        $user = User::factory()->create();
        $this->actingAs($this->admin)
            ->get(route('users.edit', $user->id))
            ->assertOk();
    }

    // ─── Non-authorized users blocked ──────────────────────────

    public function test_regular_user_cannot_edit_domain()
    {
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $this->regularUser->assignRole($userRole);

        $module = Module::factory()->create(['slug' => 'domains']);
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $module->id, 'role_id' => $userRole->id],
            ['can_read' => true]
        );

        $domain = Domain::factory()->create([
            'user_id' => $this->regularUser->id,
            'module_id' => $module->id,
        ]);
        $this->actingAs($this->regularUser)
            ->get(route('domains.edit', $domain->id))
            ->assertStatus(403);
    }

    public function test_regular_user_cannot_edit_user()
    {
        $user = User::factory()->create();
        $this->actingAs($this->regularUser)
            ->get(route('users.edit', $user->id))
            ->assertStatus(403);
    }

    // ─── Partial update preserves other fields ─────────────────

    public function test_vault_update_does_not_touch_other_fields()
    {
        $vault = VaultEntry::factory()->create([
            'service_name' => 'Original Service',
            'service_url' => 'https://original.com',
            'description' => 'Original description',
        ]);

        $this->actingAs($this->admin)
            ->put(route('vault.update', $vault->id), [
                'service_name' => 'Original Service',
                'username' => $vault->username,
                'description' => 'Only description changed',
            ])
            ->assertSessionHas('success');

        $fresh = $vault->fresh();
        $this->assertEquals('Only description changed', $fresh->description);
        $this->assertEquals('https://original.com', $fresh->service_url);
    }
}

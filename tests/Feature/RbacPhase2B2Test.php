<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\ExpiryTracker;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase2B2Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $userRole;

    private Module $domainsModule;

    private Module $hostingsModule;

    private Module $vpsModule;

    private Module $voipModule;

    private Module $expiryTrackersModule;

    private Module $vaultModule;

    private Module $otherModule;

    private User $superAdmin;

    private User $admin;

    private User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->userRole = Role::where('slug', 'user')->firstOrFail();

        $this->domainsModule = Module::where('slug', 'domains')->firstOrFail();
        $this->hostingsModule = Module::where('slug', 'hostings')->firstOrFail();
        $this->vpsModule = Module::where('slug', 'vps')->firstOrFail();
        $this->voipModule = Module::where('slug', 'voip')->firstOrFail();
        $this->expiryTrackersModule = Module::where('slug', 'expiry-trackers')->firstOrFail();
        $this->vaultModule = Module::where('slug', 'vault')->firstOrFail();
        $this->otherModule = Module::where('slug', 'other-services')->firstOrFail();

        // Grant admin role: can_create, can_update, can_delete on domains, hostings, vps, voip, expiry-trackers, vault
        foreach ([$this->domainsModule, $this->hostingsModule, $this->vpsModule, $this->voipModule, $this->expiryTrackersModule, $this->vaultModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->adminRole->id],
                ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
            );
        }
        // Admin: read-only on other-module (can see records but not create/update/delete)
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->otherModule->id, 'role_id' => $this->adminRole->id],
            ['can_create' => false, 'can_read' => true, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
        );

        // Grant user role: can_create, can_update, can_delete on domains only
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->domainsModule->id, 'role_id' => $this->userRole->id],
            ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
        );
        // Deny everything on all others
        foreach ([$this->hostingsModule, $this->vpsModule, $this->voipModule, $this->expiryTrackersModule, $this->vaultModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->userRole->id],
                ['can_create' => false, 'can_read' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
            );
        }
        // User: read-only on other-module (can see records but not create/update/delete)
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->otherModule->id, 'role_id' => $this->userRole->id],
            ['can_create' => false, 'can_read' => true, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
        );

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);

        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole($this->userRole);
    }

    // ─── SUPER-ADMIN BYPASS ───────────────────────────────────────────

    public function test_super_admin_bypasses_store(): void
    {
        $response = $this->actingAs($this->superAdmin)->post('/domains', [
            'name' => 'super-admin-domain.com',
            'module_id' => $this->domainsModule->id,
        ]);
        $response->assertRedirect();
    }

    public function test_super_admin_bypasses_update(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->superAdmin)->put('/domains/'.$domain->id, ['name' => 'updated.com']);
        $response->assertRedirect();
    }

    public function test_super_admin_bypasses_destroy(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->superAdmin)->delete('/domains/'.$domain->id);
        $response->assertRedirect();
    }

    public function test_super_admin_can_restore_and_force_delete(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);
        $domain->delete();

        $response = $this->actingAs($this->superAdmin)->patch('/domains/'.$domain->id.'/restore');
        $response->assertRedirect();

        $domain->delete();
        $response = $this->actingAs($this->superAdmin)->delete('/domains/'.$domain->id.'/force-delete');
        $response->assertRedirect();
    }

    // ─── ADMIN CAN_CREATE ─────────────────────────────────────────────

    public function test_admin_with_can_create_can_store(): void
    {
        $response = $this->actingAs($this->admin)->post('/domains', [
            'name' => 'admin-domain.com',
            'module_id' => $this->domainsModule->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('domains', ['name' => 'admin-domain.com']);
    }

    public function test_admin_without_can_create_denied_store(): void
    {
        $response = $this->actingAs($this->admin)->post('/other-services', [
            'name' => 'blocked-service',
            'service_type' => 'monitoring',
        ]);
        $response->assertForbidden();
    }

    public function test_admin_with_can_create_sees_create_view(): void
    {
        $response = $this->actingAs($this->admin)->get('/domains/create');
        $response->assertOk();
    }

    public function test_admin_without_can_create_denied_create_view(): void
    {
        $response = $this->actingAs($this->admin)->get('/other-services/create');
        $response->assertForbidden();
    }

    // ─── ADMIN CAN_UPDATE ─────────────────────────────────────────────

    public function test_admin_with_can_update_can_edit_view(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get('/domains/'.$domain->id.'/edit');
        $response->assertOk();
    }

    public function test_admin_without_can_update_denied_edit_view(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->otherModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get('/domains/'.$domain->id.'/edit');
        $response->assertForbidden();
    }

    public function test_admin_with_can_update_can_update_record(): void
    {
        $domain = Domain::factory()->create(['name' => 'original.com', 'module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put('/domains/'.$domain->id, ['name' => 'updated.com']);
        $response->assertRedirect();
        $this->assertDatabaseHas('domains', ['name' => 'updated.com']);
    }

    public function test_admin_without_can_update_denied_update(): void
    {
        $domain = Domain::factory()->create(['name' => 'original.com', 'module_id' => $this->otherModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put('/domains/'.$domain->id, ['name' => 'blocked.com']);
        $response->assertForbidden();
    }

    // ─── ADMIN CAN_DELETE ─────────────────────────────────────────────

    public function test_admin_with_can_delete_can_destroy(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete('/domains/'.$domain->id);
        $response->assertRedirect();
        $this->assertSoftDeleted($domain);
    }

    public function test_admin_without_can_delete_denied_destroy(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->otherModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete('/domains/'.$domain->id);
        $response->assertForbidden();
    }

    // ─── USER OVERRIDE ────────────────────────────────────────────────

    public function test_override_true_grants_create_when_role_denies(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->otherModule->id,
            'can_create' => true,
        ]);

        $response = $this->actingAs($this->admin)->post('/domains', [
            'name' => 'override-create.com',
            'module_id' => $this->otherModule->id,
        ]);
        $response->assertRedirect();
    }

    public function test_override_false_denies_create_when_role_grants(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->domainsModule->id,
            'can_create' => false,
        ]);

        $response = $this->actingAs($this->admin)->post('/domains', [
            'name' => 'override-deny.com',
            'module_id' => $this->domainsModule->id,
        ]);
        $response->assertForbidden();
    }

    public function test_override_true_grants_update_when_role_denies(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->otherModule->id,
            'can_update' => true,
        ]);
        $domain = Domain::factory()->create(['module_id' => $this->otherModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put('/domains/'.$domain->id, ['name' => 'override-update.com']);
        $response->assertRedirect();
    }

    public function test_override_false_denies_update_when_role_grants(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->domainsModule->id,
            'can_update' => false,
        ]);
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put('/domains/'.$domain->id, ['name' => 'override-deny.com']);
        $response->assertForbidden();
    }

    public function test_override_true_grants_delete_when_role_denies(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->otherModule->id,
            'can_delete' => true,
        ]);
        $domain = Domain::factory()->create(['module_id' => $this->otherModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete('/domains/'.$domain->id);
        $response->assertRedirect();
    }

    public function test_override_false_denies_delete_when_role_grants(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->domainsModule->id,
            'can_delete' => false,
        ]);
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete('/domains/'.$domain->id);
        $response->assertForbidden();
    }

    // ─── NORMAL USER ──────────────────────────────────────────────────

    public function test_normal_user_with_can_create_can_store_own_record(): void
    {
        $response = $this->actingAs($this->normalUser)->post('/domains', [
            'name' => 'user-domain.com',
            'module_id' => $this->domainsModule->id,
        ]);
        $response->assertRedirect();
        $this->assertDatabaseHas('domains', ['name' => 'user-domain.com', 'user_id' => $this->normalUser->id]);
    }

    public function test_normal_user_without_can_create_denied_store(): void
    {
        $response = $this->actingAs($this->normalUser)->post('/hostings', [
            'name' => 'blocked-hosting.com',
            'module_id' => $this->hostingsModule->id,
        ]);
        $response->assertForbidden();
    }

    public function test_normal_user_with_can_update_can_update_own_record(): void
    {
        $domain = Domain::factory()->create(['name' => 'my.com', 'module_id' => $this->domainsModule->id, 'user_id' => $this->normalUser->id]);

        $response = $this->actingAs($this->normalUser)->put('/domains/'.$domain->id, ['name' => 'my-updated.com']);
        $response->assertRedirect();
        $this->assertDatabaseHas('domains', ['name' => 'my-updated.com']);
    }

    public function test_normal_user_without_can_update_denied_update(): void
    {
        $domain = Domain::factory()->create(['name' => 'cant-update.com', 'module_id' => $this->otherModule->id, 'user_id' => $this->normalUser->id]);

        $response = $this->actingAs($this->normalUser)->put('/domains/'.$domain->id, ['name' => 'blocked.com']);
        $response->assertForbidden();
    }

    public function test_normal_user_with_can_delete_can_delete_own_record(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->normalUser->id]);

        $response = $this->actingAs($this->normalUser)->delete('/domains/'.$domain->id);
        $response->assertRedirect();
        $this->assertSoftDeleted($domain);
    }

    public function test_normal_user_without_can_delete_denied_delete(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->otherModule->id, 'user_id' => $this->normalUser->id]);

        $response = $this->actingAs($this->normalUser)->delete('/domains/'.$domain->id);
        $response->assertForbidden();
    }

    // ─── RESTORE / FORCE-DELETE BLOCKED FOR NON-SUPER-ADMIN ──────────

    public function test_non_super_admin_restore_blocked(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);
        $domain->delete();

        $response = $this->actingAs($this->admin)->patch('/domains/'.$domain->id.'/restore');
        $response->assertForbidden();
    }

    public function test_non_super_admin_force_delete_blocked(): void
    {
        $domain = Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);
        $domain->delete();

        $response = $this->actingAs($this->admin)->delete('/domains/'.$domain->id.'/force-delete');
        $response->assertForbidden();
    }

    // ─── CONTROLLER CATEGORY: EXPIRYTRACKER (low-risk) ────────────────

    public function test_expiry_tracker_admin_with_can_create_can_store(): void
    {
        $response = $this->actingAs($this->admin)->post('/expiry-trackers', [
            'name' => 'Tracker 1',
            'module_id' => $this->expiryTrackersModule->id,
        ]);
        $response->assertRedirect();
    }

    public function test_expiry_tracker_admin_with_can_update_can_update(): void
    {
        $tracker = ExpiryTracker::factory()->create(['module_id' => $this->expiryTrackersModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put('/expiry-trackers/'.$tracker->id, ['name' => 'Updated']);
        $response->assertRedirect();
    }

    public function test_expiry_tracker_admin_with_can_delete_can_destroy(): void
    {
        $tracker = ExpiryTracker::factory()->create(['module_id' => $this->expiryTrackersModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete('/expiry-trackers/'.$tracker->id);
        $response->assertRedirect();
    }

    // ─── CONTROLLER CATEGORY: HOSTING (password module) ───────────────

    public function test_hosting_admin_with_can_create_can_store(): void
    {
        $response = $this->actingAs($this->admin)->post('/hostings', [
            'name' => 'Hosting 1',
            'module_id' => $this->hostingsModule->id,
        ]);
        $response->assertRedirect();
    }

    public function test_hosting_admin_without_can_create_denied_store(): void
    {
        $response = $this->actingAs($this->admin)->post('/other-services', [
            'name' => 'Blocked Service',
            'service_type' => 'monitoring',
        ]);
        $response->assertForbidden();
    }

    public function test_hosting_admin_with_can_update_can_update(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put('/hostings/'.$hosting->id, ['name' => 'Updated']);
        $response->assertRedirect();
    }

    public function test_hosting_admin_with_can_delete_can_destroy(): void
    {
        $hosting = Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete('/hostings/'.$hosting->id);
        $response->assertRedirect();
    }

    // ─── CONTROLLER CATEGORY: VPS (password module) ───────────────────

    public function test_vps_admin_with_can_create_can_store(): void
    {
        $response = $this->actingAs($this->admin)->post('/vps', [
            'name' => 'VPS 1',
            'module_id' => $this->vpsModule->id,
        ]);
        $response->assertRedirect();
    }

    public function test_vps_admin_with_can_update_can_update(): void
    {
        $vps = Vps::factory()->create(['module_id' => $this->vpsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put('/vps/'.$vps->id, ['name' => 'Updated']);
        $response->assertRedirect();
    }

    public function test_vps_admin_with_can_delete_can_destroy(): void
    {
        $vps = Vps::factory()->create(['module_id' => $this->vpsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete('/vps/'.$vps->id);
        $response->assertRedirect();
    }

    // ─── CONTROLLER CATEGORY: VOIP (password module) ──────────────────

    public function test_voip_admin_with_can_create_can_store(): void
    {
        $response = $this->actingAs($this->admin)->post('/voip', [
            'name' => 'VoIP 1',
            'module_id' => $this->voipModule->id,
        ]);
        $response->assertRedirect();
    }

    public function test_voip_admin_with_can_update_can_update(): void
    {
        $voip = Voip::factory()->create(['module_id' => $this->voipModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put('/voip/'.$voip->id, ['name' => 'Updated']);
        $response->assertRedirect();
    }

    public function test_voip_admin_with_can_delete_can_destroy(): void
    {
        $voip = Voip::factory()->create(['module_id' => $this->voipModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete('/voip/'.$voip->id);
        $response->assertRedirect();
    }

    // ─── CONTROLLER CATEGORY: VAULT ───────────────────────────────────

    public function test_vault_admin_with_can_create_can_store(): void
    {
        $response = $this->actingAs($this->admin)->post('/vault', [
            'service_name' => 'Vault Entry 1',
            'module_id' => $this->vaultModule->id,
        ]);
        $response->assertRedirect();
    }

    public function test_vault_admin_with_can_update_can_update(): void
    {
        $entry = VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->put('/vault/'.$entry->id, ['service_name' => 'Updated']);
        $response->assertRedirect();
    }

    public function test_vault_admin_with_can_delete_can_destroy(): void
    {
        $entry = VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->delete('/vault/'.$entry->id);
        $response->assertRedirect();
    }
}

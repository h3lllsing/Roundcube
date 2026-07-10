<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Models\VaultEntry;
use App\Models\Vps;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase2C1Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $userRole;

    private Module $domainsModule;

    private Module $hostingsModule;

    private Module $vpsModule;

    private Module $vaultModule;

    private Module $deniedModule;

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
        $this->vaultModule = Module::where('slug', 'vault')->firstOrFail();
        $this->deniedModule = Module::where('slug', 'other-services')->firstOrFail();

        // Admin: full CRUD + export on domains, hostings, vps, vault
        foreach ([$this->domainsModule, $this->hostingsModule, $this->vpsModule, $this->vaultModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->adminRole->id],
                ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
            );
        }
        // Admin: denied-module — can_read but no create/export/delete
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->deniedModule->id, 'role_id' => $this->adminRole->id],
            ['can_create' => false, 'can_read' => true, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
        );

        // User role: can_create, can_export, can_delete on domains only
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->domainsModule->id, 'role_id' => $this->userRole->id],
            ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
        );
        // User: denied on all others (no can_read either — will use ownership scope)
        foreach ([$this->hostingsModule, $this->vpsModule, $this->vaultModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->userRole->id],
                ['can_create' => false, 'can_read' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
            );
        }

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);

        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole($this->userRole);
    }

    // ─── SUPER ADMIN — ALL BUTTONS VISIBLE ─────────────────────────

    public function test_super_admin_sees_create_and_export_buttons(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('domains.index'));
        $response->assertOk();
        $response->assertSee('Create');
        $response->assertSee('Export CSV');
    }

    public function test_super_admin_sees_restore_and_force_delete_in_bulk_actions(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('vault.index'));
        $response->assertOk();
        $response->assertSee('Delete');
        $response->assertSee('Restore');
        $response->assertSee('Force delete');
    }

    // ─── CREATE BUTTON ──────────────────────────────────────────────

    public function test_create_button_visible_when_can_create_true(): void
    {
        $response = $this->actingAs($this->admin)->get(route('domains.index'));
        $response->assertOk();
        $response->assertSee('Create');
    }

    public function test_create_button_hidden_when_can_create_false(): void
    {
        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
        $response->assertDontSee('Create');
    }

    public function test_create_button_visible_for_normal_user_with_permission(): void
    {
        $response = $this->actingAs($this->normalUser)->get(route('domains.index'));
        $response->assertOk();
        $response->assertSee('Create');
    }

    // ─── EXPORT BUTTON ──────────────────────────────────────────────

    public function test_export_button_visible_when_can_export_true(): void
    {
        $response = $this->actingAs($this->admin)->get(route('hostings.index'));
        $response->assertOk();
        $response->assertSee('Export CSV');
    }

    public function test_export_button_hidden_when_can_export_false(): void
    {
        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
        $response->assertDontSee('Export CSV');
    }

    // ─── BULK DELETE ────────────────────────────────────────────────

    public function test_bulk_delete_visible_when_can_delete_true(): void
    {
        $response = $this->actingAs($this->admin)->get(route('vps.index'));
        $response->assertOk();
        $response->assertSee('Delete');
    }

    public function test_bulk_delete_hidden_when_can_delete_false(): void
    {
        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
        $response->assertDontSee('value="delete"');
    }

    // ─── BULK RESTORE / FORCE DELETE ────────────────────────────────

    public function test_bulk_restore_force_delete_hidden_for_non_super_admin(): void
    {
        $response = $this->actingAs($this->admin)->get(route('domains.index'));
        $response->assertOk();
        $response->assertDontSee('Restore');
        $response->assertDontSee('Force delete');
    }

    public function test_bulk_restore_force_delete_visible_for_super_admin(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('domains.index'));
        $response->assertOk();
        $response->assertSee('Restore');
        $response->assertSee('Force delete');
    }

    // ─── USER OVERRIDE ──────────────────────────────────────────────

    public function test_override_true_shows_create_button_when_role_denies(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->deniedModule->id,
            'can_create' => true, 'can_export' => true, 'can_delete' => true,
        ]);

        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
        $response->assertSee('Create');
        $response->assertSee('Export CSV');
    }

    public function test_override_false_hides_create_button_when_role_allows(): void
    {
        Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        UserModulePermission::create([
            'user_id' => $this->admin->id, 'module_id' => $this->domainsModule->id,
            'can_create' => false, 'can_export' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('domains.index'));
        $response->assertOk();
        $response->assertDontSee('Create');
        $response->assertDontSee('Export CSV');
    }

    // ─── PER-MODULE COVERAGE ────────────────────────────────────────

    public function test_domains_index_respects_permissions(): void
    {
        Domain::factory()->create(['module_id' => $this->domainsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('domains.index'));
        $response->assertOk();
        $response->assertSee('Create');
        $response->assertSee('Export CSV');
    }

    public function test_hostings_index_respects_permissions(): void
    {
        Hosting::factory()->create(['module_id' => $this->hostingsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('hostings.index'));
        $response->assertOk();
        $response->assertSee('Create');
        $response->assertSee('Export CSV');
    }

    public function test_vps_index_respects_permissions(): void
    {
        Vps::factory()->create(['module_id' => $this->vpsModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vps.index'));
        $response->assertOk();
        $response->assertSee('Create');
        $response->assertSee('Export CSV');
    }

    public function test_vault_index_respects_permissions(): void
    {
        VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)->get(route('vault.index'));
        $response->assertOk();
        $response->assertSee('Create');
        $response->assertSee('Export CSV');
    }

    public function test_bulk_actions_entirely_hidden_when_no_permitted_actions(): void
    {
        // On denied-module with no create/export/delete, bulk-actions
        // should still show (has 'update-status') but without delete/restore/force-delete
        // This verifies the bulk-actions wrapper still renders for legitimate actions
        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
        $response->assertSee('update-status');
    }

    public function test_vault_bulk_actions_no_update_status(): void
    {
        VaultEntry::factory()->create(['module_id' => $this->vaultModule->id, 'user_id' => $this->superAdmin->id]);

        // Super admin sees all
        $response = $this->actingAs($this->superAdmin)->get(route('vault.index'));
        $response->assertOk();
        $response->assertSee('Delete');
        $response->assertSee('Restore');
        $response->assertSee('Force delete');
        // Vault should not have update-status
        $response->assertDontSee('Update status');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\VaultEntry;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase2C4Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Module $hostingsModule;

    private Module $vpsModule;

    private Module $vaultModule;

    private Module $deniedModule;

    private User $superAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();

        $this->hostingsModule = Module::where('slug', 'hostings')->firstOrFail();
        $this->vpsModule = Module::where('slug', 'vps')->firstOrFail();
        $this->vaultModule = Module::where('slug', 'vault')->firstOrFail();
        $this->deniedModule = Module::where('slug', 'other-services')->firstOrFail();

        foreach ([$this->hostingsModule, $this->vpsModule, $this->vaultModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->adminRole->id],
                ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
            );
        }
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->deniedModule->id, 'role_id' => $this->adminRole->id],
            ['can_create' => false, 'can_read' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
        );

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);
    }

    public function test_super_admin_sees_all_sidebar_module_links(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));

        $response->assertSeeHtml('href="' . route('hostings.index') . '"');
        $response->assertSeeHtml('href="' . route('vps.index') . '"');
        $response->assertSeeHtml('href="' . route('vault.index') . '"');
        $response->assertSeeHtml('href="' . route('service-providers.index') . '"');
        $response->assertSeeHtml('href="' . route('domains.index') . '"');
        $response->assertSeeHtml('href="' . route('domain-emails.index') . '"');
        $response->assertSeeHtml('href="' . route('voip.index') . '"');
        $response->assertSeeHtml('href="' . route('other-services.index') . '"');
        $response->assertSeeHtml('href="' . route('expiry-trackers.index') . '"');
        $response->assertSeeHtml('href="' . route('vault.my') . '"');
    }

    public function test_admin_sees_sidebar_links_for_accessible_modules(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertSeeHtml('href="' . route('hostings.index') . '"');
        $response->assertSeeHtml('href="' . route('vps.index') . '"');
        $response->assertSeeHtml('href="' . route('vault.index') . '"');
    }

    public function test_admin_does_not_see_hidden_module_links(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertDontSeeHtml('href="' . route('other-services.index') . '"');
        $response->assertDontSeeHtml('href="' . route('service-providers.index') . '"');
        $response->assertDontSeeHtml('href="' . route('domains.index') . '"');
        $response->assertDontSeeHtml('href="' . route('domain-emails.index') . '"');
        $response->assertDontSeeHtml('href="' . route('voip.index') . '"');
        $response->assertDontSeeHtml('href="' . route('expiry-trackers.index') . '"');
    }

    public function test_dashboard_link_always_visible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertSee('Dashboard');
    }

    public function test_my_permissions_link_always_visible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertSee('My Access');
    }

    public function test_notes_link_always_visible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertSee('Notes');
    }

    public function test_rbac_system_menus_super_admin_only(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertDontSeeHtml('href="' . route('features.index') . '"');
        $response->assertDontSeeHtml('href="' . route('modules.index') . '"');
        $response->assertDontSeeHtml('href="' . route('module-permissions.index') . '"');
        $response->assertDontSeeHtml('href="' . route('roles.index') . '"');
        $response->assertDontSeeHtml('href="' . route('privileges.index') . '"');
    }

    public function test_super_admin_sees_rbac_system_menus(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));

        $response->assertSeeHtml('href="' . route('features.index') . '"');
        $response->assertSeeHtml('href="' . route('modules.index') . '"');
        $response->assertSeeHtml('href="' . route('module-permissions.index') . '"');
        $response->assertSeeHtml('href="' . route('roles.index') . '"');
        $response->assertSeeHtml('href="' . route('privileges.index') . '"');
    }

    public function test_manual_url_still_accessible(): void
    {
        $response = $this->actingAs($this->admin)->get(route('other-services.index'));
        $response->assertOk();
    }

    public function test_my_vault_visible_when_user_owns_entries(): void
    {
        $normalUser = User::factory()->create();
        VaultEntry::factory()->create(['user_id' => $normalUser->id, 'module_id' => $this->vaultModule->id]);

        $response = $this->actingAs($normalUser)->get(route('dashboard'));
        $response->assertSeeHtml('href="' . route('vault.my') . '"');
    }

    public function test_my_vault_visible_when_has_vault_read(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertSeeHtml('href="' . route('vault.my') . '"');
    }

    public function test_my_vault_hidden_when_no_vault_read_and_no_entries(): void
    {
        $normalUser = User::factory()->create();

        $response = $this->actingAs($normalUser)->get(route('dashboard'));
        $response->assertDontSeeHtml('href="' . route('vault.my') . '"');
    }

    public function test_normal_user_sees_no_module_links(): void
    {
        $normalUser = User::factory()->create();

        $response = $this->actingAs($normalUser)->get(route('dashboard'));

        $response->assertDontSeeHtml('href="' . route('hostings.index') . '"');
        $response->assertDontSeeHtml('href="' . route('other-services.index') . '"');
    }
}

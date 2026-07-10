<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase2C5Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Module $domainsModule;

    private Module $hostingsModule;

    private Module $vpsModule;

    private Module $voipModule;

    private Module $vaultModule;

    private User $superAdmin;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();

        $this->domainsModule = Module::where('slug', 'domains')->firstOrFail();
        $this->hostingsModule = Module::where('slug', 'hostings')->firstOrFail();
        $this->vpsModule = Module::where('slug', 'vps')->firstOrFail();
        $this->voipModule = Module::where('slug', 'voip')->firstOrFail();
        $this->vaultModule = Module::where('slug', 'vault')->firstOrFail();

        foreach ([$this->domainsModule, $this->hostingsModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->adminRole->id],
                ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
            );
        }
        foreach ([$this->vpsModule, $this->voipModule, $this->vaultModule] as $m) {
            ModuleRolePermission::updateOrCreate(
                ['module_id' => $m->id, 'role_id' => $this->adminRole->id],
                ['can_create' => false, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
            );
        }

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);
    }

    public function test_super_admin_sees_all_quick_actions(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('dashboard'));

        $response->assertSee('+ Feature');
        $response->assertSee('+ Module');
        $response->assertSee('+ Task');
        $response->assertSee('+ Domain');
        $response->assertSee('+ Hosting');
        $response->assertSee('+ VPS');
        $response->assertSee('+ VoIP');
        $response->assertSee('+ Vault Entry');
        $response->assertSee('+ User');
    }

    public function test_admin_sees_create_buttons_for_accessible_modules(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertSee('+ Domain');
        $response->assertSee('+ Hosting');
    }

    public function test_admin_does_not_see_create_buttons_without_permission(): void
    {
        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertDontSee('+ Feature');
        $response->assertDontSee('+ Module');
        $response->assertDontSee('+ VPS');
        $response->assertDontSee('+ VoIP');
        $response->assertDontSee('+ Vault Entry');
        $response->assertDontSee('+ User');
    }

    public function test_override_true_shows_button_when_role_denies(): void
    {
        UserModulePermission::updateOrCreate(
            ['user_id' => $this->admin->id, 'module_id' => $this->vpsModule->id],
            ['can_create' => true]
        );

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertSee('+ VPS');
    }

    public function test_override_false_hides_button_when_role_allows(): void
    {
        UserModulePermission::updateOrCreate(
            ['user_id' => $this->admin->id, 'module_id' => $this->domainsModule->id],
            ['can_create' => false]
        );

        $response = $this->actingAs($this->admin)->get(route('dashboard'));
        $response->assertDontSee('+ Domain');
    }

    public function test_normal_user_sees_only_task_button(): void
    {
        $normalUser = User::factory()->create();

        $response = $this->actingAs($normalUser)->get(route('dashboard'));

        $response->assertSee('+ Task');
        $response->assertDontSee('+ Feature');
        $response->assertDontSee('+ Module');
        $response->assertDontSee('+ Domain');
        $response->assertDontSee('+ Hosting');
        $response->assertDontSee('+ VPS');
        $response->assertDontSee('+ VoIP');
        $response->assertDontSee('+ Vault Entry');
        $response->assertDontSee('+ User');
    }

    public function test_dashboard_still_loads_for_all_authenticated_users(): void
    {
        $normalUser = User::factory()->create();

        $response = $this->actingAs($normalUser)->get(route('dashboard'));
        $response->assertOk();
        $response->assertSee('Dashboard');
    }
}

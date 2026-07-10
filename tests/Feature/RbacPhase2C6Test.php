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

class RbacPhase2C6Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Module $domainsModule;

    private Module $hostingsModule;

    private Module $vpsModule;

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

        $this->domainsModule = Module::where('slug', 'domains')->firstOrFail();
        $this->hostingsModule = Module::where('slug', 'hostings')->firstOrFail();
        $this->vpsModule = Module::where('slug', 'vps')->firstOrFail();

        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->domainsModule->id, 'role_id' => $this->adminRole->id],
            ['can_create' => true, 'can_read' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => true, 'can_export' => true, 'can_reveal' => true, 'can_import' => true]
        );
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->hostingsModule->id, 'role_id' => $this->adminRole->id],
            ['can_create' => false, 'can_read' => true, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false, 'can_import' => false]
        );

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);

        $this->normalUser = User::factory()->create();
    }

    public function test_super_admin_can_view_inspector(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.show', $this->admin->id));

        $response->assertOk();
        $response->assertSee('Permission Matrix');
        $response->assertSee('Enterprise Permission Inspector');
    }

    public function test_non_super_admin_cannot_inspect_other_users(): void
    {
        $response = $this->actingAs($this->admin)->get(route('users.show', $this->normalUser->id));

        $response->assertForbidden();
    }

    public function test_summary_cards_visible(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.show', $this->admin->id));

        $response->assertSee('Roles');
        $response->assertSee('Accessible Modules');
        $response->assertSee('Denied Modules');
        $response->assertSee('Overrides');
        $response->assertSee('Allowed Permissions');
        $response->assertSee('Denied Permissions');
    }

    public function test_summary_counts_correct(): void
    {
        $totalModules = Module::count();
        $overridesCount = 1;

        UserModulePermission::updateOrCreate(
            ['user_id' => $this->admin->id, 'module_id' => $this->domainsModule->id],
            ['can_create' => false]
        );

        $response = $this->actingAs($this->superAdmin)->get(route('users.show', $this->admin->id));

        $response->assertSee('1'); // roles count
        $response->assertSee('2'); // accessible modules (domains + hostings both have can_read=true)
        $response->assertSee((string) ($totalModules - 2)); // denied modules
        $response->assertSee((string) $overridesCount); // overrides
    }

    public function test_all_modules_displayed(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.show', $this->admin->id));

        $totalModules = Module::count();
        $moduleNames = Module::pluck('name')->toArray();

        foreach ($moduleNames as $name) {
            $response->assertSee($name);
        }
    }

    public function test_role_badge_displayed(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.show', $this->admin->id));

        $response->assertSee('Role');
    }

    public function test_override_badge_displayed(): void
    {
        UserModulePermission::updateOrCreate(
            ['user_id' => $this->admin->id, 'module_id' => $this->domainsModule->id],
            ['can_create' => false]
        );

        $response = $this->actingAs($this->superAdmin)->get(route('users.show', $this->admin->id));

        $response->assertSee('Override Deny');
    }

    public function test_none_badge_displayed(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.show', $this->admin->id));

        $response->assertSee('None');
    }

    public function test_super_admin_banner_displayed(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('users.show', $this->superAdmin->id));

        $response->assertSee('unrestricted access');
        $response->assertSee('Super Admin');
    }

    public function test_existing_my_permissions_page_unchanged(): void
    {
        $response = $this->actingAs($this->normalUser)->get(route('my-permissions'));

        $response->assertOk();
        $response->assertSee('My Permissions');
        $response->assertSee('Module Permissions');
    }
}

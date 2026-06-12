<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulePermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Module $module;
    private Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);
        $this->module = Module::firstOrFail();
        $this->userRole = Role::where('slug', 'user')->firstOrFail();
    }

    public function test_index_lists_permissions_for_module()
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/modules/{$this->module->id}/permissions");

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
        $permissions = collect($response->json('data'));
        $userPerm = $permissions->firstWhere('role_id', $this->userRole->id);
        $this->assertNotNull($userPerm);
        $this->assertTrue($userPerm['can_read']);
    }

    public function test_store_creates_permission()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/modules/{$this->module->id}/permissions", [
                'role_id' => $this->userRole->id,
                'can_create' => true,
                'can_read' => true,
                'can_update' => false,
                'can_delete' => false,
                'can_approve' => false,
                'can_export' => false,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.can_create', true)
            ->assertJsonPath('data.can_read', true);

        $this->assertDatabaseHas('module_role_permissions', [
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_create' => true,
        ]);
    }

    public function test_store_updates_existing_permission()
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => false,
        ]);

        $this->actingAs($this->admin)
            ->postJson("/api/modules/{$this->module->id}/permissions", [
                'role_id' => $this->userRole->id,
                'can_read' => true,
            ])
            ->assertOk()
            ->assertJsonPath('data.can_read', true);
    }

    public function test_store_validation()
    {
        $this->actingAs($this->admin)
            ->postJson("/api/modules/{$this->module->id}/permissions", [])
            ->assertJsonValidationErrorFor('role_id');
    }

    public function test_destroy_removes_role_permissions()
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
        ]);

        $this->actingAs($this->admin)
            ->deleteJson("/api/modules/{$this->module->id}/permissions/{$this->userRole->id}")
            ->assertOk()
            ->assertJsonPath('message', 'Role permissions removed');

        $this->assertDatabaseMissing('module_role_permissions', [
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
        ]);
    }

    public function test_user_permissions_for_module()
    {
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->module->id, 'role_id' => $role->id],
            ['can_read' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true]
        );

        $response = $this->actingAs($this->admin)
            ->getJson("/api/modules/{$this->module->id}/my-permissions");

        $response->assertOk();
        $this->assertTrue($response->json('data.can_read'));
        $this->assertTrue($response->json('data.can_create'));
    }

    public function test_user_all_permissions()
    {
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->module->id, 'role_id' => $role->id],
            ['can_read' => true]
        );

        $response = $this->actingAs($this->admin)
            ->getJson('/api/my/module-permissions');

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_user_all_permissions_for_specific_user()
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole($this->userRole);

        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson("/api/users/{$otherUser->id}/module-permissions");

        $response->assertOk();
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_admin_routes_require_super_admin()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/modules/{$this->module->id}/permissions")
            ->assertStatus(403);
    }

    public function test_user_routes_require_auth()
    {
        $this->getJson("/api/modules/{$this->module->id}/my-permissions")->assertUnauthorized();
        $this->getJson('/api/my/module-permissions')->assertUnauthorized();
    }

    public function test_admin_routes_require_auth()
    {
        $this->getJson("/api/modules/{$this->module->id}/permissions")->assertUnauthorized();
        $this->postJson("/api/modules/{$this->module->id}/permissions", [])->assertUnauthorized();
        $this->deleteJson("/api/modules/{$this->module->id}/permissions/1")->assertUnauthorized();
    }
}

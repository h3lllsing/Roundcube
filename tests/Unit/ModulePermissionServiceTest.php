<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Services\ModulePermissionService;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModulePermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    private ModulePermissionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
        $this->service = app(ModulePermissionService::class);
    }

    public function test_get_for_module_returns_mapped_permissions(): void
    {
        $module = Module::firstOrFail();
        $role = Role::where('slug', 'super-admin')->firstOrFail();

        $result = $this->service->getForModule($module);

        $this->assertNotEmpty($result);
        $first = $result[0];
        $this->assertNotNull($first);
        $this->assertArrayHasKey('role_name', $first);
        $this->assertArrayHasKey('can_read', $first);
    }

    public function test_set_for_role_creates_permission(): void
    {
        $module = Module::firstOrFail();
        $role = Role::where('slug', 'user')->firstOrFail();

        $result = $this->service->setForRole($module, $role->id, [
            'can_read' => true,
            'can_create' => false,
        ]);

        $this->assertTrue($result->can_read);
        $this->assertFalse($result->can_create);
        $this->assertDatabaseHas('module_role_permissions', [
            'module_id' => $module->id,
            'role_id' => $role->id,
            'can_read' => true,
        ]);
    }

    public function test_set_for_role_updates_existing(): void
    {
        $module = Module::firstOrFail();
        $role = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $role->id,
            'can_read' => false,
            'can_write' => false,
        ]);

        $result = $this->service->setForRole($module, $role->id, [
            'can_read' => true,
        ]);

        $this->assertTrue($result->can_read);
        $this->assertCount(1, ModuleRolePermission::where('module_id', $module->id)->where('role_id', $role->id)->get());
    }

    public function test_remove_for_role_deletes_permission(): void
    {
        $module = Module::firstOrFail();
        $role = Role::where('slug', 'user')->firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $role->id,
            'can_read' => true,
        ]);

        $this->service->removeForRole($module, $role->id);

        $this->assertDatabaseMissing('module_role_permissions', [
            'module_id' => $module->id,
            'role_id' => $role->id,
        ]);
    }

    public function test_get_user_permissions_for_module_merges_multiple_roles(): void
    {
        $module = Module::firstOrFail();
        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        ModuleRolePermission::updateOrCreate(
            ['module_id' => $module->id, 'role_id' => $adminRole->id],
            ['can_read' => true, 'can_create' => true, 'can_update' => false]
        );
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $module->id, 'role_id' => $userRole->id],
            ['can_read' => true, 'can_update' => true]
        );

        $admin = User::factory()->create();
        $admin->assignRole($adminRole);
        $admin->assignRole($userRole);

        $perms = $this->service->getUserPermissionsForModule($module, $admin);

        $this->assertNotNull($perms);
        $this->assertTrue($perms['can_read']);
        $this->assertTrue($perms['can_create']);
        $this->assertTrue($perms['can_update']);
    }

    public function test_get_user_permissions_returns_null_when_no_perms(): void
    {
        $module = Module::firstOrFail();
        $user = User::factory()->create();

        $perms = $this->service->getUserPermissionsForModule($module, $user);

        $this->assertNull($perms);
    }
}

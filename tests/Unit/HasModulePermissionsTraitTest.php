<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\VaultEntry;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HasModulePermissionsTraitTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Module $module;
    private Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
        $this->userRole = Role::where('slug', 'user')->firstOrFail();
        $this->user = User::factory()->create();
        $this->user->assignRole($this->userRole);
        $this->module = Module::firstOrFail();
    }

    public function test_can_on_module_returns_true_when_permission_exists(): void
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
        ]);

        $this->assertTrue($this->user->canOnModule($this->module, 'read'));
    }

    public function test_can_on_module_returns_false_when_permission_missing(): void
    {
        $this->assertFalse($this->user->canOnModule($this->module, 'read'));
    }

    public function test_can_on_module_returns_false_when_permission_not_set(): void
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => false,
        ]);

        $this->assertFalse($this->user->canOnModule($this->module, 'read'));
    }

    public function test_get_module_permissions_returns_array(): void
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
            'can_create' => true,
        ]);

        $result = $this->user->getModulePermissions($this->module);

        $this->assertIsArray($result);
        $this->assertTrue($result['can_read']);
        $this->assertTrue($result['can_create']);
        $this->assertFalse($result['can_update']);
        $this->assertFalse($result['can_delete']);
        $this->assertFalse($result['can_approve']);
        $this->assertFalse($result['can_export']);
    }

    public function test_get_module_permissions_returns_null_when_none(): void
    {
        $result = $this->user->getModulePermissions($this->module);

        $this->assertNull($result);
    }

    public function test_get_all_module_permissions_returns_merged_permissions(): void
    {
        $module2 = Module::factory()->create(['feature_id' => $this->module->feature_id]);

        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
        ]);
        ModuleRolePermission::create([
            'module_id' => $module2->id,
            'role_id' => $this->userRole->id,
            'can_create' => true,
        ]);

        $result = $this->user->getAllModulePermissions();

        $this->assertArrayHasKey($this->module->id, $result);
        $this->assertArrayHasKey($module2->id, $result);
        $this->assertTrue($result[$this->module->id]['can_read']);
        $this->assertTrue($result[$module2->id]['can_create']);
    }

    public function test_get_accessible_module_ids_returns_matching_modules(): void
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
        ]);

        $ids = $this->user->getAccessibleModuleIds('read');

        $this->assertContains($this->module->id, $ids);
    }

    public function test_get_accessible_module_ids_excludes_without_permission(): void
    {
        $ids = $this->user->getAccessibleModuleIds('read');

        $this->assertNotContains($this->module->id, $ids);
    }

    public function test_can_access_vault_returns_true_for_owner(): void
    {
        $vault = VaultEntry::factory()->create(['user_id' => $this->user->id]);

        $this->assertTrue($this->user->canAccessVault($vault));
    }

    public function test_can_access_vault_returns_true_for_super_admin(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $admin = User::factory()->create();
        $admin->assignRole($superAdminRole);
        $vault = VaultEntry::factory()->create();

        $this->assertTrue($admin->canAccessVault($vault));
    }

    public function test_can_access_vault_returns_true_with_module_permission(): void
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
        ]);
        $vault = VaultEntry::factory()->create(['module_id' => $this->module->id]);

        $this->assertTrue($this->user->canAccessVault($vault));
    }

    public function test_can_access_vault_returns_false_when_no_access(): void
    {
        $vault = VaultEntry::factory()->create();

        $this->assertFalse($this->user->canAccessVault($vault));
    }

    public function test_is_vault_owner_returns_true_for_owner(): void
    {
        $vault = VaultEntry::factory()->create(['user_id' => $this->user->id]);

        $this->assertTrue($this->user->isVaultOwner($vault));
    }

    public function test_is_vault_owner_returns_true_for_super_admin(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $admin = User::factory()->create();
        $admin->assignRole($superAdminRole);
        $vault = VaultEntry::factory()->create();

        $this->assertTrue($admin->isVaultOwner($vault));
    }

    public function test_is_vault_owner_returns_false_for_non_owner(): void
    {
        $vault = VaultEntry::factory()->create();

        $this->assertFalse($this->user->isVaultOwner($vault));
    }
}

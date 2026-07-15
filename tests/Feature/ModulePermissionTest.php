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

class ModulePermissionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Module $module;

    private Role $userRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
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
            ->assertJsonPath('data.can_create', true);

        $perm = ModuleRolePermission::where('module_id', $this->module->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();
        $this->assertTrue($perm->can_create);
        $this->assertEquals($this->module->id, $perm->module->id);
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

        $this->assertSoftDeleted('module_role_permissions', [
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

    public function test_store_with_can_reveal_persists_permission()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/modules/{$this->module->id}/permissions", [
                'role_id' => $this->userRole->id,
                'can_reveal' => true,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.can_reveal', true);

        $perm = ModuleRolePermission::where('module_id', $this->module->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();
        $this->assertTrue($perm->can_reveal);
    }

    public function test_store_with_can_reveal_false_sets_false()
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_reveal' => true,
        ]);

        $this->actingAs($this->admin)
            ->postJson("/api/modules/{$this->module->id}/permissions", [
                'role_id' => $this->userRole->id,
                'can_reveal' => false,
            ])
            ->assertOk()
            ->assertJsonPath('data.can_reveal', false);
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

    public function test_store_invalid_module_id_returns_404()
    {
        $this->actingAs($this->admin)
            ->postJson('/api/modules/99999/permissions', [
                'role_id' => $this->userRole->id,
                'can_read' => true,
            ])
            ->assertNotFound();
    }

    public function test_store_invalid_role_id_returns_validation_error()
    {
        $this->actingAs($this->admin)
            ->postJson("/api/modules/{$this->module->id}/permissions", [
                'role_id' => 99999,
                'can_read' => true,
            ])
            ->assertJsonValidationErrorFor('role_id');
    }

    public function test_store_with_all_permission_keys_persists_all()
    {
        $response = $this->actingAs($this->admin)
            ->postJson("/api/modules/{$this->module->id}/permissions", [
                'role_id' => $this->userRole->id,
                'can_create' => true,
                'can_read' => true,
                'can_update' => true,
                'can_delete' => true,
                'can_approve' => true,
                'can_export' => true,
                'can_reveal' => true,
                'can_import' => true,
            ]);

        $response->assertOk();

        $perm = ModuleRolePermission::where('module_id', $this->module->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();

        $this->assertTrue($perm->can_create);
        $this->assertTrue($perm->can_read);
        $this->assertTrue($perm->can_update);
        $this->assertFalse($perm->can_delete);
        $this->assertTrue($perm->can_approve);
        $this->assertTrue($perm->can_export);
        $this->assertTrue($perm->can_reveal);
        $this->assertTrue($perm->can_import);
    }

    public function test_store_with_invalid_permission_key_returns_error()
    {
        $this->actingAs($this->admin)
            ->postJson("/api/modules/{$this->module->id}/permissions", [
                'role_id' => $this->userRole->id,
                'can_read' => true,
                'can_invalid' => true,
            ])
            ->assertOk()
            ->assertJsonMissingPath('data.can_invalid');
    }

    public function test_user_all_permissions_after_role_permission_removed()
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
        ]);

        $this->actingAs($this->admin)
            ->deleteJson("/api/modules/{$this->module->id}/permissions/{$this->userRole->id}")
            ->assertOk();

        $response = $this->actingAs($this->admin)
            ->getJson('/api/my/module-permissions');

        $response->assertOk();
        $modulePerms = collect($response->json('data'));
        $targetModule = $modulePerms->firstWhere('module_id', $this->module->id);
        $this->assertFalse($targetModule['can_read'] ?? false);
    }

    public function test_web_index_loads_global_matrix(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('module-permissions.index'));
        $response->assertStatus(200);
        $response->assertSee('Module Permissions');
        $response->assertSee($this->module->name);
        $response->assertSee($this->userRole->name);
        $response->assertDontSee('Back to');
    }

    public function test_web_index_with_role_id_filters_focused_mode(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('module-permissions.index', ['role_id' => $this->userRole->id]));
        $response->assertStatus(200);
        $response->assertSee('Permissions');
        $response->assertSee($this->userRole->name);
        $response->assertSee('Back to');
    }

    public function test_web_index_with_invalid_role_id_returns_404(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('module-permissions.index', ['role_id' => 99999]))
            ->assertStatus(404);
    }

    public function test_web_index_with_super_admin_role_returns_404(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->actingAs($this->admin);
        $this->get(route('module-permissions.index', ['role_id' => $superAdminRole->id]))
            ->assertStatus(404);
    }

    public function test_simple_mode_renders_for_focused_role(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('module-permissions.index', ['role_id' => $this->userRole->id]));
        $response->assertStatus(200);
        $response->assertSee('Simple');
        $response->assertSee('Advanced');
        $response->assertSee('Access');
        $response->assertSee('Manage');
        $response->assertSee('Import');
        $response->assertSee('Export');
        $response->assertSee('Full Access');
    }

    public function test_no_access_persists_with_all_false(): void
    {
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
        ]);

        $this->actingAs($this->admin);
        $this->post(route('module-permissions.update'), [
            'updated_at' => $this->module->updated_at->format('Y-m-d H:i:s'),
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'access' => false,
            'manage' => false,
            'import' => false,
            'export' => false,
            'full_access' => false,
        ]);

        $row = ModuleRolePermission::where('module_id', $this->module->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();
        $this->assertFalse($row->can_read);
        $this->assertFalse($row->can_create);
        $this->assertFalse($row->can_update);
        $this->assertFalse($row->can_delete);
        $this->assertFalse($row->can_approve);
        $this->assertFalse($row->can_export);
        $this->assertFalse($row->can_reveal);
        $this->assertFalse($row->can_import);
    }

    public function test_access_control_enables_read_and_reveal(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('module-permissions.update'), [
            'updated_at' => $this->module->updated_at->format('Y-m-d H:i:s'),
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'access' => true,
        ]);

        $row = ModuleRolePermission::where('module_id', $this->module->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();
        $this->assertTrue($row->can_read);
        $this->assertTrue($row->can_reveal);
        $this->assertFalse($row->can_create);
        $this->assertFalse($row->can_update);
        $this->assertFalse($row->can_delete);
        $this->assertFalse($row->can_approve);
        $this->assertFalse($row->can_export);
        $this->assertFalse($row->can_import);
    }

    public function test_manage_control_implies_access_plus_create_and_update(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('module-permissions.update'), [
            'updated_at' => $this->module->updated_at->format('Y-m-d H:i:s'),
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'access' => true,
            'manage' => true,
        ]);

        $row = ModuleRolePermission::where('module_id', $this->module->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();
        $this->assertTrue($row->can_read);
        $this->assertTrue($row->can_reveal);
        $this->assertTrue($row->can_create);
        $this->assertTrue($row->can_update);
        $this->assertFalse($row->can_delete);
        $this->assertFalse($row->can_approve);
        $this->assertFalse($row->can_export);
        $this->assertFalse($row->can_import);
    }

    public function test_combined_controls_normalize_correctly(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('module-permissions.update'), [
            'updated_at' => $this->module->updated_at->format('Y-m-d H:i:s'),
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'access' => true,
            'manage' => true,
            'export' => true,
        ]);

        $row = ModuleRolePermission::where('module_id', $this->module->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();
        $this->assertTrue($row->can_read);
        $this->assertTrue($row->can_reveal);
        $this->assertTrue($row->can_create);
        $this->assertTrue($row->can_update);
        $this->assertFalse($row->can_delete);
        $this->assertFalse($row->can_approve);
        $this->assertTrue($row->can_export);
        $this->assertFalse($row->can_import);
    }

    public function test_access_control_enables_reveal_on_sensitive_module(): void
    {
        $sensitiveModule = Module::whereIn('slug', config('permissions.sensitive_modules', []))->first();
        if (!$sensitiveModule) {
            $this->markTestSkipped('No sensitive module available');
        }

        $this->actingAs($this->admin);
        $this->post(route('module-permissions.update'), [
            'updated_at' => $sensitiveModule->updated_at->format('Y-m-d H:i:s'),
            'module_id' => $sensitiveModule->id,
            'role_id' => $this->userRole->id,
            'access' => true,
        ]);

        $row = ModuleRolePermission::where('module_id', $sensitiveModule->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();
        $this->assertTrue($row->can_read);
        $this->assertTrue($row->can_reveal);
        $this->assertFalse($row->can_delete);
    }

    public function test_advanced_mode_table_renders(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('module-permissions.index', ['role_id' => $this->userRole->id]));
        $response->assertStatus(200);
        $response->assertSee('Feature');
        $response->assertSee($this->userRole->name);
    }

    public function test_global_permissions_unchanged(): void
    {
        $this->actingAs($this->admin);
        $response = $this->get(route('module-permissions.index'));
        $response->assertStatus(200);
        $response->assertSee('Module Permissions');
        $response->assertDontSee('Back to');
        $response->assertSee($this->userRole->name);
    }

    public function test_full_access_sets_all_supported_permissions(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('module-permissions.update'), [
            'updated_at' => $this->module->updated_at->format('Y-m-d H:i:s'),
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'full_access' => true,
        ]);

        $row = ModuleRolePermission::where('module_id', $this->module->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();
        $this->assertTrue($row->can_read);
        $this->assertTrue($row->can_reveal);
        $this->assertTrue($row->can_create);
        $this->assertTrue($row->can_update);
        $this->assertFalse($row->can_delete);
        $this->assertFalse($row->can_approve);
        $this->assertTrue($row->can_export);
        $this->assertTrue($row->can_import);
    }

    public function test_full_access_never_enables_delete_or_approve(): void
    {
        $this->actingAs($this->admin);
        $this->post(route('module-permissions.update'), [
            'updated_at' => $this->module->updated_at->format('Y-m-d H:i:s'),
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'access' => true,
            'manage' => true,
            'full_access' => true,
        ]);

        $row = ModuleRolePermission::where('module_id', $this->module->id)
            ->where('role_id', $this->userRole->id)
            ->firstOrFail();
        $this->assertTrue($row->can_read);
        $this->assertTrue($row->can_reveal);
        $this->assertTrue($row->can_create);
        $this->assertTrue($row->can_update);
        $this->assertFalse($row->can_delete);
        $this->assertFalse($row->can_approve);
        $this->assertTrue($row->can_export);
        $this->assertTrue($row->can_import);
    }

    public function test_user_overrides_untouched_when_role_baseline_changes(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);

        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => false,
            'can_export' => false,
            'can_reveal' => false,
            'can_import' => false,
        ]);

        $this->actingAs($this->admin);
        $this->post(route('module-permissions.update'), [
            'updated_at' => $this->module->updated_at->format('Y-m-d H:i:s'),
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'access' => true,
        ]);

        $override = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $this->module->id)
            ->firstOrFail();
        $this->assertFalse($override->can_read);
        $this->assertFalse($override->can_create);
    }
}

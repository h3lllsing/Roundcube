<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    public function test_create_module_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/features/{$feature->id}/modules", [
                'name' => 'New Module',
                'slug' => 'new-module',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'slug']]);
    }

    public function test_list_modules()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/features/{$feature->id}/modules");

        $response->assertStatus(200);
    }

    public function test_show_module()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/modules/{$module->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name']]);

        $this->assertCount(0, $module->tasks);
    }

    public function test_delete_module_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/modules/{$module->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Module deleted']);
    }

    public function test_module_search()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();
        $feature->modules()->createMany([
            ['name' => 'UniqueModule', 'slug' => 'unique-module', 'feature_id' => $feature->id],
            ['name' => 'OtherModule', 'slug' => 'other-module', 'feature_id' => $feature->id],
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/features/{$feature->id}/modules?search=Unique");

        $response->assertStatus(200);
        $this->assertStringContainsString('UniqueModule', $response->getContent());
        $this->assertStringNotContainsString('OtherModule', $response->getContent());
    }

    public function test_my_module_permissions_all_modules()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/my/module-permissions');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['module_id', 'module_name', 'permissions']]]);
    }

    public function test_my_module_permissions_single_module()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/modules/{$module->id}/my-permissions");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['can_create', 'can_read', 'can_update', 'can_delete', 'can_approve', 'can_export']]);
    }

    public function test_user_all_permissions_admin_endpoint()
    {
        $admin = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $admin->assignRole($role);
        $adminToken = $admin->createToken('test')->plainTextToken;

        $targetUser = User::factory()->create();
        $targetUser->assignRole($role);

        $response = $this->withHeader('Authorization', "Bearer $adminToken")
            ->getJson("/api/users/{$targetUser->id}/module-permissions");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => [['module_id', 'module_name', 'permissions']]]);
    }

    public function test_update_module_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/modules/{$module->id}", [
                'name' => 'Updated Module',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name']]);
        $this->assertDatabaseHas('modules', ['id' => $module->id, 'name' => 'Updated Module']);
    }

    public function test_store_module_permission()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();
        $targetRole = Role::where('slug', 'admin')->firstOrFail();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/modules/{$module->id}/permissions", [
                'role_id' => $targetRole->id,
                'can_read' => true,
                'can_create' => false,
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('module_role_permissions', [
            'module_id' => $module->id,
            'role_id' => $targetRole->id,
            'can_read' => true,
            'can_create' => false,
        ]);
    }

    public function test_delete_module_permission()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();
        $targetRole = Role::where('slug', 'admin')->firstOrFail();

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/modules/{$module->id}/permissions", [
                'role_id' => $targetRole->id, 'can_read' => true,
            ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/modules/{$module->id}/permissions/{$targetRole->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Role permissions removed']);
        $this->assertSoftDeleted('module_role_permissions', [
            'module_id' => $module->id, 'role_id' => $targetRole->id,
        ]);
    }

    public function test_list_module_permissions()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/modules/{$module->id}/permissions");

        $response->assertStatus(200);
    }

    public function test_non_super_admin_cannot_create_module()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/features/{$feature->id}/modules", [
                'name' => 'Should Fail',
                'slug' => 'should-fail',
            ]);

        $response->assertStatus(403);
    }

    public function test_non_super_admin_cannot_delete_module()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/modules/{$module->id}");

        $response->assertStatus(403);
    }

    public function test_module_with_trashed_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $module = new Module;
        $module->feature_id = $feature->id;
        $module->name = 'Trash Module';
        $module->slug = 'trash-module';
        $module->save();
        $module->delete();
        Cache::flush();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/features/{$feature->id}/modules?with_trashed=1");

        $response->assertStatus(200);
        $this->assertStringContainsString('Trash Module', $response->getContent());
    }

    public function test_non_super_admin_cannot_update_module()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/modules/{$module->id}", ['name' => 'Hacked']);

        $response->assertStatus(403);
    }

    public function test_update_nonexistent_module_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/modules/99999', ['name' => 'Ghost']);

        $response->assertStatus(404);
    }

    public function test_non_super_admin_cannot_list_module_permissions()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/modules/{$module->id}/permissions");

        $response->assertStatus(403);
    }

    public function test_non_super_admin_cannot_delete_module_permission()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();
        $targetRole = Role::where('slug', 'admin')->firstOrFail();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/modules/{$module->id}/permissions/{$targetRole->id}");

        $response->assertStatus(403);
    }

    public function test_show_nonexistent_module_returns_404()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/modules/99999');

        $response->assertStatus(404);
    }

    public function test_store_permission_validation_missing_role_id()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/modules/{$module->id}/permissions", [
                'can_read' => true,
            ]);

        $response->assertStatus(422);
    }

    public function test_delete_nonexistent_module_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/modules/99999');

        $response->assertStatus(404);
    }
}

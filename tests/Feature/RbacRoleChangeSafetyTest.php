<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\User;
use App\Models\UserModulePermission;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RbacRoleChangeSafetyTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Role $userRole;
    private Role $adminRole;
    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->userRole = Role::where('slug', 'user')->firstOrFail();

        $this->admin = User::factory()->create();
        $this->admin->roles()->sync([$superRole->id]);

        $this->module = Module::firstOrFail();
    }

    /** @test */
    public function role_sync_increments_perms_generation(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);

        $before = Cache::get('perms_generation', 0);

        $user->roles()->sync([$this->adminRole->id]);

        $after = Cache::get('perms_generation', 0);
        $this->assertGreaterThan($before, $after);
    }

    /** @test */
    public function assign_role_increments_perms_generation(): void
    {
        $user = User::factory()->create();

        $before = Cache::get('perms_generation', 0);

        $user->assignRole($this->userRole);

        $after = Cache::get('perms_generation', 0);
        $this->assertGreaterThan($before, $after);
    }

    /** @test */
    public function remove_role_increments_perms_generation(): void
    {
        $user = User::factory()->create();
        $user->assignRole($this->userRole);

        $before = Cache::get('perms_generation', 0);

        $user->removeRole($this->userRole);

        $after = Cache::get('perms_generation', 0);
        $this->assertGreaterThan($before, $after);
    }

    /** @test */
    public function effective_permissions_refresh_after_role_change(): void
    {
        $roleB = Role::create(['name' => 'Role B', 'slug' => 'role-b']);

        \App\Models\ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => false,
            'can_export' => false,
            'can_reveal' => false,
            'can_import' => false,
        ]);

        \App\Models\ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $roleB->id,
            'can_read' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => false,
            'can_approve' => false,
            'can_export' => false,
            'can_reveal' => false,
            'can_import' => false,
        ]);

        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);

        $this->assertFalse($user->canOnModule($this->module, 'read'));

        $user->roles()->sync([$roleB->id]);
        $user = $user->fresh();

        $this->assertTrue($user->canOnModule($this->module, 'read'));
        $this->assertTrue($user->canOnModule($this->module, 'create'));
    }

    /** @test */
    public function user_overrides_survive_role_change(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);

        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => false,
            'can_export' => false,
            'can_reveal' => false,
            'can_import' => false,
        ]);

        $overrideBefore = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $this->module->id)
            ->first();
        $this->assertNotNull($overrideBefore);

        $user->roles()->sync([$this->adminRole->id]);

        $overrideAfter = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $this->module->id)
            ->first();
        $this->assertNotNull($overrideAfter);
        $this->assertTrue((bool) $overrideAfter->can_read);
        $this->assertFalse((bool) $overrideAfter->can_create);
        $this->assertEquals($overrideBefore->id, $overrideAfter->id);
    }

    /** @test */
    public function role_change_with_overrides_requires_confirmation(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);

        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->id), [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$this->adminRole->id],
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors('confirm_role_change');

        $this->assertTrue($user->roles()->where('roles.id', $this->adminRole->id)->doesntExist());
    }

    /** @test */
    public function role_change_with_overrides_succeeds_when_confirmed(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);

        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->id), [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$this->adminRole->id],
                'confirm_role_change' => '1',
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHas('success');

        $this->assertTrue($user->fresh()->roles()->where('roles.id', $this->adminRole->id)->exists());

        $override = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $this->module->id)
            ->first();
        $this->assertNotNull($override);
        $this->assertTrue((bool) $override->can_read);
    }

    /** @test */
    public function role_change_without_overrides_does_not_require_confirmation(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->id), [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$this->adminRole->id],
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHas('success');

        $this->assertTrue($user->fresh()->roles()->where('roles.id', $this->adminRole->id)->exists());
    }

    /** @test */
    public function non_role_user_edit_does_not_require_confirmation(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);

        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->id), [
                'name' => 'Updated Name',
                'email' => $user->email,
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHas('success');

        $this->assertEquals('Updated Name', $user->fresh()->name);
    }

    /** @test */
    public function role_change_where_roles_unchanged_does_not_require_confirmation(): void
    {
        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id]);

        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $this->module->id,
            'can_read' => true,
        ]);

        $this->actingAs($this->admin)
            ->put(route('users.update', $user->id), [
                'name' => $user->name,
                'email' => $user->email,
                'roles' => [$this->userRole->id],
                'updated_at' => $user->updated_at->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHas('success');
    }

    /** @test */
    public function multiple_roles_remain_or_merged_after_role_change(): void
    {
        $roleB = Role::create(['name' => 'Role B', 'slug' => 'role-b']);

        \App\Models\ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $this->userRole->id,
            'can_read' => true,
            'can_create' => false,
        ]);

        \App\Models\ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $roleB->id,
            'can_read' => false,
            'can_create' => true,
        ]);

        $user = User::factory()->create();
        $user->roles()->sync([$this->userRole->id, $roleB->id]);
        $user = $user->fresh();

        $this->assertTrue($user->canOnModule($this->module, 'read'));
        $this->assertTrue($user->canOnModule($this->module, 'create'));

        $user->roles()->sync([$roleB->id]);
        $user = $user->fresh();

        $this->assertFalse($user->canOnModule($this->module, 'read'));
        $this->assertTrue($user->canOnModule($this->module, 'create'));
    }
}

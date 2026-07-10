<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use Database\Seeders\FeatureModuleSeeder;
use Database\Seeders\RoleTemplateSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BetterCreateUserTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $admin;
    private Role $adminRole;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
        $this->seed(RoleTemplateSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();

        $this->superAdmin = User::where('email', 'admin@tyro.project')->firstOrFail();
        $this->superAdmin->roles()->sync([$superRole->id]);

        $this->admin = User::factory()->create(['name' => 'Admin User', 'email' => 'admin@example.com']);
        $this->admin->roles()->sync([$this->adminRole->id]);
    }

    // 1 — Basic user creation
    public function test_create_user_basic_only(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'New User',
                'email' => 'newuser@example.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
                'status' => 'active',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);
        $user = User::where('email', 'newuser@example.com')->firstOrFail();
        $this->assertNull($user->suspended_at);
    }

    // 2 — Role assignment
    public function test_create_user_with_roles(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'Role User',
                'email' => 'roleuser@example.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
                'status' => 'active',
                'roles' => [$this->adminRole->id],
            ])
            ->assertSessionHas('success');

        $user = User::where('email', 'roleuser@example.com')->firstOrFail();
        $this->assertTrue($user->roles->contains($this->adminRole->id));
    }

    // 3 — Clone user
    public function test_create_user_with_clone(): void
    {
        $source = User::factory()->create(['name' => 'Source User', 'email' => 'source@example.com']);
        $source->roles()->sync([$this->adminRole->id]);

        $domainModule = Module::where('slug', 'domains')->firstOrFail();
        UserModulePermission::create([
            'user_id' => $source->id,
            'module_id' => $domainModule->id,
            'can_read' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => false,
            'can_export' => false,
            'can_reveal' => false,
            'can_import' => false,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'Cloned User',
                'email' => 'cloned@example.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
                'status' => 'active',
                'clone_user_id' => $source->id,
                'copy_roles' => '1',
                'copy_overrides' => '1',
                'clone_role_handling' => 'use_cloned',
            ])
            ->assertSessionHas('success');

        $newUser = User::where('email', 'cloned@example.com')->firstOrFail();
        $this->assertTrue($newUser->roles->contains($this->adminRole->id));

        $override = UserModulePermission::where('user_id', $newUser->id)
            ->where('module_id', $domainModule->id)
            ->first();
        $this->assertNotNull($override);
        $this->assertFalse((bool) $override->can_read);
    }

    // 4 — Clone status
    public function test_create_user_with_clone_status(): void
    {
        $source = User::factory()->create(['name' => 'Suspended Source', 'email' => 'suspended@example.com']);
        $source->suspended_at = now();
        $source->save();

        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'Cloned Status',
                'email' => 'cloned-status@example.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
                'status' => 'active',
                'clone_user_id' => $source->id,
                'copy_status' => '1',
                'clone_role_handling' => 'use_cloned',
            ])
            ->assertSessionHas('success');

        $newUser = User::where('email', 'cloned-status@example.com')->firstOrFail();
        $this->assertNotNull($newUser->suspended_at);

        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'Cloned No Status',
                'email' => 'cloned-nostatus@example.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
                'status' => 'active',
                'clone_user_id' => $source->id,
                'clone_role_handling' => 'use_cloned',
            ])
            ->assertSessionHas('success');

        $newUser2 = User::where('email', 'cloned-nostatus@example.com')->firstOrFail();
        $this->assertNull($newUser2->suspended_at);
    }

    // 5 — Password never copied
    public function test_password_never_copied(): void
    {
        $source = User::factory()->create([
            'name' => 'Password Source',
            'email' => 'pass-source@example.com',
            'password' => bcrypt('original_password'),
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'New Pass User',
                'email' => 'newpass@example.com',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
                'status' => 'active',
                'clone_user_id' => $source->id,
                'copy_roles' => '1',
                'clone_role_handling' => 'use_cloned',
            ])
            ->assertSessionHas('success');

        $newUser = User::where('email', 'newpass@example.com')->firstOrFail();
        $this->assertTrue(password_verify('NewPass123', $newUser->password));
        $this->assertFalse(password_verify('original_password', $newUser->password));
    }

    // 6 — Non super admin blocked
    public function test_non_super_admin_cannot_access_create(): void
    {
        $this->actingAs($this->admin)
            ->get(route('users.create'))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->post(route('users.store'), [
                'name' => 'Hacker',
                'email' => 'hacker@example.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
            ])
            ->assertForbidden();
    }

    // 7 — User creation never modifies ModuleRolePermission
    public function test_create_user_never_modifies_module_role_permissions(): void
    {
        $beforeCount = ModuleRolePermission::count();

        $this->actingAs($this->superAdmin)
            ->post(route('users.store'), [
                'name' => 'Safe User',
                'email' => 'safe@example.com',
                'password' => 'Pass12345',
                'password_confirmation' => 'Pass12345',
                'status' => 'active',
                'roles' => [$this->adminRole->id],
            ])
            ->assertSessionHas('success');

        $this->assertEquals($beforeCount, ModuleRolePermission::count(),
            'User creation should never create or modify ModuleRolePermission rows.');
    }

    // 8 — Edit page does NOT show full permission matrix
    public function test_edit_page_shows_override_card_not_matrix(): void
    {
        $user = User::factory()->create(['name' => 'Edit Test', 'email' => 'edit-test@example.com']);
        $response = $this->actingAs($this->superAdmin)
            ->get(route('users.edit', $user->id))
            ->assertOk();

        $response->assertSee('Permission Overrides');
        $response->assertSee('Configure Overrides');
        $response->assertSee('View Effective Permissions');
        $response->assertDontSee('Special Per-User Permissions');
    }

    // 9 — Configure Overrides link exists on edit page
    public function test_configure_overrides_link_goes_to_permissions_page(): void
    {
        $user = User::factory()->create(['name' => 'Perm Test', 'email' => 'perm-test@example.com']);
        $response = $this->actingAs($this->superAdmin)
            ->get(route('users.edit', $user->id))
            ->assertOk();

        $response->assertSee(route('users.permissions.edit', $user->id));
    }

    // 10 — Permission override page loads with matrix
    public function test_permission_override_page_loads(): void
    {
        $user = User::factory()->create(['name' => 'Override Test', 'email' => 'override-test@example.com']);
        $response = $this->actingAs($this->superAdmin)
            ->get(route('users.permissions.edit', $user->id))
            ->assertOk();

        $response->assertSee('Module Permissions');
        $response->assertSee('Save Overrides');
        $response->assertSee('Cancel');
        $response->assertSee($user->name);
        $response->assertSee($user->email);
    }

    // 11 — Saving basic user info does not modify overrides
    public function test_saving_user_info_preserves_overrides(): void
    {
        $user = User::factory()->create(['name' => 'Preserve Test', 'email' => 'preserve@example.com']);
        $module = Module::firstOrFail();

        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'can_read' => false,
        ]);

        $this->actingAs($this->superAdmin)
            ->put(route('users.update', $user->id), [
                'name' => 'Updated Name',
                'email' => 'preserve@example.com',
            ])
            ->assertSessionHas('success');

        $override = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();

        $this->assertNotNull($override);
        $this->assertFalse((bool) $override->can_read);
    }

    // 12 — Saving override page updates user_module_permissions
    public function test_saving_overrides_updates_permissions(): void
    {
        $user = User::factory()->create(['name' => 'Override Save', 'email' => 'override-save@example.com']);
        $module = Module::firstOrFail();

        $this->actingAs($this->superAdmin)
            ->put(route('users.permissions.update', $user->id), [
                'permissions' => [
                    $module->id => [
                        'can_read' => '1',
                        'can_create' => '0',
                        'can_update' => '',
                        'can_delete' => '',
                        'can_approve' => '',
                        'can_export' => '',
                        'can_reveal' => '',
                        'can_import' => '',
                    ],
                ],
            ])
            ->assertSessionHas('success');

        $override = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();

        $this->assertNotNull($override);
        $this->assertTrue((bool) $override->can_read);
        $this->assertFalse((bool) $override->can_create);
        $this->assertNull($override->can_update);
    }

    // 13 — Non-authorized user cannot access override page
    public function test_non_super_admin_cannot_access_override_page(): void
    {
        $user = User::factory()->create(['name' => 'Auth Test', 'email' => 'auth-test@example.com']);

        $this->actingAs($this->admin)
            ->get(route('users.permissions.edit', $user->id))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->put(route('users.permissions.update', $user->id), [])
            ->assertForbidden();
    }

    // 14 — Saving overrides clears null-valued fields in DB
    public function test_saving_overrides_clears_previous_values(): void
    {
        $user = User::factory()->create(['name' => 'Clear Test', 'email' => 'clear-test@example.com']);
        $module = Module::firstOrFail();

        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $module->id,
            'can_read' => true,
            'can_create' => false,
            'can_update' => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->put(route('users.permissions.update', $user->id), [
                'permissions' => [
                    $module->id => [
                        'can_read' => '',
                        'can_create' => '',
                        'can_update' => '',
                        'can_delete' => '',
                        'can_approve' => '',
                        'can_export' => '',
                        'can_reveal' => '',
                        'can_import' => '',
                    ],
                ],
            ])
            ->assertSessionHas('success');

        $override = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $module->id)
            ->first();

        $this->assertNull($override, 'Row should be deleted when all values are null');
    }
}

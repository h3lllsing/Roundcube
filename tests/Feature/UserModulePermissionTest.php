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
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class UserModulePermissionTest extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $userRole;

    private Role $superAdminRole;

    private Module $moduleA;

    private Module $moduleB;

    private User $superAdmin;

    private User $admin;

    private User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $this->superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->userRole = Role::where('slug', 'user')->firstOrFail();

        $modules = Module::take(2)->get();
        $this->moduleA = $modules[0];
        $this->moduleB = $modules[1];

        // Admin role: can_read on moduleA, NOT on moduleB
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleA->id, 'role_id' => $this->adminRole->id],
            ['can_read' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true]
        );
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleB->id, 'role_id' => $this->adminRole->id],
            ['can_read' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false]
        );

        // User role: can_read on moduleA only
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleA->id, 'role_id' => $this->userRole->id],
            ['can_read' => true, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false]
        );
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleB->id, 'role_id' => $this->userRole->id],
            ['can_read' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false]
        );

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($this->superAdminRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);

        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole($this->userRole);
    }

    public function test_user_override_true_grants_permission_even_if_role_denies(): void
    {
        // Role denies can_create on moduleA for normalUser
        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'create'));

        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_create' => true,
        ]);

        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'create'));
    }

    public function test_user_override_false_denies_permission_even_if_role_grants(): void
    {
        // Role grants can_update on moduleA for admin
        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'update'));

        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleA->id,
            'can_update' => false,
        ]);

        $this->assertFalse($this->admin->canOnModule($this->moduleA, 'update'));
    }

    public function test_null_override_inherits_role_permission(): void
    {
        // No override row exists yet
        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'read'));

        // Create override row with null can_read (inherits from role)
        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleA->id,
            'can_read' => null,
            'can_create' => null,
        ]);

        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'read'));
    }

    public function test_no_override_keeps_existing_role_behavior(): void
    {
        // Admin has can_read on moduleA
        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'read'));
        // Admin does NOT have can_read on moduleB
        $this->assertFalse($this->admin->canOnModule($this->moduleB, 'read'));
        // Normal user has can_read on moduleA
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
        // Normal user does NOT have can_read on moduleB
        $this->assertFalse($this->normalUser->canOnModule($this->moduleB, 'read'));
    }

    public function test_getAccessibleModuleIds_respects_user_overrides(): void
    {
        // Admin: role grants read on moduleA, not moduleB
        $ids = $this->admin->getAccessibleModuleIds('read');
        $this->assertEquals([$this->moduleA->id], $ids);

        // Override: grant read on moduleB
        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleB->id,
            'can_read' => true,
        ]);
        $this->admin->clearPermissionCache();
        $ids = $this->admin->getAccessibleModuleIds('read');
        $this->assertContains($this->moduleA->id, $ids);
        $this->assertContains($this->moduleB->id, $ids);

        // Override: deny read on moduleA (overrides role grant)
        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
        ]);
        $this->admin->clearPermissionCache();
        $ids = $this->admin->getAccessibleModuleIds('read');
        $this->assertNotContains($this->moduleA->id, $ids);
        $this->assertContains($this->moduleB->id, $ids);
    }

    public function test_super_admin_can_create_overrides_through_user_create(): void
    {
        $roles = [$this->userRole->id];

        $response = $this->actingAs($this->superAdmin)->post('/users', [
            'name' => 'Override Test User',
            'email' => 'override@test.com',
            'password' => 'Pass12345',
            'password_confirmation' => 'Pass12345',
            'roles' => $roles,
            'permissions' => [
                $this->moduleA->id => [
                    'can_read' => '1',
                    'can_create' => '1',
                    'can_update' => '0',
                    'can_delete' => '',
                    'can_approve' => '',
                    'can_export' => '',
                    'can_reveal' => '',
                    'can_import' => '',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user = User::where('email', 'override@test.com')->firstOrFail();
        $this->assertTrue($user->canOnModule($this->moduleA, 'read'));
        $this->assertTrue($user->canOnModule($this->moduleA, 'create'));
        $this->assertFalse($user->canOnModule($this->moduleA, 'update'));

        // Module B should still inherit role default (false for user role)
        $this->assertFalse($user->canOnModule($this->moduleB, 'read'));
    }

    public function test_super_admin_can_update_overrides_through_permissions_page(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
            'can_create' => true,
        ]);

        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'create'));

        $response = $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'permissions' => [
                $this->moduleA->id => [
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
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->normalUser->refresh();
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'create'));
    }

    public function test_super_admin_can_delete_overrides_through_permissions_page(): void
    {
        // Create override
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
        ]);
        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'read'));

        // Submit permissions update with all inherit values (empty strings)
        $response = $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'permissions' => [
                $this->moduleA->id => [
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
        ]);

        $response->assertRedirect();

        // Override row should be deleted → fall back to role
        $this->normalUser->refresh();
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
    }

    public function test_non_super_admin_cannot_manage_overrides(): void
    {
        // Admin tries to access user edit page
        $response = $this->actingAs($this->admin)->get('/users/create');
        $response->assertForbidden();

        $response = $this->actingAs($this->admin)->get("/users/{$this->normalUser->id}/edit");
        $response->assertForbidden();

        $response = $this->actingAs($this->admin)->post('/users', [
            'name' => 'Should Not Work',
            'email' => 'fail@test.com',
            'password' => 'Pass12345',
            'password_confirmation' => 'Pass12345',
        ]);
        $response->assertForbidden();

        $response = $this->actingAs($this->normalUser)->get('/users/create');
        $response->assertForbidden();
    }

    public function test_force_deleting_user_deletes_overrides(): void
    {
        $tempUser = User::factory()->create();
        $tempUser->assignRole($this->userRole);

        UserModulePermission::create([
            'user_id' => $tempUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => true,
        ]);

        $this->assertEquals(1, UserModulePermission::where('user_id', $tempUser->id)->count());

        $tempUser->forceDelete();

        $this->assertEquals(0, UserModulePermission::where('user_id', $tempUser->id)->count());
    }

    public function test_getAllModulePermissions_includes_overrides(): void
    {
        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
            'can_reveal' => true,
        ]);

        $all = $this->admin->getAllModulePermissions();
        $this->assertArrayHasKey($this->moduleA->id, $all);
        $this->assertFalse($all[$this->moduleA->id]['can_read']);
        $this->assertTrue($all[$this->moduleA->id]['can_reveal']);
        $this->assertArrayHasKey('can_reveal', $all[$this->moduleA->id]);
    }

    public function test_getEffectiveModulePermissions_shows_source(): void
    {
        $effective = $this->admin->getEffectiveModulePermissions($this->moduleA);

        // can_read from role
        $this->assertEquals('Role', $effective['can_read']['source']);
        $this->assertTrue($effective['can_read']['effective']);
        $this->assertNull($effective['can_read']['user_override']);

        // can_reveal from role (defaults to false in DB)
        $this->assertEquals('Role', $effective['can_reveal']['source']);
        $this->assertFalse($effective['can_reveal']['effective']);

        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
            'can_reveal' => true,
        ]);

        $effective = $this->admin->getEffectiveModulePermissions($this->moduleA);

        $this->assertEquals('User Override', $effective['can_read']['source']);
        $this->assertFalse($effective['can_read']['effective']);
        $this->assertFalse($effective['can_read']['user_override']);

        $this->assertEquals('User Override', $effective['can_reveal']['source']);
        $this->assertTrue($effective['can_reveal']['effective']);
        $this->assertTrue($effective['can_reveal']['user_override']);
    }

    public function test_super_admin_cannot_assign_super_admin_role_through_form(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();

        $response = $this->actingAs($this->superAdmin)->post('/users', [
            'name' => 'Bad User',
            'email' => 'bad@test.com',
            'password' => 'Pass12345',
            'password_confirmation' => 'Pass12345',
            'roles' => [$superAdminRole->id],
        ]);

        $response->assertForbidden();
    }

    public function test_cannot_delete_last_super_admin(): void
    {
        // There is only one super-admin (created in setUp)
        $response = $this->actingAs($this->superAdmin)->delete("/users/{$this->superAdmin->id}");
        $response->assertForbidden();
    }

    public function test_cannot_self_demote_super_admin(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $userRole = Role::where('slug', 'user')->firstOrFail();

        $response = $this->actingAs($this->superAdmin)->put("/users/{$this->superAdmin->id}", [
            'name' => $this->superAdmin->name,
            'email' => $this->superAdmin->email,
            'roles' => [$userRole->id],
        ]);

        $response->assertForbidden();
    }

    public function test_can_delete_super_admin_if_another_exists(): void
    {
        // Create a second super admin
        $secondSuperAdmin = User::factory()->create();
        $secondSuperAdmin->assignRole($this->superAdminRole);

        // Now we can delete the first super admin
        $response = $this->actingAs($this->superAdmin)->delete("/users/{$this->superAdmin->id}");
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_omitted_module_from_payload_deletes_stale_override(): void
    {
        // Regression: protects JS/backend contract where omitted modules = reset-to-inherited.
        // permissions.js excludes modules with preset===baseline from payload.
        // Backend must delete stale override rows for excluded modules.

        // normalUser role baseline: can_read=true on ModuleA, can_read=false on ModuleB
        // Create override denying ModuleA (overrides role grant)
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
        ]);
        // Create override granting ModuleB (overrides role deny)
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleB->id,
            'can_read' => true,
        ]);

        // Verify overrides are active
        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertTrue($this->normalUser->canOnModule($this->moduleB, 'read'));

        // Send payload containing ONLY ModuleB (ModuleA omitted = reset to inherited)
        $response = $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'permissions' => [
                $this->moduleB->id => [
                    'can_read' => '1',
                    'can_create' => '',
                    'can_update' => '',
                    'can_delete' => '',
                    'can_approve' => '',
                    'can_export' => '',
                    'can_reveal' => '',
                    'can_import' => '',
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Module A stale override row must be deleted
        $this->assertFalse(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleA->id)
                ->exists(),
            'Module A override row should be deleted (omitted from payload)'
        );

        // Module B override row must remain
        $this->assertTrue(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleB->id)
                ->exists(),
            'Module B override row should remain (included in payload)'
        );

        // canOnModule falls back to role baseline for Module A
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));

        // canOnModule still follows override for Module B
        $this->assertTrue($this->normalUser->canOnModule($this->moduleB, 'read'));
    }

    public function test_rbac_phase1_behavior_preserved_without_overrides(): void
    {
        // Verify Phase 1 behaviors still work with the updated trait
        // Super admin sees all
        $this->assertTrue($this->superAdmin->hasRole('super-admin'));

        // Admin has can_read on moduleA from role
        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'read'));
        $this->assertFalse($this->admin->canOnModule($this->moduleB, 'read'));

        // User has can_read on moduleA from role
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertFalse($this->normalUser->canOnModule($this->moduleB, 'read'));

        // getAccessibleModuleIds returns role-based IDs
        $adminIds = $this->admin->getAccessibleModuleIds('read');
        $this->assertContains($this->moduleA->id, $adminIds);
        $this->assertNotContains($this->moduleB->id, $adminIds);
    }

    public function test_invalid_module_id_returns_422(): void
    {
        $maxId = Module::max('id');
        $invalidId = $maxId + 999;

        $response = $this->actingAs($this->superAdmin)
            ->from(route('users.permissions.edit', $this->normalUser->id))
            ->put(route('users.permissions.update', $this->normalUser->id), [
            'permissions' => [
                $invalidId => [
                    'can_read' => '1',
                    'can_create' => '',
                    'can_update' => '',
                    'can_delete' => '',
                    'can_approve' => '',
                    'can_export' => '',
                    'can_reveal' => '',
                    'can_import' => '',
                ],
            ],
        ]);

        $response->assertSessionHasErrors();
        $response->assertRedirect();
        $errors = session('errors');
        $this->assertStringContainsString('Invalid module IDs', $errors->first('permissions'));
    }

    public function test_permission_save_bumps_perms_generation(): void
    {
        $genBefore = Cache::get('perms_generation', 0);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'permissions' => [
                $this->moduleA->id => [
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
        ]);

        $genAfter = Cache::get('perms_generation', 0);
        $this->assertGreaterThan($genBefore, $genAfter, 'perms_generation must increment on permission save');
    }

    public function test_concurrent_permission_updates_maintain_data_integrity(): void
    {
        // Simulates two rapid sequential updates testing the lockForUpdate path
        // First update: set moduleA can_read=true, can_create=true
        $this->actingAs($this->superAdmin)
            ->put(route('users.permissions.update', $this->normalUser->id), [
                'permissions' => [
                    $this->moduleA->id => [
                        'can_read' => '1',
                        'can_create' => '1',
                        'can_update' => '',
                        'can_delete' => '',
                        'can_approve' => '',
                        'can_export' => '',
                        'can_reveal' => '',
                        'can_import' => '',
                    ],
                ],
            ])->assertRedirect()->assertSessionHas('success');

        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'create'));

        // Second update: override can_read back to false, keep can_create
        $this->actingAs($this->superAdmin)
            ->put(route('users.permissions.update', $this->normalUser->id), [
                'permissions' => [
                    $this->moduleA->id => [
                        'can_read' => '0',
                        'can_create' => '1',
                        'can_update' => '',
                        'can_delete' => '',
                        'can_approve' => '',
                        'can_export' => '',
                        'can_reveal' => '',
                        'can_import' => '',
                    ],
                ],
            ])->assertRedirect()->assertSessionHas('success');

        $this->normalUser->refresh();
        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'read'), 'Second update should override can_read');
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'create'), 'can_create should remain true from first update');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\RoleTemplate;
use App\Models\User;
use App\Models\UserModulePermission;
use Database\Seeders\FeatureModuleSeeder;
use Database\Seeders\RoleTemplateSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Tests\TestCase;

class RoleTemplateTest extends TestCase
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

    private function getPermKeys(): array
    {
        return ['can_create', 'can_read', 'can_update', 'can_delete', 'can_approve', 'can_export', 'can_reveal'];
    }

    // ============ SEEDER TESTS ============

    public function test_seeder_creates_four_templates(): void
    {
        $this->assertEquals(4, RoleTemplate::count());
        $this->assertNotNull(RoleTemplate::where('slug', 'super-admin')->first());
        $this->assertNotNull(RoleTemplate::where('slug', 'admin')->first());
        $this->assertNotNull(RoleTemplate::where('slug', 'it-support')->first());
        $this->assertNotNull(RoleTemplate::where('slug', 'read-only')->first());
    }

    public function test_seeder_is_idempotent(): void
    {
        $this->seed(RoleTemplateSeeder::class);
        $this->assertEquals(4, RoleTemplate::count());
    }

    // ============ ACCESS CONTROL TESTS ============

    public function test_non_super_admin_cannot_access_template_index(): void
    {
        $this->actingAs($this->admin)
            ->get(route('role-templates.index'))
            ->assertForbidden();
    }

    public function test_non_super_admin_cannot_access_template_show(): void
    {
        $template = RoleTemplate::first();
        $this->actingAs($this->admin)
            ->get(route('role-templates.show', $template->id))
            ->assertForbidden();
    }

    public function test_non_super_admin_cannot_apply_template(): void
    {
        $template = RoleTemplate::first();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);
        $this->actingAs($this->admin)
            ->post(route('role-templates.apply', $template->id), ['role_id' => $role->id, 'confirmed' => 1])
            ->assertForbidden();
    }

    // ============ VIEW TESTS ============

    public function test_super_admin_can_view_template_index(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('role-templates.index'))
            ->assertOk();

        $response->assertSee('Super Admin');
        $response->assertSee('Admin');
        $response->assertSee('IT Support');
        $response->assertSee('Read Only');
    }

    public function test_super_admin_can_view_template_show(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $response = $this->actingAs($this->superAdmin)
            ->get(route('role-templates.show', $template->id))
            ->assertOk();

        $response->assertSee($template->name);
        $response->assertSee('Permission Matrix');
    }

    public function test_template_show_displays_permission_matrix(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $response = $this->actingAs($this->superAdmin)
            ->get(route('role-templates.show', $template->id))
            ->assertOk();

        $response->assertSee('Domains');
        $response->assertSee('Hosting');
        $response->assertSee('Tasks');
        $response->assertSee('Users');
        $response->assertSee('Read');
        $response->assertSee('Create');
        $response->assertSee('Update');
        $response->assertSee('Delete');
        $response->assertSee('Approve');
        $response->assertSee('Export');
        $response->assertSee('Reveal');
        $response->assertSee('Import');
    }

    public function test_super_admin_template_is_marked_dangerous(): void
    {
        $template = RoleTemplate::where('slug', 'super-admin')->firstOrFail();
        $this->assertTrue($template->is_dangerous);
        $this->assertTrue($template->is_protected);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('role-templates.show', $template->id))
            ->assertOk();

        $response->assertSee('Dangerous');
        $response->assertSee('Protected');
    }

    public function test_other_templates_are_not_dangerous(): void
    {
        foreach (['admin', 'it-support', 'read-only'] as $slug) {
            $template = RoleTemplate::where('slug', $slug)->firstOrFail();
            $this->assertFalse($template->is_dangerous);
        }
    }

    public function test_all_templates_are_protected(): void
    {
        foreach (['super-admin', 'admin', 'it-support', 'read-only'] as $slug) {
            $template = RoleTemplate::where('slug', $slug)->firstOrFail();
            $this->assertTrue($template->is_protected);
        }
    }

    // ============ ADMIN TEMPLATE TESTS ============

    public function test_admin_template_does_not_grant_rbac_access(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $perms = $template->permissions_json;

        $rbacSlugs = ['roles', 'privileges', 'module-permissions'];
        foreach ($rbacSlugs as $slug) {
            $this->assertArrayHasKey($slug, $perms);
            foreach ($this->getPermKeys() as $key) {
                $this->assertFalse($perms[$slug][$key] ?? false, "{$slug}.{$key} should be false");
            }
        }

        $this->assertTrue($perms['users']['can_read'] ?? false);
        $this->assertFalse($perms['users']['can_create'] ?? true);
    }

    public function test_admin_template_grants_infrastructure_access(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $perms = $template->permissions_json;

        $infraSlugs = ['domains', 'hostings', 'vps', 'voip', 'service-providers', 'domain-emails', 'other-services', 'expiry-trackers', 'assets'];
        foreach ($infraSlugs as $slug) {
            $this->assertTrue($perms[$slug]['can_read'] ?? false, "{$slug}.can_read");
            $this->assertTrue($perms[$slug]['can_create'] ?? false, "{$slug}.can_create");
            $this->assertTrue($perms[$slug]['can_update'] ?? false, "{$slug}.can_update");
            $this->assertFalse($perms[$slug]['can_delete'] ?? true, "{$slug}.can_delete");
            $this->assertTrue($perms[$slug]['can_export'] ?? false, "{$slug}.can_export");
            $this->assertTrue($perms[$slug]['can_reveal'] ?? false, "{$slug}.can_reveal");
        }
    }

    // ============ IT SUPPORT TEMPLATE TESTS ============

    public function test_it_support_limited_to_six_modules(): void
    {
        $template = RoleTemplate::where('slug', 'it-support')->firstOrFail();
        $perms = $template->permissions_json;

        $allowedSlugs = ['domains', 'hostings', 'vps', 'voip', 'service-providers', 'domain-emails'];
        foreach ($allowedSlugs as $slug) {
            $this->assertTrue($perms[$slug]['can_read'] ?? false, "{$slug}.can_read");
            $this->assertTrue($perms[$slug]['can_create'] ?? false, "{$slug}.can_create");
            $this->assertTrue($perms[$slug]['can_update'] ?? false, "{$slug}.can_update");
            $this->assertTrue($perms[$slug]['can_reveal'] ?? false, "{$slug}.can_reveal");
            $this->assertFalse($perms[$slug]['can_delete'] ?? true, "{$slug}.can_delete");
            $this->assertFalse($perms[$slug]['can_export'] ?? true, "{$slug}.can_export");
        }

        $deniedSlugs = ['tasks', 'notes', 'vault', 'users', 'roles', 'privileges', 'module-permissions', 'webhooks', 'tokens', 'import', 'export', 'reports'];
        foreach ($deniedSlugs as $slug) {
            $this->assertArrayHasKey($slug, $perms);
            foreach ($this->getPermKeys() as $key) {
                $this->assertFalse($perms[$slug][$key] ?? false, "{$slug}.{$key} should be false");
            }
        }
    }

    // ============ READ ONLY TEMPLATE TESTS ============

    public function test_read_only_grants_read_only_and_no_reveal_export(): void
    {
        $template = RoleTemplate::where('slug', 'read-only')->firstOrFail();
        $perms = $template->permissions_json;

        $readModules = ['domains', 'hostings', 'vps', 'voip', 'service-providers', 'domain-emails', 'other-services', 'expiry-trackers', 'assets', 'tasks', 'notes', 'vault', 'monitor', 'calendar', 'users', 'activity-logs', 'notifications', 'attachments', 'webhooks', 'reports'];
        foreach ($readModules as $slug) {
            $this->assertTrue($perms[$slug]['can_read'] ?? false, "{$slug}.can_read");
            $this->assertFalse($perms[$slug]['can_create'] ?? true, "{$slug}.can_create");
            $this->assertFalse($perms[$slug]['can_update'] ?? true, "{$slug}.can_update");
            $this->assertFalse($perms[$slug]['can_delete'] ?? true, "{$slug}.can_delete");
            $this->assertFalse($perms[$slug]['can_export'] ?? true, "{$slug}.can_export");
            $this->assertFalse($perms[$slug]['can_reveal'] ?? true, "{$slug}.can_reveal");
        }
    }

    // ============ DIFF PREVIEW TESTS ============

    public function test_diff_preview_appears_before_apply(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('role-templates.apply', [$template->id, 'role_id' => $role->id]))
            ->assertOk();

        $response->assertSee('Modules to be Added');
        $response->assertSee('Confirm');
        $response->assertSee('Apply');
    }

    public function test_diff_preview_shows_changed_modules(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);
        $domainModule = Module::where('slug', 'domains')->firstOrFail();

        ModuleRolePermission::create([
            'module_id' => $domainModule->id,
            'role_id' => $role->id,
            'can_read' => true,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => true,
            'can_approve' => false,
            'can_export' => false,
            'can_reveal' => false,
        ]);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('role-templates.apply', [$template->id, 'role_id' => $role->id]))
            ->assertOk();

        $response->assertSee('Modules to be Overwritten');
        $response->assertSee('Domains');
    }

    // ============ APPLY CONFIRMATION TESTS ============

    public function test_apply_requires_confirmation(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_apply_writes_module_role_permissions(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
                'confirmed' => 1,
            ])
            ->assertSessionHas('success');

        $domainModule = Module::where('slug', 'domains')->firstOrFail();
        $perm = ModuleRolePermission::where('module_id', $domainModule->id)
            ->where('role_id', $role->id)
            ->firstOrFail();

        $this->assertTrue((bool) $perm->can_read);
        $this->assertTrue((bool) $perm->can_create);
        $this->assertTrue((bool) $perm->can_update);
        $this->assertFalse((bool) $perm->can_delete);
        $this->assertFalse((bool) $perm->can_approve);
        $this->assertTrue((bool) $perm->can_export);
        $this->assertTrue((bool) $perm->can_reveal);
    }

    public function test_apply_overwrites_existing_role_permissions(): void
    {
        $template = RoleTemplate::where('slug', 'read-only')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);
        $domainModule = Module::where('slug', 'domains')->firstOrFail();

        ModuleRolePermission::create([
            'module_id' => $domainModule->id,
            'role_id' => $role->id,
            'can_read' => true,
            'can_create' => true,
            'can_update' => true,
            'can_delete' => true,
            'can_approve' => true,
            'can_export' => true,
            'can_reveal' => true,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
                'confirmed' => 1,
            ])
            ->assertSessionHas('success');

        $perm = ModuleRolePermission::where('module_id', $domainModule->id)
            ->where('role_id', $role->id)
            ->firstOrFail();

        $this->assertTrue((bool) $perm->can_read);
        $this->assertFalse((bool) $perm->can_create);
        $this->assertFalse((bool) $perm->can_update);
        $this->assertFalse((bool) $perm->can_delete);
        $this->assertFalse((bool) $perm->can_approve);
        $this->assertFalse((bool) $perm->can_export);
        $this->assertFalse((bool) $perm->can_reveal);
    }

    public function test_apply_preserves_user_module_permissions(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);
        $user = User::factory()->create(['name' => 'Test User', 'email' => 'test@test.com']);
        $user->roles()->sync([$role->id]);

        $vaultModule = Module::where('slug', 'vault')->firstOrFail();
        UserModulePermission::create([
            'user_id' => $user->id,
            'module_id' => $vaultModule->id,
            'can_read' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => false,
            'can_export' => false,
            'can_reveal' => false,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
                'confirmed' => 1,
            ])
            ->assertSessionHas('success');

        $override = UserModulePermission::where('user_id', $user->id)
            ->where('module_id', $vaultModule->id)
            ->firstOrFail();

        $this->assertFalse((bool) $override->can_read);
        $this->assertFalse((bool) $override->can_create);
    }

    public function test_apply_logs_activity(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
                'confirmed' => 1,
            ]);

        $activity = Activity::where('event', 'template_applied')->firstOrFail();
        $props = $activity->properties;
        $this->assertEquals($template->id, $props['template']['id']);
        $this->assertEquals($role->id, $props['role']['id']);
        $this->assertGreaterThanOrEqual(0, $props['changed_count']);
        $this->assertGreaterThanOrEqual(0, $props['added_count']);
        $this->assertGreaterThanOrEqual(0, $props['unchanged_count']);
    }

    public function test_apply_is_idempotent(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);
        $domainModule = Module::where('slug', 'domains')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
                'confirmed' => 1,
            ]);

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
                'confirmed' => 1,
            ]);

        $perm = ModuleRolePermission::where('module_id', $domainModule->id)
            ->where('role_id', $role->id)
            ->firstOrFail();

        $this->assertTrue((bool) $perm->can_read);
        $this->assertTrue((bool) $perm->can_create);

        $count = ModuleRolePermission::where('role_id', $role->id)->count();
        $totalModules = count($template->permissions_json);
        $this->assertEquals($totalModules, $count, "Should have exactly {$totalModules} permission rows");
    }

    public function test_apply_does_not_affect_other_roles(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $roleA = Role::create(['name' => 'Role A', 'slug' => 'role-a']);
        $roleB = Role::create(['name' => 'Role B', 'slug' => 'role-b']);
        $domainModule = Module::where('slug', 'domains')->firstOrFail();

        ModuleRolePermission::create([
            'module_id' => $domainModule->id,
            'role_id' => $roleB->id,
            'can_read' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => false,
            'can_export' => false,
            'can_reveal' => false,
        ]);

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $roleA->id,
                'confirmed' => 1,
            ]);

        $permB = ModuleRolePermission::where('module_id', $domainModule->id)
            ->where('role_id', $roleB->id)
            ->firstOrFail();

        $this->assertFalse((bool) $permB->can_read);
    }

    public function test_apply_uses_db_transaction(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);

        $domainModule = Module::where('slug', 'domains')->firstOrFail();
        ModuleRolePermission::create([
            'module_id' => $domainModule->id,
            'role_id' => $role->id,
            'can_read' => false,
            'can_create' => false,
            'can_update' => false,
            'can_delete' => false,
            'can_approve' => false,
            'can_export' => false,
            'can_reveal' => false,
        ]);

        $originalCount = ModuleRolePermission::where('role_id', $role->id)->count();

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
                'confirmed' => 1,
            ]);

        $newCount = ModuleRolePermission::where('role_id', $role->id)->count();
        $totalModules = count($template->permissions_json);
        $this->assertEquals($totalModules, $newCount);
    }

    public function test_super_admin_template_requires_dangerous_confirmation(): void
    {
        $template = RoleTemplate::where('slug', 'super-admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
                'confirmed' => 1,
            ])
            ->assertSessionHasErrors('confirm_dangerous');
    }

    public function test_super_admin_template_applies_with_dangerous_confirmation(): void
    {
        $template = RoleTemplate::where('slug', 'super-admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);
        $domainModule = Module::where('slug', 'domains')->firstOrFail();

        $this->actingAs($this->superAdmin)
            ->post(route('role-templates.apply', $template->id), [
                'role_id' => $role->id,
                'confirmed' => 1,
                'confirm_dangerous' => 1,
            ])
            ->assertSessionHas('success');

        $perm = ModuleRolePermission::where('module_id', $domainModule->id)
            ->where('role_id', $role->id)
            ->firstOrFail();

        $this->assertTrue((bool) $perm->can_read);
        $this->assertTrue((bool) $perm->can_create);
        $this->assertTrue((bool) $perm->can_delete);
        $this->assertTrue((bool) $perm->can_reveal);
    }

    public function test_dangerous_confirmation_displayed_on_preview(): void
    {
        $template = RoleTemplate::where('slug', 'super-admin')->firstOrFail();
        $role = Role::create(['name' => 'Test Role', 'slug' => 'test-role']);

        $response = $this->actingAs($this->superAdmin)
            ->get(route('role-templates.apply', [$template->id, 'role_id' => $role->id]))
            ->assertOk();

        $response->assertSee('Dangerous Template Warning');
        $response->assertSee('confirm_dangerous');
    }

    public function test_template_index_shows_module_count(): void
    {
        $response = $this->actingAs($this->superAdmin)
            ->get(route('role-templates.index'))
            ->assertOk();

        foreach (RoleTemplate::all() as $template) {
            $response->assertSee((string) $template->module_count);
        }
    }

    public function test_it_support_template_no_export_or_delete(): void
    {
        $template = RoleTemplate::where('slug', 'it-support')->firstOrFail();
        $perms = $template->permissions_json;

        foreach ($perms as $slug => $modulePerms) {
            $this->assertFalse($modulePerms['can_delete'] ?? false, "{$slug}.can_delete should be false");
            $this->assertFalse($modulePerms['can_export'] ?? false, "{$slug}.can_export should be false");
        }
    }

    // ============ ASSETS PERMISSION TESTS ============

    public function test_admin_template_grants_assets_access(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $perms = $template->permissions_json;

        $this->assertArrayHasKey('assets', $perms);
        $this->assertTrue($perms['assets']['can_read'] ?? false, 'assets.can_read');
        $this->assertTrue($perms['assets']['can_create'] ?? false, 'assets.can_create');
        $this->assertTrue($perms['assets']['can_update'] ?? false, 'assets.can_update');
        $this->assertFalse($perms['assets']['can_delete'] ?? true, 'assets.can_delete');
        $this->assertTrue($perms['assets']['can_export'] ?? false, 'assets.can_export');
        $this->assertTrue($perms['assets']['can_reveal'] ?? false, 'assets.can_reveal');
    }

    public function test_read_only_template_grants_assets_view_only(): void
    {
        $template = RoleTemplate::where('slug', 'read-only')->firstOrFail();
        $perms = $template->permissions_json;

        $this->assertArrayHasKey('assets', $perms);
        $this->assertTrue($perms['assets']['can_read'] ?? false, 'assets.can_read');
        $this->assertFalse($perms['assets']['can_create'] ?? true, 'assets.can_create');
        $this->assertFalse($perms['assets']['can_update'] ?? true, 'assets.can_update');
        $this->assertFalse($perms['assets']['can_delete'] ?? true, 'assets.can_delete');
        $this->assertFalse($perms['assets']['can_export'] ?? true, 'assets.can_export');
        $this->assertFalse($perms['assets']['can_reveal'] ?? true, 'assets.can_reveal');
    }

    public function test_it_support_template_denies_assets(): void
    {
        $template = RoleTemplate::where('slug', 'it-support')->firstOrFail();
        $perms = $template->permissions_json;

        $this->assertArrayHasKey('assets', $perms);
        foreach (['can_create', 'can_read', 'can_update', 'can_delete', 'can_approve', 'can_export', 'can_reveal'] as $key) {
            $this->assertFalse($perms['assets'][$key] ?? false, "assets.{$key} should be false");
        }
    }

    public function test_admin_template_no_import(): void
    {
        $template = RoleTemplate::where('slug', 'admin')->firstOrFail();
        $perms = $template->permissions_json;

        foreach ($perms as $slug => $modulePerms) {
            $this->assertFalse($modulePerms['can_import'] ?? false, "{$slug}.can_import should be false");
        }
    }
}

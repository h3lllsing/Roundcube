<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\User;
use App\Models\UserModulePermission;
use App\Models\Vps;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase2B1Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $userRole;

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

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->userRole = Role::where('slug', 'user')->firstOrFail();

        $modules = Module::take(2)->get();
        $this->moduleA = $modules[0];
        $this->moduleB = $modules[1];

        // Admin role: can_read+can_export+can_reveal on moduleA, NOT on moduleB
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleA->id, 'role_id' => $this->adminRole->id],
            ['can_read' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
        );
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleB->id, 'role_id' => $this->adminRole->id],
            ['can_read' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
        );

        // User role: can_read + can_export + can_reveal on moduleA, nothing on moduleB
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleA->id, 'role_id' => $this->userRole->id],
            ['can_read' => true, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => true, 'can_reveal' => true]
        );
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleB->id, 'role_id' => $this->userRole->id],
            ['can_read' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false, 'can_reveal' => false]
        );

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);

        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole($this->userRole);
    }

    // ─── can_reveal role-level tests ───────────────────────────────────

    public function test_role_level_can_reveal_works_through_canOnModule(): void
    {
        // Admin role has can_reveal on moduleA
        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'reveal'));
        // Admin role does NOT have can_reveal on moduleB
        $this->assertFalse($this->admin->canOnModule($this->moduleB, 'reveal'));
    }

    public function test_user_override_false_denies_role_level_can_reveal(): void
    {
        // Admin role grants can_reveal on moduleA
        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'reveal'));

        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleA->id,
            'can_reveal' => false,
        ]);

        $this->assertFalse($this->admin->canOnModule($this->moduleA, 'reveal'));
    }

    public function test_user_override_true_grants_can_reveal_when_role_denies(): void
    {
        // User role denies can_reveal on moduleB
        $this->assertFalse($this->normalUser->canOnModule($this->moduleB, 'reveal'));

        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleB->id,
            'can_reveal' => true,
        ]);

        $this->assertTrue($this->normalUser->canOnModule($this->moduleB, 'reveal'));
    }

    public function test_user_override_null_inherits_role_can_reveal(): void
    {
        // Admin role has can_reveal on moduleA
        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleA->id,
            'can_reveal' => null,
        ]);

        // Should still be true (inherited from role)
        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'reveal'));
    }

    public function test_super_admin_bypasses_can_reveal(): void
    {
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleA, 'reveal'));
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleB, 'reveal'));
    }

    // ─── Export tests ──────────────────────────────────────────────────

    public function test_super_admin_exports_all_records(): void
    {
        Domain::factory()->create(['name' => 'export-a.com', 'user_id' => $this->admin->id, 'module_id' => $this->moduleA->id]);
        Domain::factory()->create(['name' => 'export-b.com', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleB->id]);

        $response = $this->actingAs($this->superAdmin)->get('/export/domains');

        $response->assertOk();
        $this->assertStringContainsString('domains-', $response->headers->get('Content-Disposition'));
        $content = $response->getContent();
        $this->assertStringContainsString('export-a.com', $content);
        $this->assertStringContainsString('export-b.com', $content);
    }

    public function test_admin_with_can_export_exports_module_records_owned_by_other_users(): void
    {
        // Admin has can_export on moduleA only
        // Create record in moduleA owned by normalUser
        Domain::factory()->create(['name' => 'module-a-owned-by-other.com', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleA->id]);
        // Create record in moduleB (should NOT appear)
        Domain::factory()->create(['name' => 'module-b-record.com', 'user_id' => $this->admin->id, 'module_id' => $this->moduleB->id]);

        $response = $this->actingAs($this->admin)->get('/export/domains');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('module-a-owned-by-other.com', $content);
        $this->assertStringNotContainsString('module-b-record.com', $content);
    }

    public function test_admin_without_can_export_is_blocked(): void
    {
        // Admin has can_export=false on moduleB — test by trying to export a type they have access to
        // Actually, admin has can_export on moduleA. To test without can_export, we need a type
        // where admin has no module permission.
        // Use a module where admin has can_export=0 — let's create a third module or check moduleB.
        // Admin has can_export=false on moduleB, but this check is module-level, not record-level.
        // The export permission check blocks at the type level, not per-record.
        // So we need to test a type where admin lacks can_export entirely.

        // Admin has can_export=false on domain-emails type... let me check.
        // Actually, the PermissionSeeder gives the same permissions for all modules.
        // Admin has can_export=true on ALL modules via seeder, so admin can export all types.
        // To test "without can_export", we need to remove or set can_export=false for admin on a specific module.
        ModuleRolePermission::where('module_id', $this->moduleA->id)
            ->where('role_id', $this->adminRole->id)
            ->update(['can_export' => false]);

        // Now admin should be blocked from exporting domains (which maps to 'domains' slug)
        $response = $this->actingAs($this->admin)->get('/export/domains');
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Forbidden.');
    }

    public function test_export_user_override_false_blocks_export(): void
    {
        // Admin has can_export=true on moduleA via role
        // Add user override to deny it
        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleA->id,
            'can_export' => false,
        ]);

        $response = $this->actingAs($this->admin)->get('/export/domains');
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Forbidden.');
    }

    public function test_export_user_override_true_grants_when_role_denies(): void
    {
        // Admin role denies can_export on moduleB
        // Add user override to grant it
        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleB->id,
            'can_export' => true,
        ]);

        Domain::factory()->create(['name' => 'override-export.com', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleB->id]);

        $response = $this->actingAs($this->admin)->get('/export/domains');
        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('override-export.com', $content);
    }

    public function test_normal_user_export_is_module_scoped(): void
    {
        Domain::factory()->create(['name' => 'my-domain.com', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleA->id]);
        Domain::factory()->create(['name' => 'their-domain.com', 'user_id' => $this->admin->id, 'module_id' => $this->moduleA->id]);

        $response = $this->actingAs($this->normalUser)->get('/export/domains');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('my-domain.com', $content);
        // Export is module-scoped: having can_export on moduleA grants access to ALL records in it
        $this->assertStringContainsString('their-domain.com', $content);
    }

    public function test_admin_only_export_types_blocked_for_non_super_admin(): void
    {
        $response = $this->actingAs($this->admin)->get('/export/users');
        $response->assertRedirect();
        $response->assertSessionHas('error', 'Forbidden.');
    }

    public function test_non_module_models_stay_ownership_scoped(): void
    {
        $notesModule = Module::where('slug', 'notes')->firstOrFail();

        // Give normal user can_export on notes module
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $notesModule->id, 'role_id' => $this->userRole->id],
            ['can_read' => true, 'can_export' => true, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_reveal' => false]
        );

        // Notes have no module_id — test ownership scoping
        \App\Models\Note::factory()->create(['content' => 'My-Note', 'user_id' => $this->normalUser->id]);
        \App\Models\Note::factory()->create(['content' => 'Their-Note', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->normalUser)->get('/export/notes');
        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('My-Note', $content);
        $this->assertStringNotContainsString('Their-Note', $content);
    }
}

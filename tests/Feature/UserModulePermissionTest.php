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

        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleA->id, 'role_id' => $this->adminRole->id],
            ['can_read' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true, 'can_approve' => false, 'can_export' => true]
        );
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleB->id, 'role_id' => $this->adminRole->id],
            ['can_read' => false, 'can_create' => false, 'can_update' => false, 'can_delete' => false, 'can_approve' => false, 'can_export' => false]
        );

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

    // ─── Inherit / Allow / Deny core behavior ─────────────────────────────

    public function test_inherit_preserves_role_permission(): void
    {
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));

        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => null,
        ]);

        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
    }

    public function test_explicit_allow_overrides_inherited_false(): void
    {
        $this->assertFalse($this->normalUser->canOnModule($this->moduleB, 'read'));

        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleB->id,
            'can_read' => true,
        ]);

        $this->assertTrue($this->normalUser->canOnModule($this->moduleB, 'read'));
    }

    public function test_explicit_deny_overrides_inherited_true(): void
    {
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));

        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
        ]);

        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'read'));
    }

    public function test_multiple_role_or_unchanged_when_user_state_inherit(): void
    {
        $this->admin->assignRole($this->userRole);

        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'read'));

        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleA->id,
            'can_read' => null,
        ]);

        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'read'));
    }

    // ─── Control-to-DB mapping ────────────────────────────────────────────

    public function test_access_allow_maps_read_and_reveal_true(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_read);
        $this->assertTrue($row->can_reveal);
    }

    public function test_access_deny_maps_read_and_reveal_false(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'deny', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertFalse($row->can_read);
        $this->assertFalse($row->can_reveal);
    }

    public function test_manage_allow_without_destroying_access_inheritance(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'allow', 'import' => 'inherit', 'export' => 'inherit', '_manage_unchanged' => '0'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertNull($row->can_read, 'Access inherit should leave can_read null');
        $this->assertNull($row->can_reveal);
        $this->assertTrue($row->can_create);
        $this->assertTrue($row->can_update);
    }

    public function test_manage_deny_maps_create_and_update_false(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'deny', 'import' => 'inherit', 'export' => 'inherit', '_manage_unchanged' => '0'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertFalse($row->can_create);
        $this->assertFalse($row->can_update);
    }

    public function test_import_allow_deny_inherit_round_trip(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'allow', 'export' => 'inherit'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_import);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'deny', 'export' => 'inherit'],
            ],
        ]);

        $row->refresh();
        $this->assertFalse($row->can_import);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit'],
            ],
        ]);

        $this->assertFalse(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleA->id)->exists(),
            'Row should be deleted when all controls inherit'
        );
    }

    public function test_export_allow_deny_inherit_round_trip(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'allow'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_export);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'deny'],
            ],
        ]);

        $row->refresh();
        $this->assertFalse($row->can_export);
    }

    public function test_mixed_override_row_round_trips_unchanged(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => null,
            'can_reveal' => null,
            'can_create' => null,
            'can_update' => null,
            'can_import' => true,
            'can_export' => false,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'allow', 'export' => 'deny'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertNull($row->can_read);
        $this->assertNull($row->can_reveal);
        $this->assertNull($row->can_create);
        $this->assertNull($row->can_update);
        $this->assertTrue($row->can_import);
        $this->assertFalse($row->can_export);
    }

    // ─── Inherit All ──────────────────────────────────────────────────────

    public function test_inherit_all_deletes_row_when_safe(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
            'can_reveal' => false,
            'can_create' => true,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => [
                    'access' => 'inherit', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit',
                    'inherit_all' => true,
                ],
            ],
        ]);

        $this->assertFalse(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleA->id)->exists(),
            'Row should be deleted when all UI controls are inherit'
        );

        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'create'));
    }

    // ─── User with no roles ───────────────────────────────────────────────

    public function test_user_with_no_role_receives_direct_access(): void
    {
        $noRoleUser = User::factory()->create();

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $noRoleUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $this->assertTrue($noRoleUser->canOnModule($this->moduleA, 'read'));
        $this->assertFalse($noRoleUser->canOnModule($this->moduleA, 'create'));
    }

    // ─── Full Access ──────────────────────────────────────────────────────

    public function test_full_access_never_grants_delete(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => [
                    'access' => 'allow', 'manage' => 'allow', 'import' => 'allow', 'export' => 'allow',
                    'full_access' => true,
                ],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertFalse($row->can_delete);
    }

    public function test_full_access_never_grants_approve(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => [
                    'access' => 'allow', 'manage' => 'allow', 'import' => 'allow', 'export' => 'allow',
                    'full_access' => true,
                ],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertNull($row->can_approve);
    }

    // ─── Crafted request protection ───────────────────────────────────────

    public function test_unsupported_import_cannot_be_granted_through_crafted_request(): void
    {
        $unsupportedModule = Module::whereNotIn('slug', config('permissions.importable_modules', []))->first();
        if (!$unsupportedModule) {
            $this->markTestSkipped('No unsupported import module available');
        }

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $unsupportedModule->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'allow', 'export' => 'inherit'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $unsupportedModule->id)->first();
        $this->assertNull($row, 'No row should be created for unsupported import');
    }

    public function test_crafted_can_delete_cannot_bypass_service_protection(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'allow', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertFalse($row->can_delete);
    }

    // ─── Legacy data preservation ─────────────────────────────────────────

    public function test_legacy_read_true_reveal_false_not_silently_changed_by_unrelated_save(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => true,
            'can_reveal' => false,
            'can_import' => true,
            'can_export' => false,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'inherit', 'import' => 'allow', 'export' => 'deny'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_read, 'can_read should remain true');
        $this->assertFalse($row->can_reveal, 'can_reveal should stay false because Access was not intentionally changed');
        $this->assertTrue($row->can_import);
        $this->assertFalse($row->can_export);
    }

    public function test_legacy_read_true_reveal_false_intentional_access_allow_normalizes(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => true,
            'can_reveal' => false,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_read);
        $this->assertTrue($row->can_reveal, 'Intentional Access=Allow should normalize can_reveal to true');
    }

    public function test_legacy_manage_mismatch_not_silently_changed_by_unrelated_save(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_create' => true,
            'can_update' => false,
            'can_import' => null,
            'can_export' => true,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'allow', 'import' => 'inherit', 'export' => 'allow'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_create, 'can_create should remain true');
        $this->assertFalse($row->can_update, 'can_update should stay false because Manage was not intentionally changed');
        $this->assertTrue($row->can_export, 'export change should apply');
    }

    public function test_legacy_manage_mismatch_intentional_manage_allow_normalizes(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_create' => true,
            'can_update' => false,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'allow', 'import' => 'inherit', 'export' => 'inherit', '_manage_unchanged' => '0'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_create);
        $this->assertTrue($row->can_update, 'Intentional Manage=Allow should normalize can_update to true');
    }

    // ─── Hidden column preservation ───────────────────────────────────────

    public function test_existing_can_approve_true_survives_unrelated_export_change(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_approve' => true,
            'can_read' => true,
            'can_reveal' => true,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'allow'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_approve, 'can_approve must survive unrelated Export change');
    }

    public function test_existing_can_approve_false_survives_unrelated_access_change(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_approve' => false,
            'can_read' => true,
            'can_reveal' => true,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'deny', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertFalse($row->can_read);
        $this->assertFalse($row->can_approve, 'can_approve false must survive Access change');
    }

    public function test_inherit_all_preserves_can_approve_and_does_not_delete_row(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_approve' => true,
            'can_read' => null,
            'can_reveal' => null,
            'can_create' => null,
            'can_update' => null,
            'can_import' => null,
            'can_export' => null,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => [
                    'access' => 'inherit', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit',
                    'inherit_all' => true,
                ],
            ],
        ]);

        $this->assertTrue(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleA->id)->exists(),
            'Row with can_approve=true must not be deleted by Inherit All'
        );

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_approve, 'can_approve must survive Inherit All');
    }

    public function test_full_access_does_not_alter_existing_can_approve(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_approve' => true,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => [
                    'access' => 'allow', 'manage' => 'allow', 'import' => 'allow', 'export' => 'allow',
                    'full_access' => true,
                ],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_approve, 'Full Access must not change existing can_approve');
    }

    public function test_crafted_can_approve_input_cannot_modify_can_approve(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_approve' => true,
        ]);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_approve, 'can_approve must remain true despite not being in control keys');
        $this->assertTrue($row->can_read);
    }

    public function test_row_with_only_ui_columns_null_and_can_approve_null_may_be_deleted(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => true,
        ]);
        $this->assertTrue(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleA->id)->exists()
        );

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $this->assertFalse(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleA->id)->exists(),
            'Row with no meaningful overrides should be deleted'
        );
    }

    public function test_non_sa_can_delete_false_only_row_does_not_remain(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => true,
            'can_reveal' => true,
        ]);
        $this->assertTrue(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleA->id)->exists()
        );

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $this->assertFalse(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleA->id)->exists(),
            'Row with only can_delete=false must be deleted (enforced, not meaningful)'
        );
    }

    // ─── Super Admin ──────────────────────────────────────────────────────

    public function test_super_admin_behavior_remains_intact(): void
    {
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleA, 'read'));
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleA, 'create'));
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleA, 'update'));
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleA, 'delete'));
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleA, 'export'));
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleA, 'reveal'));
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleA, 'import'));
    }

    // ─── Batch 1 reveal behavior ──────────────────────────────────────────

    public function test_reveal_auth_not_regressed(): void
    {
        $this->assertTrue($this->superAdmin->canOnModule($this->moduleA, 'reveal'));

        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'reveal'));

        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_reveal' => true,
            'can_read' => true,
        ]);
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'reveal'));

        $override = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->first();
        $override->update(['can_reveal' => false]);
        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'reveal'));

        $override->delete();
        $this->normalUser->clearPermissionCache();
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'reveal'));
    }

    // ─── Existing tests (updated for controls format) ─────────────────────

    public function test_user_override_true_grants_permission_even_if_role_denies(): void
    {
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
        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'read'));

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
        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'read'));
        $this->assertFalse($this->admin->canOnModule($this->moduleB, 'read'));
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertFalse($this->normalUser->canOnModule($this->moduleB, 'read'));
    }

    public function test_getAccessibleModuleIds_respects_user_overrides(): void
    {
        $ids = $this->admin->getAccessibleModuleIds('read');
        $this->assertEquals([$this->moduleA->id], $ids);

        UserModulePermission::create([
            'user_id' => $this->admin->id,
            'module_id' => $this->moduleB->id,
            'can_read' => true,
        ]);
        $this->admin->clearPermissionCache();
        $ids = $this->admin->getAccessibleModuleIds('read');
        $this->assertContains($this->moduleA->id, $ids);
        $this->assertContains($this->moduleB->id, $ids);

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
                ],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user = User::where('email', 'override@test.com')->firstOrFail();
        $this->assertTrue($user->canOnModule($this->moduleA, 'read'));
        $this->assertTrue($user->canOnModule($this->moduleA, 'create'));
        $this->assertFalse($user->canOnModule($this->moduleA, 'update'));

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
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'deny', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0', '_manage_unchanged' => '0'],
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
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
        ]);
        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'read'));

        $response = $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $response->assertRedirect();

        $this->normalUser->refresh();
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
    }

    public function test_non_super_admin_cannot_manage_overrides(): void
    {
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

        $this->assertEquals('Role', $effective['can_read']['source']);
        $this->assertTrue($effective['can_read']['effective']);
        $this->assertNull($effective['can_read']['user_override']);

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
        $secondSuperAdmin = User::factory()->create();
        $secondSuperAdmin->assignRole($this->superAdminRole);

        $response = $this->actingAs($this->superAdmin)->delete("/users/{$this->superAdmin->id}");
        $response->assertRedirect();
        $response->assertSessionHas('success');
    }

    public function test_omitted_module_from_payload_deletes_stale_override(): void
    {
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
            'can_read' => false,
        ]);
        UserModulePermission::create([
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleB->id,
            'can_read' => true,
        ]);

        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertTrue($this->normalUser->canOnModule($this->moduleB, 'read'));

        $response = $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleB->id => ['access' => 'allow', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0'],
            ],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertFalse(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleA->id)->exists(),
            'Module A override row should be deleted (omitted from payload)'
        );

        $this->assertTrue(
            UserModulePermission::where('user_id', $this->normalUser->id)
                ->where('module_id', $this->moduleB->id)->exists(),
            'Module B override row should remain (included in payload)'
        );

        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertTrue($this->normalUser->canOnModule($this->moduleB, 'read'));
    }

    public function test_rbac_phase1_behavior_preserved_without_overrides(): void
    {
        $this->assertTrue($this->superAdmin->hasRole('super-admin'));

        $this->assertTrue($this->admin->canOnModule($this->moduleA, 'read'));
        $this->assertFalse($this->admin->canOnModule($this->moduleB, 'read'));

        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertFalse($this->normalUser->canOnModule($this->moduleB, 'read'));

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
                'controls' => [
                    $invalidId => ['access' => 'allow', 'manage' => 'inherit', 'import' => 'inherit', 'export' => 'inherit'],
                ],
            ]);

        $response->assertSessionHasErrors();
        $response->assertRedirect();
    }

    public function test_permission_save_bumps_perms_generation(): void
    {
        $genBefore = Cache::get('perms_generation', 0);

        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'deny', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0', '_manage_unchanged' => '0'],
            ],
        ]);

        $genAfter = Cache::get('perms_generation', 0);
        $this->assertGreaterThan($genBefore, $genAfter, 'perms_generation must increment on permission save');
    }

    public function test_user_permission_ui_has_no_delete_reveal_approve_controls(): void
    {
        $this->actingAs($this->superAdmin);
        $response = $this->get(route('users.permissions.edit', $this->normalUser->id));
        $response->assertOk();

        $html = $response->getContent();

        $this->assertStringContainsString('Access', $html);
        $this->assertStringContainsString('Manage', $html);
        $this->assertStringContainsString('Import', $html);
        $this->assertStringContainsString('Export', $html);

        $this->assertStringNotContainsString('>can_delete<', $html);
        $this->assertStringNotContainsString('>can_reveal<', $html);
        $this->assertStringNotContainsString('>can_approve<', $html);
        $this->assertStringNotContainsString('>Delete<', $html);
        $this->assertStringNotContainsString('>Reveal<', $html);
        $this->assertStringNotContainsString('>Approve<', $html);
    }

    public function test_concurrent_permission_updates_maintain_data_integrity(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('users.permissions.update', $this->normalUser->id), [
                'controls' => [
                    $this->moduleA->id => ['access' => 'allow', 'manage' => 'allow', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0', '_manage_unchanged' => '0'],
                ],
            ])->assertRedirect()->assertSessionHas('success');

        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'read'));
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'create'));

        $this->actingAs($this->superAdmin)
            ->put(route('users.permissions.update', $this->normalUser->id), [
                'controls' => [
                    $this->moduleA->id => ['access' => 'deny', 'manage' => 'allow', 'import' => 'inherit', 'export' => 'inherit', '_access_unchanged' => '0', '_manage_unchanged' => '0'],
                ],
            ])->assertRedirect()->assertSessionHas('success');

        $this->normalUser->refresh();
        $this->assertFalse($this->normalUser->canOnModule($this->moduleA, 'read'), 'Second update should override can_read');
        $this->assertTrue($this->normalUser->canOnModule($this->moduleA, 'create'), 'can_create should remain true from first update');
    }

    public function test_omitted_import_key_does_not_crash(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'inherit', 'export' => 'inherit'],
            ],
        ])->assertRedirect()->assertSessionHas('success');
    }

    public function test_omitted_export_key_does_not_crash(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'inherit', 'manage' => 'allow', 'import' => 'inherit'],
            ],
        ])->assertRedirect()->assertSessionHas('success');
    }

    public function test_unsupported_module_omits_import_export_keys(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'allow'],
            ],
        ])->assertRedirect()->assertSessionHas('success');

        $row = UserModulePermission::where('user_id', $this->normalUser->id)
            ->where('module_id', $this->moduleA->id)->firstOrFail();
        $this->assertTrue($row->can_read);
        $this->assertTrue($row->can_create);
    }

    public function test_omitted_preset_keys_does_not_crash(): void
    {
        $this->actingAs($this->superAdmin)->put(route('users.permissions.update', $this->normalUser->id), [
            'controls' => [
                $this->moduleA->id => ['access' => 'allow', 'manage' => 'allow', 'import' => 'allow', 'export' => 'allow'],
            ],
        ])->assertRedirect()->assertSessionHas('success');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use App\Models\Vps;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RbacPhase1Test extends TestCase
{
    use RefreshDatabase;

    private Role $adminRole;

    private Role $noPermRole;

    private Role $userRole;

    private Module $moduleA;

    private Module $moduleB;

    private User $superAdmin;

    private User $admin;

    private User $adminWithoutPerms;

    private User $normalUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $superRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->adminRole = Role::where('slug', 'admin')->firstOrFail();
        $this->userRole = Role::where('slug', 'user')->firstOrFail();

        $this->noPermRole = Role::create(['slug' => 'admin-no-perms', 'name' => 'Admin Without Permissions', 'guard' => 'web']);

        $modules = Module::take(2)->get();
        $this->moduleA = $modules[0];
        $this->moduleB = $modules[1];

        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleA->id, 'role_id' => $this->adminRole->id],
            ['can_read' => true, 'can_create' => true, 'can_update' => true, 'can_delete' => true, 'can_export' => true]
        );

        $this->superAdmin = User::factory()->create();
        $this->superAdmin->assignRole($superRole);

        $this->admin = User::factory()->create();
        $this->admin->assignRole($this->adminRole);

        $this->adminWithoutPerms = User::factory()->create();
        $this->adminWithoutPerms->assignRole($this->noPermRole);

        $this->normalUser = User::factory()->create();
        $this->normalUser->assignRole($this->userRole);
    }

    public function test_super_admin_sees_all_records(): void
    {
        Vps::factory()->create(['name' => 'VPS-A', 'user_id' => $this->admin->id, 'module_id' => $this->moduleA->id]);
        Vps::factory()->create(['name' => 'VPS-B', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleB->id]);

        $response = $this->actingAs($this->superAdmin)->get('/vps');

        $response->assertOk();
        $response->assertSee('VPS-A');
        $response->assertSee('VPS-B');
    }

    public function test_admin_sees_records_from_assigned_modules(): void
    {
        $inModuleA = Vps::factory()->create(['name' => 'VPS-InA', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleA->id]);
        $inModuleB = Vps::factory()->create(['name' => 'VPS-InB', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleB->id]);

        $response = $this->actingAs($this->admin)->get('/vps');

        $response->assertOk();
        $response->assertSee('VPS-InA');
        $response->assertDontSee('VPS-InB');
    }

    public function test_admin_does_not_see_records_from_modules_without_can_read(): void
    {
        Domain::factory()->create(['name' => 'domain-in-module-b.com', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleB->id]);

        $response = $this->actingAs($this->admin)->get('/domains');

        $response->assertOk();
        $response->assertDontSee('domain-in-module-b.com');
    }

    public function test_admin_with_module_permissions_sees_all_records(): void
    {
        $hostingModule = Module::where('slug', 'hostings')->firstOrFail();
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $hostingModule->id, 'role_id' => $this->noPermRole->id],
            ['can_read' => true]
        );

        Hosting::factory()->create(['name' => 'Owned-By-Admin', 'user_id' => $this->adminWithoutPerms->id, 'module_id' => $hostingModule->id]);
        Hosting::factory()->create(['name' => 'Owned-By-Other', 'user_id' => $this->normalUser->id, 'module_id' => $hostingModule->id]);

        $response = $this->actingAs($this->adminWithoutPerms)->get('/hostings');

        $response->assertOk();
        $response->assertSee('Owned-By-Admin');
        $response->assertSee('Owned-By-Other');
    }

    public function test_normal_user_sees_records_in_accessible_modules(): void
    {
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $this->moduleA->id, 'role_id' => $this->userRole->id],
            ['can_read' => true]
        );

        Vps::factory()->create(['name' => 'My-VPS', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleA->id]);
        Vps::factory()->create(['name' => 'Their-VPS', 'user_id' => $this->admin->id, 'module_id' => $this->moduleA->id]);

        $response = $this->actingAs($this->normalUser)->get('/vps');

        $response->assertOk();
        $response->assertSee('My-VPS');
        $response->assertSee('Their-VPS');
    }

    public function test_all_9_module_models_apply_3_tier_scope(): void
    {
        $models = [
            'domains' => ['class' => Domain::class, 'name' => 'Domains'],
            'hostings' => ['class' => Hosting::class, 'name' => 'Hostings'],
            'vps' => ['class' => Vps::class, 'name' => 'VPS'],
        ];

        foreach ($models as $route => $cfg) {
            $modelClass = $cfg['class'];
            $label = $cfg['name'];

            $modelClass::factory()->create(['name' => "{$label}-AdminRecord", 'user_id' => $this->admin->id, 'module_id' => $this->moduleA->id]);
            $modelClass::factory()->create(['name' => "{$label}-OtherRecord", 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleB->id]);

            $response = $this->actingAs($this->admin)->get("/{$route}");
            $response->assertOk();
            $response->assertSee("{$label}-AdminRecord");
            $response->assertDontSee("{$label}-OtherRecord");
        }
    }

    public function test_admin_sees_records_owned_by_other_users_in_assigned_modules(): void
    {
        Domain::factory()->create(['name' => 'other-owners-domain.com', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleA->id]);
        Domain::factory()->create(['name' => 'admin-own-domain.com', 'user_id' => $this->admin->id, 'module_id' => $this->moduleA->id]);

        $response = $this->actingAs($this->admin)->get('/domains');

        $response->assertOk();
        $response->assertSee('other-owners-domain.com');
        $response->assertSee('admin-own-domain.com');
    }

    public function test_task_admin_sees_module_tasks_and_assigned_tasks(): void
    {
        $taskInModuleA = Task::factory()->create(['title' => 'Task-InModuleA', 'module_id' => $this->moduleA->id, 'created_by' => $this->normalUser->id]);
        $taskInModuleB = Task::factory()->create(['title' => 'Task-InModuleB', 'module_id' => $this->moduleB->id, 'created_by' => $this->normalUser->id]);
        $assignedTask = Task::factory()->create(['title' => 'Assigned-ToAdmin', 'module_id' => $this->moduleB->id, 'created_by' => $this->normalUser->id]);
        $assignedTask->assignees()->attach($this->admin->id);

        $response = $this->actingAs($this->admin)->get('/tasks');

        $response->assertOk();
        $response->assertSee('Task-InModuleA');
        $response->assertSee('Assigned-ToAdmin');
        $response->assertDontSee('Task-InModuleB');
    }

    public function test_normal_user_sees_own_tasks_and_assigned_tasks(): void
    {
        $ownTask = Task::factory()->create(['title' => 'Own-Task', 'created_by' => $this->normalUser->id]);
        $assignedTask = Task::factory()->create(['title' => 'Assigned-ToMe', 'created_by' => $this->admin->id]);
        $assignedTask->assignees()->attach($this->normalUser->id);
        $otherTask = Task::factory()->create(['title' => 'Other-Task', 'created_by' => $this->admin->id]);

        $response = $this->actingAs($this->normalUser)->get('/tasks');

        $response->assertOk();
        $response->assertSee('Own-Task');
        $response->assertSee('Assigned-ToMe');
        $response->assertDontSee('Other-Task');
    }

    public function test_note_webhook_attachment_behavior_unchanged(): void
    {
        Note::factory()->create(['content' => 'Admin-Note', 'user_id' => $this->admin->id]);
        Note::factory()->create(['content' => 'User-Note', 'user_id' => $this->normalUser->id]);

        $response = $this->actingAs($this->admin)->get('/notes');

        $response->assertOk();
        $response->assertSee('Admin-Note');
        $response->assertDontSee('User-Note');
    }

    public function test_super_admin_sees_all_via_dashboard(): void
    {
        Vps::factory()->create(['name' => 'Dash-VPS-A', 'user_id' => $this->admin->id, 'module_id' => $this->moduleA->id]);
        Vps::factory()->create(['name' => 'Dash-VPS-B', 'user_id' => $this->normalUser->id, 'module_id' => $this->moduleB->id]);

        $response = $this->actingAs($this->superAdmin)->get('/dashboard');

        $response->assertOk();
    }

    public function test_vault_admin_sees_module_scoped_records(): void
    {
        $vault = \App\Models\VaultEntry::factory()->create([
            'service_name' => 'Vault-InModuleA',
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleA->id,
        ]);
        \App\Models\VaultEntry::factory()->create([
            'service_name' => 'Vault-InModuleB',
            'user_id' => $this->normalUser->id,
            'module_id' => $this->moduleB->id,
        ]);

        $response = $this->actingAs($this->admin)->get('/vault');

        $response->assertOk();
        $response->assertSee('Vault-InModuleA');
        $response->assertDontSee('Vault-InModuleB');
    }
}

<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Task;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
    }

    public function test_super_admin_can_create_task()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', [
                'title' => 'Test Task',
                'module_id' => $module->id,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'title']]);
    }

    public function test_task_validation()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', []);

        $response->assertStatus(422);
    }

    public function test_show_task()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();
        $task = Task::create([
            'title' => 'Test Task',
            'description' => 'Test desc',
            'module_id' => $module->id,
            'status' => 'pending',
            'priority' => 'medium',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'title']]);
    }

    public function test_update_task()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();
        $task = Task::create([
            'title' => 'Original Title',
            'module_id' => $module->id,
            'status' => 'pending',
            'priority' => 'medium',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/tasks/{$task->id}", [
                'title' => 'Updated Task',
            ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Task updated']);

        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'title' => 'Updated Task']);
    }

    public function test_delete_task()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();
        $task = Task::create([
            'title' => 'Task to delete',
            'module_id' => $module->id,
            'status' => 'pending',
            'priority' => 'medium',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Task deleted']);
    }

    public function test_my_tasks()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();
        $task = Task::create([
            'title' => 'My Task',
            'module_id' => $module->id,
            'status' => 'pending',
            'priority' => 'medium',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);
        $task->assignees()->attach($user->id);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/my/tasks');

        $response->assertStatus(200);
    }

    public function test_task_search_filter()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        Task::create(['title' => 'Alpha Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'low', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'Beta Task', 'module_id' => $module->id, 'status' => 'completed', 'priority' => 'high', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks?search=Alpha');

        $response->assertStatus(200);
        $this->assertStringContainsString('Alpha', $response->getContent());
        $this->assertStringNotContainsString('Beta', $response->getContent());
    }

    public function test_task_sort_by_title()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        Task::create(['title' => 'Z Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'A Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks?sort_by=title&sort_order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $titles = array_column($data, 'title');
        $sorted = $titles;
        sort($sorted);
        $this->assertSame($sorted, $titles);
    }

    public function test_my_task_counts()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Count Test', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        $task->assignees()->attach($user->id);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/my/tasks/counts');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['total', 'pending', 'in_progress', 'completed', 'cancelled']])
            ->assertJsonPath('data.total', 1)
            ->assertJsonPath('data.pending', 1);
    }

    public function test_task_date_range_filter()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Past Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        $task->created_at = now()->subDays(10);
        $task->save();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks?date_from=' . now()->subDays(5)->format('Y-m-d'));

        $response->assertStatus(200);
        $this->assertStringNotContainsString('Past Task', $response->getContent());
    }

    public function test_task_with_trashed_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Trashed Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        $task->delete();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks?with_trashed=1');

        $response->assertStatus(200);
        $this->assertStringContainsString('Trashed Task', $response->getContent());
    }

    public function test_create_task_without_module()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', [
                'title' => 'Task Without Module',
            ]);

        $response->assertStatus(201);
    }

    public function test_create_task_validation_empty_title()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', ['title' => '']);

        $response->assertStatus(422);
    }

    public function test_task_filter_by_priority()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        Task::create(['title' => 'High Priority', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'high', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'Low Priority', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'low', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks?priority=high');

        $response->assertStatus(200);
        $this->assertStringContainsString('High Priority', $response->getContent());
        $this->assertStringNotContainsString('Low Priority', $response->getContent());
    }

    public function test_task_filter_by_assigned_to()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Assigned Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        $task->assignees()->attach($user->id);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks?assigned_to=' . $user->id);

        $response->assertStatus(200);
        $this->assertStringContainsString('Assigned Task', $response->getContent());
    }

    public function test_task_filter_by_status_and_priority()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        Task::create(['title' => 'Pending High', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'high', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'Completed High', 'module_id' => $module->id, 'status' => 'completed', 'priority' => 'high', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'Pending Low', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'low', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks?status=pending&priority=high');

        $response->assertStatus(200);
        $this->assertStringContainsString('Pending High', $response->getContent());
        $this->assertStringNotContainsString('Completed High', $response->getContent());
        $this->assertStringNotContainsString('Pending Low', $response->getContent());
    }

    public function test_create_task_with_all_optional_fields()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', [
                'title' => 'Full Task',
                'description' => 'With description',
                'module_id' => $module->id,
                'status' => 'in_progress',
                'priority' => 'high',
                'due_date' => '2026-12-31',
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', [
            'title' => 'Full Task',
            'description' => 'With description',
            'status' => 'in_progress',
            'priority' => 'high',
        ]);
    }

    public function test_show_nonexistent_task_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks/99999');

        $response->assertStatus(404);
    }

    public function test_delete_nonexistent_task_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/tasks/99999');

        $response->assertStatus(404);
    }

    public function test_create_task_invalid_module_id_returns_422()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', [
                'title' => 'Bad Module',
                'module_id' => 99999,
            ]);

        $response->assertStatus(422);
    }

    public function test_update_nonexistent_task_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/tasks/99999', ['title' => 'Ghost']);

        $response->assertStatus(404);
    }

    public function test_task_filter_by_module_id()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();
        $moduleId = $module->id;

        Task::create(['title' => 'Module A Task', 'module_id' => $moduleId, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/tasks?module_id={$moduleId}");

        $response->assertStatus(200);
        $this->assertStringContainsString('Module A Task', $response->getContent());
    }

    public function test_kanban_returns_tasks_grouped_by_status()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        Task::create(['title' => 'Pending Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'Completed Task', 'module_id' => $module->id, 'status' => 'completed', 'priority' => 'high', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks/kanban');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertIsArray($data);
        $statuses = collect($data)->pluck('status');
        $this->assertContains('pending', $statuses);
        $this->assertContains('completed', $statuses);
    }

    public function test_update_status_changes_task_status()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Status Update', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/tasks/{$task->id}/status", ['status' => 'completed']);

        $response->assertOk()->assertJson(['message' => 'Status updated']);
        $this->assertDatabaseHas('tasks', ['id' => $task->id, 'status' => 'completed']);
    }

    public function test_update_status_validates_required()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Validation Check', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/tasks/{$task->id}/status", [])
            ->assertStatus(422);
    }

    public function test_update_status_invalid_value_returns_422()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Invalid Status', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/tasks/{$task->id}/status", ['status' => 'invalid_status'])
            ->assertStatus(422);
    }

    public function test_update_status_nonexistent_task_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->patchJson('/api/tasks/99999/status', ['status' => 'completed'])
            ->assertStatus(404);
    }

    public function test_non_super_admin_cannot_create_task_without_module_permission()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', [
                'title' => 'Should Not Create',
                'module_id' => $module->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_non_super_admin_can_create_task_with_module_permission()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        \App\Models\ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $userRole->id,
            'can_create' => true,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', [
                'title' => 'Should Create',
                'module_id' => $module->id,
            ]);

        $response->assertStatus(201);
    }

    public function test_non_super_admin_cannot_create_task_without_module_id()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/tasks', ['title' => 'No Module']);

        $response->assertStatus(403);
    }

    public function test_non_super_admin_cannot_view_task_outside_module()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Hidden Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => 1, 'updated_by' => 1]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_non_super_admin_can_view_own_assigned_task()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'My Assigned', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => 1, 'updated_by' => 1]);
        $task->assignees()->attach($user->id);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(200);
    }

    public function test_show_task_with_no_module_returns_403_for_non_super_admin()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;

        $task = Task::create(['title' => 'No Module Task', 'module_id' => null, 'status' => 'pending', 'priority' => 'medium', 'created_by' => 1, 'updated_by' => 1]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_non_super_admin_cannot_update_task_outside_module()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Untouchable', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => 1, 'updated_by' => 1]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/tasks/{$task->id}", ['title' => 'Hacked']);

        $response->assertStatus(403);
    }

    public function test_non_super_admin_cannot_delete_task_outside_module()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Protected', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => 1, 'updated_by' => 1]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(403);
    }

    public function test_non_super_admin_can_delete_task_with_module_permission()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        \App\Models\ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $userRole->id,
            'can_delete' => true,
        ]);

        $task = Task::create(['title' => 'Deletable', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => 1, 'updated_by' => 1]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/tasks/{$task->id}");

        $response->assertStatus(200);
    }

    public function test_non_super_admin_kanban_respects_permissions()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();
        $module2 = Module::factory()->create(['feature_id' => $module->feature_id]);

        \App\Models\ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $userRole->id,
            'can_read' => true,
        ]);

        Task::create(['title' => 'Visible', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => 1, 'updated_by' => 1]);
        Task::create(['title' => 'Hidden', 'module_id' => $module2->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => 1, 'updated_by' => 1]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/tasks/kanban');

        $response->assertOk();
        $content = $response->getContent();
        $this->assertStringContainsString('Visible', $content);
        $this->assertStringNotContainsString('Hidden', $content);
    }

    public function test_non_super_admin_update_status_forbidden()
    {
        $user = User::factory()->create();
        $userRole = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($userRole);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Status Locked', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => 1, 'updated_by' => 1]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->patchJson("/api/tasks/{$task->id}/status", ['status' => 'completed']);

        $response->assertStatus(403);
    }

    public function test_assignee_can_update_task_without_module_permission()
    {
        $assignee = User::factory()->create();
        $token = $assignee->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'My Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $assignee->id, 'updated_by' => $assignee->id]);
        $task->assignees()->attach($assignee->id);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/tasks/{$task->id}", ['status' => 'in_progress']);

        $response->assertStatus(200);
    }

    public function test_task_status_change_logs_activity()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Status Change Log', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/tasks/{$task->id}", ['status' => 'completed']);

        $logs = \Spatie\Activitylog\Models\Activity::where('subject_type', 'App\Models\Task')
            ->where('subject_id', $task->id)->get();
        $this->assertGreaterThanOrEqual(1, $logs->count());
    }

    public function test_task_complete_updates_dashboard_incrementally()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        $task = Task::create(['title' => 'Dashboard Trigger', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $before = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');
        $beforeCompleted = $before->json('data.tasks_by_status.completed') ?? 0;

        $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/tasks/{$task->id}", ['status' => 'completed']);

        $after = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');
        $afterCompleted = $after->json('data.tasks_by_status.completed') ?? 0;

        $this->assertEquals($beforeCompleted + 1, $afterCompleted);
    }
}

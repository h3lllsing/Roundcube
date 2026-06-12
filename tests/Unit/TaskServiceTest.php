<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskServiceTest extends TestCase
{
    use RefreshDatabase;

    private TaskService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(TaskService::class);
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
    }

    public function test_creates_task(): void
    {
        $user = User::factory()->create();
        $module = Module::first();

        $task = $this->service->create([
            'title' => 'Test Task',
            'module_id' => $module->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $this->assertInstanceOf(Task::class, $task);
        $this->assertEquals('Test Task', $task->title);
        $this->assertEquals($module->id, $task->module_id);
    }

    public function test_creates_task_with_assignees(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $assignee = User::factory()->create();
        $module = Module::first();

        $task = $this->service->create([
            'title' => 'Assigned Task',
            'module_id' => $module->id,
            'created_by' => $user->id,
            'updated_by' => $user->id,
            'assignee_ids' => [$assignee->id],
        ]);

        $this->assertCount(1, $task->assignees);
        $this->assertEquals($assignee->id, $task->assignees->first()->id);

        Notification::assertSentTo($assignee, TaskAssigned::class);
    }

    public function test_update_changes_title(): void
    {
        $user = User::factory()->create();
        $module = Module::first();
        $task = Task::create(['title' => 'Original', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $updated = $this->service->update($task, ['title' => 'Updated']);

        $this->assertEquals('Updated', $updated->title);
    }

    public function test_update_with_assignees_sync(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $oldAssignee = User::factory()->create();
        $newAssignee = User::factory()->create();
        $module = Module::first();

        $task = Task::create(['title' => 'Reassign', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        $task->assignees()->attach($oldAssignee->id, ['assigned_at' => now()]);

        $updated = $this->service->update($task, ['assignee_ids' => [$newAssignee->id]]);

        $this->assertCount(1, $updated->assignees);
        $this->assertEquals($newAssignee->id, $updated->assignees->first()->id);

        Notification::assertSentTo($newAssignee, TaskAssigned::class);
    }

    public function test_delete_soft_deletes(): void
    {
        $user = User::factory()->create();
        $module = Module::first();
        $task = Task::create(['title' => 'Delete Me', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $this->service->delete($task);

        $this->assertSoftDeleted($task);
    }

    public function test_create_increments_dashboard_cache_version(): void
    {
        Cache::forever('dashboard:version', 5);
        $user = User::factory()->create();
        $module = Module::first();

        $this->service->create(['title' => 'Cache Test', 'module_id' => $module->id, 'created_by' => $user->id, 'updated_by' => $user->id]);

        $this->assertEquals(6, Cache::get('dashboard:version'));
    }

    public function test_list_filters_by_status(): void
    {
        $user = User::factory()->create();
        $module = Module::first();
        Task::create(['title' => 'Pending', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'Completed', 'module_id' => $module->id, 'status' => 'completed', 'priority' => 'low', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $result = $this->service->list(['status' => 'pending']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Pending', $result->items()[0]->title);
    }

    public function test_list_filters_by_assigned_to(): void
    {
        $user = User::factory()->create();
        $module = Module::first();
        $task = Task::create(['title' => 'Mine', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        $task->assignees()->attach($user->id, ['assigned_at' => now()]);

        $result = $this->service->list(['assigned_to' => $user->id]);

        $this->assertCount(1, $result->items());
    }

    public function test_find_returns_task_with_relations(): void
    {
        $user = User::factory()->create();
        $module = Module::first();
        $task = Task::create(['title' => 'Find Me', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $found = $this->service->find($task->id);

        $this->assertEquals('Find Me', $found->title);
        $this->assertTrue($found->relationLoaded('module'));
        $this->assertTrue($found->relationLoaded('assignees'));
    }

    public function test_find_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->service->find(99999);
    }

    public function test_list_filters_by_priority(): void
    {
        $module = Module::first();
        $user = User::factory()->create();
        Task::create(['title' => 'High', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'high', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'Low', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'low', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $result = $this->service->list(['priority' => 'high']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('High', $result->items()[0]->title);
    }

    public function test_list_filters_by_module_id(): void
    {
        $module1 = Module::factory()->create(['feature_id' => Module::first()->feature_id]);
        $module2 = Module::factory()->create(['feature_id' => Module::first()->feature_id]);
        $user = User::factory()->create();
        Task::create(['title' => 'M1', 'module_id' => $module1->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'M2', 'module_id' => $module2->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $result = $this->service->list(['module_id' => $module1->id]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('M1', $result->items()[0]->title);
    }

    public function test_list_searches_by_title(): void
    {
        $module = Module::first();
        $user = User::factory()->create();
        Task::create(['title' => 'Unique Bug Fix', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'Other Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $result = $this->service->list(['search' => 'Bug']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_module_ids(): void
    {
        $module1 = Module::factory()->create(['feature_id' => Module::first()->feature_id]);
        $module2 = Module::factory()->create(['feature_id' => Module::first()->feature_id]);
        $module3 = Module::factory()->create(['feature_id' => Module::first()->feature_id]);
        $user = User::factory()->create();
        Task::create(['title' => 'A', 'module_id' => $module1->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'B', 'module_id' => $module2->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'C', 'module_id' => $module3->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $result = $this->service->list(['module_ids' => [$module1->id, $module2->id]]);

        $this->assertCount(2, $result->items());
    }

    public function test_list_filters_by_my_assignee_id(): void
    {
        $module = Module::first();
        $user = User::factory()->create();
        $other = User::factory()->create();
        $task = Task::create(['title' => 'Assigned To Me', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        $task->assignees()->attach($user->id, ['assigned_at' => now()]);
        $task2 = Task::create(['title' => 'Not Mine', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $other->id, 'updated_by' => $other->id]);

        $result = $this->service->list(['my_assignee_id' => $user->id]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Assigned To Me', $result->items()[0]->title);
    }

    public function test_list_filters_by_date_range(): void
    {
        $module = Module::first();
        $user = User::factory()->create();
        $old = Task::create(['title' => 'Old', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        $old->created_at = '2025-01-01';
        $old->save();
        $new = Task::create(['title' => 'New', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $result = $this->service->list(['date_from' => '2026-01-01', 'date_to' => '2026-12-31']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('New', $result->items()[0]->title);
    }

    public function test_list_with_trashed(): void
    {
        $module = Module::first();
        $user = User::factory()->create();
        $task = Task::create(['title' => 'Deleted Task', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        $task->delete();

        $result = $this->service->list(['with_trashed' => true]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_invalid_sort_falls_back_to_created_at(): void
    {
        $module = Module::first();
        $user = User::factory()->create();
        Task::create(['title' => 'Second', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);
        Task::create(['title' => 'First', 'module_id' => $module->id, 'status' => 'pending', 'priority' => 'medium', 'created_by' => $user->id, 'updated_by' => $user->id]);

        $result = $this->service->list(['sort_by' => 'invalid']);

        $this->assertCount(2, $result->items());
    }

    public function test_list_clamps_per_page_to_max(): void
    {
        $module = Module::first();
        $user = User::factory()->create();
        Task::factory()->count(150)->create(['module_id' => $module->id, 'created_by' => $user->id, 'updated_by' => $user->id]);

        $result = $this->service->list(['per_page' => 200]);

        $this->assertEquals(100, $result->perPage());
    }
}

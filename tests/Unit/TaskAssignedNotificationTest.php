<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskAssignedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_returns_database_channel(): void
    {
        $task = Task::factory()->create();
        $notification = new TaskAssigned($task);

        $this->assertEquals(['database'], $notification->via(new User));
    }

    public function test_to_array_returns_task_data(): void
    {
        $creator = User::factory()->create(['name' => 'John Doe']);
        $module = Module::factory()->create(['name' => 'Billing']);
        $task = Task::factory()->create([
            'title' => 'Fix login issue',
            'status' => 'in_progress',
            'priority' => 'high',
            'due_date' => now()->addDays(5),
            'created_by' => $creator->id,
            'module_id' => $module->id,
        ]);
        $notification = new TaskAssigned($task);

        $result = $notification->toArray(new User);

        $this->assertSame('task_assigned', $result['type']);
        $this->assertEquals($task->id, $result['task_id']);
        $this->assertSame('Fix login issue', $result['title']);
        $this->assertSame('in_progress', $result['status']);
        $this->assertSame('high', $result['priority']);
        $this->assertEquals($task->due_date, $result['due_date']);
        $this->assertSame('John Doe', $result['assigned_by_name']);
        $this->assertEquals($creator->id, $result['assigned_by_id']);
        $this->assertSame('Billing', $result['module_name']);
    }

    public function test_to_array_handles_null_creator(): void
    {
        $task = Task::factory()->create(['created_by' => null]);
        $notification = new TaskAssigned($task);

        $result = $notification->toArray(new User);

        $this->assertNull($result['assigned_by_name']);
        $this->assertNull($result['assigned_by_id']);
    }

    public function test_to_array_handles_null_module(): void
    {
        $task = Task::factory()->create(['module_id' => null]);
        $notification = new TaskAssigned($task);

        $result = $notification->toArray(new User);

        $this->assertNull($result['module_name']);
    }
}

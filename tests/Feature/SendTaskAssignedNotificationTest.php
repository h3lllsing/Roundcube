<?php

namespace Tests\Feature;

use App\Events\TaskCreated;
use App\Events\TaskUpdated;
use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskAssigned;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class SendTaskAssignedNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
    }

    public function test_notifies_assignees_when_task_created(): void
    {
        Notification::fake();

        $assignee1 = User::factory()->create();
        $assignee2 = User::factory()->create();
        $task = Task::factory()->create();

        TaskCreated::dispatch($task, [$assignee1->id, $assignee2->id]);

        Notification::assertSentTo($assignee1, TaskAssigned::class);
        Notification::assertSentTo($assignee2, TaskAssigned::class);
    }

    public function test_notifies_assignees_when_task_updated(): void
    {
        Notification::fake();

        $assignee1 = User::factory()->create();
        $assignee2 = User::factory()->create();
        $task = Task::factory()->create();

        TaskUpdated::dispatch($task, null, [$assignee1->id, $assignee2->id]);

        Notification::assertSentTo($assignee1, TaskAssigned::class);
        Notification::assertSentTo($assignee2, TaskAssigned::class);
    }

    public function test_skips_nonexistent_users(): void
    {
        Notification::fake();

        $assignee = User::factory()->create();
        $task = Task::factory()->create();

        TaskCreated::dispatch($task, [$assignee->id, 99999]);

        Notification::assertSentTo($assignee, TaskAssigned::class);
    }

    public function test_notification_contains_task_details(): void
    {
        Notification::fake();

        $assignee = User::factory()->create();
        $task = Task::factory()->create(['title' => 'Test Task', 'status' => 'pending', 'priority' => 'high']);

        TaskCreated::dispatch($task, [$assignee->id]);

        Notification::assertSentTo($assignee, TaskAssigned::class, function ($notification) {
            $data = $notification->toArray(new User());
            return $data['type'] === 'task_assigned'
                && $data['title'] === 'Test Task'
                && $data['status'] === 'pending'
                && $data['priority'] === 'high';
        });
    }
}

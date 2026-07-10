<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckOverdueTasksCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        Notification::fake();
    }

    public function test_sends_notification_for_overdue_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'due_date' => Carbon::now()->subDay(),
            'status' => 'in_progress',
        ]);
        $task->assignees()->attach($user);

        $this->artisan('tasks:check-overdue')
            ->expectsOutputToContain('Found 1 overdue tasks, sent 1 notifications')
            ->assertSuccessful();

        Notification::assertSentTo($user, \App\Notifications\ExpiringSoon::class);
    }

    public function test_sends_notification_to_all_assignees(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $task = Task::factory()->create([
            'due_date' => Carbon::now()->subDay(),
            'status' => 'in_progress',
        ]);
        $task->assignees()->attach([$user1->id, $user2->id]);

        $this->artisan('tasks:check-overdue')
            ->expectsOutputToContain('Found 1 overdue tasks, sent 2 notifications')
            ->assertSuccessful();

        Notification::assertSentTo($user1, \App\Notifications\ExpiringSoon::class);
        Notification::assertSentTo($user2, \App\Notifications\ExpiringSoon::class);
    }

    public function test_skips_non_overdue_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'due_date' => Carbon::now()->addDay(),
            'status' => 'in_progress',
        ]);
        $task->assignees()->attach($user);

        $this->artisan('tasks:check-overdue')
            ->expectsOutputToContain('Found 0 overdue tasks, sent 0 notifications')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_skips_completed_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'due_date' => Carbon::now()->subDay(),
            'status' => 'completed',
        ]);
        $task->assignees()->attach($user);

        $this->artisan('tasks:check-overdue')
            ->expectsOutputToContain('Found 0 overdue tasks, sent 0 notifications')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_skips_cancelled_tasks(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'due_date' => Carbon::now()->subDay(),
            'status' => 'cancelled',
        ]);
        $task->assignees()->attach($user);

        $this->artisan('tasks:check-overdue')
            ->expectsOutputToContain('Found 0 overdue tasks, sent 0 notifications')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_skips_tasks_without_due_date(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'due_date' => null,
            'status' => 'in_progress',
        ]);
        $task->assignees()->attach($user);

        $this->artisan('tasks:check-overdue')
            ->expectsOutputToContain('Found 0 overdue tasks, sent 0 notifications')
            ->assertSuccessful();

        Notification::assertNothingSent();
    }

    public function test_handles_tasks_without_assignees(): void
    {
        Task::factory()->create([
            'due_date' => Carbon::now()->subDay(),
            'status' => 'in_progress',
        ]);

        $this->artisan('tasks:check-overdue')
            ->expectsOutputToContain('Found 1 overdue tasks, sent 0 notifications')
            ->assertSuccessful();
    }
}

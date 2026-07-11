<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskOverdue;
use Carbon\Carbon;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class TaskOverdueNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $this->admin = User::factory()->create(['name' => 'Admin', 'email' => 'admin@test.com']);
        $this->admin->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
    }

    public function test_subject_uses_task_terminology(): void
    {
        $task = Task::factory()->create([
            'title' => 'Configure SMTP',
            'due_date' => Carbon::today()->subDays(3),
        ]);

        $notification = new TaskOverdue($task, 3);
        $mail = $notification->toMail($this->admin);

        $this->assertStringContainsString('Task', $mail->subject);
        $this->assertStringContainsString('overdue', $mail->subject);
        $this->assertStringContainsString('[OpsPilot]', $mail->subject);
        $this->assertStringContainsString('Configure SMTP', $mail->subject);
    }

    public function test_subject_does_not_use_expiry_terminology(): void
    {
        $task = Task::factory()->create([
            'title' => 'Update DNS',
            'due_date' => Carbon::today()->subDays(1),
        ]);

        $notification = new TaskOverdue($task, 1);
        $mail = $notification->toMail($this->admin);

        $this->assertStringNotContainsString('expir', $mail->subject);
        $this->assertStringNotContainsString('renew', $mail->subject);
    }

    public function test_body_uses_task_terminology(): void
    {
        $task = Task::factory()->create([
            'title' => 'Review logs',
            'due_date' => Carbon::today()->subDays(2),
        ]);

        $notification = new TaskOverdue($task, 2);
        $rendered = $notification->toMail($this->admin)->render();

        $this->assertStringContainsString('Task', $rendered);
        $this->assertStringContainsString('overdue', $rendered);
        $this->assertStringContainsString('Review logs', $rendered);
        $this->assertStringNotContainsString('expired', $rendered);
        $this->assertStringNotContainsString('renewal', $rendered);
    }

    public function test_links_to_tasks_show_route(): void
    {
        $task = Task::factory()->create([
            'title' => 'Deploy server',
            'due_date' => Carbon::today()->subDays(1),
        ]);

        $notification = new TaskOverdue($task, 1);
        $mail = $notification->toMail($this->admin);

        $this->assertStringContainsString(route('tasks.show', $task->id), $mail->actionUrl);
    }

    public function test_contains_due_date_and_status(): void
    {
        $task = Task::factory()->create([
            'title' => 'Fix bug',
            'due_date' => Carbon::today()->subDays(5),
            'status' => 'in_progress',
        ]);

        $notification = new TaskOverdue($task, 5);
        $rendered = $notification->toMail($this->admin)->render();

        $this->assertStringContainsString($task->due_date->format('Y-m-d'), $rendered);
        $this->assertStringContainsString('in_progress', $rendered);
    }

    public function test_contains_days_overdue(): void
    {
        $task = Task::factory()->create([
            'title' => 'Fix bug',
            'due_date' => Carbon::today()->subDays(3),
        ]);

        $notification = new TaskOverdue($task, 3);
        $rendered = $notification->toMail($this->admin)->render();

        $this->assertStringContainsString('3', $rendered);
    }

    public function test_stores_database_notification(): void
    {
        Notification::fake();

        $task = Task::factory()->create([
            'title' => 'Test task',
            'due_date' => Carbon::today()->subDays(1),
        ]);

        $this->admin->notify(new TaskOverdue($task, 1));

        Notification::assertSentTo($this->admin, TaskOverdue::class, function ($notification) {
            $data = $notification->toArray($this->admin);

            return $data['type'] === 'task_overdue'
                && $data['title'] === 'Test task'
                && $data['days_overdue'] === 1;
        });
    }

    public function test_recipient_reason(): void
    {
        $task = Task::factory()->create([
            'title' => 'My task',
            'due_date' => Carbon::today()->subDays(1),
        ]);

        $notification = new TaskOverdue($task, 1);
        $rendered = $notification->toMail($this->admin)->render();

        $this->assertStringContainsString('assigned to this task', $rendered);
    }
}

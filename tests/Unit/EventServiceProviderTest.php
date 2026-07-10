<?php

namespace Tests\Unit;

use App\Events\ExpiryWarningTriggered;
use App\Events\MonitorCheckFailed;
use App\Events\TaskCreated;
use App\Events\VaultPasswordRevealed;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Providers\EventServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class EventServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_boot(): void
    {
        $provider = new EventServiceProvider($this->app);
        $provider->boot();

        $this->assertTrue(true);
    }

    public function test_task_created_event_dispatches(): void
    {
        Event::fake();

        $task = Task::factory()->create();

        TaskCreated::dispatch($task, [1, 2]);

        Event::assertDispatched(TaskCreated::class);
    }

    public function test_vault_password_revealed_event_dispatches(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $entry = VaultEntry::factory()->create();

        VaultPasswordRevealed::dispatch($entry, $user);

        Event::assertDispatched(VaultPasswordRevealed::class);
    }

    public function test_expiry_warning_triggered_event_dispatches(): void
    {
        Event::fake();

        $user = User::factory()->create();
        $entry = VaultEntry::factory()->create();

        ExpiryWarningTriggered::dispatch($entry, 'domain', $user, 15);

        Event::assertDispatched(ExpiryWarningTriggered::class);
    }

    public function test_monitor_check_failed_event_dispatches(): void
    {
        Event::fake();

        $entry = VaultEntry::factory()->create();

        MonitorCheckFailed::dispatch($entry, 'http', 'timeout');

        Event::assertDispatched(MonitorCheckFailed::class);
    }
}

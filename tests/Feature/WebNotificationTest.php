<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebNotificationTest extends TestCase
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

    public function test_notifications_index_page_loads(): void
    {
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Test'],
        ]);
        $this->actingAs($this->admin);
        $this->get(route('notifications.index'))->assertStatus(200)->assertSee('Test');
    }

    public function test_notifications_index_empty(): void
    {
        $this->actingAs($this->admin);
        $this->get(route('notifications.index'))->assertStatus(200)->assertSee('No notifications');
    }

    public function test_notification_mark_as_read(): void
    {
        $notification = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Read me'],
        ]);
        $this->assertNull($notification->fresh()->read_at);
        $this->actingAs($this->admin);
        $this->post(route('notifications.read', $notification->id))
            ->assertRedirect();
        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_notification_mark_all_as_read(): void
    {
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'A'],
        ]);
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'B'],
        ]);
        $this->actingAs($this->admin);
        $this->post(route('notifications.read-all'))->assertRedirect();
        $this->assertEquals(0, $this->admin->fresh()->unreadNotifications->count());
    }

    public function test_notification_destroy_deletes(): void
    {
        $notification = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Delete me'],
        ]);
        $this->actingAs($this->admin);
        $this->delete(route('notifications.destroy', $notification->id))
            ->assertRedirect();
        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    public function test_notifications_unread_filter(): void
    {
        $this->actingAs($this->admin);
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'AlreadyRead'],
            'read_at' => now(),
        ]);
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'StillUnread'],
        ]);
        $this->get(route('notifications.index', ['unread' => 1]))
            ->assertStatus(200)
            ->assertSee('StillUnread')
            ->assertDontSee('AlreadyRead');
    }

    public function test_notifications_bulk_delete(): void
    {
        $n1 = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Bulk1'],
        ]);
        $n2 = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Bulk2'],
        ]);
        $this->actingAs($this->admin);
        $this->post(route('notifications.bulk-delete'), ['ids' => [$n1->id, $n2->id]])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertDatabaseMissing('notifications', ['id' => $n1->id]);
        $this->assertDatabaseMissing('notifications', ['id' => $n2->id]);
    }

    public function test_notifications_bulk_mark_as_read(): void
    {
        $n1 = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Unread1'],
        ]);
        $n2 = $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\Test', 'data' => ['message' => 'Unread2'],
        ]);
        $this->assertNull($n1->fresh()->read_at);
        $this->assertNull($n2->fresh()->read_at);
        $this->actingAs($this->admin);
        $this->post(route('notifications.bulk-read'), ['ids' => [$n1->id, $n2->id]])
            ->assertRedirect()
            ->assertSessionHas('success');
        $this->assertNotNull($n1->fresh()->read_at);
        $this->assertNotNull($n2->fresh()->read_at);
    }

    public function test_notification_shows_task_assigned_richly(): void
    {
        $task = Task::factory()->create(['title' => 'RichNotifTask']);
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\TaskAssigned',
            'data' => [
                'type' => 'task_assigned',
                'task_id' => $task->id,
                'title' => 'RichNotifTask',
                'status' => 'pending',
                'priority' => 'high',
                'due_date' => now()->addDays(5)->toDateString(),
                'assigned_by_name' => 'Admin',
            ],
        ]);
        $this->actingAs($this->admin);
        $this->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertSee('RichNotifTask')
            ->assertSee('high')
            ->assertSee('pending');
    }

    public function test_notification_shows_note_added_richly(): void
    {
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\NoteAdded',
            'data' => [
                'type' => 'note_added',
                'note_id' => 1,
                'content' => 'This is a rich note notification content',
                'added_by_name' => 'Admin',
            ],
        ]);
        $this->actingAs($this->admin);
        $this->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertSee('added a note')
            ->assertSee('Admin');
    }

    public function test_notification_shows_expiring_soon_richly(): void
    {
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\ExpiringSoon',
            'data' => [
                'type' => 'expiring_soon',
                'name' => 'ExpiringDomain',
                'entity_type' => 'Domain',
                'days_remaining' => 3,
                'expiry_date' => now()->addDays(3)->toDateString(),
            ],
        ]);
        $this->actingAs($this->admin);
        $this->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertSee('ExpiringDomain')
            ->assertSee('expires in 3 day(s)');
    }

    public function test_notification_shows_vault_revealed_richly(): void
    {
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\VaultPasswordRevealed',
            'data' => [
                'type' => 'vault_password_revealed',
                'service' => 'MySecretService',
                'revealed_by' => 'Admin',
            ],
        ]);
        $this->actingAs($this->admin);
        $this->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertSee('MySecretService')
            ->assertSee('Vault password');
    }

    public function test_notification_shows_monitor_failed_richly(): void
    {
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\MonitorCheckFailed',
            'data' => [
                'type' => 'monitor_check_failed',
                'resource_name' => 'MyServer',
                'error' => 'Connection timeout',
            ],
        ]);
        $this->actingAs($this->admin);
        $this->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertSee('MyServer')
            ->assertSee('Monitor check failed')
            ->assertSee('Connection timeout');
    }

    public function test_notifications_index_search_filters_by_type(): void
    {
        $this->actingAs($this->admin);
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\TaskAssigned', 'data' => ['title' => 'TaskOne'],
        ]);
        $this->admin->notifications()->create([
            'id' => Str::uuid(), 'type' => 'App\Notifications\NoteAdded', 'data' => ['title' => 'TaskTwo'],
        ]);

        $this->get(route('notifications.index', ['search' => 'TaskAssigned']))
            ->assertStatus(200)
            ->assertSee('TaskAssigned')
            ->assertDontSee('NoteAdded');
    }

    protected function tearDown(): void
    {
        // Ensure notification cleanup doesn't interfere with other tests
        $this->admin->notifications()->delete();
        parent::tearDown();
    }
}

<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use App\Notifications\NoteAdded;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    public function test_list_notifications()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $user->notify(new NoteAdded(new Note(['id' => 1, 'content' => 'Test'])));

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/notifications');

        $response->assertStatus(200);
    }

    public function test_unread_count()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $user->notify(new NoteAdded(new Note(['id' => 2, 'content' => 'Unread test'])));

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/notifications/unread');

        $response->assertStatus(200);
    }

    public function test_mark_as_read()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $user->notify(new NoteAdded(new Note(['id' => 3, 'content' => 'Mark read'])));
        $user->refresh();
        $notifId = $user->notifications->first()->id;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/notifications/{$notifId}/read");

        $response->assertStatus(200);
    }

    public function test_mark_all_as_read()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $user->notify(new NoteAdded(new Note(['id' => 4, 'content' => 'All read 1'])));
        $user->notify(new NoteAdded(new Note(['id' => 5, 'content' => 'All read 2'])));
        $user->refresh();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/notifications/read-all');

        $response->assertStatus(200);
        $this->assertEquals(0, $user->fresh()->unreadNotifications->count());
    }

    public function test_delete_notification()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $user->notify(new NoteAdded(new Note(['id' => 6, 'content' => 'Delete me'])));
        $user->refresh();
        $notifId = $user->notifications->first()->id;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/notifications/{$notifId}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Notification deleted']);
        $this->assertDatabaseMissing('notifications', ['id' => $notifId]);
    }

    public function test_mark_nonexistent_notification_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/notifications/99999/read');

        $response->assertStatus(404);
    }

    public function test_delete_nonexistent_notification_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/notifications/99999');

        $response->assertStatus(404);
    }

    public function test_notifications_unauthenticated()
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(401);
    }

    public function test_notifications_other_user_cannot_see_them()
    {
        $alice = User::factory()->create();
        $alice->notify(new NoteAdded(new Note(['id' => 10, 'content' => 'Alice note'])));
        $bob = User::factory()->create();
        $bob->assignRole(Role::where('slug', 'super-admin')->firstOrFail());
        $token = $bob->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/notifications');

        $response->assertStatus(200);
        $this->assertCount(0, $response->json('data'));
    }

    public function test_notification_index_search(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $user->notify(new NoteAdded(new Note(['id' => 20, 'content' => 'Searchable'])));
        $user->notify(new NoteAdded(new Note(['id' => 21, 'content' => 'Other'])));
        $user->refresh();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/notifications?search=Searchable');

        $response->assertStatus(200);
        $this->assertStringContainsString('Searchable', $response->getContent());
    }

    public function test_notification_unread_search(): void
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $user->notify(new NoteAdded(new Note(['id' => 30, 'content' => 'UnreadSearch'])));
        $user->notify(new NoteAdded(new Note(['id' => 31, 'content' => 'UnreadOther'])));
        $user->refresh();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/notifications/unread?search=UnreadSearch');

        $response->assertStatus(200);
        $this->assertStringContainsString('UnreadSearch', $response->getContent());
    }
}

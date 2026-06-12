<?php

namespace Tests\Unit;

use App\Models\Note;
use App\Models\User;
use App\Services\NoteService;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class NoteServiceTest extends TestCase
{
    use RefreshDatabase;

    private NoteService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(NoteService::class);
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
    }

    public function test_creates_global_note(): void
    {
        $user = User::factory()->create();
        $note = $this->service->create(['content' => 'Test note', 'user_id' => $user->id]);

        $this->assertInstanceOf(Note::class, $note);
        $this->assertEquals('Test note', $note->content);
        $this->assertEquals($user->id, $note->user_id);
        $this->assertNull($note->notable_type);
    }

    public function test_lists_global_notes(): void
    {
        $user = User::factory()->create();
        Note::create(['content' => 'Note 1', 'user_id' => $user->id]);
        Note::create(['content' => 'Note 2', 'user_id' => $user->id]);

        $result = $this->service->listFor();
        $this->assertCount(2, $result->items());
    }

    public function test_deletes_note(): void
    {
        $user = User::factory()->create();
        $note = Note::create(['content' => 'Delete me', 'user_id' => $user->id]);

        $this->service->delete($note);

        $this->assertModelMissing($note);
    }

    public function test_create_sends_notification_to_other_super_admins(): void
    {
        Notification::fake();

        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $author = User::factory()->create();
        $author->assignRole($superAdminRole);
        $otherAdmin = User::factory()->create();
        $otherAdmin->assignRole($superAdminRole);

        $this->service->create(['content' => 'Notify test', 'user_id' => $author->id]);

        Notification::assertSentTo($otherAdmin, \App\Notifications\NoteAdded::class);
    }

    public function test_create_does_not_notify_note_author(): void
    {
        Notification::fake();

        $superAdminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $author = User::factory()->create();
        $author->assignRole($superAdminRole);

        $this->service->create(['content' => 'Self note', 'user_id' => $author->id]);

        Notification::assertNotSentTo($author, \App\Notifications\NoteAdded::class);
    }

    public function test_list_filters_by_user_id(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        Note::create(['content' => 'Mine', 'user_id' => $user->id]);
        Note::create(['content' => 'Theirs', 'user_id' => $other->id]);

        $result = $this->service->listFor(null, ['user_id' => $user->id]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Mine', $result->items()[0]->content);
    }

    public function test_list_searches_by_content(): void
    {
        $user = User::factory()->create();
        Note::create(['content' => 'Alpha note', 'user_id' => $user->id]);
        Note::create(['content' => 'Beta note', 'user_id' => $user->id]);

        $result = $this->service->listFor(null, ['search' => 'Alpha']);

        $this->assertCount(1, $result->items());
        $this->assertStringContainsString('Alpha', $result->items()[0]->content);
    }
}

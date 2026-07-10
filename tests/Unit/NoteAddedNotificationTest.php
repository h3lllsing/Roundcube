<?php

namespace Tests\Unit;

use App\Models\Note;
use App\Models\User;
use App\Notifications\NoteAdded;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteAddedNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_via_returns_database_channel(): void
    {
        $note = Note::factory()->create();
        $notification = new NoteAdded($note);

        $this->assertEquals(['database'], $notification->via(new User));
    }

    public function test_to_array_returns_note_data(): void
    {
        $user = User::factory()->create(['name' => 'Jane Doe']);
        $note = Note::factory()->create([
            'content' => 'Important note content',
            'user_id' => $user->id,
            'notable_type' => 'App\Models\Module',
            'notable_id' => 42,
        ]);
        $notification = new NoteAdded($note);

        $result = $notification->toArray(new User);

        $this->assertSame('note_added', $result['type']);
        $this->assertEquals($note->id, $result['note_id']);
        $this->assertSame('Important note content', $result['content']);
        $this->assertSame('Jane Doe', $result['added_by_name']);
        $this->assertEquals($user->id, $result['added_by_id']);
        $this->assertSame('App\Models\Module', $result['notable_type']);
        $this->assertSame(42, $result['notable_id']);
    }
}

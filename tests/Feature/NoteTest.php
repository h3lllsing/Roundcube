<?php

namespace Tests\Feature;

use App\Models\Note;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
    }

    public function test_create_global_note_validation_empty_content()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/notes', ['content' => '']);

        $response->assertStatus(422);
    }

    public function test_create_global_note()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/notes', ['content' => 'My global note']);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'content']]);
    }

    public function test_list_global_notes()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        Note::create(['content' => 'Note 1', 'user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/notes');

        $response->assertStatus(200);
    }

    public function test_owner_can_delete_note()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $note = Note::create(['content' => 'Delete me', 'user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/notes/{$note->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Note deleted']);
    }

    public function test_other_user_cannot_delete_note()
    {
        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;
        $note = Note::create(['content' => 'Protected', 'user_id' => $owner->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/notes/{$note->id}");

        $response->assertStatus(403);
    }

    public function test_note_search_filter()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        Note::create(['content' => 'Unique searchable content', 'user_id' => $user->id]);
        Note::create(['content' => 'Something else entirely', 'user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/notes?search=Unique');

        $response->assertStatus(200);
        $this->assertStringContainsString('Unique', $response->getContent());
        $this->assertStringNotContainsString('Something else', $response->getContent());
    }

    public function test_note_sort_by_content()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        Note::create(['content' => 'Z note content', 'user_id' => $user->id]);
        Note::create(['content' => 'A note content', 'user_id' => $user->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/notes?sort_by=content&sort_order=asc');

        $response->assertStatus(200);
        $data = $response->json('data');
        $contents = array_column($data, 'content');
        $sorted = $contents;
        sort($sorted);
        $this->assertSame($sorted, $contents);
    }

    public function test_create_note_on_feature()
    {
        $user = User::factory()->create();
        $user->assignRole(\HasinHayder\Tyro\Models\Role::where('slug', 'super-admin')->firstOrFail());
        $token = $user->createToken('test')->plainTextToken;
        $feature = \App\Models\Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/features/{$feature->id}/notes", ['content' => 'Feature note']);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'content']]);
    }

    public function test_create_note_on_module()
    {
        $user = User::factory()->create();
        $user->assignRole(\HasinHayder\Tyro\Models\Role::where('slug', 'super-admin')->firstOrFail());
        $token = $user->createToken('test')->plainTextToken;
        $module = \App\Models\Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/modules/{$module->id}/notes", ['content' => 'Module note']);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'content']]);
    }

    public function test_list_feature_notes()
    {
        $user = User::factory()->create();
        $user->assignRole(\HasinHayder\Tyro\Models\Role::where('slug', 'super-admin')->firstOrFail());
        $token = $user->createToken('test')->plainTextToken;
        $feature = \App\Models\Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/features/{$feature->id}/notes");

        $response->assertStatus(200);
    }

    public function test_list_module_notes()
    {
        $user = User::factory()->create();
        $user->assignRole(\HasinHayder\Tyro\Models\Role::where('slug', 'super-admin')->firstOrFail());
        $token = $user->createToken('test')->plainTextToken;
        $module = \App\Models\Module::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/modules/{$module->id}/notes");

        $response->assertStatus(200);
    }

    public function test_delete_nonexistent_note_returns_404()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/notes/99999');

        $response->assertStatus(404);
    }

    public function test_module_notes_returns_paginated_response()
    {
        $user = User::factory()->create();
        $user->assignRole(\HasinHayder\Tyro\Models\Role::where('slug', 'super-admin')->firstOrFail());
        $token = $user->createToken('test')->plainTextToken;
        $module = \App\Models\Module::first();

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/modules/{$module->id}/notes", ['content' => 'Paginated note 1']);
        $this->withHeader('Authorization', "Bearer $token")
            ->postJson("/api/modules/{$module->id}/notes", ['content' => 'Paginated note 2']);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/modules/{$module->id}/notes");

        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }
}

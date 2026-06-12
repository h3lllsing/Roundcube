<?php

namespace Tests\Feature;

use App\Models\Attachment;
use App\Models\Domain;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
    }

    public function test_upload_attachment()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $file = UploadedFile::fake()->create('document.pdf', 500, 'application/pdf');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/attachments', [
                'file' => $file,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'filename', 'original_name', 'mime_type', 'size']]);

        $this->assertDatabaseHas('attachments', [
            'original_name' => 'document.pdf',
            'user_id' => $user->id,
        ]);

        Storage::disk('public')->assertExists('attachments/' . $response->json('data.filename'));
    }

    public function test_list_attachments()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        Attachment::create([
            'user_id' => $user->id,
            'filename' => 'test.txt',
            'original_name' => 'test.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/attachments');

        $response->assertStatus(200);
    }

    public function test_show_attachment()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $attachment = Attachment::create([
            'user_id' => $user->id,
            'filename' => 'test.txt',
            'original_name' => 'test.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/attachments/{$attachment->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'filename']]);
    }

    public function test_download_attachment()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $file = UploadedFile::fake()->create('download.docx', 200, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        $path = $file->store('attachments', 'public');

        $attachment = Attachment::create([
            'user_id' => $user->id,
            'filename' => basename($path),
            'original_name' => 'download.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'size' => 200,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/attachments/{$attachment->id}/download");

        $response->assertStatus(200);
    }

    public function test_delete_attachment_owner()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $file = UploadedFile::fake()->create('delete.txt', 100);
        $path = $file->store('attachments', 'public');

        $attachment = Attachment::create([
            'user_id' => $user->id,
            'filename' => basename($path),
            'original_name' => 'delete.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/attachments/{$attachment->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Attachment deleted']);

        $this->assertSoftDeleted($attachment);
        Storage::disk('public')->assertMissing('attachments/' . $attachment->filename);
    }

    public function test_delete_attachment_forbidden_other_user()
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $attachment = Attachment::create([
            'user_id' => $owner->id,
            'filename' => 'test.txt',
            'original_name' => 'test.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/attachments/{$attachment->id}");

        $response->assertStatus(403);
    }

    public function test_list_filtered_by_notable()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feature = \App\Models\Feature::first();

        $attachment = Attachment::create([
            'user_id' => $user->id,
            'notable_type' => get_class($feature),
            'notable_id' => $feature->id,
            'filename' => 'feature.txt',
            'original_name' => 'feature.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        Attachment::create([
            'user_id' => $user->id,
            'notable_type' => null,
            'notable_id' => null,
            'filename' => 'global.txt',
            'original_name' => 'global.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/attachments?notable_type=' . urlencode(get_class($feature)) . '&notable_id=' . $feature->id);

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('feature.txt', $response->json('data.0.original_name'));
    }

    public function test_requires_authentication()
    {
        $response = $this->postJson('/api/attachments', []);
        $response->assertStatus(401);

        $response = $this->getJson('/api/attachments');
        $response->assertStatus(401);

        $response = $this->getJson('/api/attachments/1');
        $response->assertStatus(401);
    }

    public function test_show_attachment_forbidden_other_user()
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $attachment = Attachment::create([
            'user_id' => $owner->id,
            'filename' => 'secret.txt',
            'original_name' => 'secret.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/attachments/{$attachment->id}")
            ->assertStatus(403);
    }

    public function test_download_attachment_forbidden_other_user()
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;

        $attachment = Attachment::create([
            'user_id' => $owner->id,
            'filename' => 'private.docx',
            'original_name' => 'private.docx',
            'mime_type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'size' => 200,
        ]);

        $this->withHeader('Authorization', "Bearer $token")
            ->get("/api/attachments/{$attachment->id}/download")
            ->assertStatus(403);
    }

    public function test_store_attachment_rejects_invalid_notable_type()
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $file = UploadedFile::fake()->create('test.pdf', 100, 'application/pdf');

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/attachments', [
                'file' => $file,
                'notable_type' => 'App\Models\InvalidModel',
                'notable_id' => 1,
            ]);

        $response->assertStatus(422);
    }

    public function test_store_attachment_forbidden_to_attach_to_others_notable()
    {
        Storage::fake('public');

        $owner = User::factory()->create();
        $other = User::factory()->create();
        $token = $other->createToken('test')->plainTextToken;
        $file = UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf');

        $domain = Domain::factory()->create(['user_id' => $owner->id]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/attachments', [
                'file' => $file,
                'notable_type' => get_class($domain),
                'notable_id' => $domain->id,
            ]);

        $response->assertStatus(403);
    }

    public function test_index_filters_by_user_for_non_admin(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $other = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        Attachment::create([
            'user_id' => $user->id,
            'filename' => 'mine.txt',
            'original_name' => 'mine.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);
        Attachment::create([
            'user_id' => $other->id,
            'filename' => 'theirs.txt',
            'original_name' => 'theirs.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/attachments');

        $response->assertOk();
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('mine.txt', $response->json('data.0.original_name'));
    }
}

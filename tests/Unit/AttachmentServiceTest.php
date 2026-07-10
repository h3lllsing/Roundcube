<?php

namespace Tests\Unit;

use App\Models\Attachment;
use App\Models\Feature;
use App\Models\User;
use App\Services\AttachmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentServiceTest extends TestCase
{
    use RefreshDatabase;

    private AttachmentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(AttachmentService::class);
    }

    public function test_create_stores_file_and_record(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('report.pdf', 500, 'application/pdf');

        $attachment = $this->service->create([
            'user_id' => $user->id,
            'file' => $file,
        ]);

        $this->assertInstanceOf(Attachment::class, $attachment);
        $this->assertEquals('report.pdf', $attachment->original_name);
        $this->assertEquals($user->id, $attachment->user_id);

        Storage::disk('public')->assertExists('attachments/'.$attachment->filename);
    }

    public function test_create_with_notable(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $feature = Feature::factory()->create();
        $file = UploadedFile::fake()->create('feature.pdf', 200, 'application/pdf');

        $attachment = $this->service->create([
            'user_id' => $user->id,
            'file' => $file,
        ], $feature);

        $this->assertEquals($feature->getMorphClass(), $attachment->notable_type);
        $this->assertEquals($feature->id, $attachment->notable_id);
    }

    public function test_delete_removes_file_and_soft_deletes(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();
        $file = UploadedFile::fake()->create('delete-me.pdf', 100, 'application/pdf');

        $attachment = $this->service->create([
            'user_id' => $user->id,
            'file' => $file,
        ]);

        $filename = $attachment->filename;
        $this->service->delete($attachment);

        Storage::disk('public')->assertExists('attachments/'.$filename);
        $this->assertSoftDeleted($attachment);
    }

    public function test_list_for_global_attachments(): void
    {
        $user = User::factory()->create();
        Attachment::create([
            'user_id' => $user->id,
            'filename' => 'global.txt',
            'original_name' => 'global.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        $result = $this->service->listFor(null, ['user_id' => $user->id]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_for_notable(): void
    {
        $user = User::factory()->create();
        $feature = Feature::factory()->create();

        $attachment = $feature->attachments()->create([
            'user_id' => $user->id,
            'filename' => 'notable.txt',
            'original_name' => 'notable.txt',
            'mime_type' => 'text/plain',
            'size' => 100,
        ]);

        $result = $this->service->listFor($feature);

        $this->assertCount(1, $result->items());
        $this->assertEquals('notable.txt', $result->items()[0]->original_name);
    }

    public function test_list_searches_by_original_name(): void
    {
        $user = User::factory()->create();
        Attachment::create(['user_id' => $user->id, 'filename' => 'a.txt', 'original_name' => 'Alpha Report', 'mime_type' => 'text/plain', 'size' => 100]);
        Attachment::create(['user_id' => $user->id, 'filename' => 'b.txt', 'original_name' => 'Beta Report', 'mime_type' => 'text/plain', 'size' => 200]);

        $result = $this->service->listFor(null, ['search' => 'Alpha']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_invalid_sort_falls_back_to_created_at(): void
    {
        $user = User::factory()->create();
        Attachment::create(['user_id' => $user->id, 'filename' => 'a.txt', 'original_name' => 'A', 'mime_type' => 'text/plain', 'size' => 100]);
        Attachment::create(['user_id' => $user->id, 'filename' => 'b.txt', 'original_name' => 'B', 'mime_type' => 'text/plain', 'size' => 200]);

        $result = $this->service->listFor(null, ['sort_by' => 'invalid']);

        $this->assertCount(2, $result->items());
    }

    public function test_list_clamps_per_page_to_max(): void
    {
        $user = User::factory()->create();
        Attachment::factory()->count(150)->create(['user_id' => $user->id, 'mime_type' => 'text/plain']);

        $result = $this->service->listFor(null, ['per_page' => 200]);

        $this->assertEquals(100, $result->perPage());
    }

    public function test_list_invalid_sort_order_falls_back_to_desc(): void
    {
        $user = User::factory()->create();
        Attachment::create(['user_id' => $user->id, 'filename' => 'a.txt', 'original_name' => 'A', 'mime_type' => 'text/plain', 'size' => 100]);
        Attachment::create(['user_id' => $user->id, 'filename' => 'b.txt', 'original_name' => 'B', 'mime_type' => 'text/plain', 'size' => 200]);

        $result = $this->service->listFor(null, ['sort_order' => 'invalid']);

        $this->assertCount(2, $result->items());
    }

    public function test_create_throws_when_store_fails(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to store file');

        $user = User::factory()->create();
        $file = $this->createMock(UploadedFile::class);
        $file->method('store')->willReturn(false);
        $file->method('getClientOriginalName')->willReturn('fail.pdf');
        $file->method('getMimeType')->willReturn('application/pdf');
        $file->method('getSize')->willReturn(100);

        $this->service->create(['user_id' => $user->id, 'file' => $file]);
    }
}

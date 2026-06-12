<?php

namespace Tests\Unit;

use App\Models\OtherService;
use App\Models\User;
use App\Services\OtherServiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OtherServiceServiceTest extends TestCase
{
    use RefreshDatabase;

    private OtherServiceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(OtherServiceService::class);
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $otherService = $this->service->create(['name' => 'Sentry', 'service_type' => 'monitoring', 'user_id' => $user->id]);

        $this->assertEquals('Sentry', $otherService->name);
    }

    public function test_list(): void
    {
        $user = User::factory()->create();
        OtherService::factory()->count(2)->create(['user_id' => $user->id]);

        $result = $this->service->list(['user_id' => $user->id]);

        $this->assertCount(2, $result->items());
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $otherService = OtherService::factory()->create(['user_id' => $user->id, 'name' => 'Old Service']);

        $updated = $this->service->update($otherService, ['name' => 'Updated']);

        $this->assertEquals('Updated', $updated->name);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $otherService = OtherService::factory()->create(['user_id' => $user->id]);

        $this->service->delete($otherService);

        $this->assertSoftDeleted($otherService);
    }
}

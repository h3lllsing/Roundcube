<?php

namespace Tests\Unit;

use App\Models\ServiceProvider;
use App\Models\User;
use App\Services\ServiceProviderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceProviderServiceTest extends TestCase
{
    use RefreshDatabase;

    private ServiceProviderService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ServiceProviderService::class);
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $serviceProvider = $this->service->create(['name' => 'ISP Co', 'type' => 'internet', 'user_id' => $user->id]);

        $this->assertEquals('ISP Co', $serviceProvider->name);
    }

    public function test_list(): void
    {
        $user = User::factory()->create();
        ServiceProvider::factory()->count(2)->create(['user_id' => $user->id]);

        $result = $this->service->list(['user_id' => $user->id]);

        $this->assertCount(2, $result->items());
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $serviceProvider = ServiceProvider::factory()->create(['user_id' => $user->id, 'name' => 'Old SP']);

        $updated = $this->service->update($serviceProvider, ['name' => 'Updated']);

        $this->assertEquals('Updated', $updated->name);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $serviceProvider = ServiceProvider::factory()->create(['user_id' => $user->id]);

        $this->service->delete($serviceProvider);

        $this->assertSoftDeleted($serviceProvider);
    }
}

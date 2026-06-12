<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Vps;
use App\Services\VpsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VpsServiceTest extends TestCase
{
    use RefreshDatabase;

    private VpsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(VpsService::class);
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $vps = $this->service->create(['name' => 'Web Server', 'provider' => 'DigitalOcean', 'user_id' => $user->id]);

        $this->assertEquals('Web Server', $vps->name);
    }

    public function test_list(): void
    {
        $user = User::factory()->create();
        Vps::factory()->count(2)->create(['user_id' => $user->id]);

        $result = $this->service->list(['user_id' => $user->id]);

        $this->assertCount(2, $result->items());
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $vps = Vps::factory()->create(['user_id' => $user->id, 'name' => 'Old VPS']);

        $updated = $this->service->update($vps, ['name' => 'Updated']);

        $this->assertEquals('Updated', $updated->name);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $vps = Vps::factory()->create(['user_id' => $user->id]);

        $this->service->delete($vps);

        $this->assertSoftDeleted($vps);
    }
}

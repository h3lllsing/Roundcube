<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\Voip;
use App\Services\VoipService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VoipServiceTest extends TestCase
{
    use RefreshDatabase;

    private VoipService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(VoipService::class);
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $voip = $this->service->create(['name' => 'Office Line', 'provider' => 'Twilio', 'user_id' => $user->id]);

        $this->assertEquals('Office Line', $voip->name);
    }

    public function test_list(): void
    {
        $user = User::factory()->create();
        Voip::factory()->count(2)->create(['user_id' => $user->id]);

        $result = $this->service->list(['user_id' => $user->id]);

        $this->assertCount(2, $result->items());
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $voip = Voip::factory()->create(['user_id' => $user->id, 'name' => 'Old VoIP']);

        $updated = $this->service->update($voip, ['name' => 'Updated']);

        $this->assertEquals('Updated', $updated->name);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $voip = Voip::factory()->create(['user_id' => $user->id]);

        $this->service->delete($voip);

        $this->assertSoftDeleted($voip);
    }
}

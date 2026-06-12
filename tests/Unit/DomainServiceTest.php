<?php

namespace Tests\Unit;

use App\Models\Domain;
use App\Models\User;
use App\Services\DomainService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainServiceTest extends TestCase
{
    use RefreshDatabase;

    private DomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DomainService::class);
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $domain = $this->service->create(['name' => 'example.com', 'user_id' => $user->id]);

        $this->assertEquals('example.com', $domain->name);
    }

    public function test_list(): void
    {
        $user = User::factory()->create();
        Domain::factory()->count(2)->create(['user_id' => $user->id]);

        $result = $this->service->list(['user_id' => $user->id]);

        $this->assertCount(2, $result->items());
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id, 'name' => 'old.com']);

        $updated = $this->service->update($domain, ['name' => 'new.com']);

        $this->assertEquals('new.com', $updated->name);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $domain = Domain::factory()->create(['user_id' => $user->id]);

        $this->service->delete($domain);

        $this->assertSoftDeleted($domain);
    }
}

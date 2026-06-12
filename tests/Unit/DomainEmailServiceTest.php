<?php

namespace Tests\Unit;

use App\Models\DomainEmail;
use App\Models\User;
use App\Services\DomainEmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DomainEmailServiceTest extends TestCase
{
    use RefreshDatabase;

    private DomainEmailService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(DomainEmailService::class);
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $domainEmail = $this->service->create(['email' => 'admin@example.com', 'provider' => 'Google', 'user_id' => $user->id]);

        $this->assertEquals('admin@example.com', $domainEmail->email);
    }

    public function test_list(): void
    {
        $user = User::factory()->create();
        DomainEmail::factory()->count(2)->create(['user_id' => $user->id]);

        $result = $this->service->list(['user_id' => $user->id]);

        $this->assertCount(2, $result->items());
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $domainEmail = DomainEmail::factory()->create(['user_id' => $user->id, 'email' => 'old@example.com']);

        $updated = $this->service->update($domainEmail, ['email' => 'new@example.com']);

        $this->assertEquals('new@example.com', $updated->email);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $domainEmail = DomainEmail::factory()->create(['user_id' => $user->id]);

        $this->service->delete($domainEmail);

        $this->assertSoftDeleted($domainEmail);
    }
}

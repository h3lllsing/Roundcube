<?php

namespace Tests\Unit;

use App\Models\Module;
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
        $provider = \App\Models\ServiceProvider::factory()->create();
        $entry = $this->service->create(['user_id' => $user->id, 'name' => 'example.com', 'service_provider_id' => $provider->id, 'expiry_date' => '2027-01-01']);
 
        $this->assertEquals('example.com', $entry->name);
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $entry = $this->service->create(['user_id' => $user->id, 'name' => 'old.com', 'expiry_date' => '2027-01-01']);
        $updated = $this->service->update($entry, ['name' => 'new.com']);

        $this->assertEquals('new.com', $updated->name);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $entry = $this->service->create(['user_id' => $user->id, 'name' => 'del.com', 'expiry_date' => '2027-01-01']);
        $this->service->delete($entry);

        $this->assertSoftDeleted($entry);
    }

    public function test_list_with_trashed(): void
    {
        $user = User::factory()->create();
        $entry = $this->service->create(['user_id' => $user->id, 'name' => 'del.com', 'expiry_date' => '2027-01-01']);
        $entry->delete();

        $result = $this->service->list(['with_trashed' => true]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_module_id(): void
    {
        $user = User::factory()->create();
        $m1 = Module::factory()->create();
        $m2 = Module::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'a.com', 'expiry_date' => '2027-01-01', 'module_id' => $m1->id]);
        $this->service->create(['user_id' => $user->id, 'name' => 'b.com', 'expiry_date' => '2027-01-01', 'module_id' => $m2->id]);

        $result = $this->service->list(['module_id' => $m1->id]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_status(): void
    {
        $user = User::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'a.com', 'expiry_date' => '2027-01-01', 'status' => 'active']);
        $this->service->create(['user_id' => $user->id, 'name' => 'b.com', 'expiry_date' => '2027-01-01', 'status' => 'expired']);

        $result = $this->service->list(['status' => 'active']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_user_id(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $this->service->create(['user_id' => $u1->id, 'name' => 'a.com', 'expiry_date' => '2027-01-01']);
        $this->service->create(['user_id' => $u2->id, 'name' => 'b.com', 'expiry_date' => '2027-01-01']);

        $result = $this->service->list(['user_id' => $u1->id]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_search(): void
    {
        $user = User::factory()->create();
        $provider = \App\Models\ServiceProvider::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'example.com', 'service_provider_id' => $provider->id, 'expiry_date' => '2027-01-01']);
        $this->service->create(['user_id' => $user->id, 'name' => 'test.org', 'service_provider_id' => $provider->id, 'expiry_date' => '2027-01-01']);

        $result = $this->service->list(['search' => 'example']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_accessible_module_ids(): void
    {
        $user = User::factory()->create();
        $m1 = Module::factory()->create();
        $m2 = Module::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'a.com', 'expiry_date' => '2027-01-01', 'module_id' => $m1->id]);
        $this->service->create(['user_id' => $user->id, 'name' => 'b.com', 'expiry_date' => '2027-01-01', 'module_id' => $m2->id]);

        $result = $this->service->list(['accessible_module_ids' => [$m1->id]]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_invalid_sort_falls_back(): void
    {
        $user = User::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'a.com', 'expiry_date' => '2026-01-01']);
        $this->service->create(['user_id' => $user->id, 'name' => 'z.com', 'expiry_date' => '2027-01-01']);

        $result = $this->service->list(['sort_by' => 'invalid']);

        $this->assertCount(2, $result->items());
        $this->assertEquals('a.com', $result->items()[0]->name);
    }

    public function test_list_invalid_sort_order_falls_back(): void
    {
        $user = User::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'a.com', 'expiry_date' => '2026-01-01']);
        $this->service->create(['user_id' => $user->id, 'name' => 'b.com', 'expiry_date' => '2027-01-01']);

        $result = $this->service->list(['sort_order' => 'invalid']);

        $this->assertEquals('asc', $result->items()[0]->expiry_date <= $result->items()[1]->expiry_date);
    }

    public function test_list_clamps_per_page(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 110; $i++) {
            $this->service->create(['user_id' => $user->id, 'name' => "d{$i}.com", 'expiry_date' => '2027-01-01']);
        }

        $result = $this->service->list(['per_page' => 200]);

        $this->assertEquals(100, $result->perPage());
    }
}

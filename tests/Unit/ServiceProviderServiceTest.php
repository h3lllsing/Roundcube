<?php

namespace Tests\Unit;

use App\Models\Module;
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

    private function defaultData(array $overrides = []): array
    {
        return array_merge([
            'user_id' => User::factory()->create()->id,
            'name' => 'Provider',
            'type' => 'Email',
            'expiry_date' => '2027-01-01',
        ], $overrides);
    }

    public function test_create(): void
    {
        $entry = $this->service->create($this->defaultData(['name' => 'Provider A']));
        $this->assertEquals('Provider A', $entry->name);
    }

    public function test_update(): void
    {
        $entry = $this->service->create($this->defaultData(['name' => 'Old']));
        $updated = $this->service->update($entry, ['name' => 'New']);
        $this->assertEquals('New', $updated->name);
    }

    public function test_delete(): void
    {
        $entry = $this->service->create($this->defaultData(['name' => 'Del']));
        $this->service->delete($entry);
        $this->assertSoftDeleted($entry);
    }

    public function test_list_with_trashed(): void
    {
        $entry = $this->service->create($this->defaultData(['name' => 'Del']));
        $entry->delete();
        $result = $this->service->list(['with_trashed' => true]);
        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_module_id(): void
    {
        $m1 = Module::factory()->create();
        $m2 = Module::factory()->create();
        $this->service->create($this->defaultData(['name' => 'A', 'module_id' => $m1->id]));
        $this->service->create($this->defaultData(['name' => 'B', 'module_id' => $m2->id]));
        $result = $this->service->list(['module_id' => $m1->id]);
        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_status(): void
    {
        $this->service->create($this->defaultData(['name' => 'A', 'status' => 'active']));
        $this->service->create($this->defaultData(['name' => 'B', 'status' => 'expired']));
        $result = $this->service->list(['status' => 'active']);
        $this->assertCount(1, $result->items());
    }

    public function test_list_search(): void
    {
        $this->service->create($this->defaultData(['name' => 'My Provider', 'provider' => 'AWS']));
        $this->service->create($this->defaultData(['name' => 'Other', 'provider' => 'GCP']));
        $result = $this->service->list(['search' => 'My Provider']);
        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_user_id(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $this->service->create($this->defaultData(['user_id' => $u1->id, 'name' => 'A']));
        $this->service->create($this->defaultData(['user_id' => $u2->id, 'name' => 'B']));
        $result = $this->service->list(['user_id' => $u1->id]);
        $this->assertCount(1, $result->items());
    }

    public function test_list_invalid_sort_falls_back(): void
    {
        $this->service->create($this->defaultData(['name' => 'A', 'expiry_date' => '2026-01-01']));
        $this->service->create($this->defaultData(['name' => 'Z', 'expiry_date' => '2027-01-01']));
        $result = $this->service->list(['sort_by' => 'invalid']);
        $this->assertCount(2, $result->items());
        $this->assertEquals('A', $result->items()[0]->name);
    }

    public function test_list_clamps_per_page(): void
    {
        for ($i = 0; $i < 110; $i++) {
            $this->service->create($this->defaultData(['name' => "SP{$i}"]));
        }
        $result = $this->service->list(['per_page' => 200]);
        $this->assertEquals(100, $result->perPage());
    }

    public function test_list_accessible_module_ids(): void
    {
        $m1 = Module::factory()->create();
        $m2 = Module::factory()->create();
        $this->service->create($this->defaultData(['name' => 'A', 'module_id' => $m1->id]));
        $this->service->create($this->defaultData(['name' => 'B', 'module_id' => $m2->id]));
        $result = $this->service->list(['accessible_module_ids' => [$m1->id]]);
        $this->assertCount(1, $result->items());
    }
}

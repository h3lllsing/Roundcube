<?php

namespace Tests\Unit;

use App\Models\Module;
use App\Models\User;
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
        $entry = $this->service->create(['user_id' => $user->id, 'name' => 'Office Line', 'expiry_date' => '2027-01-01']);
        $this->assertEquals('Office Line', $entry->name);
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $entry = $this->service->create(['user_id' => $user->id, 'name' => 'Old', 'expiry_date' => '2027-01-01']);
        $updated = $this->service->update($entry, ['name' => 'New']);
        $this->assertEquals('New', $updated->name);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $entry = $this->service->create(['user_id' => $user->id, 'name' => 'Del', 'expiry_date' => '2027-01-01']);
        $this->service->delete($entry);
        $this->assertSoftDeleted($entry);
    }

    public function test_list_with_trashed(): void
    {
        $user = User::factory()->create();
        $entry = $this->service->create(['user_id' => $user->id, 'name' => 'Del', 'expiry_date' => '2027-01-01']);
        $entry->delete();
        $result = $this->service->list(['with_trashed' => true]);
        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_module_id(): void
    {
        $user = User::factory()->create();
        $m1 = Module::factory()->create();
        $m2 = Module::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'A', 'expiry_date' => '2027-01-01', 'module_id' => $m1->id]);
        $this->service->create(['user_id' => $user->id, 'name' => 'B', 'expiry_date' => '2027-01-01', 'module_id' => $m2->id]);
        $result = $this->service->list(['module_id' => $m1->id]);
        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_status(): void
    {
        $user = User::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'A', 'expiry_date' => '2027-01-01', 'status' => 'active']);
        $this->service->create(['user_id' => $user->id, 'name' => 'B', 'expiry_date' => '2027-01-01', 'status' => 'expired']);
        $result = $this->service->list(['status' => 'active']);
        $this->assertCount(1, $result->items());
    }

    public function test_list_search(): void
    {
        $user = User::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'Main Line', 'phone_number' => '+123', 'expiry_date' => '2027-01-01']);
        $this->service->create(['user_id' => $user->id, 'name' => 'Backup', 'phone_number' => '+456', 'expiry_date' => '2027-01-01']);
        $result = $this->service->list(['search' => 'Main Line']);
        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_user_id(): void
    {
        $u1 = User::factory()->create();
        $u2 = User::factory()->create();
        $this->service->create(['user_id' => $u1->id, 'name' => 'A', 'expiry_date' => '2027-01-01']);
        $this->service->create(['user_id' => $u2->id, 'name' => 'B', 'expiry_date' => '2027-01-01']);
        $result = $this->service->list(['user_id' => $u1->id]);
        $this->assertCount(1, $result->items());
    }

    public function test_list_invalid_sort_falls_back(): void
    {
        $user = User::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'A', 'expiry_date' => '2026-01-01']);
        $this->service->create(['user_id' => $user->id, 'name' => 'Z', 'expiry_date' => '2027-01-01']);

        $result = $this->service->list(['sort_by' => 'invalid']);

        $this->assertCount(2, $result->items());
        $this->assertEquals('A', $result->items()[0]->name);
    }

    public function test_list_clamps_per_page(): void
    {
        $user = User::factory()->create();
        for ($i = 0; $i < 110; $i++) {
            $this->service->create(['user_id' => $user->id, 'name' => "V{$i}", 'expiry_date' => '2027-01-01']);
        }
        $result = $this->service->list(['per_page' => 200]);
        $this->assertEquals(100, $result->perPage());
    }

    public function test_list_accessible_module_ids(): void
    {
        $user = User::factory()->create();
        $m1 = Module::factory()->create();
        $m2 = Module::factory()->create();
        $this->service->create(['user_id' => $user->id, 'name' => 'A', 'expiry_date' => '2027-01-01', 'module_id' => $m1->id]);
        $this->service->create(['user_id' => $user->id, 'name' => 'B', 'expiry_date' => '2027-01-01', 'module_id' => $m2->id]);
        $result = $this->service->list(['accessible_module_ids' => [$m1->id]]);
        $this->assertCount(1, $result->items());
    }
}

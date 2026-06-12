<?php

namespace Tests\Unit;

use App\Models\ExpiryTracker;
use App\Models\Module;
use App\Models\User;
use App\Services\ExpiryTrackerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiryTrackerServiceTest extends TestCase
{
    use RefreshDatabase;

    private ExpiryTrackerService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ExpiryTrackerService::class);
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $expiryTracker = $this->service->create(['name' => 'AWS Hosting', 'provider' => 'Amazon', 'expiry_date' => '2026-12-31', 'cost' => 100, 'user_id' => $user->id]);

        $this->assertEquals('AWS Hosting', $expiryTracker->name);
    }

    public function test_list(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->count(2)->create(['user_id' => $user->id]);

        $result = $this->service->list(['user_id' => $user->id]);

        $this->assertCount(2, $result->items());
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $expiryTracker = ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Old Tracker']);

        $updated = $this->service->update($expiryTracker, ['name' => 'Updated']);

        $this->assertEquals('Updated', $updated->name);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $expiryTracker = ExpiryTracker::factory()->create(['user_id' => $user->id]);

        $this->service->delete($expiryTracker);

        $this->assertSoftDeleted($expiryTracker);
    }

    public function test_list_filters_by_module_id(): void
    {
        $user = User::factory()->create();
        $module1 = Module::factory()->create();
        $module2 = Module::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'module_id' => $module1->id]);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'module_id' => $module2->id]);

        $result = $this->service->list(['module_id' => $module1->id]);

        $this->assertCount(1, $result->items());
        $this->assertEquals($module1->id, $result->items()[0]->module_id);
    }

    public function test_list_filters_by_status(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'status' => 'expired']);

        $result = $this->service->list(['status' => 'active']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('active', $result->items()[0]->status);
    }

    public function test_list_searches_by_name(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'License A']);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Subscription B']);

        $result = $this->service->list(['search' => 'License']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_searches_by_provider(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Svc A', 'provider' => 'Amazon']);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Svc B', 'provider' => 'Google']);

        $result = $this->service->list(['search' => 'Amazon']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_accessible_module_ids(): void
    {
        $user = User::factory()->create();
        $module1 = Module::factory()->create();
        $module2 = Module::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'module_id' => $module1->id]);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'module_id' => $module2->id]);

        $result = $this->service->list(['accessible_module_ids' => [$module1->id]]);

        $this->assertCount(1, $result->items());
        $this->assertEquals($module1->id, $result->items()[0]->module_id);
    }

    public function test_list_with_trashed(): void
    {
        $user = User::factory()->create();
        $tracker = ExpiryTracker::factory()->create(['user_id' => $user->id]);
        $tracker->delete();

        $result = $this->service->list(['with_trashed' => true]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_without_trashed_excludes_deleted(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id]);
        $deleted = ExpiryTracker::factory()->create(['user_id' => $user->id]);
        $deleted->delete();

        $result = $this->service->list([]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_expiring_soon(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Soon', 'expiry_date' => now()->addDays(5)]);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Far', 'expiry_date' => now()->addDays(90)]);

        $result = $this->service->list(['expiring_soon' => true]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Soon', $result->items()[0]->name);
    }

    public function test_list_filters_expired(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Expired', 'expiry_date' => now()->subDays(5)]);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Valid', 'expiry_date' => now()->addDays(30)]);

        $result = $this->service->list(['expired' => true]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Expired', $result->items()[0]->name);
    }

    public function test_list_filters_by_date_from(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Old', 'expiry_date' => '2025-01-01']);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'New', 'expiry_date' => '2026-12-31']);

        $result = $this->service->list(['date_from' => '2026-01-01']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('New', $result->items()[0]->name);
    }

    public function test_list_filters_by_date_to(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Early', 'expiry_date' => '2025-06-01']);
        ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Late', 'expiry_date' => '2026-12-31']);

        $result = $this->service->list(['date_to' => '2026-01-01']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Early', $result->items()[0]->name);
    }

    public function test_list_invalid_sort_falls_back_to_expiry_date(): void
    {
        $user = User::factory()->create();
        $a = ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'Z', 'expiry_date' => '2026-12-31']);
        $b = ExpiryTracker::factory()->create(['user_id' => $user->id, 'name' => 'A', 'expiry_date' => '2026-06-15']);

        $result = $this->service->list(['sort_by' => 'invalid_field']);

        $this->assertCount(2, $result->items());
    }

    public function test_list_respects_per_page(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->count(5)->create(['user_id' => $user->id]);

        $result = $this->service->list(['per_page' => 2]);

        $this->assertCount(2, $result->items());
        $this->assertEquals(2, $result->perPage());
    }

    public function test_list_clamps_per_page_to_max(): void
    {
        $user = User::factory()->create();
        ExpiryTracker::factory()->count(150)->create(['user_id' => $user->id]);

        $result = $this->service->list(['per_page' => 200]);

        $this->assertEquals(100, $result->perPage());
    }
}

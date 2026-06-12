<?php

namespace Tests\Unit;

use App\Models\Hosting;
use App\Models\Module;
use App\Models\User;
use App\Services\HostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HostingServiceTest extends TestCase
{
    use RefreshDatabase;

    private HostingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(HostingService::class);
    }

    public function test_create(): void
    {
        $user = User::factory()->create();
        $hosting = $this->service->create(['name' => 'My Hosting', 'user_id' => $user->id]);

        $this->assertEquals('My Hosting', $hosting->name);
    }

    public function test_list(): void
    {
        $user = User::factory()->create();
        Hosting::factory()->count(2)->create(['user_id' => $user->id]);

        $result = $this->service->list(['user_id' => $user->id]);

        $this->assertCount(2, $result->items());
    }

    public function test_update(): void
    {
        $user = User::factory()->create();
        $hosting = Hosting::factory()->create(['user_id' => $user->id, 'name' => 'Old Hosting']);

        $updated = $this->service->update($hosting, ['name' => 'Updated']);

        $this->assertEquals('Updated', $updated->name);
    }

    public function test_delete(): void
    {
        $user = User::factory()->create();
        $hosting = Hosting::factory()->create(['user_id' => $user->id]);

        $this->service->delete($hosting);

        $this->assertSoftDeleted($hosting);
    }

    public function test_list_filters_by_module_id(): void
    {
        $user = User::factory()->create();
        $module1 = Module::factory()->create();
        $module2 = Module::factory()->create();
        Hosting::factory()->create(['user_id' => $user->id, 'module_id' => $module1->id]);
        Hosting::factory()->create(['user_id' => $user->id, 'module_id' => $module2->id]);

        $result = $this->service->list(['module_id' => $module1->id]);

        $this->assertCount(1, $result->items());
        $this->assertEquals($module1->id, $result->items()[0]->module_id);
    }

    public function test_list_filters_by_status(): void
    {
        $user = User::factory()->create();
        Hosting::factory()->create(['user_id' => $user->id, 'status' => 'active']);
        Hosting::factory()->create(['user_id' => $user->id, 'status' => 'expired']);

        $result = $this->service->list(['status' => 'active']);

        $this->assertCount(1, $result->items());
        $this->assertEquals('active', $result->items()[0]->status);
    }

    public function test_list_searches_by_keyword(): void
    {
        $user = User::factory()->create();
        Hosting::factory()->create(['user_id' => $user->id, 'name' => 'Alpha Hosting', 'provider' => 'ProviderA', 'domain' => 'alpha.com']);
        Hosting::factory()->create(['user_id' => $user->id, 'name' => 'Beta Hosting', 'provider' => 'ProviderB', 'domain' => 'beta.com']);

        $result = $this->service->list(['search' => 'Alpha']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_searches_by_provider(): void
    {
        $user = User::factory()->create();
        Hosting::factory()->create(['user_id' => $user->id, 'name' => 'Host X', 'provider' => 'SiteGround', 'domain' => 'x.com']);
        Hosting::factory()->create(['user_id' => $user->id, 'name' => 'Host Y', 'provider' => 'Bluehost', 'domain' => 'y.com']);

        $result = $this->service->list(['search' => 'SiteGround']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_filters_by_accessible_module_ids(): void
    {
        $user = User::factory()->create();
        $module1 = Module::factory()->create();
        $module2 = Module::factory()->create();
        Hosting::factory()->create(['user_id' => $user->id, 'module_id' => $module1->id]);
        Hosting::factory()->create(['user_id' => $user->id, 'module_id' => $module2->id]);

        $result = $this->service->list(['accessible_module_ids' => [$module1->id]]);

        $this->assertCount(1, $result->items());
        $this->assertEquals($module1->id, $result->items()[0]->module_id);
    }

    public function test_list_with_trashed(): void
    {
        $user = User::factory()->create();
        $hosting = Hosting::factory()->create(['user_id' => $user->id]);
        $hosting->delete();

        $result = $this->service->list(['with_trashed' => true]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_without_trashed_excludes_deleted(): void
    {
        $user = User::factory()->create();
        Hosting::factory()->create(['user_id' => $user->id]);
        $deleted = Hosting::factory()->create(['user_id' => $user->id]);
        $deleted->delete();

        $result = $this->service->list([]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_invalid_sort_falls_back_to_expiry_date(): void
    {
        $user = User::factory()->create();
        $a = Hosting::factory()->create(['user_id' => $user->id, 'name' => 'Late', 'expiry_date' => '2026-12-31']);
        $b = Hosting::factory()->create(['user_id' => $user->id, 'name' => 'Early', 'expiry_date' => '2026-06-15']);

        $result = $this->service->list(['sort_by' => 'invalid_field']);

        $items = $result->items();
        $this->assertCount(2, $items);
        $this->assertEquals('Early', $items[0]->name);
        $this->assertEquals('Late', $items[1]->name);
    }

    public function test_list_invalid_sort_order_falls_back_to_asc(): void
    {
        $user = User::factory()->create();
        $a = Hosting::factory()->create(['user_id' => $user->id, 'name' => 'Early', 'expiry_date' => '2026-01-01']);
        $b = Hosting::factory()->create(['user_id' => $user->id, 'name' => 'Late', 'expiry_date' => '2026-12-31']);

        $result = $this->service->list(['sort_order' => 'invalid']);

        $items = $result->items();
        $this->assertCount(2, $items);
        $this->assertEquals('Early', $items[0]->name);
        $this->assertEquals('Late', $items[1]->name);
    }

    public function test_list_respects_per_page(): void
    {
        $user = User::factory()->create();
        Hosting::factory()->count(5)->create(['user_id' => $user->id]);

        $result = $this->service->list(['per_page' => 2]);

        $this->assertCount(2, $result->items());
        $this->assertEquals(2, $result->perPage());
    }

    public function test_list_clamps_per_page_to_max(): void
    {
        $user = User::factory()->create();
        Hosting::factory()->count(150)->create(['user_id' => $user->id]);

        $result = $this->service->list(['per_page' => 200]);

        $this->assertEquals(100, $result->perPage());
    }
}

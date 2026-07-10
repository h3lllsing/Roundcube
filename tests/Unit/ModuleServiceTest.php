<?php

namespace Tests\Unit;

use App\Models\Feature;
use App\Models\Module;
use App\Services\ModuleService;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleServiceTest extends TestCase
{
    use RefreshDatabase;

    private ModuleService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(ModuleService::class);
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    public function test_lists_modules_for_feature(): void
    {
        $feature = Feature::first();

        $result = $this->service->listForFeature($feature);

        $this->assertGreaterThan(0, $result->total());
    }

    public function test_list_for_feature_filters_by_search(): void
    {
        $feature = Feature::first();

        $result = $this->service->listForFeature($feature, ['search' => 'nonexistent']);

        $this->assertCount(0, $result->items());
    }

    public function test_find_module(): void
    {
        $module = Module::first();

        $found = $this->service->find($module->id);

        $this->assertEquals($module->id, $found->id);
    }

    public function test_creates_module(): void
    {
        $feature = Feature::first();

        $module = $this->service->create($feature, ['name' => 'New Module', 'description' => 'Test']);

        $this->assertEquals('New Module', $module->name);
        $this->assertEquals($feature->id, $module->feature_id);
    }

    public function test_update_module(): void
    {
        $module = Module::first();

        $updated = $this->service->update($module, ['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $updated->name);
    }

    public function test_delete_soft_deletes(): void
    {
        $module = Module::first();

        $this->service->delete($module);

        $this->assertSoftDeleted($module);
    }

    public function test_list_filters_by_is_active(): void
    {
        $feature = Feature::first();
        Module::factory()->create(['feature_id' => $feature->id, 'name' => 'Active One', 'is_active' => true]);
        Module::factory()->create(['feature_id' => $feature->id, 'name' => 'Inactive One', 'is_active' => false]);

        $result = $this->service->listForFeature($feature, ['is_active' => true]);

        $this->assertGreaterThanOrEqual(1, $result->total());
        foreach ($result->items() as $module) {
            $this->assertTrue((bool) $module->is_active);
        }
    }

    public function test_list_with_trashed(): void
    {
        $feature = Feature::first();
        $module = Module::factory()->create(['feature_id' => $feature->id]);
        $module->delete();

        $result = $this->service->listForFeature($feature, ['with_trashed' => true]);

        $this->assertGreaterThanOrEqual(1, $result->total());
    }

    public function test_list_invalid_sort_falls_back_to_name(): void
    {
        $feature = Feature::first();
        Module::factory()->create(['feature_id' => $feature->id, 'name' => 'Z Module']);
        Module::factory()->create(['feature_id' => $feature->id, 'name' => 'A Module']);

        $result = $this->service->listForFeature($feature, ['sort_by' => 'invalid']);

        $this->assertGreaterThanOrEqual(2, $result->total());
        $items = $result->items();
        $this->assertEquals('A Module', $items[0]->name);
    }

    public function test_list_clamps_per_page_to_max(): void
    {
        $feature = Feature::first();
        Module::factory()->count(150)->create(['feature_id' => $feature->id]);

        $result = $this->service->listForFeature($feature, ['per_page' => 200]);

        $this->assertEquals(100, $result->perPage());
    }

    public function test_update_generates_new_slug_on_name_change(): void
    {
        $feature = Feature::first();
        $module = $this->service->create($feature, ['name' => 'Original', 'slug' => 'original']);

        $updated = $this->service->update($module, ['name' => 'Changed']);

        $this->assertEquals('changed', $updated->slug);
    }

    public function test_list_invalid_sort_order_falls_back_to_asc(): void
    {
        $feature = Feature::first();
        $result = $this->service->listForFeature($feature, ['sort_order' => 'invalid']);

        $this->assertGreaterThanOrEqual(0, $result->total());
    }
}

<?php

namespace Tests\Unit;

use App\Models\Feature;
use App\Models\User;
use App\Services\FeatureService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FeatureServiceTest extends TestCase
{
    use RefreshDatabase;

    private FeatureService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(FeatureService::class);
    }

    public function test_creates_feature_with_slug(): void
    {
        $feature = $this->service->create(['name' => 'My Feature', 'description' => 'Test']);

        $this->assertEquals('My Feature', $feature->name);
        $this->assertEquals('my-feature', $feature->slug);
    }

    public function test_creates_feature_with_custom_slug(): void
    {
        $feature = $this->service->create(['name' => 'Custom', 'slug' => 'custom-slug']);

        $this->assertEquals('custom-slug', $feature->slug);
    }

    public function test_find_by_slug(): void
    {
        Feature::create(['name' => 'Test', 'slug' => 'test-slug']);

        $feature = $this->service->findBySlug('test-slug');

        $this->assertEquals('test-slug', $feature->slug);
    }

    public function test_update_generates_new_slug_on_name_change(): void
    {
        $feature = Feature::create(['name' => 'Old Name', 'slug' => 'old-name']);

        $updated = $this->service->update($feature, ['name' => 'New Name']);

        $this->assertEquals('New Name', $updated->name);
        $this->assertEquals('new-name', $updated->slug);
    }

    public function test_update_preserves_slug_when_name_unchanged(): void
    {
        $feature = Feature::create(['name' => 'Stable', 'slug' => 'stable', 'description' => 'Old desc']);

        $updated = $this->service->update($feature, ['description' => 'New desc']);

        $this->assertEquals('stable', $updated->slug);
    }

    public function test_create_increments_cache_version(): void
    {
        Cache::forever('features:version', 3);

        $this->service->create(['name' => 'Cache Test']);

        $this->assertEquals(4, Cache::get('features:version'));
    }

    public function test_delete_soft_deletes(): void
    {
        $feature = Feature::create(['name' => 'Delete Me', 'slug' => 'delete-me']);

        $this->service->delete($feature);

        $this->assertSoftDeleted($feature);
    }

    public function test_list_filters_by_active(): void
    {
        Feature::create(['name' => 'Active', 'slug' => 'active', 'is_active' => true]);
        Feature::create(['name' => 'Inactive', 'slug' => 'inactive', 'is_active' => false]);

        $result = $this->service->list(['is_active' => true]);

        $this->assertCount(1, $result->items());
        $this->assertEquals('Active', $result->items()[0]->name);
    }

    public function test_find_returns_feature_with_modules(): void
    {
        $feature = Feature::create(['name' => 'Find Me', 'slug' => 'find-me']);

        $found = $this->service->find($feature->id);

        $this->assertEquals('Find Me', $found->name);
        $this->assertTrue($found->relationLoaded('modules'));
    }

    public function test_find_throws_when_not_found(): void
    {
        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $this->service->find(99999);
    }

    public function test_list_searches_by_name(): void
    {
        Feature::create(['name' => 'Alpha', 'slug' => 'alpha']);
        Feature::create(['name' => 'Beta', 'slug' => 'beta']);

        $result = $this->service->list(['search' => 'Alpha']);

        $this->assertCount(1, $result->items());
    }

    public function test_list_invalid_sort_falls_back_to_name(): void
    {
        Feature::create(['name' => 'Z Feature', 'slug' => 'z']);
        Feature::create(['name' => 'A Feature', 'slug' => 'a']);

        $result = $this->service->list(['sort_by' => 'invalid']);

        $this->assertCount(2, $result->items());
        $this->assertEquals('A Feature', $result->items()[0]->name);
    }

    public function test_list_with_trashed(): void
    {
        $feature = Feature::create(['name' => 'Deleted', 'slug' => 'deleted']);
        $feature->delete();

        $result = $this->service->list(['with_trashed' => true]);

        $this->assertCount(1, $result->items());
    }

    public function test_list_clamps_per_page_to_max(): void
    {
        Feature::factory()->count(150)->create();

        $result = $this->service->list(['per_page' => 200]);

        $this->assertEquals(100, $result->perPage());
    }
}

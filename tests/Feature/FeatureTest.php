<?php

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    public function test_list_features_as_authenticated_user()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/features');

        $response->assertStatus(200);
    }

    public function test_create_feature_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/features', [
                'name' => 'Test Feature',
                'slug' => 'test-feature',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['data' => ['id', 'name', 'slug']]);

        $feature = Feature::where('slug', 'test-feature')->firstOrFail();
        $this->assertCount(0, $feature->activeModules);

        $this->assertDatabaseHas('features', ['slug' => 'test-feature', 'is_active' => true]);
    }

    public function test_create_feature_forbidden_for_non_super_admin()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/features', [
                'name' => 'Test Feature',
                'slug' => 'test-feature',
            ]);

        $response->assertStatus(403);
    }

    public function test_show_feature()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/features/{$feature->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name']]);
    }

    public function test_delete_feature_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/features/{$feature->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Feature deleted']);

        $this->assertSoftDeleted('features', ['id' => $feature->id]);
    }

    public function test_feature_sort_by_name()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/features?sort_by=name&sort_order=asc');

        $response->assertStatus(200);
    }

    public function test_module_sort_by_name()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/features/{$feature->id}/modules?sort_by=name&sort_order=desc");

        $response->assertStatus(200);
    }

    public function test_feature_with_trashed_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();
        $feature->delete();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/features?with_trashed=1');

        $response->assertStatus(200);
        $this->assertStringContainsString((string) $feature->id, $response->getContent());
    }

    public function test_super_admin_creates_then_sees_feature()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/features', ['name' => 'NewFeature', 'slug' => 'new-feature']);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/features');
        $this->assertStringContainsString('NewFeature', $response->getContent());
    }

    public function test_update_feature_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/features/{$feature->id}", [
                'name' => 'Updated Feature',
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name']]);
        $this->assertDatabaseHas('features', ['id' => $feature->id, 'name' => 'Updated Feature']);
    }

    public function test_show_nonexistent_feature_returns_404()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/features/99999');

        $response->assertStatus(404);
    }

    public function test_create_feature_validation_empty_name()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/features', ['name' => '', 'slug' => 'test-slug']);

        $response->assertStatus(422);
    }

    public function test_non_super_admin_cannot_update_feature()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/features/{$feature->id}", ['name' => 'Hacked']);

        $response->assertStatus(403);
    }

    public function test_update_nonexistent_feature_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson('/api/features/99999', ['name' => 'Ghost']);

        $response->assertStatus(404);
    }

    public function test_create_feature_duplicate_slug_returns_422()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $existing = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/features', [
                'name' => 'Duplicate Slug',
                'slug' => $existing->slug,
            ]);

        $response->assertStatus(422);
    }

    public function test_feature_soft_delete_orphans_modules()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $feature = Feature::first();
        $moduleId = $feature->modules->first()->id;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/features/{$feature->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('features', ['id' => $feature->id]);
        $this->assertDatabaseHas('modules', ['id' => $moduleId]);
    }

    public function test_delete_nonexistent_feature_returns_404()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson('/api/features/99999');

        $response->assertStatus(404);
    }

    public function test_update_feature_toggles_is_active()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/features/{$feature->id}", ['is_active' => false]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('features', ['id' => $feature->id, 'is_active' => false]);
    }

    public function test_feature_search()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        Feature::create(['name' => 'UniqueSearchFeature', 'slug' => 'unique-search-feature']);
        Feature::create(['name' => 'OtherFeature', 'slug' => 'other-feature']);
        Cache::flush();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/features?search=UniqueSearch');

        $response->assertStatus(200);
        $content = $response->getContent();
        $this->assertStringContainsString('UniqueSearchFeature', $content);
        $this->assertStringNotContainsString('OtherFeature', $content);
    }

    public function test_feature_show_includes_modules()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;
        $feature = Feature::first();

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/features/{$feature->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'modules']]);
        $this->assertNotEmpty($response->json('data.modules'));
    }

    public function test_soft_deleted_feature_hidden_from_regular_user()
    {
        $feature = Feature::first();
        $feature->delete();

        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson("/api/features/{$feature->id}");

        $response->assertStatus(404);
    }

    public function test_soft_deleted_feature_accessible_by_super_admin_with_trashed()
    {
        $feature = Feature::first();
        $feature->delete();

        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/features?with_trashed=1');

        $response->assertStatus(200);
        $this->assertStringContainsString($feature->name, $response->getContent());
    }
}

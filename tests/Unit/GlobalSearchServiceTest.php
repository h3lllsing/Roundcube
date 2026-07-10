<?php

namespace Tests\Unit;

use App\Models\Asset;
use App\Models\Domain;
use App\Models\Feature;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use App\Models\VaultEntry;
use App\Services\GlobalSearchService;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private GlobalSearchService $service;
    private User $admin;
    private User $user;
    private Module $module;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);

        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->user = User::factory()->create();
        $role = Role::where('slug', 'user')->firstOrFail();
        $this->user->assignRole($role);

        $this->module = Module::first();
        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $role->id,
            'can_read' => true,
        ]);

        $this->service = app(GlobalSearchService::class);
    }

    public function test_filters_returns_all_categories(): void
    {
        $filters = GlobalSearchService::filters();
        $this->assertArrayHasKey('all', $filters);
        $this->assertArrayHasKey('services', $filters);
        $this->assertArrayHasKey('assets', $filters);
        $this->assertArrayHasKey('tasks', $filters);
        $this->assertArrayHasKey('vault', $filters);
        $this->assertArrayHasKey('users', $filters);
        $this->assertCount(6, $filters);
    }

    public function test_filters_labels_are_readable(): void
    {
        $filters = GlobalSearchService::filters();
        $this->assertEquals('All', $filters['all']);
        $this->assertEquals('Services', $filters['services']);
        $this->assertEquals('Assets', $filters['assets']);
    }

    public function test_search_returns_empty_for_large_query_with_no_matches(): void
    {
        $results = $this->service->search('xyznonexistent123456', $this->admin);
        $this->assertSame([], $results);
    }

    public function test_search_max_total_limit(): void
    {
        for ($i = 1; $i <= 20; $i++) {
            Domain::factory()->create(['name' => "max-total-test-{$i}.com", 'user_id' => $this->admin->id]);
        }

        $results = $this->service->search('max-total-test', $this->admin);
        $totalItems = 0;
        foreach ($results as $group) {
            $totalItems += count($group['items']);
        }
        $this->assertLessThanOrEqual(50, $totalItems);
    }

    public function test_non_admin_gets_empty_for_sa_only_filter(): void
    {
        Feature::factory()->create(['name' => 'hidden-feature']);
        $results = $this->service->search('hidden-feature', $this->user);
        $this->assertArrayNotHasKey('features', $results);
    }

    public function test_search_respects_filter_parameter(): void
    {
        Domain::factory()->create(['name' => 'filtered-domain.com', 'user_id' => $this->admin->id]);
        Asset::factory()->create(['asset_tag' => 'AST-FILTERED', 'user_id' => $this->admin->id]);

        $results = $this->service->search('FILTERED', $this->admin, 'assets');
        $this->assertArrayNotHasKey('domains', $results);
        $this->assertArrayHasKey('assets', $results);
    }

    public function test_search_for_api_returns_structured_groups(): void
    {
        Domain::factory()->create(['name' => 'api-structured.com', 'user_id' => $this->admin->id]);

        $groups = $this->service->searchForApi('api-structured', $this->admin);
        $this->assertNotEmpty($groups);
        $this->assertArrayHasKey('key', $groups[0]);
        $this->assertArrayHasKey('label', $groups[0]);
        $this->assertArrayHasKey('url', $groups[0]);
        $this->assertArrayHasKey('items', $groups[0]);
    }
}

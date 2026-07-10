<?php

namespace Tests\Feature;

use App\Helpers\SearchHelper;
use App\Models\Asset;
use App\Models\Domain;
use App\Models\Feature;
use App\Models\Hosting;
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

class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $user;
    private Module $module;
    private GlobalSearchService $service;

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
        $this->assertNotNull($this->module);

        ModuleRolePermission::create([
            'module_id' => $this->module->id,
            'role_id' => $role->id,
            'can_read' => true,
        ]);

        $this->service = app(GlobalSearchService::class);
    }

    // ─── Helpers ──────────────────────────────────────────────────

    private function getGroup(array $data, string $key): ?array
    {
        foreach ($data as $group) {
            if (($group['key'] ?? null) === $key) {
                return $group;
            }
        }
        return null;
    }

    // ─── API: Authentication & Basic Scoping ───────────────────────

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/search?q=test')->assertUnauthorized();
    }

    public function test_requires_minimum_two_characters(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/search?q=a')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_super_admin_sees_all_records_across_modules(): void
    {
        Domain::factory()->create(['name' => 'search-all-alpha.com', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'search-all-beta.com', 'user_id' => $this->user->id]);
        Note::create(['content' => 'search-all note', 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=search-all');

        $response->assertOk();
        $data = $response->json('data');

        $domains = $this->getGroup($data, 'domains');
        $this->assertNotNull($domains);
        $this->assertCount(2, $domains['items']);

        $notes = $this->getGroup($data, 'notes');
        $this->assertNotNull($notes);
        $this->assertCount(1, $notes['items']);
    }

    public function test_non_admin_only_sees_own_records(): void
    {
        Domain::factory()->create(['name' => 'scoped-mine.com', 'user_id' => $this->user->id]);
        Domain::factory()->create(['name' => 'scoped-theirs.com', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/search?q=scoped');

        $response->assertOk();
        $data = $response->json('data');

        $domains = $this->getGroup($data, 'domains');
        $this->assertNotNull($domains);
        $this->assertCount(1, $domains['items']);
        $this->assertEquals('scoped-mine.com', $domains['items'][0]['title']);
    }

    // ─── SA-Only Modules (Features, Modules, Users, SMTP) ─────────

    public function test_non_admin_cannot_see_sa_only_modules(): void
    {
        Feature::factory()->create(['name' => 'sa-only-feature']);
        Module::factory()->create(['name' => 'sa-only-module']);
        User::factory()->create(['name' => 'sa-only-user']);

        $response = $this->actingAs($this->user)
            ->getJson('/api/search?q=sa-only');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNull($this->getGroup($data, 'features'));
        $this->assertNull($this->getGroup($data, 'modules'));
        $this->assertNull($this->getGroup($data, 'users'));
    }

    public function test_super_admin_sees_sa_only_modules(): void
    {
        Feature::factory()->create(['name' => 'super-feature-x']);
        Module::factory()->create(['name' => 'super-module-y']);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=super-');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotNull($this->getGroup($data, 'features'));
        $this->assertNotNull($this->getGroup($data, 'modules'));
    }

    // ─── Notes: User-Only Ownership ────────────────────────────────

    public function test_non_admin_notes_scoped_to_own(): void
    {
        Note::create(['content' => 'secret note mine', 'user_id' => $this->user->id]);
        Note::create(['content' => 'secret note admin', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/search?q=secret note');

        $response->assertOk();
        $data = $response->json('data');
        $notes = $this->getGroup($data, 'notes');
        $this->assertNotNull($notes);
        $this->assertCount(1, $notes['items']);
        $this->assertEquals('secret note mine', $notes['items'][0]['title']);
    }

    // ─── Vault: User-Or-Module Ownership ──────────────────────────

    public function test_non_admin_sees_own_vault_entries(): void
    {
        VaultEntry::factory()->create([
            'service_name' => 'my-vault-item',
            'user_id' => $this->user->id,
        ]);
        VaultEntry::factory()->create([
            'service_name' => 'admin-vault-item',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/search?q=vault');

        $response->assertOk();
        $data = $response->json('data');
        $vault = $this->getGroup($data, 'vault');
        $this->assertNotNull($vault);
        $this->assertCount(1, $vault['items']);
        $this->assertEquals('my-vault-item', $vault['items'][0]['title']);
    }

    public function test_non_admin_sees_vault_in_accessible_module(): void
    {
        VaultEntry::factory()->create([
            'service_name' => 'module-vault-item',
            'user_id' => $this->admin->id,
            'module_id' => $this->module->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/search?q=module-vault');

        $response->assertOk();
        $data = $response->json('data');
        $vault = $this->getGroup($data, 'vault');
        $this->assertNotNull($vault);
        $this->assertCount(1, $vault['items']);
    }

    // ─── Tasks: Module + Assignment Scoping ────────────────────────

    public function test_non_admin_sees_tasks_in_accessible_module(): void
    {
        $otherModule = Module::factory()->create(['name' => 'Other Module']);

        Task::factory()->create([
            'title' => 'accessible task',
            'module_id' => $this->module->id,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        Task::factory()->create([
            'title' => 'inaccessible task',
            'module_id' => $otherModule->id,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/search?q=task');

        $response->assertOk();
        $data = $response->json('data');
        $tasks = $this->getGroup($data, 'tasks');
        $this->assertNotNull($tasks);
        $titles = collect($tasks['items'])->pluck('title');
        $this->assertContains('accessible task', $titles);
        $this->assertNotContains('inaccessible task', $titles);
    }

    // ─── Filter Parameter ───────────────────────────────────────────

    public function test_filter_services_returns_only_service_modules(): void
    {
        Domain::factory()->create(['name' => 'filter-svc-domain.com', 'user_id' => $this->admin->id]);
        Asset::factory()->create(['asset_tag' => 'AST-FILTER-SVC', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=filter-svc&filter=services');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotNull($this->getGroup($data, 'domains'));
        $this->assertNull($this->getGroup($data, 'assets'));
    }

    public function test_filter_assets_returns_only_assets(): void
    {
        Asset::factory()->create(['asset_tag' => 'AST-FILTER-1', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'filter-other.com', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=FILTER&filter=assets');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotNull($this->getGroup($data, 'assets'));
        $this->assertNull($this->getGroup($data, 'domains'));
    }

    public function test_filter_tasks_returns_only_tasks(): void
    {
        Task::factory()->create([
            'title' => 'filter-task-item',
            'module_id' => $this->module->id,
            'created_by' => $this->admin->id,
            'updated_by' => $this->admin->id,
        ]);
        Domain::factory()->create(['name' => 'filter-other.com', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=filter&filter=tasks');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotNull($this->getGroup($data, 'tasks'));
        $this->assertNull($this->getGroup($data, 'domains'));
    }

    public function test_filter_vault_returns_only_vault(): void
    {
        VaultEntry::factory()->create([
            'service_name' => 'filter-vault-secret',
            'user_id' => $this->admin->id,
        ]);
        Domain::factory()->create(['name' => 'filter-other.com', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=filter&filter=vault');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotNull($this->getGroup($data, 'vault'));
        $this->assertNull($this->getGroup($data, 'domains'));
    }

    public function test_filter_users_returns_only_users(): void
    {
        User::factory()->create(['name' => 'filter-user-person']);
        Domain::factory()->create(['name' => 'filter-other.com', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=filter&filter=users');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotNull($this->getGroup($data, 'users'));
        $this->assertNull($this->getGroup($data, 'domains'));
    }

    // ─── Relevance Ordering ────────────────────────────────────────

    public function test_relevance_ordering_exact_match_first(): void
    {
        Domain::factory()->create(['name' => 'exact-order.com', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'exact-order-extra.com', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=exact-order');

        $response->assertOk();
        $data = $response->json('data');
        $domains = $this->getGroup($data, 'domains');
        $this->assertNotNull($domains);
        $this->assertEquals('exact-order.com', $domains['items'][0]['title']);
    }

    public function test_relevance_ordering_starts_with_before_contains(): void
    {
        Domain::factory()->create(['name' => 'zzz-startsWith-other.com', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'startsWith-match.com', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=startsWith');

        $response->assertOk();
        $data = $response->json('data');
        $domains = $this->getGroup($data, 'domains');
        $this->assertNotNull($domains);
        $this->assertEquals('startsWith-match.com', $domains['items'][0]['title']);
    }

    // ─── Limits ─────────────────────────────────────────────────────

    public function test_max_five_per_module(): void
    {
        for ($i = 1; $i <= 7; $i++) {
            Domain::factory()->create(['name' => "limit-module-dom-{$i}.com", 'user_id' => $this->admin->id]);
        }

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=limit-module-dom');

        $response->assertOk();
        $data = $response->json('data');
        $domains = $this->getGroup($data, 'domains');
        $this->assertNotNull($domains);
        $this->assertCount(5, $domains['items']);
    }

    public function test_limit_parameter_respected(): void
    {
        Domain::factory()->create(['name' => 'lim-param-one.com', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'lim-param-two.com', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'lim-param-three.com', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=lim-param&limit=2');

        $response->assertOk();
        $data = $response->json('data');
        $domains = $this->getGroup($data, 'domains');
        $this->assertNotNull($domains);
        $this->assertCount(2, $domains['items']);
    }

    // ─── Highlighting ──────────────────────────────────────────────

    public function test_highlighting_in_title(): void
    {
        Domain::factory()->create(['name' => 'highlight-test-domain.com', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=highlight');

        $response->assertOk();
        $data = $response->json('data');
        $domains = $this->getGroup($data, 'domains');
        $this->assertNotNull($domains);
        $title = $domains['items'][0]['title_highlighted'];
        $this->assertStringContainsString('<mark', $title);
    }

    public function test_highlighting_helper(): void
    {
        $result = SearchHelper::highlight('Hello World', 'world');
        $this->assertStringContainsString('<mark', $result);
        $this->assertStringContainsString('World', $result);
    }

    public function test_highlighting_case_insensitive(): void
    {
        $result = SearchHelper::highlight('Find Me Please', 'me');
        $this->assertStringContainsString('<mark', $result);
    }

    // ─── Empty / No Results ────────────────────────────────────────

    public function test_no_results_returns_empty_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=nonexistent-term-xyz-123');

        $response->assertOk();
        $this->assertEquals([], $response->json('data'));
    }

    // ─── Web Search Page ───────────────────────────────────────────

    public function test_web_search_page_loads(): void
    {
        $this->actingAs($this->admin)
            ->get(route('search', ['q' => 'test']))
            ->assertStatus(200);
    }

    public function test_web_search_with_filter(): void
    {
        $this->actingAs($this->admin)
            ->get(route('search', ['q' => 'test', 'filter' => 'services']))
            ->assertStatus(200);
    }

    public function test_web_search_short_query(): void
    {
        $this->actingAs($this->admin)
            ->get(route('search', ['q' => 'x']))
            ->assertStatus(200);
    }

    public function test_web_search_non_admin(): void
    {
        Note::create(['content' => 'web search note', 'user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('search', ['q' => 'web search']))
            ->assertStatus(200);
    }

    public function test_web_search_shows_results(): void
    {
        Domain::factory()->create(['name' => 'web-display-domain.com', 'user_id' => $this->admin->id]);

        $this->actingAs($this->admin)
            ->get(route('search', ['q' => 'web-display']))
            ->assertStatus(200)
            ->assertSee('web-display');
    }

    // ─── Service: Unit-Level Tests ─────────────────────────────────

    public function test_service_returns_empty_for_short_query(): void
    {
        $this->assertSame([], $this->service->search('x', $this->admin));
    }

    public function test_service_returns_results_for_valid_query(): void
    {
        Domain::factory()->create(['name' => 'service-test-domain.com', 'user_id' => $this->admin->id]);

        $results = $this->service->search('service-test', $this->admin);
        $this->assertArrayHasKey('domains', $results);
        $this->assertCount(1, $results['domains']['items']);
        $this->assertEquals('Domains', $results['domains']['label']);
    }

    public function test_service_search_for_api_returns_groups(): void
    {
        Domain::factory()->create(['name' => 'api-search-group.com', 'user_id' => $this->admin->id]);

        $groups = $this->service->searchForApi('api-search-group', $this->admin);
        $this->assertIsArray($groups);
        $this->assertArrayHasKey('key', $groups[0]);
        $this->assertArrayHasKey('label', $groups[0]);
        $this->assertArrayHasKey('url', $groups[0]);
        $this->assertArrayHasKey('items', $groups[0]);
    }

    // ─── Badge Values ──────────────────────────────────────────────

    public function test_badge_value_present_in_results(): void
    {
        Domain::factory()->create([
            'name' => 'badge-test-domain.com',
            'status' => 'active',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=badge-test');

        $response->assertOk();
        $data = $response->json('data');
        $domains = $this->getGroup($data, 'domains');
        $this->assertNotNull($domains);
        $this->assertEquals('active', $domains['items'][0]['badge']);
    }

    // ─── Multi-Column Search ──────────────────────────────────────

    public function test_search_matches_subtitle_column(): void
    {
        Hosting::factory()->create([
            'name' => 'Example Hosting',
            'domain' => 'subtitle-search.com',
            'user_id' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=subtitle-search');

        $response->assertOk();
        $data = $response->json('data');
        $hostings = $this->getGroup($data, 'hostings');
        $this->assertNotNull($hostings);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $adminRole = Role::where('slug', 'super-admin')->firstOrFail();
        $this->admin = User::factory()->create();
        $this->admin->assignRole($adminRole);

        $this->user = User::factory()->create();
    }

    public function test_super_admin_sees_all_records(): void
    {
        Domain::factory()->create(['name' => 'alpha-example.com', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'alpha-other.com', 'user_id' => $this->user->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=alpha');

        $response->assertOk();
        $data = $response->json('data');
        $this->assertNotEmpty($data);
        $items = collect($data)->firstWhere('key', 'domains');
        $this->assertNotNull($items);
        $this->assertCount(2, $items['items']);
    }

    public function test_non_admin_only_sees_own_records(): void
    {
        Domain::factory()->create(['name' => 'beta-mine.com', 'user_id' => $this->user->id]);
        Domain::factory()->create(['name' => 'beta-theirs.com', 'user_id' => $this->admin->id]);

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/search?q=beta');

        $response->assertOk();
        $data = $response->json('data');
        $items = collect($data)->firstWhere('key', 'domains');
        $this->assertNotNull($items);
        $this->assertCount(1, $items['items']);
        $this->assertEquals('beta-mine.com', $items['items'][0]['title']);
    }

    public function test_notes_scoped_to_own_records_for_non_admin(): void
    {
        Note::create(['content' => 'gamma secret note', 'user_id' => $this->user->id]);
        Note::create(['content' => 'gamma admin note', 'user_id' => $this->admin->id]);

        $token = $this->user->createToken('test')->plainTextToken;
        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/search?q=gamma');

        $response->assertOk();
        $data = $response->json('data');
        $items = collect($data)->firstWhere('key', 'notes');
        $this->assertNotNull($items);
        $this->assertCount(1, $items['items']);
        $this->assertEquals('gamma secret note', $items['items'][0]['title']);
    }

    public function test_requires_minimum_two_characters(): void
    {
        $this->actingAs($this->admin)
            ->getJson('/api/search?q=a')
            ->assertOk()
            ->assertJsonPath('data', []);
    }

    public function test_requires_authentication(): void
    {
        $this->getJson('/api/search?q=test')->assertUnauthorized();
    }

    public function test_limit_parameter_respected(): void
    {
        Domain::factory()->create(['name' => 'eta-one', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'eta-two', 'user_id' => $this->admin->id]);
        Domain::factory()->create(['name' => 'eta-three', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=eta&limit=2');

        $response->assertOk();
        $data = $response->json('data');
        $items = collect($data)->firstWhere('key', 'domains');
        $this->assertNotNull($items);
        $this->assertCount(2, $items['items']);
    }

    public function test_super_admin_sees_all_users_notes(): void
    {
        Note::create(['content' => 'theta-secret', 'user_id' => $this->user->id]);
        Note::create(['content' => 'theta-admin', 'user_id' => $this->admin->id]);

        $response = $this->actingAs($this->admin)
            ->getJson('/api/search?q=theta');

        $response->assertOk();
        $data = $response->json('data');
        $items = collect($data)->firstWhere('key', 'notes');
        $this->assertNotNull($items);
        $this->assertCount(2, $items['items']);
    }

    public function test_non_admin_sees_tasks_in_accessible_modules(): void
    {
        $this->seed(FeatureModuleSeeder::class);

        $role = Role::where('slug', 'user')->firstOrFail();
        $this->user->assignRole($role);

        $module = Module::first();
        $this->assertNotNull($module);

        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $role->id,
            'can_read' => true,
        ]);

        Task::create([
            'title' => 'searchable task for modules',
            'module_id' => $module->id,
            'status' => 'pending',
            'priority' => 'medium',
            'created_by' => 1,
            'updated_by' => 1,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/search?q=searchable');

        $response->assertOk();
        $data = $response->json('data');
        $items = collect($data)->firstWhere('key', 'tasks');
        $this->assertNotNull($items);
        $this->assertCount(1, $items['items']);
    }

    public function test_web_search_non_admin(): void
    {
        $role = Role::where('slug', 'user')->firstOrFail();
        $this->user->assignRole($role);
        Note::create(['content' => 'web search note', 'user_id' => $this->user->id]);

        $this->actingAs($this->user)
            ->get(route('search', ['q' => 'web search']))
            ->assertStatus(200);
    }

    public function test_web_search_super_admin(): void
    {
        Domain::factory()->create(['name' => 'web-search-domain.com']);

        $this->actingAs($this->admin)
            ->get(route('search', ['q' => 'web-search']))
            ->assertStatus(200);
    }

    public function test_web_search_short_query(): void
    {
        $this->actingAs($this->admin)
            ->get(route('search', ['q' => 'x']))
            ->assertStatus(200);
    }

    public function test_web_search_non_admin_tasks(): void
    {
        $this->seed(FeatureModuleSeeder::class);
        $role = Role::where('slug', 'user')->firstOrFail();
        $this->user->assignRole($role);
        $module = Module::first();
        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $role->id,
            'can_read' => true,
        ]);
        Task::create([
            'title' => 'web search task', 'module_id' => $module->id,
            'status' => 'pending', 'priority' => 'medium',
            'created_by' => 1, 'updated_by' => 1,
        ]);

        $this->actingAs($this->user)
            ->get(route('search', ['q' => 'web search']))
            ->assertStatus(200);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Domain;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    public function test_app_root_redirects_guest_to_login(): void
    {
        $response = $this->get('/');
        $response->assertStatus(302);
    }

    public function test_unknown_api_route_returns_404(): void
    {
        $response = $this->getJson('/api/nonexistent-route');
        $response->assertStatus(404);
    }

    public function test_unauthenticated_user_gets_json_error(): void
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated']);
    }

    public function test_authenticated_user_can_access_me(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'email']]);
    }

    public function test_admin_without_super_admin_cannot_see_total_users(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $user->assignRole($adminRole);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonMissingPath('data.total_users');
    }

    public function test_non_admin_dashboard_with_module_permissions_tasks(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $user->assignRole($adminRole);
        $token = $user->createToken('test')->plainTextToken;
        $module = Module::first();

        ModuleRolePermission::create([
            'module_id' => $module->id,
            'role_id' => $adminRole->id,
            'can_read' => true,
        ]);

        Task::create([
            'title' => 'Assigned Task',
            'module_id' => $module->id,
            'status' => 'pending',
            'priority' => 'medium',
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');

        $response->assertStatus(200);
        $response->assertJsonPath('data.total_tasks', 1);
    }

    public function test_non_admin_dashboard_with_upcoming_expiries(): void
    {
        $user = User::factory()->create();
        $adminRole = Role::where('slug', 'admin')->firstOrFail();
        $user->assignRole($adminRole);
        $token = $user->createToken('test')->plainTextToken;

        $module = Module::first();
        ModuleRolePermission::updateOrCreate(
            ['module_id' => $module->id, 'role_id' => $adminRole->id],
            ['can_read' => true]
        );

        Domain::factory()->create([
            'name' => 'expiring-now.com',
            'module_id' => $module->id,
            'expiry_date' => now()->addDays(5)->toDateString(),
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        Hosting::factory()->create([
            'name' => 'hosting-expiring.com',
            'module_id' => $module->id,
            'expiry_date' => now()->addDays(10)->toDateString(),
            'status' => 'active',
            'user_id' => $user->id,
        ]);

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/dashboard');

        $response->assertStatus(200);
        $response->assertJsonPath('data.total_services', 2);
        $this->assertCount(2, $response->json('data.upcoming_expiries'));
    }
}

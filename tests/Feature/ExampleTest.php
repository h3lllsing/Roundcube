<?php

namespace Tests\Feature;

use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
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
}

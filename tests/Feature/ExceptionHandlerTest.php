<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExceptionHandlerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    public function test_unknown_api_route_returns_404_json()
    {
        $response = $this->getJson('/api/this-route-does-not-exist');
        $response->assertStatus(404);
    }

    public function test_validation_error_returns_422_with_errors_key()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/features', ['name' => '']);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    public function test_model_not_found_returns_404_json()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/features/99999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Not Found']);
    }

    public function test_authorization_error_returns_403_json()
    {
        $user = User::factory()->create();
        $feature = \App\Models\Feature::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/features/{$feature->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Forbidden']);
    }
}

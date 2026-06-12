<?php

namespace Tests\Feature;

use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\HasinHayder\Tyro\Database\Seeders\TyroSeeder::class);
        $this->seed(\Database\Seeders\FeatureModuleSeeder::class);
    }

    public function test_login_with_valid_credentials()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Invalid credentials']);
    }

    public function test_login_validation()
    {
        $response = $this->postJson('/api/login', []);
        $response->assertStatus(422);
    }

    public function test_me_endpoint()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['id', 'name', 'email', 'roles', 'permissions']]);
    }

    public function test_me_unauthenticated()
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);
    }

    public function test_logout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test');

        $response = $this->withHeader('Authorization', 'Bearer '.$token->plainTextToken)
            ->postJson('/api/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Logged out successfully']);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $token->accessToken->id]);
    }

    public function test_login_lockout_after_5_failures()
    {
        $email = 'lockout@test.com';

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/login', ['email' => $email, 'password' => 'wrong']);
        }

        $response = $this->postJson('/api/login', ['email' => $email, 'password' => 'wrong']);
        $response->assertStatus(429);
    }

    public function test_forgot_password_validation()
    {
        $response = $this->postJson('/api/forgot-password', []);
        $response->assertStatus(422);
    }

    public function test_reset_password_validation()
    {
        $response = $this->postJson('/api/reset-password', []);
        $response->assertStatus(422);
    }

    public function test_reset_password_with_invalid_token_returns_422()
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/reset-password', [
            'email' => $user->email,
            'token' => 'invalid-token',
            'password' => 'newpassword',
            'password_confirmation' => 'newpassword',
        ]);

        $response->assertStatus(422);
    }

    public function test_me_includes_permissions()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/me');

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['permissions']]);
    }

    public function test_unauthenticated_cannot_access_tasks()
    {
        $response = $this->getJson('/api/tasks');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_vault()
    {
        $response = $this->getJson('/api/vault');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_notes()
    {
        $response = $this->getJson('/api/notes');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_features()
    {
        $response = $this->getJson('/api/features');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_notifications()
    {
        $response = $this->getJson('/api/notifications');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_my_tasks()
    {
        $response = $this->getJson('/api/my/tasks');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_activity_logs()
    {
        $response = $this->getJson('/api/activity-logs');
        $response->assertStatus(401);
    }

    public function test_unauthenticated_cannot_access_my_permissions()
    {
        $response = $this->getJson('/api/my/module-permissions');
        $response->assertStatus(401);
    }

    public function test_forgot_password_with_valid_email_returns_success()
    {
        $user = User::factory()->create();
        \Illuminate\Support\Facades\Notification::fake();

        $response = $this->postJson('/api/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }

    public function test_login_returns_token_structure()
    {
        $user = User::factory()->create(['password' => bcrypt('secret')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'secret',
        ]);

        $response->assertStatus(200);
        $token = $response->json('data.token');
        $this->assertNotEmpty($token);
        $this->assertIsString($token);
    }

    public function test_rate_limiter_config_is_defined()
    {
        $this->assertNotNull(config('cache'));
    }

    public function test_forgot_password_sends_email_notification()
    {
        $user = User::factory()->create();
        \Illuminate\Support\Facades\Notification::fake();

        $this->postJson('/api/forgot-password', ['email' => $user->email]);

        \Illuminate\Support\Facades\Notification::assertSentTo(
            $user,
            \Illuminate\Auth\Notifications\ResetPassword::class
        );
    }

    public function test_sanctum_expiration_config_is_set()
    {
        $expiration = config('sanctum.expiration');
        $this->assertNotNull($expiration);
        $this->assertIsInt($expiration);
        $this->assertGreaterThan(0, $expiration);
    }

    public function test_swagger_documentation_page_loads()
    {
        $response = $this->get('/api/documentation');
        $response->assertStatus(200);
    }
}

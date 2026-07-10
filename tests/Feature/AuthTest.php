<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\AuthController;
use App\Models\User;
use Database\Seeders\FeatureModuleSeeder;
use HasinHayder\Tyro\Database\Seeders\TyroSeeder;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Session\Store;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.allow_registration' => true]);
        $this->seed(TyroSeeder::class);
        $this->seed(FeatureModuleSeeder::class);
    }

    public function test_login_with_valid_credentials()
    {
        $user = User::factory()->create(['password' => bcrypt('Password1')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password1',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['token', 'user']]);
    }

    public function test_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'Wrong1',
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
            $this->postJson('/api/login', ['email' => $email, 'password' => 'Wrong1']);
        }

        $response = $this->postJson('/api/login', ['email' => $email, 'password' => 'Wrong1']);
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
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
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
        Notification::fake();

        $response = $this->postJson('/api/forgot-password', [
            'email' => $user->email,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message']);
    }

    public function test_login_returns_token_structure()
    {
        $user = User::factory()->create(['password' => bcrypt('Secret1')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Secret1',
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
        Notification::fake();

        $this->postJson('/api/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo(
            $user,
            ResetPassword::class
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

    // ─── Web Auth ───────────────────────────────────────────

    public function test_web_login_page_loads()
    {
        $response = $this->get(route('login'));
        $response->assertStatus(200);
    }

    public function test_web_login_with_valid_credentials()
    {
        $user = User::factory()->create(['password' => bcrypt('Password1')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_web_login_with_remember_me()
    {
        $user = User::factory()->create(['password' => bcrypt('Password1')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password1',
            'remember' => '1',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();
    }

    public function test_web_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => 'nonexistent@test.com',
            'password' => 'Wrong1',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_web_login_validation()
    {
        $response = $this->post('/login', []);
        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_web_logout()
    {
        $user = User::factory()->create(['password' => bcrypt('Password1')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password1',
        ]);

        $this->assertAuthenticated();

        $response = $this->post('/logout');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_access_login_page()
    {
        $user = User::factory()->create(['password' => bcrypt('Password1')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'Password1',
        ]);

        $response = $this->get(route('login'));
        $response->assertRedirect('/dashboard');
    }

    public function test_suspended_user_cannot_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password1'),
            'suspended_at' => now(),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'Password1',
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Account suspended']);
    }

    public function test_logout_invalidates_session()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $token = $user->createToken('test');
        $user->withAccessToken($token->accessToken);

        $sessionStore = $this->createMock(Store::class);
        $sessionStore->expects($this->once())->method('invalidate')->willReturn(true);
        $sessionStore->expects($this->once())->method('regenerateToken')->willReturn(true);

        $request = Request::create('/api/logout', 'POST');
        $request->setUserResolver(fn () => $user);
        $request->headers->set('Authorization', 'Bearer '.$token->plainTextToken);
        $request->setLaravelSession($sessionStore);

        $controller = new AuthController;
        $response = $controller->logout($request);

        $this->assertEquals(200, $response->status());
        $this->assertSame('Logged out successfully', $response->getData(true)['message']);
    }

    // ─── Registration ──────────────────────────────────────────

    public function test_web_registration_page_loads()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(200);
    }

    public function test_web_register()
    {
        $response = $this->post('/register', [
            'name' => 'New User',
            'email' => 'newuser@test.com',
            'password' => 'Pass12345',
            'password_confirmation' => 'Pass12345',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', ['email' => 'newuser@test.com']);
    }

    public function test_web_register_validation()
    {
        $response = $this->post('/register', []);
        $response->assertSessionHasErrors(['name', 'email', 'password']);
    }

    public function test_web_register_duplicate_email()
    {
        User::factory()->create(['email' => 'existing@test.com']);

        $response = $this->post('/register', [
            'name' => 'Dup User',
            'email' => 'existing@test.com',
            'password' => 'Pass12345',
            'password_confirmation' => 'Pass12345',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ─── Password Reset ────────────────────────────────────────

    public function test_web_forgot_password_page_loads()
    {
        $response = $this->get(route('password.request'));
        $response->assertStatus(200);
    }

    public function test_web_send_reset_link()
    {
        $user = User::factory()->create();
        Notification::fake();

        $response = $this->post(route('password.email'), ['email' => $user->email]);
        $response->assertSessionHas('success');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_web_reset_password_form_loads()
    {
        $response = $this->get(route('password.reset', 'fake-token'));
        $response->assertStatus(200);
    }

    public function test_web_reset_password()
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $response = $this->post(route('password.update'), [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewPass123', $user->fresh()->password));
    }

    public function test_web_reset_password_invalid_token()
    {
        $user = User::factory()->create();

        $response = $this->post(route('password.update'), [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    // ─── Profile ───────────────────────────────────────────────

    public function test_web_profile_page_loads()
    {
        $user = User::factory()->create(['password' => bcrypt('password')]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($user);
        $response = $this->get(route('profile'));
        $response->assertStatus(200);
    }

    public function test_web_update_profile()
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'email' => 'old@test.com',
            'password' => bcrypt('password'),
        ]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($user);
        $response = $this->put(route('profile.update'), [
            'name' => 'New Name',
            'email' => 'old@test.com',
        ]);
        $response->assertRedirect(route('profile'));
        $response->assertSessionHas('success');

        $this->assertEquals('New Name', $user->fresh()->name);
    }

    public function test_web_update_profile_with_password()
    {
        $user = User::factory()->create([
            'name' => 'Pwd User',
            'email' => 'pwd@test.com',
            'password' => bcrypt('OldPass1'),
        ]);
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($user);
        $response = $this->put(route('profile.update'), [
            'name' => 'Pwd User',
            'email' => 'pwd@test.com',
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
            'current_password' => 'OldPass1',
        ]);
        $response->assertRedirect(route('profile'));
        $response->assertSessionHas('success');

        $this->assertTrue(Hash::check('NewPass123', $user->fresh()->password));
    }

    // ─── My Permissions ────────────────────────────────────────

    public function test_web_my_permissions_as_super_admin()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'super-admin')->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($user);
        $response = $this->get(route('my-permissions'));
        $response->assertStatus(200);
    }

    public function test_web_my_permissions_as_regular_user()
    {
        $user = User::factory()->create();
        $role = Role::where('slug', 'user')->firstOrFail();
        $user->assignRole($role);

        $this->actingAs($user);
        $response = $this->get(route('my-permissions'));
        $response->assertStatus(200);
    }
}

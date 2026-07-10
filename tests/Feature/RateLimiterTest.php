<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class RateLimiterTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_rate_limiter_defined()
    {
        $this->assertNotNull(RateLimiter::limiter('api'));
    }

    public function test_api_rate_limiter_limit_is_60_per_minute()
    {
        $limiter = RateLimiter::limiter('api');
        $request = Request::create('/api/me', 'GET', [], [], [], ['REMOTE_ADDR' => '127.0.0.1']);
        $limit = $limiter($request);

        $this->assertEquals(60, $limit->maxAttempts);
    }

    public function test_api_rate_limiter_uses_user_id_when_authenticated()
    {
        $user = User::factory()->create();
        $limiter = RateLimiter::limiter('api');
        $request = Request::create('/api/me', 'GET');
        $request->setUserResolver(fn () => $user);

        $limit = $limiter($request);

        $this->assertEquals($user->id, $limit->key);
    }

    public function test_api_rate_limiter_uses_ip_when_unauthenticated()
    {
        $limiter = RateLimiter::limiter('api');
        $request = Request::create('/api/me', 'GET', [], [], [], ['REMOTE_ADDR' => '192.168.1.1']);

        $limit = $limiter($request);

        $this->assertEquals('192.168.1.1', $limit->key);
    }

    public function test_search_rate_limiter_defined()
    {
        $this->assertNotNull(RateLimiter::limiter('search'));
    }

    public function test_search_rate_limiter_limit_is_20_per_minute()
    {
        $limiter = RateLimiter::limiter('search');
        $user = User::factory()->create();
        $request = Request::create('/api/search', 'GET');
        $request->setUserResolver(fn () => $user);

        $limit = $limiter($request);

        $this->assertEquals(20, $limit->maxAttempts);
    }

    public function test_export_rate_limiter_defined()
    {
        $this->assertNotNull(RateLimiter::limiter('export'));
    }

    public function test_export_rate_limiter_limit_is_5_per_minute()
    {
        $limiter = RateLimiter::limiter('export');
        $user = User::factory()->create();
        $request = Request::create('/api/export/domains', 'GET');
        $request->setUserResolver(fn () => $user);

        $limit = $limiter($request);

        $this->assertEquals(5, $limit->maxAttempts);
    }

    public function test_bulk_rate_limiter_defined()
    {
        $this->assertNotNull(RateLimiter::limiter('bulk'));
    }

    public function test_bulk_rate_limiter_limit_is_10_per_minute()
    {
        $limiter = RateLimiter::limiter('bulk');
        $user = User::factory()->create();
        $request = Request::create('/api/bulk/domains', 'POST');
        $request->setUserResolver(fn () => $user);

        $limit = $limiter($request);

        $this->assertEquals(10, $limit->maxAttempts);
    }

    public function test_import_rate_limiter_defined()
    {
        $this->assertNotNull(RateLimiter::limiter('import'));
    }

    public function test_import_rate_limiter_limit_is_5_per_minute()
    {
        $limiter = RateLimiter::limiter('import');
        $user = User::factory()->create();
        $request = Request::create('/api/import/domains', 'POST');
        $request->setUserResolver(fn () => $user);

        $limit = $limiter($request);

        $this->assertEquals(5, $limit->maxAttempts);
    }

    public function test_forgot_password_throttles_after_5_attempts()
    {
        $user = User::factory()->create();

        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/forgot-password', ['email' => $user->email]);
        }

        $response = $this->postJson('/api/forgot-password', ['email' => $user->email]);
        $response->assertStatus(429);
    }

    public function test_reset_password_throttles_after_5_attempts()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/reset-password', [
                'email' => 'test@example.com',
                'token' => 'token',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ]);
        }

        $response = $this->postJson('/api/reset-password', [
            'email' => 'test@example.com',
            'token' => 'token',
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ]);
        $response->assertStatus(429);
    }

    public function test_login_throttle_middleware_applied()
    {
        $routes = collect(Route::getRoutes()->getRoutes());
        $loginRoute = $routes->first(fn ($r) => $r->uri === 'api/login');

        $this->assertNotNull($loginRoute);
        $middleware = $loginRoute->middleware();
        $this->assertContains('throttle:5,1', $middleware);
    }

    public function test_api_throttle_middleware_applied_to_authenticated_routes()
    {
        $routes = collect(Route::getRoutes()->getRoutes());
        $meRoute = $routes->first(fn ($r) => $r->uri === 'api/me');

        $this->assertNotNull($meRoute);
        $middleware = $meRoute->middleware();
        $this->assertContains('throttle:api', $middleware);
    }
}

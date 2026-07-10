<?php

namespace Tests\Unit;

use App\Http\Middleware\LogApiRequests;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Mockery;
use Tests\TestCase;

class LogApiRequestsMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    public function test_logs_request_info_unauthenticated(): void
    {
        $logger = Mockery::mock();
        $logger->shouldReceive('info')
            ->once()
            ->with('API Request', Mockery::on(function ($ctx) {
                return $ctx['method'] === 'GET'
                    && $ctx['status'] === 200
                    && $ctx['user_id'] === null;
            }));

        Log::shouldReceive('channel')
            ->once()
            ->with('api')
            ->andReturn($logger);

        $request = Request::create('_test/path', 'GET');
        $middleware = new LogApiRequests;
        $response = $middleware->handle($request, fn ($req) => new Response('ok'));

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function test_logs_authenticated_user_id(): void
    {
        $user = User::factory()->create();
        $logger = Mockery::mock();
        $logger->shouldReceive('info')
            ->once()
            ->with('API Request', Mockery::on(fn ($ctx) => $ctx['user_id'] === $user->id));

        Log::shouldReceive('channel')
            ->once()
            ->with('api')
            ->andReturn($logger);

        $request = Request::create('_test/path', 'GET');
        $request->setUserResolver(fn () => $user);
        $middleware = new LogApiRequests;
        $response = $middleware->handle($request, fn ($req) => new Response('ok'));

        $this->assertEquals(200, $response->getStatusCode());
    }
}

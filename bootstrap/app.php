<?php

use App\Providers\RateLimiterServiceProvider;
use App\Providers\ViewServiceProvider;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withProviders([
        RateLimiterServiceProvider::class,
        ViewServiceProvider::class,
    ])
    ->withEvents(discover: [
        __DIR__.'/../app/Listeners',
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'suspended' => \App\Http\Middleware\CheckSuspended::class,
            'security.headers' => \App\Http\Middleware\AddSecurityHeaders::class,
        ]);

        $middleware->append(\App\Http\Middleware\AddSecurityHeaders::class);


    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash([
            'current_password',
            'password',
            'password_confirmation',
        ]);

        $exceptions->render(function (Throwable $e, $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $status = 500;
                $message = 'Server Error';

                if ($e instanceof ValidationException) {
                    return response()->json([
                        'message' => $e->getMessage(),
                        'errors' => $e->errors(),
                    ], 422);
                }

                if ($e instanceof AuthenticationException) {
                    return response()->json(['message' => 'Unauthenticated'], 401);
                }

                if ($e instanceof AuthorizationException) {
                    return response()->json(['message' => 'Forbidden'], 403);
                }

                if ($e instanceof HttpExceptionInterface) {
                    $status = $e->getStatusCode();
                    $message = match ($status) {
                        403 => 'Forbidden',
                        404 => 'Not Found',
                        405 => 'Method Not Allowed',
                        429 => 'Too Many Requests',
                        default => $e->getMessage() ?: 'Server Error',
                    };
                }

                $payload = ['message' => $message];

                if ($status === 500 && in_array(config('app.env'), ['local', 'development'])) {
                    $payload['exception'] = $e->getMessage();
                    $payload['file'] = $e->getFile();
                    $payload['line'] = $e->getLine();
                }

                return response()->json($payload, $status);
            }
        });
    })->create();

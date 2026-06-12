<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\LoginAudit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use OpenApi\Attributes as OA;

class AuthController extends Controller
{
    private function logAudit(int|string|null $userId, string $email, string $event, Request $request): void
    {
        LoginAudit::create([
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event' => $event,
        ]);
    }
    #[OA\Post(
        path: '/login',
        summary: 'Authenticate user and return Sanctum token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(properties: [
                new OA\Property(property: 'email', type: 'string', format: 'email'),
                new OA\Property(property: 'password', type: 'string'),
                new OA\Property(property: 'device_name', type: 'string', default: 'default'),
            ])
        ),
        responses: [
            new OA\Response(response: 200, description: 'Login successful, returns token', content: new OA\JsonContent(ref: '#/components/schemas/LoginResponse')),
            new OA\Response(response: 422, description: 'Validation error', content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')),
            new OA\Response(response: 401, description: 'Invalid credentials', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
            new OA\Response(response: 429, description: 'Too many attempts', content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')),
        ]
    )]
    public function login(LoginRequest $request): \Illuminate\Http\JsonResponse
    {
        $email = $request->email;
        $lockoutKey = 'login_lockout:'.$email;
        $maxAttempts = 5;
        $lockoutMinutes = 1;

        if (Cache::has($lockoutKey) && Cache::get($lockoutKey) >= $maxAttempts) {
            $this->logAudit(null, $email, 'login_locked', $request);
            return $this->message('Too many login attempts. Try again in 1 minute.', 429);
        }

        $user = \App\Models\User::where('email', $email)->first();

        if (!$user || !\Illuminate\Support\Facades\Hash::check($request->password, $user->password)) {
            $attempts = Cache::get($lockoutKey, 0) + 1;
            Cache::put($lockoutKey, $attempts, now()->addMinutes($lockoutMinutes));
            $this->logAudit(null, $email, 'login_failed', $request);
            return $this->message('Invalid credentials', 401);
        }

        if ($user->suspended_at) {
            $this->logAudit($user->id, $email, 'login_suspended', $request);
            return $this->message('Account suspended', 403);
        }

        Cache::forget($lockoutKey);

        $this->logAudit($user->id, $email, 'login_success', $request);

        Auth::login($user);

        $deviceName = $request->device_name ?? 'default';
        $token = $user->createToken($deviceName)->plainTextToken;

        return $this->success([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('slug'),
            ],
        ]);
    }

    #[OA\Post(
        path: '/logout',
        summary: 'Revoke current Sanctum token',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Logged out successfully', content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse')),
        ]
    )]
    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $this->logAudit($user->id, $user->email, 'logout', $request);

        $user->currentAccessToken()->delete();

        if ($request->hasSession()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return $this->message('Logged out successfully');
    }

    #[OA\Get(
        path: '/me',
        summary: 'Get current authenticated user with roles and module permissions',
        security: [['sanctum' => []]],
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Current user details', content: new OA\JsonContent(ref: '#/components/schemas/UserData')),
        ]
    )]
    public function me(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = $request->user()->load('roles');
        return $this->success([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'roles' => $user->roles->pluck('slug'),
            'permissions' => $user->getAllModulePermissions(),
        ]);
    }
}

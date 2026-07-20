<?php

namespace App\Http\Controllers\Api;

use App\Enums\LoginEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ApiLoginRequest;
use App\Http\Resources\UserResource;
use App\Models\LoginAudit;
use App\Models\User;
use App\Services\LoginLockoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly LoginLockoutService $lockoutService,
    ) {}

    public function login(ApiLoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $email = $request->email;

        if ($this->lockoutService->tooManyAttempts($email)) {
            throw ValidationException::withMessages([
                'email' => ['Too many login attempts. Please try again later.'],
            ]);
        }

        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            $this->lockoutService->hit($request, $email);
            LoginAudit::create(['email' => $email, 'event' => LoginEvent::LoginFailed, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        if ($user->suspended_at) {
            LoginAudit::create(['user_id' => $user->id, 'email' => $email, 'event' => LoginEvent::LoginSuspended, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        $this->lockoutService->clear($email);
        $token = $user->createToken($request->device_name ?? 'api-token')->plainTextToken;

        LoginAudit::create(['user_id' => $user->id, 'email' => $email, 'event' => LoginEvent::LoginSuccess, 'ip_address' => $request->ip(), 'user_agent' => $request->userAgent()]);

        return response()->json(['token' => $token, 'user' => new UserResource($user)]);
    }
}

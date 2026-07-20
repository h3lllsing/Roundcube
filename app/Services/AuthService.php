<?php

namespace App\Services;

use App\Enums\LoginEvent;
use App\Models\LoginAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function logAudit(int|string|null $userId, string $email, string $event, Request $request): void
    {
        LoginAudit::create([
            'user_id' => $userId,
            'email' => $email,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'event' => $event,
        ]);
    }

    public function register(array $data): User
    {
        $data['password'] = Hash::make($data['password']);
        $data['password_changed_at'] = now();
        return User::create($data);
    }

    public function updateProfile(User $user, array $data): void
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
            $data['password_changed_at'] = now();
            $user->tokens()->delete();
        } else {
            unset($data['password']);
        }

        $user->update($data);
    }

    public function logPasswordReset(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
            'password_changed_at' => now(),
        ])->setRememberToken(Str::random(60));
        $user->save();

        $user->tokens()->delete();

        LoginAudit::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'event' => LoginEvent::PasswordReset,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        activity()->event('updated')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties(['type' => 'password_reset'])
            ->log('Password reset for user: '.$user->email);
    }
}

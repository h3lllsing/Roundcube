<?php

namespace App\Services;

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
        $user = User::create($data);
        $user->sendEmailVerificationNotification();

        return $user;
    }

    public function updateProfile(User $user, array $data): void
    {
        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
    }

    public function verifyEmail(User $user, string $hash): string
    {
        if (! hash_equals($hash, hash('sha256', $user->getEmailForVerification()))) {
            return 'invalid_link';
        }

        if ($user->hasVerifiedEmail()) {
            return 'already_verified';
        }

        $user->markEmailAsVerified();

        activity()->event('verified')
            ->causedBy($user)
            ->log('Email verified: '.$user->email);

        return 'verified';
    }

    public function logPasswordReset(User $user): void
    {
        $user->forceFill([
            'password' => Hash::make(request()->input('password')),
        ])->setRememberToken(Str::random(60));
        $user->save();

        activity()->event('updated')
            ->performedOn($user)
            ->causedBy($user)
            ->withProperties(['type' => 'password_reset'])
            ->log('Password reset for user: '.$user->email);
    }
}

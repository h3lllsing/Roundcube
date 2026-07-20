<?php

namespace App\Services;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;

class LoginLockoutService
{
    private const MAX_ATTEMPTS = 5;
    private const DECAY_SECONDS = 900;

    public function __construct(private readonly RateLimiter $limiter) {}

    public function hit(Request $request, string $email): void
    {
        $this->limiter->hit($this->throttleKey($email), self::DECAY_SECONDS);
    }

    public function tooManyAttempts(string $email): bool
    {
        return $this->limiter->tooManyAttempts($this->throttleKey($email), self::MAX_ATTEMPTS);
    }

    public function clear(string $email): void
    {
        $this->limiter->clear($this->throttleKey($email));
    }

    public function availableIn(string $email): int
    {
        return $this->limiter->availableIn($this->throttleKey($email));
    }

    private function throttleKey(string $email): string
    {
        return 'login-lockout:' . strtolower($email);
    }
}

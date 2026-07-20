<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class NotCommonPassword implements ValidationRule
{
    private const COMMON_PASSWORDS = [
        'password', 'password1', 'password123', '123456', '12345678', '123456789',
        'qwerty', 'qwerty123', 'abc123', 'letmein', 'welcome', 'monkey',
        'dragon', 'master', 'admin', 'admin123', 'root', 'toor',
        'iloveyou', 'sunshine', 'princess', 'football', 'baseball',
        'trustno1', 'hunter', 'ranger', 'passw0rd', 'Passw0rd',
        'changeme', 'secret', 'test', 'test123', 'guest',
        '000000', '111111', '11111111', '222222', '333333',
        'aaaaaa', 'aaaaaaa', 'abcdef', 'abcdefg',
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $lower = Str::lower((string) $value);

        if (in_array($lower, self::COMMON_PASSWORDS, true)) {
            $fail('This password is too common and easily guessed. Please choose a different one.');
        }
    }
}

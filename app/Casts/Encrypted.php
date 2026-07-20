<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;

class Encrypted implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            Crypt::decryptString($value);
            return $value;
        } catch (\Throwable) {
            return Crypt::encryptString($value);
        }
    }
}

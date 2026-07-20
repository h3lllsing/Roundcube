<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class Encrypted implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?string
    {
        if (!$value) {
            return null;
        }
        try {
            return Crypt::decryptString($value);
        } catch (\Throwable $e) {
            Log::warning('Failed to decrypt field: {key} for model {model}#{id}', [
                'key' => $key,
                'model' => get_class($model),
                'id' => $model->getKey(),
                'error' => $e->getMessage(),
            ]);
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

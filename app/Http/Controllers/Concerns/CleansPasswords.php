<?php

namespace App\Http\Controllers\Concerns;

trait CleansPasswords
{
    protected function cleanPasswordField(array &$data, string $field = 'password'): void
    {
        if (empty($data[$field])) {
            unset($data[$field]);
        }
    }
}

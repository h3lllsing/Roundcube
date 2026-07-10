<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait HasAutoNotes
{
    public static function bootHasAutoNotes(): void
    {
        static::updated(function ($model) {
            $user = Auth::user();
            if (! $user) return;

            $changes = $model->getChanges();
            $original = $model->getOriginal();

            $notes = [];

            if (isset($changes['status']) && isset($original['status']) && $changes['status'] !== $original['status']) {
                $notes[] = "Status changed from '{$original['status']}' to '{$changes['status']}'";
            }

            if (isset($changes['password'])) {
                $notes[] = "Password updated";
            }

            if (isset($changes['cost']) && array_key_exists('cost', $original) && (float) $changes['cost'] !== (float) $original['cost']) {
                $notes[] = "Cost changed from {$original['cost']} to {$changes['cost']}";
            }

            if (isset($changes['expiry_date']) && isset($original['expiry_date']) && $changes['expiry_date'] !== $original['expiry_date']) {
                $notes[] = "Expiry date changed from {$original['expiry_date']} to {$changes['expiry_date']}";
            }

            foreach ($notes as $content) {
                $model->notes()->create([
                    'content' => $content . ' by ' . $user->name,
                    'user_id' => $user->id,
                ]);
            }
        });
    }
}

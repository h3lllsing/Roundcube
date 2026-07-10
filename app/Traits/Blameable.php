<?php

namespace App\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait Blameable
{
    public static function bootBlameable(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                if (! $model->created_by) {
                    $model->created_by = Auth::id();
                }
                if (! $model->updated_by) {
                    $model->updated_by = Auth::id();
                }
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        /** @var BelongsTo<User, $this> $relation */
        /** @phpstan-ignore-next-line  */
        $relation = $this->belongsTo(User::class, 'created_by');

        return $relation;
    }

    /** @return BelongsTo<User, $this> */
    public function updater(): BelongsTo
    {
        /** @var BelongsTo<User, $this> $relation */
        /** @phpstan-ignore-next-line  */
        $relation = $this->belongsTo(User::class, 'updated_by');

        return $relation;
    }
}

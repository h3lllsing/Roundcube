<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait Blameable
{
    public static function bootBlameable(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                if (!$model->created_by) {
                    $model->created_by = Auth::id();
                }
                if (!$model->updated_by) {
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

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this> */
    public function creator(): BelongsTo
    {
        /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this> $relation */
        /** @phpstan-ignore-next-line  */
        $relation = $this->belongsTo(config('tyro.models.user', 'App\Models\User'), 'created_by');
        return $relation;
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this> */
    public function updater(): BelongsTo
    {
        /** @var \Illuminate\Database\Eloquent\Relations\BelongsTo<\App\Models\User, $this> $relation */
        /** @phpstan-ignore-next-line  */
        $relation = $this->belongsTo(config('tyro.models.user', 'App\Models\User'), 'updated_by');
        return $relation;
    }
}

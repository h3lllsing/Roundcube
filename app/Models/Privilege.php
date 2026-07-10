<?php

namespace App\Models;

use HasinHayder\Tyro\Models\Privilege as TyroPrivilege;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * @deprecated Legacy privilege system — CRUD-able but never evaluated at runtime.
 *             Kept for reference; do not add new features.
 */
class Privilege extends TyroPrivilege
{
    use LogsActivity, SoftDeletes;

    protected $fillable = ['name', 'slug', 'description'];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Privilege {$this->name} {$eventName}");
    }
}

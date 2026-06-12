<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ModuleRolePermission extends Model
{
    use LogsActivity;

    protected $fillable = [
        'module_id',
        'role_id',
        'can_create',
        'can_read',
        'can_update',
        'can_delete',
        'can_approve',
        'can_export',
    ];

    protected function casts(): array
    {
        return [
            'can_create' => 'boolean',
            'can_read' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
            'can_approve' => 'boolean',
            'can_export' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "ModuleRolePermission for module {$this->module_id}, role {$this->role_id} {$eventName}");
    }

    /** @return BelongsTo<\App\Models\Module, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /** @return BelongsTo<\HasinHayder\Tyro\Models\Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(\HasinHayder\Tyro\Models\Role::class);
    }
}

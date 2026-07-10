<?php

namespace App\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class ModuleRolePermission extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'module_id',
        'role_id',
        'can_create',
        'can_read',
        'can_update',
        'can_delete',
        'can_export',
        'can_reveal',
        'can_import',
        'can_approve',
    ];

    protected function casts(): array
    {
        return [
            'can_create' => 'boolean',
            'can_read' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
            'can_export' => 'boolean',
            'can_reveal' => 'boolean',
            'can_import' => 'boolean',
            'can_approve' => 'boolean',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "ModuleRolePermission for module {$this->module_id}, role {$this->role_id} {$eventName}");
    }

    /** @return BelongsTo<Module, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /** @return BelongsTo<Role, $this> */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}

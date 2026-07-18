<?php

namespace App\Models;

use App\Traits\Blameable;
use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use Blameable, HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'feature_id',
        'name',
        'slug',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function isImportSupported(): bool
    {
        return in_array($this->slug, config('permissions.importable_modules', []));
    }

    public function isExportSupported(): bool
    {
        return in_array($this->slug, config('permissions.exportable_modules', []));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Module {$this->name} {$eventName}");
    }

    /** @return BelongsTo<Feature, $this> */
    public function feature(): BelongsTo
    {
        return $this->belongsTo(Feature::class);
    }

    /** @return BelongsTo<User, $this> */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** @return HasMany<ModuleRolePermission, $this> */
    public function rolePermissions(): HasMany
    {
        return $this->hasMany(ModuleRolePermission::class);
    }

}

<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Database\Factories\AssetFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasAutoNotes;

class Asset extends Model
{
    /** @use HasFactory<AssetFactory> */
    use HasAttachments, HasFactory, LogsActivity, SoftDeletes, HasAutoNotes;

    protected $fillable = [
        'user_id',
        'asset_tag',
        'brand',
        'model',
        'processor',
        'ram',
        'storage',
        'os',
        'category_id',
        'type_id',
        'serial_number',
        'status',
        'headphone',
        'additional_equipments',
        'assigned_to',
        'assigned_user_name',
        'reporting_authority',
        'location_id',
        'premises',
        'department',
        'issue_date',
        'return_date',
        'condition',
        'specifications',
        'description',
        'additional_comments',
        'primary_image',
        'vault_entry_id',
        'qr_identifier',
        'anydesk_id',
        'anydesk_password',
        'module_id',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'return_date' => 'date',
            'specifications' => 'array',
        ];
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('asset_tag', 'like', "%{$search}%")
              ->orWhere('brand', 'like', "%{$search}%")
              ->orWhere('model', 'like', "%{$search}%")
              ->orWhere('serial_number', 'like', "%{$search}%")
              ->orWhere('anydesk_id', 'like', "%{$search}%")
              ->orWhere('department', 'like', "%{$search}%")
              ->orWhere('premises', 'like', "%{$search}%")
              ->orWhere('reporting_authority', 'like', "%{$search}%")
              ->orWhere('assigned_user_name', 'like', "%{$search}%");
        });
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'type_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(AssetLocation::class, 'location_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class, 'module_id');
    }

    public function vaultEntry(): BelongsTo
    {
        return $this->belongsTo(VaultEntry::class, 'vault_entry_id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(AssetAssignment::class, 'asset_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return MorphMany<Note, $this> */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SmtpProfile extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    private ?int $cachedUsageCount = null;

    protected static function booted(): void
    {
        static::creating(function (SmtpProfile $profile) {
            if ($profile->is_default) {
                static::where('is_default', true)->lockForUpdate()->update(['is_default' => false]);
            }
        });
    }

    protected $fillable = [
        'name',
        'sender_name',
        'sender_email',
        'reply_to_email',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'is_default',
        'is_active',
        'priority',
        'last_tested_at',
        'last_test_status',
        'last_test_error',
        'created_by',
    ];

    protected $hidden = [
        'smtp_password',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
            'priority' => 'integer',
            'last_tested_at' => 'datetime',
        ];
    }

    public static function consumerTables(): array
    {
        return [
            ExpiryTracker::class => [
                'fk' => 'smtp_profile_id',
                'status_fk' => 'status',
                'active' => ['active', 'pending_renewal'],
            ],
        ];
    }

    public function usageCount(): int
    {
        if ($this->cachedUsageCount !== null) {
            return $this->cachedUsageCount;
        }

        $count = 0;
        foreach (static::consumerTables() as $modelClass => $config) {
            $count += $modelClass::where($config['fk'], $this->id)
                ->whereIn($config['status_fk'], $config['active'])
                ->count();
        }

        $this->cachedUsageCount = $count;
        return $count;
    }

    public function isInUse(): bool
    {
        return $this->usageCount() > 0;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function expiryTrackers(): HasMany
    {
        return $this->hasMany(ExpiryTracker::class, 'smtp_profile_id');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ExpiryTrackerNotification::class, 'smtp_profile_id');
    }

    public function setSmtpPasswordAttribute(?string $value): void
    {
        if ($value !== null) {
            $this->attributes['smtp_password'] = encrypt($value);
        }
    }

    public function getSmtpPasswordAttribute(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }
        return decrypt($value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->logExcept(['smtp_password'])
            ->dontLogIfAttributesChangedOnly(['last_tested_at', 'last_test_status', 'last_test_error']);
    }
}

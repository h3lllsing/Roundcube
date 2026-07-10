<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Database\Factories\ExpiryTrackerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasAutoNotes;

class ExpiryTracker extends Model
{
    /** @use HasFactory<ExpiryTrackerFactory> */
    use HasAttachments, HasFactory, LogsActivity, SoftDeletes, HasAutoNotes;

    protected $fillable = [
        'module_id',
        'user_id',
        'service_provider_id',
        'name',
        'username',

        'login_url',
        'expiry_date',
        'renewal_date',
        'billing_period_months',
        'cost',
        'status',
        'description',
        'monitoring_url',
        'last_ping_at',
        'email_notifications_enabled',
        'smtp_profile_id',
        'notify_days_before',
        'notify_on_expiry_day',
        'notify_assigned_user',
        'notify_admins',
        'notify_custom_emails',
        'last_notification_sent_at',
        'next_notification_due_at',
        'disabled_by',
        'disabled_at',
        'disable_reason',
        'trackable_type',
        'trackable_id',
    ];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'renewal_date' => 'date',
            'billing_period_months' => 'integer',
            'cost' => 'decimal:2',
            'last_ping_at' => 'datetime',
            'email_notifications_enabled' => 'boolean',
            'notify_days_before' => 'array',
            'notify_on_expiry_day' => 'boolean',
            'notify_assigned_user' => 'boolean',
            'notify_admins' => 'boolean',
            'notify_custom_emails' => 'array',
            'last_notification_sent_at' => 'datetime',
            'next_notification_due_at' => 'date',
            'disabled_at' => 'datetime',
        ];
    }

    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Module, $this> */
    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    /** @return BelongsTo<ServiceProvider, $this> */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function smtpProfile(): BelongsTo
    {
        return $this->belongsTo(SmtpProfile::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(ExpiryTrackerNotification::class);
    }

    public function disabledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disabled_by');
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

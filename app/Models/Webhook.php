<?php

namespace App\Models;

use Database\Factories\WebhookFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Webhook extends Model
{
    /** @use HasFactory<WebhookFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'user_id',
        'name',
        'url',
        'events',
        'is_active',
        'last_fired_at',
    ];

    protected $hidden = [
        'secret',
    ];

    protected function casts(): array
    {
        return [
            'events' => 'array',
            'is_active' => 'boolean',
            'last_fired_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $webhook) {
            if (empty($webhook->secret)) {
                $webhook->secret = bin2hex(random_bytes(32));
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "Webhook {$this->name} {$eventName}");
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

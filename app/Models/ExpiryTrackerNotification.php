<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ExpiryTrackerNotification extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'expiry_tracker_id',
        'smtp_profile_id',
        'sender_email',
        'reminder_day',
        'recipient_email',
        'recipient_type',
        'trigger_source',
        'status',
        'sent_at',
        'error_message',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
        ];
    }

    public function expiryTracker(): BelongsTo
    {
        return $this->belongsTo(ExpiryTracker::class);
    }

    public function smtpProfile(): BelongsTo
    {
        return $this->belongsTo(SmtpProfile::class);
    }
}

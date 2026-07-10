<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Database\Factories\DomainFactory;
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

class Domain extends Model
{
    /** @use HasFactory<DomainFactory> */
    use HasAttachments, HasFactory, LogsActivity, SoftDeletes, HasAutoNotes;

    protected $fillable = [
        'module_id', 'hosting_id', 'service_provider_id', 'user_id', 'name', 'registration_date', 'expiry_date',
        'auto_renew', 'billing_period_months', 'cost', 'status', 'cloudflare_status', 'dns_servers', 'description', 'monitoring_url', 'last_ping_at',
    ];

    protected function casts(): array
    {
        return [
            'auto_renew' => 'boolean',
            'registration_date' => 'date',
            'expiry_date' => 'date',
            'billing_period_months' => 'integer',
            'cost' => 'decimal:2',
            'dns_servers' => 'array',
            'last_ping_at' => 'datetime',
        ];
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

    /** @return BelongsTo<Hosting, $this> */
    public function hosting(): BelongsTo
    {
        return $this->belongsTo(Hosting::class);
    }

    /** @return BelongsTo<ServiceProvider, $this> */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    /** @return HasMany<DomainEmail, $this> */
    public function domainEmails(): HasMany
    {
        return $this->hasMany(DomainEmail::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs();
    }

    /** @return MorphMany<Note, $this> */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}

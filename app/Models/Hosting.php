<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Database\Factories\HostingFactory;
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

class Hosting extends Model
{
    /** @use HasFactory<HostingFactory> */
    use HasAttachments, HasFactory, LogsActivity, SoftDeletes, HasAutoNotes;

    protected $fillable = ['module_id', 'user_id', 'service_provider_id', 'name', 'username', 'password', 'cpanel_url', 'plan', 'domain', 'domain_ip', 'mail_domain_ip', 'cpanel_ip', 'start_date', 'expiry_date', 'billing_period_months', 'cost', 'status', 'description', 'monitoring_url', 'last_ping_at'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expiry_date' => 'date',
            'billing_period_months' => 'integer',
            'cost' => 'decimal:2',
            'password' => 'encrypted',
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

    /** @return HasMany<Domain, $this> */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /** @return BelongsTo<ServiceProvider, $this> */
    public function serviceProvider(): BelongsTo
    {
        return $this->belongsTo(ServiceProvider::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logFillable()->logOnlyDirty()->dontSubmitEmptyLogs()->dontLogIfAttributesChangedOnly(['password']);
    }

    /** @return MorphMany<Note, $this> */
    public function notes(): MorphMany
    {
        return $this->morphMany(Note::class, 'notable');
    }
}

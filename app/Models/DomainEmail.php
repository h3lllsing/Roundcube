<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Database\Factories\DomainEmailFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\Note;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use App\Traits\HasAutoNotes;

class DomainEmail extends Model
{
    /** @use HasFactory<DomainEmailFactory> */
    use HasAttachments, HasFactory, LogsActivity, SoftDeletes, HasAutoNotes;

    protected $table = 'domain_emails';

    protected $fillable = ['module_id', 'user_id', 'service_provider_id', 'domain_id', 'email', 'password', 'storage_mb', 'billing_period_months', 'cost', 'expiry_date', 'status', 'description', 'monitoring_url', 'last_ping_at'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'expiry_date' => 'date',
            'storage_mb' => 'integer',
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

    /** @return BelongsTo<Domain, $this> */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
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

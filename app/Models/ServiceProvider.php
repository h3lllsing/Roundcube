<?php

namespace App\Models;

use App\Traits\HasAttachments;
use Database\Factories\ServiceProviderFactory;
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

class ServiceProvider extends Model
{
    /** @use HasFactory<ServiceProviderFactory> */
    use HasAttachments, HasFactory, LogsActivity, SoftDeletes, HasAutoNotes;

    protected $table = 'service_providers';

    protected $fillable = ['module_id', 'user_id', 'name', 'type', 'provider', 'email', 'website', 'login_id', 'password', 'cost', 'start_date', 'expiry_date', 'status', 'description', 'monitoring_url', 'last_ping_at'];

    protected $hidden = ['password'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'expiry_date' => 'date',
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

    /** @return HasMany<Hosting, $this> */
    public function hostings(): HasMany
    {
        return $this->hasMany(Hosting::class);
    }

    /** @return HasMany<Domain, $this> */
    public function domains(): HasMany
    {
        return $this->hasMany(Domain::class);
    }

    /** @return HasMany<Vps, $this> */
    public function vps(): HasMany
    {
        return $this->hasMany(Vps::class);
    }

    /** @return HasMany<ExpiryTracker, $this> */
    public function expiryTrackers(): HasMany
    {
        return $this->hasMany(ExpiryTracker::class);
    }

    /** @return HasMany<OtherService, $this> */
    public function otherServices(): HasMany
    {
        return $this->hasMany(OtherService::class);
    }

    /** @return HasMany<Voip, $this> */
    public function voip(): HasMany
    {
        return $this->hasMany(Voip::class);
    }

    /** @return HasMany<DomainEmail, $this> */
    public function domainEmails(): HasMany
    {
        return $this->hasMany(DomainEmail::class);
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

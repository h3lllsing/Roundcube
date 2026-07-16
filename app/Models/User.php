<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Traits\HasModulePermissions;
use Database\Factories\UserFactory;
use HasinHayder\Tyro\Concerns\HasTyroRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasModulePermissions, HasTyroRoles, LogsActivity, MustVerifyEmailTrait;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new \App\Notifications\VerifyEmail);
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'suspension_reason',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function notes(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Note::class);
    }

    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function domains(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Domain::class);
    }

    public function hostings(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Hosting::class);
    }

    public function vaultEntries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(VaultEntry::class);
    }

    public function loginAudits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoginAudit::class);
    }

    public function vps(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Vps::class);
    }

    public function voip(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Voip::class);
    }

    public function serviceProviders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ServiceProvider::class);
    }

    public function domainEmails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(DomainEmail::class);
    }

    public function otherServices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OtherService::class);
    }

    public function expiryTrackers(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ExpiryTracker::class);
    }

    public function assignedAssets(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Asset::class, 'assigned_to');
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'causer');
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Attachment::class);
    }

    public function webhooks(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['password'])
            ->setDescriptionForEvent(fn (string $eventName) => "User {$this->email} {$eventName}");
    }

    public function getUnreadNotificationCountAttribute(): int
    {
        return $this->unreadNotifications()->count();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'suspended_at' => 'datetime',
            'suspension_reason' => 'string',
        ];
    }
}

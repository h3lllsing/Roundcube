<?php

namespace App\Models;

use App\Traits\Sortable;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class User extends Authenticatable
{
    use LogsActivity;

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes, Sortable;

    protected array $sortableColumns = ['name', 'email', 'role', 'created_at'];

    protected $fillable = [
        'name',
        'email',
        'role',
        'password',
        'password_changed_at',
        'suspension_reason',
        'deleted_by',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function loginAudits(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LoginAudit::class);
    }

    public function activities(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(\Spatie\Activitylog\Models\Activity::class, 'causer');
    }

    public function deleter(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function assignedEmailAccounts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(EmailAccount::class, 'email_account_user')
            ->withPivot('can_send', 'can_receive', 'assigned_by')
            ->withTimestamps();
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

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super-admin';
    }

    public function isAdmin(): bool
    {
        return in_array($this->role, ['super-admin', 'admin']);
    }

    public function isSuspended(): bool
    {
        return $this->suspended_at !== null;
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
            'suspended_at' => 'datetime',
            'suspension_reason' => 'string',
        ];
    }
}

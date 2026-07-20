<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;

class EmailAccount extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'domain_id',
        'email',
        'password',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
        'smtp_password',
        'status',
        'sync_enabled',
        'created_by',
        'deleted_by',
        'last_sync_at',
    ];

    protected function casts(): array
    {
        return [
            'imap_port' => 'integer',
            'smtp_port' => 'integer',
            'sync_enabled' => 'boolean',
            'last_sync_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    protected function password(): Attribute
    {
        return Attribute::make(
            set: fn (string $value) => Crypt::encryptString($value),
            get: fn (string $value) => Crypt::decryptString($value),
        );
    }

    protected function smtpPassword(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value) => $value ? Crypt::encryptString($value) : null,
            get: fn (?string $value) => $value ? Crypt::decryptString($value) : null,
        );
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'email_account_user')
            ->withPivot('can_send', 'can_receive', 'assigned_by')
            ->withTimestamps();
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    public function scopeAssignedToActiveUsers($query): void
    {
        $query->whereHas('assignedUsers', fn ($q) => $q->whereNull('suspended_at'));
    }
}

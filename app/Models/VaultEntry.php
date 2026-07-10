<?php

namespace App\Models;

use Database\Factories\VaultEntryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use RuntimeException;

class VaultEntry extends Model
{
    /** @use HasFactory<VaultEntryFactory> */
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'password_vault';

    protected $fillable = [
        'user_id',
        'module_id',
        'service_name',
        'service_url',
        'username',
        'description',
    ];

    protected $hidden = ['encrypted_password'];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $vaultEntry) {
            if (empty($vaultEntry->getAttribute('encrypted_password'))) {
                throw new RuntimeException('VaultEntry requires an encrypted password on creation. Call encryptPassword() first.');
            }
        });
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

    public function getPasswordMaskedAttribute(): string
    {
        try {
            $decrypted = $this->decryptPassword();

            return str_repeat('•', max(6, strlen($decrypted)));
        } catch (\Exception $e) {
            return '••••••';
        }
    }

    public function encryptPassword(string $plainText): void
    {
        $this->encrypted_password = Crypt::encryptString($plainText);
    }

    public function decryptPassword(): string
    {
        $value = $this->getAttribute('encrypted_password');
        if (empty($value)) {
            throw new \RuntimeException('Encrypted password is empty');
        }

        return Crypt::decryptString($value);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->dontLogIfAttributesChangedOnly(['encrypted_password']);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Crypt;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class VaultEntry extends Model
{
    /** @use HasFactory<\Database\Factories\VaultEntryFactory> */
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'password_vault';

    protected $fillable = [
        'user_id',
        'module_id',
        'service_name',
        'service_url',
        'username',
        'encrypted_password',
        'description',
    ];

    protected $hidden = ['encrypted_password'];

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

    public function setEncryptedPasswordAttribute(mixed $value): void
    {
        $this->attributes['encrypted_password'] = $value;
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}

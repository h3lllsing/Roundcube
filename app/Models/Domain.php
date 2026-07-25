<?php

namespace App\Models;

use App\Enums\AccountStatus;
use App\Traits\Sortable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use HasFactory, SoftDeletes, Sortable;

    protected array $sortableColumns = ['name', 'status', 'created_at'];

    protected static function booted(): void
    {
        static::deleted(function (Domain $domain) {
            if ($domain->isForceDeleting()) {
                return;
            }
            $domain->emailAccounts()->delete();
        });

        static::restored(function (Domain $domain) {
            $domain->emailAccounts()->withTrashed()->restore();
        });
    }

    protected $fillable = [
        'name',
        'status',
        'notes',
        'created_by',
        'deleted_by',
        'imap_host',
        'imap_port',
        'imap_encryption',
        'smtp_host',
        'smtp_port',
        'smtp_encryption',
        'smtp_username',
    ];

    protected function casts(): array
    {
        return [
            'status' => \App\Enums\DomainStatus::class,
            'imap_port' => 'integer',
            'smtp_port' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function emailAccounts(): HasMany
    {
        return $this->hasMany(EmailAccount::class);
    }

    public function activeEmailAccounts(): HasMany
    {
        return $this->hasMany(EmailAccount::class)
            ->where('sync_enabled', true)
            ->where('status', AccountStatus::Active);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }
}

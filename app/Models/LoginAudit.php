<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAudit extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'event',
    ];

    protected function casts(): array
    {
        return [
            'event' => \App\Enums\LoginEvent::class,
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::creating(function (self $audit) {
            $previous = self::orderByDesc('id')->value('hash_chain');
            $audit->hash_chain = hash(
                'sha256',
                ($previous ?? 'genesis') . '|' . $audit->event . '|' . ($audit->created_at ?? now()->toIso8601String()),
            );
        });
    }
}

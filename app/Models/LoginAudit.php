<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LoginAudit extends Model
{
    use HasFactory, SoftDeletes;
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
}

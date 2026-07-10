<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserModulePermission extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'module_id',
        'can_create',
        'can_read',
        'can_update',
        'can_delete',
        'can_export',
        'can_reveal',
        'can_import',
        'can_approve',
    ];

    protected function casts(): array
    {
        return [
            'can_create' => 'boolean',
            'can_read' => 'boolean',
            'can_update' => 'boolean',
            'can_delete' => 'boolean',
            'can_export' => 'boolean',
            'can_reveal' => 'boolean',
            'can_import' => 'boolean',
            'can_approve' => 'boolean',
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
}

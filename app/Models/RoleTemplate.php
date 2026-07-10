<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoleTemplate extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name', 'slug', 'description', 'version',
        'is_protected', 'is_dangerous', 'permissions_json',
    ];

    protected function casts(): array
    {
        return [
            'is_protected' => 'boolean',
            'is_dangerous' => 'boolean',
            'permissions_json' => 'array',
        ];
    }

    public function getModuleCountAttribute(): int
    {
        return count($this->permissions_json ?? []);
    }

    public static array $permissionColumns = [
        'can_create', 'can_read', 'can_update', 'can_delete',
        'can_approve', 'can_export', 'can_import', 'can_reveal',
    ];
}

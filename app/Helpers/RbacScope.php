<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RbacScope
{
    public static function apply(string $modelClass, string $visibility = 'ownership'): void
    {
        $user = Auth::user();

        if ($user->isSuperAdmin()) {
            return;
        }

        if ($visibility === 'module' || $user->isAdmin()) {
            $modelClass::addGlobalScope('noAccess', fn ($q) => $q->whereRaw('1 = 0'));
            return;
        }

        $modelClass::addGlobalScope('ownership', fn ($q) => $q->where('user_id', $user->id));
    }
}

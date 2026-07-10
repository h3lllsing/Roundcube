<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

class RbacScope
{
    public static function apply(string $modelClass, string $visibility = 'ownership'): void
    {
        $user = Auth::user();

        if ($user->hasRole('super-admin')) {
            return;
        }

        if ($visibility === 'module') {
            $accessibleIds = $user->getAccessibleModuleIds('read');
            if (! empty($accessibleIds)) {
                $modelClass::addGlobalScope('moduleScope', fn ($q) => $q->whereIn('module_id', $accessibleIds)->orWhereNull('module_id'));
            } else {
                $modelClass::addGlobalScope('noAccess', fn ($q) => $q->whereRaw('1 = 0'));
            }
            return;
        }

        if ($user->hasRole('admin')) {
            $accessibleIds = $user->getAccessibleModuleIds('read');
            if (! empty($accessibleIds)) {
                $modelClass::addGlobalScope('adminScope', fn ($q) => $q->whereIn('module_id', $accessibleIds)->orWhereNull('module_id'));
                return;
            }
        }

        $modelClass::addGlobalScope('ownership', fn ($q) => $q->where('user_id', $user->id));
    }
}

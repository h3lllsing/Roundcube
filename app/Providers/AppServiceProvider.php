<?php

namespace App\Providers;

use App\Models\Feature;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\UserModulePermission;
use HasinHayder\Tyro\Models\UserRole;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Paginator::useTailwind();

        Feature::saved(fn () => Cache::increment('dashboard:version'));
        Feature::deleted(fn () => Cache::increment('dashboard:version'));
        Module::saved(fn () => Cache::increment('dashboard:version'));
        Module::deleted(fn () => Cache::increment('dashboard:version'));
        ModuleRolePermission::saved(fn () => Cache::increment('perms_generation'));
        ModuleRolePermission::deleted(fn () => Cache::increment('perms_generation'));
        UserModulePermission::saved(fn () => Cache::increment('perms_generation'));
        UserModulePermission::deleted(fn () => Cache::increment('perms_generation'));

        UserRole::saved(fn () => Cache::increment('perms_generation'));
        UserRole::deleted(fn () => Cache::increment('perms_generation'));
    }
}

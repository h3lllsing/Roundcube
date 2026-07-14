<?php

namespace App\Providers;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Models\ExpiryTracker;
use App\Models\Feature;
use App\Models\Hosting;
use App\Models\Module;
use App\Models\ModuleRolePermission;
use App\Models\Note;
use App\Models\OtherService;
use App\Models\ServiceProvider as ServiceProviderModel;
use App\Models\Task;
use App\Models\UserModulePermission;
use App\Models\VaultEntry;
use App\Models\Voip;
use App\Models\Vps;
use App\Observers\DashboardCacheObserver;
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

        $models = [
            Domain::class, Hosting::class, Vps::class, Voip::class,
            ServiceProviderModel::class, DomainEmail::class, OtherService::class,
            ExpiryTracker::class, Note::class, VaultEntry::class, Task::class,
            Feature::class, Module::class, ModuleRolePermission::class,
            UserModulePermission::class,
        ];
        foreach ($models as $model) {
            $model::observe(DashboardCacheObserver::class);
        }

        Module::deleted(fn () => Cache::increment('perms_generation'));

        UserRole::saved(fn () => Cache::increment('perms_generation'));
        UserRole::deleted(fn () => Cache::increment('perms_generation'));
    }
}

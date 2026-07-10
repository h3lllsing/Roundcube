<?php

namespace App\Providers;

use App\Http\View\Composers\SidebarComposer;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('layouts.admin', SidebarComposer::class);

        View::share('statusColors', [
            'active'      => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
            'in_progress' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
            'pending'     => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-600 dark:text-yellow-400',
            'completed'   => 'bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400',
            'cancelled'   => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
            'expired'     => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
            'suspended'   => 'bg-orange-100 dark:bg-orange-900/30 text-orange-600 dark:text-orange-400',
        ]);
    }
}

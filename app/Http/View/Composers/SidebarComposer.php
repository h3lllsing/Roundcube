<?php

namespace App\Http\View\Composers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SidebarComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (!$user) {
            $view->with('showMonitoring', false);
            return;
        }

        if ($user->hasRole('super-admin')) {
            $view->with('showMonitoring', true);
            return;
        }

        $accessibleIds = $user->getAccessibleModuleIds('read');
        $modulesBySlug = \App\Helpers\ModuleCache::allBySlug();

        $monitoredSlugs = ['dashboard', 'monitor'];
        $hasAnyMonitoredModule = false;
        foreach ($monitoredSlugs as $slug) {
            $module = $modulesBySlug[$slug] ?? null;
            if ($module && in_array($module->id, $accessibleIds)) {
                $hasAnyMonitoredModule = true;
                break;
            }
        }
        $view->with('showMonitoring', $hasAnyMonitoredModule);
    }
}

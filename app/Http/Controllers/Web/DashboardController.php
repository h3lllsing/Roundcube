<?php

namespace App\Http\Controllers\Web;

use App\Dashboard\ActivityWidget;
use App\Dashboard\AssetsWidget;
use App\Dashboard\MonitoringWidget;
use App\Dashboard\OperationsWidget;
use App\Dashboard\QuickActionsWidget;
use App\Dashboard\RenewalsWidget;
use App\Dashboard\ServerHealthWidget;
use App\Dashboard\SmtpWidget;
use App\Dashboard\TasksWidget;
use App\Dashboard\VaultWidget;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private array $allWidgets = [
        OperationsWidget::class,
        RenewalsWidget::class,
        TasksWidget::class,
        AssetsWidget::class,
        MonitoringWidget::class,
        QuickActionsWidget::class,
        ActivityWidget::class,
        VaultWidget::class,
        SmtpWidget::class,
        ServerHealthWidget::class,
    ];

    public function index(): View
    {
        $user = Auth::user();
        $user->loadMissing('roles');
        $version = Cache::get('dashboard:version', 0);
        $isSA = $user->hasRole('super-admin');
        $accessibleIds = $isSA ? null : $user->getAccessibleModuleIds('read');

        $widgetClasses = $this->getWidgetsForRole($user);

        $data = ['dashboardRole' => $this->getRoleGroup($user)];
        foreach ($widgetClasses as $class) {
            $slug = $class::SLUG;
            $key = "dashboard:w:{$slug}:{$user->id}:v{$version}";

            try {
                $instance = app($class);
                $ttl = method_exists($instance, 'cacheTtl') ? $instance->cacheTtl() : 300;

                $widgetData = Cache::remember($key, $ttl, fn () => $instance->data($user, $accessibleIds));
                $data = array_merge($data, $widgetData);
            } catch (\Throwable $e) {
                Log::warning("Dashboard widget [{$slug}] failed", [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return view('dashboard.index', $data);
    }

    protected function getRoleGroup($user): string
    {
        $priority = ['super-admin', 'admin', 'editor', 'user', 'customer'];
        $userRoles = $user->roles->pluck('slug')->toArray();

        foreach ($priority as $role) {
            if (in_array($role, $userRoles)) {
                return $role;
            }
        }

        return 'user';
    }

    protected function getWidgetsForRole($user): array
    {
        if ($user->hasRole('super-admin')) {
            return $this->allWidgets;
        }

        $widgetMap = [
            'admin' => [
                OperationsWidget::class,
                RenewalsWidget::class,
                TasksWidget::class,
                AssetsWidget::class,
                MonitoringWidget::class,
                QuickActionsWidget::class,
                ActivityWidget::class,
                VaultWidget::class,
            ],
            'editor' => [
                TasksWidget::class,
                AssetsWidget::class,
                MonitoringWidget::class,
                QuickActionsWidget::class,
                ActivityWidget::class,
                VaultWidget::class,
            ],
            'user' => [
                OperationsWidget::class,
                MonitoringWidget::class,
                TasksWidget::class,
                QuickActionsWidget::class,
                ActivityWidget::class,
                VaultWidget::class,
            ],
            'customer' => [
                TasksWidget::class,
                QuickActionsWidget::class,
                ActivityWidget::class,
                VaultWidget::class,
            ],
        ];

        foreach ($widgetMap as $slug => $widgets) {
            if ($user->hasRole($slug)) {
                return $widgets;
            }
        }

        return $widgetMap['user'];
    }
}

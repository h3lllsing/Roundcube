@props(['title' => null])

@php
$routeName = request()->route()?->getName();
if (!$routeName) { return; }

$labels = [
    'dashboard' => 'Dashboard', 'calendar' => 'Calendar',
    'notifications' => 'Notifications',
    'service-providers' => 'Service Providers', 'hostings' => 'Hosting',
    'domains' => 'Domains', 'domain-emails' => 'Domain Emails',
    'voip' => 'VoIP', 'vps' => 'VPS Accounts',
    'other-services' => 'Other Services', 'expiry-trackers' => 'Renewals',
    'vault' => 'Shared Credentials', 'vault.my' => 'My Credentials',
    'tasks' => 'Task Management', 'tasks.my' => 'My Tasks',
    'features' => 'Features', 'modules' => 'Modules',
    'module-permissions' => 'Permissions', 'roles' => 'Roles',
    'role-templates' => 'Role Templates', 'privileges' => 'Privileges',
    'webhooks' => 'Webhooks', 'smtp-profiles' => 'SMTP Profiles',
    'activity-logs' => 'Activity Logs', 'login-audits' => 'Login Audits',
    'attachments' => 'Attachments', 'reports' => 'Reports',
    'import' => 'Import', 'tokens' => 'API Access',
    'users' => 'Users', 'my-permissions' => 'My Permissions',
    'guide' => 'Help Center', 'profile' => 'My Profile',
];

$parts = explode('.', $routeName);
$resource = $parts[0] ?? null;
$action = $parts[1] ?? null;
$crumbs = [['label' => 'Dashboard', 'url' => route('dashboard')]];

if ($resource && isset($labels[$routeName])) {
    $crumbs[] = ['label' => $labels[$routeName], 'url' => null];
} elseif ($resource && isset($labels[$resource])) {
    $indexRoute = "$resource.index";
    $indexUrl = \Illuminate\Support\Facades\Route::has($indexRoute) ? route($indexRoute) : null;
    $crumbs[] = ['label' => $labels[$resource], 'url' => $indexUrl];
    if ($action === 'create') {
        $crumbs[] = ['label' => 'Create', 'url' => null];
    } elseif ($action === 'edit') {
        if ($title) { $crumbs[] = ['label' => $title, 'url' => null]; }
        $crumbs[] = ['label' => 'Edit', 'url' => null];
    } elseif ($action === 'show') {
        $crumbs[] = ['label' => ($title && trim($title)) ? $title : 'View', 'url' => null];
    }
}
@endphp

@if (count($crumbs) > 1)
<nav aria-label="Breadcrumb" class="mb-4">
    <ol class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
        @foreach ($crumbs as $i => $crumb)
        <li class="flex items-center gap-1.5">
            @if ($i > 0)
            <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            @endif
            @if ($crumb['url'] && !$loop->last)
                <a href="{{ $crumb['url'] }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $crumb['label'] }}</a>
            @else
                <span class="text-gray-900 dark:text-gray-100 font-medium" aria-current="page">{{ $crumb['label'] }}</span>
            @endif
        </li>
        @endforeach
    </ol>
</nav>
@endif

@extends('layouts.admin')

@section('title', $user->name)
@section('breadcrumbTitle', $user->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="{{ $user->name }}" subtitle="Enterprise Permission Inspector" back-url="{{ route('users.index') }}">
            <x-slot:actions>
                <span class="text-xs text-gray-400 dark:text-gray-500 bg-white/70 dark:bg-black/70 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                    {{ now()->format('Y-m-d H:i') }}
                </span>
            </x-slot:actions>
        </x-page-header>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Roles</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @forelse ($user->roles as $role)
                        <a href="{{ route('roles.show', $role->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $role->name }}</a>@if(!$loop->last), @endif
                    @empty
                        <span class="text-gray-400">—</span>
                    @endforelse
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                <p class="mt-1 text-sm">
                    @if ($user->suspended_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Suspended</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                    @endif
                </p>
            </div>
            @if ($lastLogin)
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $lastLogin->created_at->format('Y-m-d H:i') }}</p>
            </div>
            @endif
        </div>
        <div class="flex items-center gap-3 pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
            <x-button href="{{ route('users.permissions.edit', $user->id) }}" variant="outline" size="sm">Edit Permissions</x-button>
            <x-button href="{{ route('users.clone', $user->id) }}" variant="outline" size="sm">Clone User</x-button>
            <x-button href="{{ route('users.edit', $user->id) }}" variant="primary" size="sm">Edit</x-button>
            <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
        </div>
    </div>

    @if ($inspectedIsSuperAdmin)
    <div class="mt-6 p-4 rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-sm text-green-700 dark:text-green-300">
        This user has unrestricted access through the <strong>Super Admin</strong> role.
    </div>
    @endif

    <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
        <x-stat-card label="Roles" :value="$summary['roles_count']" icon="users" color="indigo" />
        <x-stat-card label="Accessible Modules" :value="$summary['accessible_modules']" icon="shield" color="emerald" />
        <x-stat-card label="Denied Modules" :value="$summary['denied_modules']" icon="shield" color="rose" />
        <x-stat-card label="Overrides" :value="$summary['overrides_count']" icon="document" color="violet" />
        <x-stat-card label="Allowed Permissions" :value="$summary['allowed_permissions']" icon="features" color="emerald" />
        <x-stat-card label="Denied Permissions" :value="$summary['denied_permissions']" icon="features" color="rose" />
    </div>

    @php
        $featureGroups = $modulePermissions->groupBy(fn($mp) => $mp->feature ?? 'Uncategorized');
        $expandedByDefault = ['Infrastructure', 'Productivity'];
    @endphp
    @php
        $importableSlugs = config('permissions.importable_modules', []);
        $exportableSlugs = config('permissions.exportable_modules', []);
        $isViewerSA = auth()->user()->hasRole('super-admin');
    @endphp
    <div x-data="{ showRaw: false }" class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-1">
            <h3 class="text-md font-semibold">Permission Matrix</h3>
            @if ($isViewerSA)
            <label class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 cursor-pointer select-none">
                <input type="checkbox" x-model="showRaw" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                Debug: Show raw columns
            </label>
            @endif
        </div>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Shows all modules with effective permissions grouped by feature. Expand each group to inspect permissions.</p>
        <div class="space-y-3">
            @forelse ($featureGroups as $featureName => $modules)
                @php $isExpanded = in_array($featureName, $expandedByDefault); @endphp
                <div x-data="{ expanded: {{ $isExpanded ? 'true' : 'false' }} }" class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                    <button type="button" @click="expanded = !expanded" class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300 bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40">
                        <span>{{ $featureName }}</span>
                        <svg x-show="!expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        <svg x-show="expanded" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="expanded" x-cloak>
                        <div class="overflow-x-auto border-t border-gray-200 dark:border-gray-700">

                            {{-- Conceptual columns (always shown) --}}
                            <table class="w-full text-sm" x-show="!showRaw">
                                <thead>
                                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                                        <th scope="col" class="text-left px-3 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Module</th>
                                        <th scope="col" class="text-left px-3 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Feature</th>
                                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Access</th>
                                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Manage</th>
                                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Import</th>
                                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Export</th>
                                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">Full Access</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($modules as $mp)
                                    @php
                                        $cr = $mp->permissions['can_read'] ?? ['effective' => false, 'source' => 'None'];
                                        $cc = $mp->permissions['can_create'] ?? ['effective' => false, 'source' => 'None'];
                                        $cu = $mp->permissions['can_update'] ?? ['effective' => false, 'source' => 'None'];
                                        $ci = $mp->permissions['can_import'] ?? ['effective' => false, 'source' => 'None'];
                                        $ce = $mp->permissions['can_export'] ?? ['effective' => false, 'source' => 'None'];

                                        $hasAccess = $cr['effective'];
                                        $canManage = $cc['effective'] && $cu['effective'];
                                        $canImport = $ci['effective'];
                                        $canExport = $ce['effective'];

                                        $isImportable = in_array($mp->module_slug ?? '', $importableSlugs);
                                        $isExportable = in_array($mp->module_slug ?? '', $exportableSlugs);

                                        $isFullAccess = $hasAccess && $canManage
                                            && (!$isImportable || $canImport)
                                            && (!$isExportable || $canExport);
                                    @endphp
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-3 py-2.5 font-medium whitespace-nowrap">{{ $mp->module_name }}</td>
                                        <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">{{ $mp->feature ?? '—' }}</td>

                                        {{-- Access --}}
                                        <td class="px-2 py-2.5 text-center">
                                            @php $src = $cr['source']; @endphp
                                            @if ($hasAccess)
                                                <span class="text-green-600 dark:text-green-400 font-bold text-sm">&#10003;</span>
                                            @else
                                                <span class="text-red-400 dark:text-red-300 font-bold text-sm">&#10005;</span>
                                            @endif
                                            <span class="block text-[9px] leading-tight mt-0.5 text-blue-600 dark:text-blue-400">{{ $src }}</span>
                                        </td>

                                        {{-- Manage --}}
                                        <td class="px-2 py-2.5 text-center">
                                            @php
                                                $mSrc = $cc['source'] === $cu['source'] ? $cc['source'] : $cc['source'] . '/' . $cu['source'];
                                            @endphp
                                            @if ($canManage)
                                                <span class="text-green-600 dark:text-green-400 font-bold text-sm">&#10003;</span>
                                            @else
                                                <span class="text-red-400 dark:text-red-300 font-bold text-sm">&#10005;</span>
                                            @endif
                                            <span class="block text-[9px] leading-tight mt-0.5 text-blue-600 dark:text-blue-400">{{ $mSrc }}</span>
                                        </td>

                                        {{-- Import --}}
                                        <td class="px-2 py-2.5 text-center">
                                            @if ($isImportable)
                                                @php $iSrc = $ci['source']; @endphp
                                                @if ($canImport)
                                                    <span class="text-green-600 dark:text-green-400 font-bold text-sm">&#10003;</span>
                                                @else
                                                    <span class="text-red-400 dark:text-red-300 font-bold text-sm">&#10005;</span>
                                                @endif
                                                <span class="block text-[9px] leading-tight mt-0.5 text-blue-600 dark:text-blue-400">{{ $iSrc }}</span>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                            @endif
                                        </td>

                                        {{-- Export --}}
                                        <td class="px-2 py-2.5 text-center">
                                            @if ($isExportable)
                                                @php $eSrc = $ce['source']; @endphp
                                                @if ($canExport)
                                                    <span class="text-green-600 dark:text-green-400 font-bold text-sm">&#10003;</span>
                                                @else
                                                    <span class="text-red-400 dark:text-red-300 font-bold text-sm">&#10005;</span>
                                                @endif
                                                <span class="block text-[9px] leading-tight mt-0.5 text-blue-600 dark:text-blue-400">{{ $eSrc }}</span>
                                            @else
                                                <span class="text-gray-300 dark:text-gray-600">—</span>
                                            @endif
                                        </td>

                                        {{-- Full Access (derived, no source) --}}
                                        <td class="px-2 py-2.5 text-center">
                                            @if ($isFullAccess)
                                                <span class="text-rose-600 dark:text-rose-400 font-bold text-sm">&#10003;</span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500 font-bold text-sm">&#10005;</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            @if ($isViewerSA)
                            {{-- Raw internal columns (SA debug only) --}}
                            <table class="w-full text-sm" x-show="showRaw" x-cloak>
                                <thead>
                                    @php
                                        $rawLabels = [
                                            'can_read' => 'can_read',
                                            'can_create' => 'can_create',
                                            'can_update' => 'can_update',
                                            'can_delete' => 'can_delete',
                                            'can_approve' => 'can_approve',
                                            'can_export' => 'can_export',
                                            'can_reveal' => 'can_reveal',
                                            'can_import' => 'can_import',
                                        ];
                                    @endphp
                                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50/50 dark:bg-gray-800/30">
                                        <th scope="col" class="text-left px-3 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Module</th>
                                        <th scope="col" class="text-left px-3 py-2 font-medium text-gray-500 dark:text-gray-400 whitespace-nowrap">Feature</th>
                                        @foreach (config('permissions.keys') as $perm)
                                        <th scope="col" class="text-center px-2 py-2 font-medium text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">{{ $rawLabels[$perm] ?? $perm }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @foreach ($modules as $mp)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50">
                                        <td class="px-3 py-2.5 font-medium whitespace-nowrap">{{ $mp->module_name }}</td>
                                        <td class="px-3 py-2.5 text-gray-500 dark:text-gray-400 text-xs whitespace-nowrap">{{ $mp->feature ?? '—' }}</td>
                                        @foreach (config('permissions.keys') as $perm)
                                        <td class="px-2 py-2.5 text-center">
                                            @php
                                                $p = $mp->permissions[$perm] ?? ['effective' => false, 'source' => 'None', 'user_override' => null, 'role' => null];
                                                $allowed = $p['effective'];
                                                $source = $p['source'];
                                            @endphp
                                            @if ($allowed)
                                                <span class="text-green-600 dark:text-green-400 font-bold text-sm">&#10003;</span>
                                            @else
                                                <span class="text-red-400 dark:text-red-300 font-bold text-sm">&#10005;</span>
                                            @endif
                                            <span class="block text-[9px] leading-tight mt-0.5
                                                @if ($source === 'Role')
                                                    text-blue-600 dark:text-blue-400
                                                @elseif ($source === 'User Override' && $allowed)
                                                    text-purple-600 dark:text-purple-400
                                                @elseif ($source === 'User Override' && !$allowed)
                                                    text-purple-600 dark:text-purple-400
                                                @elseif ($inspectedIsSuperAdmin)
                                                    text-green-600 dark:text-green-400
                                                @else
                                                    text-gray-400 dark:text-gray-500
                                                @endif
                                            ">
                                                @if ($inspectedIsSuperAdmin)
                                                    Super Admin
                                                @elseif ($source === 'Role')
                                                    Role
                                                @elseif ($source === 'User Override' && $allowed)
                                                    Override Allow
                                                @elseif ($source === 'User Override' && !$allowed)
                                                    Override Deny
                                                @else
                                                    None
                                                @endif
                                            </span>
                                        </td>
                                        @endforeach
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-gray-400 dark:text-gray-500 text-center py-8">No modules found.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-md font-semibold">Offboarding Checklist</h3>
            @if($offboardingChecklist['suspended_at'])
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Suspended</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
            @endif
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400">Vault Entries</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $offboardingChecklist['vault_entries_count'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400">Assigned Tasks</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $offboardingChecklist['assigned_tasks_count'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400">Assigned Assets</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $offboardingChecklist['assigned_assets_count'] }}</p>
            </div>
            <div class="p-3 bg-gray-50 dark:bg-gray-800/50 rounded-lg">
                <p class="text-xs text-gray-500 dark:text-gray-400">Activities (30d)</p>
                <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $offboardingChecklist['activities_30d_count'] }}</p>
            </div>
        </div>
        <div class="mt-4 flex items-center gap-3">
            @if($offboardingChecklist['can_suspend'])
                <form action="{{ route('users.suspend', $user->id) }}" method="POST" class="flex items-center gap-2">
                    @csrf
                    <input type="text" name="reason" placeholder="Suspension reason..." class="px-3 py-1.5 text-xs border border-gray-300 dark:border-gray-600 rounded-lg input-focus outline-none bg-white dark:bg-black text-gray-900 dark:text-gray-100" required>
                    <x-button type="submit" variant="danger" size="sm" data-confirm="Suspend this user? They will lose access immediately.">Suspend</x-button>
                </form>
            @elseif($offboardingChecklist['can_unsuspend'])
                <form action="{{ route('users.unsuspend', $user->id) }}" method="POST" class="inline">
                    @csrf
                    @if($user->suspension_reason)
                        <span class="text-xs text-gray-500 dark:text-gray-400">Reason: {{ $user->suspension_reason }}</span>
                    @endif
                    <x-button type="submit" variant="success" size="sm" data-confirm="Unsuspend this user? They will regain access.">Unsuspend</x-button>
                </form>
            @endif
        </div>
    </div>

    <x-activity-timeline subjectType="App\Models\User" :subjectId="$user->id" />
</div>
@endsection
@extends('layouts.admin')

@section('title', 'My Permissions')

@section('content')
<div class="max-w-4xl mx-auto">
    <x-page-header title="My Permissions" subtitle="View your assigned roles and module permissions." />

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <h2 class="text-lg font-semibold mb-3">Roles</h2>
        <div class="flex flex-wrap gap-2">
            @forelse ($roles as $role)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ $role->name }}</span>
            @empty
                <span class="text-gray-400 dark:text-gray-500">No roles assigned.</span>
            @endforelse
        </div>
    </div>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <h2 class="text-lg font-semibold mb-3">Module Permissions</h2>

        @if ($isSuperAdmin)
            <div class="mb-4 p-3 rounded-xl bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-sm text-green-700 dark:text-green-300">
                You are a <strong>Super Admin</strong> — you have unrestricted access to all modules.
            </div>
        @endif

        @php
            $importableSlugs = config('permissions.importable_modules', []);
            $exportableSlugs = config('permissions.exportable_modules', []);
        @endphp

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                        <th class="text-left px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Module</th>
                        <th class="text-left px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Feature</th>
                        <th class="text-center px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Access</th>
                        <th class="text-center px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Manage</th>
                        <th class="text-center px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Import</th>
                        <th class="text-center px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Export</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($modules as $module)
                        @php
                            $perm = $modulePermissions[$module->id] ?? null;
                            $isImportable = in_array($module->slug, $importableSlugs, true);
                            $isExportable = in_array($module->slug, $exportableSlugs, true);
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-2 font-medium">{{ $module->name }}</td>
                            <td class="px-4 py-2 text-gray-500 dark:text-gray-400">{{ $module->feature->name ?? '—' }}</td>
                            <td class="px-4 py-2 text-center">{!! $isSuperAdmin || ($perm['can_read'] ?? false) ? '<span class="text-green-600 dark:text-green-400">✓</span>' : '<span class="text-red-400 dark:text-red-300">✗</span>' !!}</td>
                            <td class="px-4 py-2 text-center">{!! $isSuperAdmin || (($perm['can_create'] ?? false) && ($perm['can_update'] ?? false)) ? '<span class="text-green-600 dark:text-green-400">✓</span>' : '<span class="text-red-400 dark:text-red-300">✗</span>' !!}</td>
                            <td class="px-4 py-2 text-center">
                                @if ($isImportable)
                                    {!! $isSuperAdmin || ($perm['can_import'] ?? false) ? '<span class="text-green-600 dark:text-green-400">✓</span>' : '<span class="text-red-400 dark:text-red-300">✗</span>' !!}
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-2 text-center">
                                @if ($isExportable)
                                    {!! $isSuperAdmin || ($perm['can_export'] ?? false) ? '<span class="text-green-600 dark:text-green-400">✓</span>' : '<span class="text-red-400 dark:text-red-300">✗</span>' !!}
                                @else
                                    <span class="text-gray-300 dark:text-gray-600">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><x-empty-state tag="td" :colspan="6" icon="lock" title="No modules found." message="Assign modules to see permissions here." /></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
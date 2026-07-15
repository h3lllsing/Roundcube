@extends('layouts.admin')

@section('title', $template->name . ' - Role Template')

@section('content')
<div class="max-w-7xl mx-auto">
    @if(session('success'))
    <div class="mb-4 px-4 py-3 rounded-xl bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 text-sm">
        {{ session('success') }}
    </div>
    @endif

    <x-page-header :title="$template->name" subtitle="Role Template Details">
        <x-slot:actions>
            <x-button href="{{ route('role-templates.index') }}" variant="outline" size="sm">
                &larr; Back
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <div class="flex flex-wrap gap-4 items-start justify-between">
            <div class="space-y-1">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Slug:</span>
                    <code class="text-xs bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded">{{ $template->slug }}</code>
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Version:</span> {{ $template->version }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    <span class="font-medium text-gray-700 dark:text-gray-300">Modules:</span> {{ $template->module_count }}
                </p>
                @if($template->description)
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">{{ $template->description }}</p>
                @endif
            </div>
            <div class="flex gap-1.5">
                @if($template->is_protected)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    Protected
                </span>
                @endif
                @if($template->is_dangerous)
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                    Dangerous
                </span>
                @endif
            </div>
        </div>
    </div>

    @php
        $templateImportable = config('permissions.importable_modules', []);
        $templateExportable = config('permissions.exportable_modules', []);
        $permKeys = config('permissions.keys');
        $permsJson = $template->permissions_json ?? [];
    @endphp
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <h3 class="text-md font-semibold mb-1">Permission Matrix</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Read-only view of the permissions defined by this template.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                        <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Module</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Feature</th>
                        <th class="text-center px-3 py-3 font-medium text-gray-500 dark:text-gray-400">Access</th>
                        <th class="text-center px-3 py-3 font-medium text-gray-500 dark:text-gray-400">Manage</th>
                        <th class="text-center px-3 py-3 font-medium text-gray-500 dark:text-gray-400">Import</th>
                        <th class="text-center px-3 py-3 font-medium text-gray-500 dark:text-gray-400">Export</th>
                        <th class="text-center px-3 py-3 font-medium text-gray-500 dark:text-gray-400">Full Access</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($modules as $module)
                        @php
                            $mp = $permsJson[$module->slug] ?? [];
                            $hasAccess = !empty($mp['can_read']);
                            $canManage = !empty($mp['can_create']) && !empty($mp['can_update']);
                            $canImport = !empty($mp['can_import']);
                            $canExport = !empty($mp['can_export']);
                            $isImportable = in_array($module->slug, $templateImportable);
                            $isExportable = in_array($module->slug, $templateExportable);
                            $isFullAccess = $hasAccess && $canManage
                                && (!$isImportable || $canImport)
                                && (!$isExportable || $canExport);
                        @endphp
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-2.5 font-medium">{{ $module->name }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-500">{{ $module->feature->name ?? '—' }}</td>
                        <td class="px-3 py-2.5 text-center">
                            @if ($hasAccess)
                                <span class="text-green-600 font-bold text-lg leading-none">&#10003;</span>
                            @else
                                <span class="text-red-400 font-bold text-lg leading-none">&#10005;</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if ($canManage)
                                <span class="text-green-600 font-bold text-lg leading-none">&#10003;</span>
                            @else
                                <span class="text-red-400 font-bold text-lg leading-none">&#10005;</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if ($isImportable)
                                @if ($canImport)
                                    <span class="text-green-600 font-bold text-lg leading-none">&#10003;</span>
                                @else
                                    <span class="text-red-400 font-bold text-lg leading-none">&#10005;</span>
                                @endif
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if ($isExportable)
                                @if ($canExport)
                                    <span class="text-green-600 font-bold text-lg leading-none">&#10003;</span>
                                @else
                                    <span class="text-red-400 font-bold text-lg leading-none">&#10005;</span>
                                @endif
                            @else
                                <span class="text-gray-300 dark:text-gray-600">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2.5 text-center">
                            @if ($isFullAccess)
                                <span class="text-rose-600 font-bold text-lg leading-none">&#10003;</span>
                            @else
                                <span class="text-gray-400 dark:text-gray-500 font-bold text-lg leading-none">&#10005;</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">No module permissions defined for this template.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($allRoles->isNotEmpty())
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-md font-semibold mb-1">Apply Template to Role</h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">Select a role to apply this template. This will overwrite existing role permissions for modules defined in this template.</p>

        <form method="GET" action="{{ route('role-templates.apply', $template->id) }}" class="flex flex-wrap items-end gap-4">
            <div class="flex-1 min-w-[200px]">
                <label for="role_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Target Role</label>
                <select name="role_id" id="role_id" required
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none bg-white dark:bg-black">
                    <option value="">— Select a role —</option>
                    @foreach ($allRoles as $role)
                    <option value="{{ $role->id }}">{{ $role->name }} ({{ $role->slug }})</option>
                    @endforeach
                </select>
            </div>
            <x-button type="submit" variant="primary" size="sm">Preview &amp; Apply</x-button>
        </form>
    </div>
    @endif
</div>
@endsection

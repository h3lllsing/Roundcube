@extends('layouts.admin')

@section('title', $focusedRole ? "Permissions — {$focusedRole->name}" : 'Module Permissions')

@section('content')
<div class="max-w-7xl mx-auto">
    @php
        $moduleControls = [];
        $importableSlugs = config('permissions.importable_modules', []);
        $exportableSlugs = config('permissions.exportable_modules', []);
    @endphp
    @if ($focusedRole)
    <div class="mb-6">
        <a href="{{ route('roles.show', $focusedRole->id) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">&larr; Back to {{ $focusedRole->name }}</a>
    </div>

    @php
        $sensitiveSlugs = config('permissions.sensitive_modules', []);
        $sensitivePermKeys = config('permissions.sensitive_permissions', []);

        $accessibleCount = 0;
        $noAccessCount = 0;
        $sensitiveWithAccess = 0;

        foreach ($modules as $module) {
            $perm = $module->rolePermissions->firstWhere('role_id', $focusedRole->id);
            $isSensitive = in_array($module->slug, $sensitiveSlugs);
            $isImportable = in_array($module->slug, $importableSlugs);
            $isExportable = in_array($module->slug, $exportableSlugs);
            $activeSensitive = [];
            $hasAccess = false;
            $ctlAccess = false;
            $ctlManage = false;
            $ctlImport = false;
            $ctlExport = false;
            $ctlFullAccess = false;

            if ($perm) {
                $cr = (bool) $perm->can_read;
                $cc = (bool) $perm->can_create;
                $cu = (bool) $perm->can_update;
                $ci = (bool) $perm->can_import;
                $ce = (bool) $perm->can_export;

                $ctlAccess = $cr;
                $ctlManage = $cr && $cc && $cu;
                $ctlImport = $cr && $ci;
                $ctlExport = $cr && $ce;
                $ctlFullAccess = $ctlAccess && $ctlManage;
                if ($isImportable && !$ctlImport) { $ctlFullAccess = false; }
                if ($isExportable && !$ctlExport) { $ctlFullAccess = false; }

                $hasAccess = $ctlAccess;
                if ($hasAccess) { $accessibleCount++; } else { $noAccessCount++; }

                if ($isSensitive) {
                    foreach ($sensitivePermKeys as $spk) {
                        if ($perm->$spk) { $activeSensitive[] = $spk; }
                    }
                    if (!empty($activeSensitive)) { $sensitiveWithAccess++; }
                }
            } else {
                $noAccessCount++;
            }

            $moduleControls[$module->id] = [
                'hasAccess' => $hasAccess,
                'ctlAccess' => $ctlAccess,
                'ctlManage' => $ctlManage,
                'ctlImport' => $ctlImport,
                'ctlExport' => $ctlExport,
                'ctlFullAccess' => $ctlFullAccess,
                'isSensitive' => $isSensitive,
                'activeSensitive' => $activeSensitive,
                'isImportable' => $isImportable,
                'isExportable' => $isExportable,
            ];
        }
    @endphp

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Role: {{ $focusedRole->name }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Configure module access permissions for this role.</p>
            </div>
            <div class="text-right text-sm">
                <a href="{{ route('module-permissions.index') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">View all roles</a>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">With Access</span>
                <p class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $accessibleCount }}</p>
            </div>
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">No Access</span>
                <p class="text-lg font-semibold text-gray-400 dark:text-gray-500">{{ $noAccessCount }}</p>
            </div>
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Sensitive Granted</span>
                <p class="text-lg font-semibold {{ $sensitiveWithAccess > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-gray-500' }}">{{ $sensitiveWithAccess }}</p>
            </div>
        </div>
    </div>

    {{-- Mode Tabs --}}
    <div class="mb-6">
        <div class="flex items-center gap-2 border-b border-gray-200 dark:border-gray-700">
            <button id="simpleModeTab" class="mode-tab px-4 py-2 text-sm font-medium border-b-2 -mb-px cursor-pointer" onclick="setRolePermMode(true)">Simple</button>
            <button id="advancedModeTab" class="mode-tab px-4 py-2 text-sm font-medium border-b-2 -mb-px cursor-pointer" onclick="setRolePermMode(false)">Advanced</button>
        </div>
    </div>

    <div id="simpleMode" class="">
        <div class="mb-4 flex items-center gap-3">
            <input type="text" id="smSearch" placeholder="Search modules..." class="w-64 px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-black" oninput="filterSimpleModules(this.value)">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="checkbox" id="smFilterAccess" onchange="filterSimpleModules(document.getElementById('smSearch').value)" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                Show only with access
            </label>
        </div>

        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            @forelse ($modules as $module)
                @php $info = $moduleControls[$module->id]; @endphp
                <form method="POST" action="{{ route('module-permissions.update') }}" class="sm-row-form">
                    @csrf
                    <input type="hidden" name="updated_at" value="{{ $module->updated_at->format('Y-m-d H:i:s') }}">
                    <input type="hidden" name="module_id" value="{{ $module->id }}">
                    <input type="hidden" name="role_id" value="{{ $focusedRole->id }}">
                    <input type="hidden" name="access" value="{{ $info['ctlAccess'] ? '1' : '0' }}">
                    <input type="hidden" name="manage" value="{{ $info['ctlManage'] ? '1' : '0' }}">
                    <input type="hidden" name="import" value="{{ $info['ctlImport'] ? '1' : '0' }}">
                    <input type="hidden" name="export" value="{{ $info['ctlExport'] ? '1' : '0' }}">
                    <input type="hidden" name="full_access" value="{{ $info['ctlFullAccess'] ? '1' : '0' }}">

                    <div class="sm-row flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                        <div class="sm-info flex items-center gap-2 min-w-0">
                            <span class="sm-name text-sm font-medium text-gray-900 dark:text-gray-100">{{ $module->name }}</span>
                            <span class="sm-cat text-xs text-gray-400">{{ $module->feature->name ?? '—' }}</span>
                            @if ($info['isSensitive'])
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 font-medium">sensitive</span>
                            @endif
                        </div>
                        <div class="sm-setting flex-shrink-0 ml-4 flex items-center gap-1.5">
                            <button type="button" class="perm-chip {{ $info['ctlAccess'] ? 'perm-chip-active perm-chip-blue' : '' }}" data-control="access" onclick="toggleModuleControl(this.closest('form'), 'access')">Access</button>
                            <button type="button" class="perm-chip {{ $info['ctlManage'] ? 'perm-chip-active perm-chip-green' : '' }}" data-control="manage" onclick="toggleModuleControl(this.closest('form'), 'manage')">Manage</button>
                            <button type="button" class="perm-chip {{ $info['ctlImport'] ? 'perm-chip-active perm-chip-purple' : '' }} {{ !$info['isImportable'] ? 'perm-chip-disabled' : '' }}" data-control="import" onclick="if(this.classList.contains('perm-chip-disabled')) return; toggleModuleControl(this.closest('form'), 'import')">Import</button>
                            <button type="button" class="perm-chip {{ $info['ctlExport'] ? 'perm-chip-active perm-chip-orange' : '' }} {{ !$info['isExportable'] ? 'perm-chip-disabled' : '' }}" data-control="export" onclick="if(this.classList.contains('perm-chip-disabled')) return; toggleModuleControl(this.closest('form'), 'export')">Export</button>
                            <button type="button" class="perm-chip {{ $info['ctlFullAccess'] ? 'perm-chip-active perm-chip-rose' : '' }}" data-control="full_access" onclick="toggleModuleControl(this.closest('form'), 'full_access')">Full Access</button>
                        </div>
                    </div>
                </form>
            @empty
                <div class="p-6 text-center text-sm text-gray-400">No modules found. Add features and modules to manage permissions.</div>
            @endforelse
        </div>
    </div>

    <div id="advancedMode" class="">
        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                        <th scope="col" class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Feature</th>
                        <th scope="col" class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Module</th>
                        @foreach ($roles as $role)
                            @if (! in_array($role->slug, ['super-admin', '*']))
                                <th scope="col" class="text-center px-2 py-3 font-medium text-gray-500 dark:text-gray-400 text-xs">{{ $role->name }}</th>
                            @endif
                        @endforeach
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($modules as $module)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ $module->feature->name ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium">{{ $module->name }}</td>
                            @foreach ($roles as $role)
                                @if (! in_array($role->slug, ['super-admin', '*']))
                                    @php
                                        $perm = $module->rolePermissions->firstWhere('role_id', $role->id);
                                        $isImportable = in_array($module->slug, $importableSlugs);
                                        $isExportable = in_array($module->slug, $exportableSlugs);
                                        $ctlAccess = $perm && $perm->can_read;
                                        $ctlManage = $perm && $perm->can_read && $perm->can_create && $perm->can_update;
                                        $ctlImport = $perm && $perm->can_read && $perm->can_import;
                                        $ctlExport = $perm && $perm->can_read && $perm->can_export;
                                        $ctlFullAccess = $ctlAccess && $ctlManage;
                                        if ($isImportable && !$ctlImport) { $ctlFullAccess = false; }
                                        if ($isExportable && !$ctlExport) { $ctlFullAccess = false; }
                                    @endphp
                                    <td class="px-2 py-3 text-center">
                                        <a href="#"
                                           data-edit-perm="{{ $module->id }},{{ $role->id }}"
                                           data-updated-at="{{ $module->updated_at->format('Y-m-d H:i:s') }}"
                                           class="inline-flex items-center justify-center gap-0.5 text-xs {{ $perm ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}">
                                            @if ($perm)
                                                @php
                                                    $labels = [];
                                                    if ($ctlAccess) $labels[] = '<span class="px-1 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">A</span>';
                                                    if ($ctlManage) $labels[] = '<span class="px-1 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">M</span>';
                                                    if ($ctlImport) $labels[] = '<span class="px-1 rounded bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">I</span>';
                                                    if ($ctlExport) $labels[] = '<span class="px-1 rounded bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">E</span>';
                                                    if ($ctlFullAccess) $labels[] = '<span class="px-1 rounded bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">F</span>';
                                                @endphp
                                                {!! implode('', $labels) ?: '<span class="text-gray-400 dark:text-gray-500">—</span>' !!}
                                            @else
                                                —
                                            @endif
                                        </a>
                                    </td>
                                @endif
                            @endforeach
                        </tr>
                    @empty
                        <tr><x-empty-state tag="td" :colspan="2 + count($roles)" icon="lock" title="No modules found." message="Add features and modules to manage permissions." /></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div id="permModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
        <div class="bg-white dark:bg-black rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold mb-4">Edit Permissions</h3>
            <form method="POST" action="{{ route('module-permissions.update') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="updated_at" id="edit_updated_at">
                <input type="hidden" name="module_id" id="edit_module_id">
                <input type="hidden" name="role_id" id="edit_role_id">

                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" name="access" value="1" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 perm-modal-check" data-control="access">
                    <span class="font-medium text-blue-600 dark:text-blue-400">Access</span>
                    <span class="text-xs text-gray-400">View and see details</span>
                </label>
                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" name="manage" value="1" class="rounded border-gray-300 dark:border-gray-600 text-green-600 focus:ring-green-500 perm-modal-check" data-control="manage">
                    <span class="font-medium text-green-600 dark:text-green-400">Manage</span>
                    <span class="text-xs text-gray-400">Create and update</span>
                </label>
                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer" id="modalImportLabel">
                    <input type="checkbox" name="import" value="1" class="rounded border-gray-300 dark:border-gray-600 text-purple-600 focus:ring-purple-500 perm-modal-check" data-control="import">
                    <span class="font-medium text-purple-600 dark:text-purple-400">Import</span>
                    <span class="text-xs text-gray-400">Import data</span>
                </label>
                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer" id="modalExportLabel">
                    <input type="checkbox" name="export" value="1" class="rounded border-gray-300 dark:border-gray-600 text-orange-600 focus:ring-orange-500 perm-modal-check" data-control="export">
                    <span class="font-medium text-orange-600 dark:text-orange-400">Export</span>
                    <span class="text-xs text-gray-400">Export data</span>
                </label>
                <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                    <input type="checkbox" name="full_access" value="1" class="rounded border-gray-300 dark:border-gray-600 text-rose-600 focus:ring-rose-500 perm-modal-check" data-control="full_access">
                    <span class="font-medium text-rose-600 dark:text-rose-400">Full Access</span>
                    <span class="text-xs text-gray-400">All available controls</span>
                </label>

                <div class="flex items-center gap-3 pt-4">
                    <x-button type="submit" variant="primary" size="sm">Save</x-button>
                    <x-button type="button" variant="danger" size="sm" id="confirmRemoveBtn">Remove</x-button>
                    <x-button type="button" variant="outline" size="sm" id="closeEditorBtn">Cancel</x-button>
                </div>
            </form>

            <form id="removeForm" method="POST" action="{{ route('module-permissions.destroy') }}" class="hidden">
                @csrf
                @method('DELETE')
                <input type="hidden" name="module_id" id="remove_module_id">
                <input type="hidden" name="role_id" id="remove_role_id">
            </form>
        </div>
    </div>
    @else
    <x-page-header title="Module Permissions" subtitle="Configure module access permissions.">
        <x-slot:actions>
            <x-button href="{{ route('roles.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Manage Roles
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Feature</th>
                    <th scope="col" class="text-left px-4 py-3 font-medium text-gray-500 dark:text-gray-400">Module</th>
                    @foreach ($roles as $role)
                        @if (! in_array($role->slug, ['super-admin', '*']))
                            <th scope="col" class="text-center px-2 py-3 font-medium text-gray-500 dark:text-gray-400 text-xs">{{ $role->name }}</th>
                        @endif
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($modules as $module)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $module->feature->name ?? '—' }}</td>
                        <td class="px-4 py-3 font-medium">{{ $module->name }}</td>
                        @foreach ($roles as $role)
                            @if (! in_array($role->slug, ['super-admin', '*']))
                                @php
                                    $perm = $module->rolePermissions->firstWhere('role_id', $role->id);
                                    $isImportable = in_array($module->slug, $importableSlugs);
                                    $isExportable = in_array($module->slug, $exportableSlugs);
                                    $ctlAccess = $perm && $perm->can_read;
                                    $ctlManage = $perm && $perm->can_read && $perm->can_create && $perm->can_update;
                                    $ctlImport = $perm && $perm->can_read && $perm->can_import;
                                    $ctlExport = $perm && $perm->can_read && $perm->can_export;
                                    $ctlFullAccess = $ctlAccess && $ctlManage;
                                    if ($isImportable && !$ctlImport) { $ctlFullAccess = false; }
                                    if ($isExportable && !$ctlExport) { $ctlFullAccess = false; }
                                @endphp
                                <td class="px-2 py-3 text-center">
                                    <a href="#"
                                       data-edit-perm="{{ $module->id }},{{ $role->id }}"
                                       data-updated-at="{{ $module->updated_at->format('Y-m-d H:i:s') }}"
                                       class="inline-flex items-center justify-center gap-0.5 text-xs {{ $perm ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}">
                                        @if ($perm)
                                            @php
                                                $labels = [];
                                                if ($ctlAccess) $labels[] = '<span class="px-1 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300">A</span>';
                                                if ($ctlManage) $labels[] = '<span class="px-1 rounded bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">M</span>';
                                                if ($ctlImport) $labels[] = '<span class="px-1 rounded bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300">I</span>';
                                                if ($ctlExport) $labels[] = '<span class="px-1 rounded bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-300">E</span>';
                                                if ($ctlFullAccess) $labels[] = '<span class="px-1 rounded bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300">F</span>';
                                            @endphp
                                            {!! implode('', $labels) ?: '<span class="text-gray-400 dark:text-gray-500">—</span>' !!}
                                        @else
                                            —
                                        @endif
                                    </a>
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @empty
                    <tr><x-empty-state tag="td" :colspan="2 + count($roles)" icon="lock" title="No modules found." message="Add features and modules to manage permissions." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @endif

</div>

<style>
.perm-chip {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    border: 1px solid #d1d5db;
    background: transparent;
    color: #6b7280;
    cursor: pointer;
    transition: all 0.15s ease;
    line-height: 1.25rem;
}
.perm-chip:hover {
    border-color: #9ca3af;
    background: #f9fafb;
}
.perm-chip-active {
    border-color: transparent;
    color: #fff;
}
.perm-chip-active:hover {
    opacity: 0.85;
}
.perm-chip-blue { background: #3b82f6; border-color: #3b82f6; }
.perm-chip-green { background: #22c55e; border-color: #22c55e; }
.perm-chip-purple { background: #a855f7; border-color: #a855f7; }
.perm-chip-orange { background: #f97316; border-color: #f97316; }
.perm-chip-rose { background: #f43f5e; border-color: #f43f5e; }
.perm-chip-disabled {
    opacity: 0.35;
    cursor: not-allowed;
    pointer-events: none;
}
.dark .perm-chip {
    border-color: #4b5563;
    color: #9ca3af;
    background: transparent;
}
.dark .perm-chip:hover {
    background: #1f2937;
}
.dark .perm-chip-active {
    color: #fff;
}
.dark .perm-chip-active:hover {
    opacity: 0.8;
}
</style>

<script>
var roleModulePermData = @json($focusedRole ? $moduleControls : (object)[]);

function initRolePermMode() {
    var isSimple = localStorage.getItem('role_perm_mode') !== 'advanced';
    var simpleEl = document.getElementById('simpleMode');
    var advancedEl = document.getElementById('advancedMode');
    var simpleTab = document.getElementById('simpleModeTab');
    var advancedTab = document.getElementById('advancedModeTab');
    if (!simpleEl || !advancedEl) return;

    if (isSimple) {
        simpleEl.classList.remove('hidden');
        advancedEl.classList.add('hidden');
        if (simpleTab) { simpleTab.className = 'px-4 py-2 text-sm font-medium border-b-2 -mb-px cursor-pointer text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400'; }
        if (advancedTab) { advancedTab.className = 'px-4 py-2 text-sm font-medium border-b-2 -mb-px cursor-pointer text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-300'; }
    } else {
        simpleEl.classList.add('hidden');
        advancedEl.classList.remove('hidden');
        if (simpleTab) { simpleTab.className = 'px-4 py-2 text-sm font-medium border-b-2 -mb-px cursor-pointer text-gray-500 dark:text-gray-400 border-transparent hover:text-gray-700 dark:hover:text-gray-300'; }
        if (advancedTab) { advancedTab.className = 'px-4 py-2 text-sm font-medium border-b-2 -mb-px cursor-pointer text-indigo-600 dark:text-indigo-400 border-indigo-600 dark:border-indigo-400'; }
    }
}

function setRolePermMode(simple) {
    localStorage.setItem('role_perm_mode', simple ? 'simple' : 'advanced');
    initRolePermMode();
}

function filterSimpleModules(query) {
    var q = query.toLowerCase().trim();
    var filterAccess = document.getElementById('smFilterAccess')?.checked || false;
    var rows = document.querySelectorAll('#simpleMode .sm-row');
    rows.forEach(function(row) {
        var name = row.querySelector('.sm-name')?.textContent?.toLowerCase() || '';
        var chips = row.querySelectorAll('.perm-chip-active');
        var hasAccess = chips.length > 0;
        var matchSearch = !q || name.includes(q);
        var matchFilter = !filterAccess || hasAccess;
        row.style.display = (matchSearch && matchFilter) ? '' : 'none';
    });
}

function toggleModuleControl(formEl, controlName) {
    var hidden = formEl.querySelector('input[name="' + controlName + '"]');
    if (!hidden) return;
    hidden.value = hidden.value === '1' ? '0' : '1';
    enforceModuleDeps(formEl);
    formEl.submit();
}

function enforceModuleDeps(formEl) {
    var get = function(name) { var el = formEl.querySelector('input[name="' + name + '"]'); return el && el.value === '1'; };
    var set = function(name, val) { var el = formEl.querySelector('input[name="' + name + '"]'); if (el) el.value = val ? '1' : '0'; };

    var access = get('access');
    var manage = get('manage');
    var import_ = get('import');
    var export_ = get('export');
    var fullAccess = get('full_access');

    if (fullAccess) {
        set('access', true);
        set('manage', true);
        set('import', true);
        set('export', true);
        return;
    }

    if (!access) {
        set('manage', false);
        set('import', false);
        set('export', false);
        return;
    }

    var moduleId = formEl.querySelector('input[name="module_id"]')?.value;
    var info = roleModulePermData[moduleId];
    if (info) {
        if (!info.isImportable) set('import', false);
        if (!info.isExportable) set('export', false);
    }

    var shouldBeFull = access && manage;
    if (info) {
        if (info.isImportable && !get('import')) shouldBeFull = false;
        if (info.isExportable && !get('export')) shouldBeFull = false;
    }
    if (!shouldBeFull) set('full_access', false);
}

function openRoleCustomEditor(moduleId, roleId, selectEl) {
    var form = selectEl.closest('form');
    var updatedAt = form.querySelector('input[name="updated_at"]').value;
    var info = roleModulePermData[moduleId] || {};

    document.getElementById('edit_module_id').value = moduleId;
    document.getElementById('edit_role_id').value = roleId;
    document.getElementById('edit_updated_at').value = updatedAt;

    var controlKeys = ['access', 'manage', 'import', 'export', 'full_access'];
    controlKeys.forEach(function(key) {
        var checkbox = document.querySelector('#permModal input[name="' + key + '"]');
        if (checkbox) {
            checkbox.checked = !!info['ctl' + key.charAt(0).toUpperCase() + key.slice(1)];
        }
    });

    var isImportable = !!info.isImportable;
    var isExportable = !!info.isExportable;
    var importLabel = document.getElementById('modalImportLabel');
    var exportLabel = document.getElementById('modalExportLabel');
    if (importLabel) {
        importLabel.style.opacity = isImportable ? '1' : '0.35';
        importLabel.querySelector('input').disabled = !isImportable;
    }
    if (exportLabel) {
        exportLabel.style.opacity = isExportable ? '1' : '0.35';
        exportLabel.querySelector('input').disabled = !isExportable;
    }

    document.getElementById('permModal').classList.remove('hidden');
}

initRolePermMode();

document.querySelectorAll('[data-edit-perm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        var parts = this.dataset.editPerm.split(',');
        var moduleId = parseInt(parts[0]);
        var roleId = parseInt(parts[1]);
        var info = roleModulePermData[moduleId] || {};

        document.getElementById('edit_module_id').value = moduleId;
        document.getElementById('edit_role_id').value = roleId;
        document.getElementById('edit_updated_at').value = this.dataset.updatedAt;

        var controlKeys = ['access', 'manage', 'import', 'export', 'full_access'];
        controlKeys.forEach(function(key) {
            var checkbox = document.querySelector('#permModal input[name="' + key + '"]');
            if (checkbox) {
                checkbox.checked = !!info['ctl' + key.charAt(0).toUpperCase() + key.slice(1)];
            }
        });

        var isImportable = !!info.isImportable;
        var isExportable = !!info.isExportable;
        var importLabel = document.getElementById('modalImportLabel');
        var exportLabel = document.getElementById('modalExportLabel');
        if (importLabel) {
            importLabel.style.opacity = isImportable ? '1' : '0.35';
            importLabel.querySelector('input').disabled = !isImportable;
        }
        if (exportLabel) {
            exportLabel.style.opacity = isExportable ? '1' : '0.35';
            exportLabel.querySelector('input').disabled = !isExportable;
        }

        document.getElementById('permModal').classList.remove('hidden');
    });
});

document.getElementById('closeEditorBtn')?.addEventListener('click', function() {
    document.getElementById('permModal').classList.add('hidden');
});

document.getElementById('confirmRemoveBtn')?.addEventListener('click', function() {
    if (confirm('Remove all permissions for this role on this module?')) {
        document.getElementById('remove_module_id').value = document.getElementById('edit_module_id').value;
        document.getElementById('remove_role_id').value = document.getElementById('edit_role_id').value;
        document.getElementById('removeForm').submit();
    }
});

document.querySelectorAll('.perm-modal-check').forEach(function(cb) {
    cb.addEventListener('change', function() {
        var form = this.closest('form');
        var get = function(name) { var el = form.querySelector('input[name="' + name + '"]'); return el && el.checked; };
        var set = function(name, val) { var el = form.querySelector('input[name="' + name + '"]'); if (el) el.checked = val; };

        var access = get('access');
        var manage = get('manage');
        var import_ = get('import');
        var export_ = get('export');
        var fullAccess = get('full_access');

        if (fullAccess) {
            set('access', true);
            set('manage', true);
            set('import', true);
            set('export', true);
            return;
        }

        if (!access) {
            set('manage', false);
            set('import', false);
            set('export', false);
            set('full_access', false);
            return;
        }

        var moduleId = form.querySelector('input[name="module_id"]')?.value;
        var info = roleModulePermData[moduleId];
        if (info) {
            if (!info.isImportable) set('import', false);
            if (!info.isExportable) set('export', false);
        }

        var shouldBeFull = access && manage;
        if (info) {
            if (info.isImportable && !get('import')) shouldBeFull = false;
            if (info.isExportable && !get('export')) shouldBeFull = false;
        }
        if (!shouldBeFull) set('full_access', false);
    });
});
</script>
@endsection
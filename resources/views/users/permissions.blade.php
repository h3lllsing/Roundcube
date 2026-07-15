@php
    $roleName = $user->roles->first()?->name ?? 'No Role';
    $stateLabels = ['inherit' => 'Inherit', 'allow' => 'Allow', 'deny' => 'Deny'];
    $capabilityTip = 'This module does not support this capability.';
@endphp

@extends('layouts.admin')

@section('title', 'Edit Permissions — ' . $user->name)

@section('content')
<div
    class="max-w-7xl mx-auto"
    x-data="userPerms({{ Js::from(['moduleList' => $moduleList, 'userName' => $user->name, 'roleName' => $roleName, 'userId' => $user->id, 'saveUrl' => route('users.permissions.update', $user->id), 'backUrl' => route('users.show', $user->id)]) }})"
>
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
            <li class="flex items-center gap-1.5">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Dashboard</a>
            </li>
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('users.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Users</a>
            </li>
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('users.show', $user->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $user->name }}</a>
            </li>
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 dark:text-gray-100 font-medium" aria-current="page">Edit Permissions</span>
            </li>
        </ol>
    </nav>

    <div class="hd">
        <h1 class="pt">Edit Permissions</h1>
        <p class="ps">
            {{ $user->name }} <span class="text-slate-500 font-normal">— {{ $roleName }}</span>
            &nbsp;<span class="text-slate-400">·</span>&nbsp;
            <span class="text-sm text-slate-500">{{ $user->email }}</span>
        </p>
    </div>

    <form method="POST" action="{{ route('users.permissions.update', $user->id) }}" id="perms-form">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="ch">
                <h2>Module Permissions</h2>
                <span class="text-xs text-slate-400">Baseline: <span class="font-medium text-slate-600">{{ $roleName }}</span></span>
            </div>
            <div class="cb">
                @foreach ($categories as $cat)
                    <details class="group border border-gray-200 dark:border-gray-700 rounded-xl mb-4 overflow-hidden" open>
                        <summary class="flex items-center gap-2 px-5 py-3 cursor-pointer list-none text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors bg-gray-50 dark:bg-gray-800/30">
                            <svg class="w-4 h-4 text-gray-500 group-open:rotate-90 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            {{ $cat }}
                        </summary>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                            @foreach (collect($moduleList)->where('category', $cat) as $mod)
                                <div class="px-5 py-4 hover:bg-gray-50 dark:hover:bg-gray-800/30 transition-colors">
                                    <div class="flex flex-wrap items-start justify-between gap-3">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex items-center gap-2 flex-wrap">
                                                <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $mod['name'] }}</span>
                                                <span class="text-xs text-gray-400">({{ $mod['slug'] }})</span>
                                            </div>
                                            <div class="flex flex-wrap gap-1.5 mt-1.5">
                                                @php
                                                    $effLabels = [];
                                                    if ($mod['effectivePerms']['can_read'] ?? false) $effLabels[] = 'Read';
                                                    if ($mod['effectivePerms']['can_create'] ?? false) $effLabels[] = 'Create';
                                                    if ($mod['effectivePerms']['can_update'] ?? false) $effLabels[] = 'Update';
                                                    if ($mod['effectivePerms']['can_export'] ?? false) $effLabels[] = 'Export';
                                                    if ($mod['effectivePerms']['can_reveal'] ?? false) $effLabels[] = 'Reveal';
                                                    if ($mod['effectivePerms']['can_import'] ?? false) $effLabels[] = 'Import';
                                                @endphp
                                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                                    Effective:
                                                    @if (empty($effLabels))
                                                        <span class="text-gray-400 italic">No access</span>
                                                    @else
                                                        <span class="font-medium text-gray-600 dark:text-gray-300">{{ implode(', ', $effLabels) }}</span>
                                                    @endif
                                                </span>
                                            </div>
                                        </div>
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <input type="hidden" name="controls[{{ $mod['id'] }}][full_access]" value="0">
                                            <input type="hidden" name="controls[{{ $mod['id'] }}][inherit_all]" value="0">
                                            <input type="hidden" name="controls[{{ $mod['id'] }}][_access_unchanged]" value="1">
                                            <input type="hidden" name="controls[{{ $mod['id'] }}][_manage_unchanged]" value="1">

                                            {{-- Access --}}
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs font-medium text-gray-500 w-12">Access</span>
                                                <select name="controls[{{ $mod['id'] }}][access]"
                                                    class="text-xs border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1.5 bg-white dark:bg-black text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500"
                                                    @change="markUnsaved(); controlChanged({{ $mod['id'] }}, 'access')">
                                                    <option value="inherit" {{ $mod['controls']['access'] === 'inherit' ? 'selected' : '' }}>Inherit</option>
                                                    <option value="allow" {{ $mod['controls']['access'] === 'allow' ? 'selected' : '' }}>Allow</option>
                                                    <option value="deny" {{ $mod['controls']['access'] === 'deny' ? 'selected' : '' }}>Deny</option>
                                                </select>
                                            </div>

                                            {{-- Manage --}}
                                            <div class="flex items-center gap-1">
                                                <span class="text-xs font-medium text-gray-500 w-12">Manage</span>
                                                <select name="controls[{{ $mod['id'] }}][manage]"
                                                    class="text-xs border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1.5 bg-white dark:bg-black text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500"
                                                    @change="markUnsaved(); controlChanged({{ $mod['id'] }}, 'manage')">
                                                    <option value="inherit" {{ $mod['controls']['manage'] === 'inherit' ? 'selected' : '' }}>Inherit</option>
                                                    <option value="allow" {{ $mod['controls']['manage'] === 'allow' ? 'selected' : '' }}>Allow</option>
                                                    <option value="deny" {{ $mod['controls']['manage'] === 'deny' ? 'selected' : '' }}>Deny</option>
                                                </select>
                                            </div>

                                            {{-- Import --}}
                                            @if ($mod['isImportable'])
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs font-medium text-gray-500 w-12">Import</span>
                                                    <select name="controls[{{ $mod['id'] }}][import]"
                                                        class="text-xs border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1.5 bg-white dark:bg-black text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500"
                                                        @change="markUnsaved()">
                                                        <option value="inherit" {{ $mod['controls']['import'] === 'inherit' ? 'selected' : '' }}>Inherit</option>
                                                        <option value="allow" {{ $mod['controls']['import'] === 'allow' ? 'selected' : '' }}>Allow</option>
                                                        <option value="deny" {{ $mod['controls']['import'] === 'deny' ? 'selected' : '' }}>Deny</option>
                                                    </select>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400 italic px-2 py-1.5">Import unavailable</span>
                                            @endif

                                            {{-- Export --}}
                                            @if ($mod['isExportable'])
                                                <div class="flex items-center gap-1">
                                                    <span class="text-xs font-medium text-gray-500 w-12">Export</span>
                                                    <select name="controls[{{ $mod['id'] }}][export]"
                                                        class="text-xs border border-gray-200 dark:border-gray-600 rounded-lg px-2 py-1.5 bg-white dark:bg-black text-gray-900 dark:text-gray-100 focus:ring-1 focus:ring-indigo-500"
                                                        @change="markUnsaved()">
                                                        <option value="inherit" {{ $mod['controls']['export'] === 'inherit' ? 'selected' : '' }}>Inherit</option>
                                                        <option value="allow" {{ $mod['controls']['export'] === 'allow' ? 'selected' : '' }}>Allow</option>
                                                        <option value="deny" {{ $mod['controls']['export'] === 'deny' ? 'selected' : '' }}>Deny</option>
                                                    </select>
                                                </div>
                                            @else
                                                <span class="text-xs text-gray-400 italic px-2 py-1.5">Export unavailable</span>
                                            @endif

                                            {{-- Full Access --}}
                                            @php
                                                $isFa = $mod['controls']['access'] === 'allow' && $mod['controls']['manage'] === 'allow'
                                                    && (!$mod['isImportable'] || $mod['controls']['import'] === 'allow')
                                                    && (!$mod['isExportable'] || $mod['controls']['export'] === 'allow');
                                            @endphp
                                            <label class="flex items-center gap-1 text-xs text-gray-500 cursor-pointer px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700">
                                                <input type="checkbox"
                                                    {{ $isFa ? 'checked' : '' }}
                                                    @change="toggleFullAccess($event, {{ $mod['id'] }}, {{ $mod['isImportable'] ? 'true' : 'false' }}, {{ $mod['isExportable'] ? 'true' : 'false' }})">
                                                Full
                                            </label>

                                            {{-- Inherit All --}}
                                            <button type="button"
                                                class="text-xs text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 px-2 py-1 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                                                @click="inheritAll({{ $mod['id'] }})"
                                                title="Set all controls to Inherit">
                                                ↺ All
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </details>
                @endforeach

                <div class="sticky bottom-0 bg-white dark:bg-black border-t border-gray-200 dark:border-gray-700 px-5 py-4 flex items-center justify-between rounded-b-xl">
                    <div class="flex items-center gap-3">
                        <a href="{{ $backUrl ?? route('users.show', $user->id) }}" class="text-sm text-gray-500 hover:text-gray-700 dark:hover:text-gray-300">← Back to User</a>
                        <span x-show="hasUnsaved" class="text-xs text-amber-600 flex items-center gap-1" x-cloak>
                            <span class="w-2 h-2 bg-amber-500 rounded-full inline-block animate-pulse"></span>
                            Unsaved changes
                        </span>
                    </div>
                    <button type="submit" class="btn btn-p" id="save-btn">Save Overrides</button>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function userPerms(init) {
    return {
        hasUnsaved: false,
        markUnsaved() {
            this.hasUnsaved = true;
        },
        controlChanged(moduleId, control) {
            const form = document.getElementById('perms-form');
            const hidden = form.querySelector(`input[name="controls[${moduleId}][_${control}_unchanged]"]`);
            if (hidden) hidden.value = '0';
        },
        toggleFullAccess(event, moduleId, isImportable, isExportable) {
            this.markUnsaved();
            const form = document.getElementById('perms-form');
            this.controlChanged(moduleId, 'access');
            this.controlChanged(moduleId, 'manage');
            if (event.target.checked) {
                const accessSel = form.querySelector(`select[name="controls[${moduleId}][access]"]`);
                const manageSel = form.querySelector(`select[name="controls[${moduleId}][manage]"]`);
                if (accessSel) accessSel.value = 'allow';
                if (manageSel) manageSel.value = 'allow';
                if (isImportable) {
                    const importSel = form.querySelector(`select[name="controls[${moduleId}][import]"]`);
                    if (importSel) importSel.value = 'allow';
                }
                if (isExportable) {
                    const exportSel = form.querySelector(`select[name="controls[${moduleId}][export]"]`);
                    if (exportSel) exportSel.value = 'allow';
                }
            }
        },
        inheritAll(moduleId) {
            this.markUnsaved();
            const form = document.getElementById('perms-form');
            this.controlChanged(moduleId, 'access');
            this.controlChanged(moduleId, 'manage');
            const selects = form.querySelectorAll(`select[name^="controls[${moduleId}]"]`);
            selects.forEach(sel => { sel.value = 'inherit'; });
        }
    };
}
</script>
@endpush

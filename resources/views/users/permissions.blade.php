@php
    $sensitiveSlugs = config('permissions.sensitive_modules', []);
    $permKeys = config('permissions.keys');
    $toggleNames = ['view', 'create', 'edit', 'delete', 'approve', 'export', 'reveal', 'import'];
    $toggleMap = array_combine($permKeys, $toggleNames);
    $toggleToColumn = array_combine($toggleNames, $permKeys);

    $moduleList = [];
    foreach ($modules as $module) {
        $effective = $user->getEffectiveModulePermissions($module);

        $rolePerms = [];
        $effectivePerms = [];
        $overridePerms = [];
        foreach ($permKeys as $key) {
            $roleVal = $effective[$key]['role'] ?? false;
            $overrideVal = $effective[$key]['user_override'] ?? null;
            $effVal = $effective[$key]['effective'] ?? false;

            $rolePerms[$key] = $roleVal;
            $effectivePerms[$key] = $effVal;
            if ($overrideVal !== null) {
                $overridePerms[$key] = $overrideVal;
            }
        }

        $baselinePreset = (function ($p) {
            if (!$p['can_read'] && !$p['can_create'] && !$p['can_update'] && !$p['can_delete'] && !$p['can_approve'] && !$p['can_export'] && !$p['can_reveal'] && !$p['can_import']) return 0;
            if ($p['can_read'] && !$p['can_create'] && !$p['can_update'] && !$p['can_delete'] && !$p['can_approve'] && !$p['can_export'] && !$p['can_reveal'] && !$p['can_import']) return 1;
            if ($p['can_read'] && $p['can_create'] && $p['can_update'] && !$p['can_delete'] && !$p['can_approve'] && !$p['can_reveal'] && !$p['can_import']) return 2;
            return 3;
        })($rolePerms);

        $currentPreset = (function ($p) {
            if (!$p['can_read'] && !$p['can_create'] && !$p['can_update'] && !$p['can_delete'] && !$p['can_approve'] && !$p['can_export'] && !$p['can_reveal'] && !$p['can_import']) return 0;
            if ($p['can_read'] && !$p['can_create'] && !$p['can_update'] && !$p['can_delete'] && !$p['can_approve'] && !$p['can_export'] && !$p['can_reveal'] && !$p['can_import']) return 1;
            if ($p['can_read'] && $p['can_create'] && $p['can_update'] && !$p['can_delete'] && !$p['can_approve'] && !$p['can_reveal'] && !$p['can_import']) return 2;
            return 3;
        })($effectivePerms);

        $isSensitive = in_array($module->slug, $sensitiveSlugs);
        $module->is_sensitive = $isSensitive;
        if ($isSensitive) {
            $module->sensitive_tip = 'This module handles sensitive data. Elevated permissions require confirmation.';
        }

        $toggles = [];
        foreach ($permKeys as $key) {
            $toggles[$toggleMap[$key]] = $effectivePerms[$key];
        }

        $moduleList[] = [
            'id' => $module->id,
            'name' => $module->name,
            'category' => $module->feature->name ?? 'Uncategorized',
            'preset' => $currentPreset,
            'baseline' => $baselinePreset,
            'isSensitive' => $isSensitive,
            'hasOverride' => !empty($overridePerms),
            'toggles' => $toggles,
            'module' => $module,
            'overrides' => $overridePerms,
        ];
    }

    $roleName = $user->roles->first()?->name ?? 'No Role';

    $initModules = [];
    foreach ($moduleList as $m) {
        $initModules[$m['id']] = [
            'id' => $m['id'],
            'name' => $m['name'],
            'category' => $m['category'],
            'preset' => $m['preset'],
            'baseline' => $m['baseline'],
            'isSensitive' => $m['isSensitive'],
            'hasOverride' => $m['hasOverride'],
            'toggles' => $m['toggles'],
        ];
    }

    $initData = [
        'modules' => $initModules,
        'categories' => $categories,
        'userName' => $user->name,
        'userRole' => $roleName,
        'userId' => $user->id,
        'saveUrl' => route('users.permissions.update', $user->id),
        'backUrl' => route('users.show', $user->id),
        'sensitivePermNames' => config('permissions.sensitive_permissions', ['can_delete', 'can_reveal', 'can_approve', 'can_import']),
        'toggleToColumn' => $toggleToColumn,
    ];
@endphp

@extends('layouts.admin')

@section('title', 'Edit Permissions — ' . $user->name)

@section('content')
<div
    class="max-w-4xl mx-auto"
    x-data="editPerms({{ Js::from($initData) }})"
    @keydown.escape.window="if(openEditor) closeEditor()"
>
    {{-- Breadcrumb --}}
    <nav aria-label="Breadcrumb" class="mb-4">
        <ol class="flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400">
            <li class="flex items-center gap-1.5">
                <a href="{{ route('dashboard') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Dashboard</a>
            </li>
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('users.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Users</a>
            </li>
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <a href="{{ route('users.show', $user->id) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">{{ $user->name }}</a>
            </li>
            <li class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-gray-300 dark:text-gray-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                <span class="text-gray-900 dark:text-gray-100 font-medium" aria-current="page">Edit Permissions</span>
            </li>
        </ol>
    </nav>

    {{-- Page header --}}
    <div class="hd">
        <h1 class="pt">Edit Permissions</h1>
        <p class="ps">
            {{ $user->name }} <span class="text-slate-500 font-normal">— {{ $roleName }}</span>
            &nbsp;<span class="text-slate-400">·</span>&nbsp;
            <span class="text-sm text-slate-500">{{ $user->email }}</span>
            &nbsp;<span class="text-slate-400">·</span>&nbsp;
            <span class="text-sm text-slate-500">Override role defaults for specific modules.</span>
        </p>
    </div>

    {{-- Unsaved changes warning bar --}}
    <x-permissions.unsaved-bar />

    {{-- Role changed warning --}}
    <x-permissions.role-warning />

    {{-- Mode Toggle Tabs --}}
    <div class="mode-tabs" role="tablist">
        <button
            class="mode-tab"
            :class="{ active: isSimple }"
            role="tab"
            :aria-selected="isSimple"
            @click="toggleSimpleMode(true)"
        >Simple</button>
        <button
            class="mode-tab"
            :class="{ active: !isSimple }"
            role="tab"
            :aria-selected="!isSimple"
            @click="toggleSimpleMode(false)"
        >Advanced</button>
    </div>

    {{-- Main card --}}
    <div class="card">
        <div class="ch">
            <h2>Module Permissions</h2>
            <div class="flex gap-3 items-center">
                <span class="text-xs text-slate-400">
                    Baseline: <span class="font-medium text-slate-600">{{ $roleName }}</span>
                </span>
            </div>
        </div>
        <div class="cb">

            {{-- ═══ SIMPLE MODE ═══ --}}
            <div x-show="isSimple" x-cloak>
                <div class="sm-stats">
                    <span class="sm-stat" x-show="overridesCount > 0">
                        <strong x-text="overridesCount"></strong> module<span x-text="overridesCount !== 1 ? 's' : ''"></span> with custom permissions
                    </span>
                    <span class="sm-stat" x-show="overridesCount === 0">
                        No custom permissions — <strong>all inherited</strong> from role
                    </span>
                    <span class="sm-stat text-xs text-slate-400">Baseline: {{ $roleName }}</span>
                </div>

                <div class="sm-controls">
                    <label class="sm-toggle">
                        <input type="checkbox" x-model="filterOverrideOnly" @change="searchQuery = ''">
                        <span>Show only overridden modules</span>
                    </label>
                    <div class="sw sm-search">
                        <i class="ic" aria-hidden="true">🔍</i>
                        <input
                            class="si"
                            type="text"
                            placeholder="Search modules..."
                            x-model="searchQuery"
                            @input="filterOverrideOnly = false"
                            aria-label="Search modules"
                        >
                    </div>
                </div>

                {{-- Inline editor (shared with Advanced mode) --}}
                <x-permissions.inline-editor />

                {{-- Zero-overrides empty state --}}
                <div x-show="overridesCount === 0 && !searchQuery" class="sm-empty" x-cloak>
                    <div class="sm-empty-icon">⚙</div>
                    <h3 class="sm-empty-title">No custom permissions yet</h3>
                    <p class="sm-empty-desc">
                        This user inherits all module permissions from their role (<strong>{{ $roleName }}</strong>).
                        Override specific modules to grant or restrict access.
                    </p>
                    <button class="btn btn-p" @click="filterOverrideOnly = false; $nextTick(() => { searchQuery = ''; })">Browse All Modules</button>
                </div>

                {{-- Simple mode module rows --}}
                <div x-show="!(overridesCount === 0 && !searchQuery)" x-cloak>
                    <template x-for="mod in simpleModuleList" :key="mod.id">
                        <div class="sm-row" :class="{ 'sm-overridden': mod.preset !== mod.baseline }">
                            <div class="sm-info">
                                <span class="sm-name" x-text="mod.name"></span>
                                <span x-show="mod.isSensitive" class="sen-tag" data-tip="Contains sensitive permissions">sensitive</span>
                                <span class="sm-baseline">
                                    Role: <span x-text="presetLabels[mod.baseline] || 'No Access'"></span>
                                </span>
                            </div>
                            <div class="sm-setting">
                                <select
                                    class="sm-select"
                                    :class="{ 'sm-inherit': mod.preset === mod.baseline, 'sm-override': mod.preset !== mod.baseline && mod.preset !== 3, 'sm-custom-sel': mod.preset === 3 }"
                                    x-on:change="
                                        const v = parseInt($event.target.value);
                                        if (v === -1) { modules[mod.id].preset = modules[mod.id].baseline; markUnsaved(); }
                                        else { modules[mod.id].preset = v; markUnsaved(); }
                                        if (v === 3) { $dispatch('open-editor', {id: mod.id}); }
                                    "
                                >
                                    <option value="-1" x-bind:selected="mod.preset === mod.baseline">Inherit from Role</option>
                                    <option value="0" x-bind:selected="mod.preset === 0">No Access</option>
                                    <option value="1" x-bind:selected="mod.preset === 1">View Only</option>
                                    <option value="2" x-bind:selected="mod.preset === 2">Manage</option>
                                    <option value="3" x-bind:selected="mod.preset === 3">Custom…</option>
                                </select>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Unsaved warning --}}
                <div class="unsaved-bar" :class="{ show: hasUnsavedChanges }">
                    <span class="dot-pulse"></span>
                    <span>You have unsaved permission changes.</span>
                </div>
            </div>

            {{-- ═══ ADVANCED MODE ═══ --}}
            <div x-show="!isSimple" x-cloak>
                {{-- Stats bar --}}
                <x-permissions.stats-bar />

                {{-- Sensitive criteria --}}
                <x-permissions.sensitive-criteria />

                {{-- Search + Filters --}}
                <div class="filters" role="tablist" aria-label="Permission filters">
                    <x-permissions.filter-chip filter="all" label="All" />
                    <x-permissions.filter-chip filter="modified" label="Modified" />
                    <x-permissions.filter-chip filter="sensitive" label="Sensitive" />
                    <x-permissions.filter-chip filter="2" label="Manage" />
                    <x-permissions.filter-chip filter="3" label="Custom" />
                    <x-permissions.filter-chip filter="inherited" label="From Role" />
                    <div class="sw">
                        <i class="ic" aria-hidden="true">🔍</i>
                        <input
                            class="si"
                            type="text"
                            placeholder="Search modules..."
                            x-model="searchQuery"
                            aria-label="Search modules"
                        >
                    </div>
                </div>

                {{-- Inline editor (shared) --}}
                <x-permissions.inline-editor />

                {{-- Category accordions --}}
                @foreach ($categories as $cat)
                    <x-permissions.category-accordion name="{{ $cat }}">
                        @foreach (collect($moduleList)->where('category', $cat) as $mod)
                            <x-permissions.module-row
                                :module="$mod['module']"
                                :baseline="$mod['baseline']"
                                :overrides="$mod['overrides']"
                            />
                        @endforeach
                    </x-permissions.category-accordion>
                @endforeach
            </div>

        </div>

        {{-- Summary collapsible --}}
        <x-permissions.summary-collapsible />

        {{-- Diff panel --}}
        <x-permissions.diff-panel />

        {{-- Footer actions --}}
        <div class="fa">
            <button class="btn btn-s" @click="if(hasUnsavedChanges) showModal('nav-modal'); else goBack()">← Back to User</button>
            <button class="btn btn-p" @click="save()">Save Overrides</button>
        </div>
    </div>

    {{-- Modals --}}
    {{-- Role change modal --}}
    <x-permissions.modal
        id="role-modal"
        icon="🔄"
        icon-bg="#dbeafe"
        title="Role Changed"
        description="Role changed from IT Support to Administrator."
    >
        <p class="text-sm text-slate-600 mb-2">How should existing permission overrides be handled?</p>
        <div class="opt-group">
            <label class="opt">
                <input type="radio" name="role-action" value="reset" checked>
                <div>
                    <strong class="text-sm">Reset to new role defaults</strong>
                    <div class="desc">Discard all existing overrides. User permissions will match the new role exactly.</div>
                </div>
            </label>
            <label class="opt">
                <input type="radio" name="role-action" value="keep">
                <div>
                    <strong class="text-sm">Keep existing overrides</strong>
                    <div class="desc">Preserve all current overrides. Review and adjust manually if needed.</div>
                </div>
            </label>
        </div>
        <x-slot name="footer">
            <button class="btn btn-s" @click="closeModal('role-modal')">Cancel</button>
            <button class="btn btn-p" @click="closeModal('role-modal');markUnsaved()">Confirm Role Change</button>
        </x-slot>
    </x-permissions.modal>

    {{-- Reset all overrides modal --}}
    <x-permissions.modal
        id="reset-all-modal"
        icon="↺"
        icon-bg="#fee2e2"
        title="Reset All Overrides"
        description="This will reset all module overrides to the role defaults."
    >
        <p class="text-sm text-slate-600 mb-1">
            The following <strong x-text="overriddenModules.length"></strong> modules will be affected:
        </p>
        <ul class="bg-slate-50 rounded-lg p-3 list-none mt-2.5">
            <template x-for="mod in overriddenModules" :key="mod.id">
                <li class="text-sm text-slate-600 py-1">
                    • <span x-text="mod.name"></span>
                    <span class="text-slate-400">(<span x-text="mod.currentLabel"></span> → <span x-text="mod.baselineLabel"></span>)</span>
                </li>
            </template>
        </ul>
        <p class="text-sm text-amber-800 mt-3 flex gap-1.5">⚠ This action can be undone by re-applying overrides before saving.</p>
        <x-slot name="footer">
            <button class="btn btn-s" @click="closeModal('reset-all-modal')">Cancel</button>
            <button class="btn btn-d" @click="closeModal('reset-all-modal');markUnsaved()">Reset All Overrides</button>
        </x-slot>
    </x-permissions.modal>

    {{-- Sensitive confirmation modal --}}
    <x-permissions.modal
        id="sen-modal"
        icon="⚠"
        icon-bg="#fef3c7"
        title="Sensitive Permissions Detected"
    >
        <p class="text-sm text-slate-600 mb-3">
            <span x-text="sensitiveChanges.length"></span> modules with elevated permissions will be saved:
        </p>
        <table class="w-full border-collapse text-sm bg-slate-50 rounded-lg overflow-hidden">
            <template x-for="change in sensitiveChanges" :key="change.id">
                <tr>
                    <td class="p-2 border-b border-slate-200 font-medium" x-text="change.name"></td>
                    <td class="p-2 border-b border-slate-200" x-text="change.sensitivePerms.join(', ')"></td>
                    <td class="p-2 border-b border-slate-200 text-amber-800">⚠ Elevated permission</td>
                </tr>
            </template>
        </table>
        <p class="text-sm text-slate-500 mt-3">Review carefully. These actions cannot be undone. This confirmation covers all changes.</p>
        <x-slot name="footer">
            <span class="text-xs text-slate-500 mr-auto self-center">Super-admin bypasses this check</span>
            <button class="btn btn-s" @click="closeModal('sen-modal')">Cancel</button>
            <button class="btn btn-d" @click="closeModal('sen-modal');markUnsaved()">I understand, enable</button>
        </x-slot>
    </x-permissions.modal>

    {{-- Bulk apply preview modal --}}
    <x-permissions.modal
        id="bulk-modal"
        icon="⚡"
        icon-bg="#dbeafe"
        title="Bulk Apply"
        description="Set all modules in this category"
    >
        <p class="text-sm text-slate-600 mb-2.5">
            The following <strong x-text="bulkAffectedModules.length"></strong> modules will be affected:
        </p>
        <table class="w-full border-collapse text-sm">
            <template x-for="mod in bulkAffectedModules" :key="mod.id">
                <tr>
                    <td class="py-1" x-text="mod.name"></td>
                    <td class="text-right">
                        <span class="p-std" :class="'p-' + mod.currentClass" x-text="mod.currentLabel"></span>
                        <span class="text-slate-400 mx-2">→</span>
                        <span class="p-std" :class="'p-' + mod.targetClass" x-text="mod.targetLabel"></span>
                        <span x-show="mod.currentLabel === mod.targetLabel" class="text-slate-400 text-[11px] ml-1">(unchanged)</span>
                    </td>
                </tr>
            </template>
        </table>
        <x-slot name="footer">
            <button class="btn btn-s" @click="closeModal('bulk-modal')">Cancel</button>
            <button class="btn btn-p" @click="bulkApply()">Apply to <span x-text="bulkAffectedModules.length"></span> modules</button>
        </x-slot>
    </x-permissions.modal>

    {{-- Editor reset confirmation modal --}}
    <x-permissions.modal
        id="reset-editor-modal"
        icon="↺"
        icon-bg="#fef3c7"
        title="Reset to Role Default"
        description="This will discard all custom overrides for this module."
    >
        <p class="text-sm text-slate-600">The module permissions will be restored to match the role baseline. This can be undone by re-applying overrides before saving.</p>
        <x-slot name="footer">
            <button class="btn btn-s" @click="closeModal('reset-editor-modal')">Cancel</button>
            <button class="btn btn-d" @click="resetModuleToBaseline(openEditor.id);closeModal('reset-editor-modal')">Reset to Default</button>
        </x-slot>
    </x-permissions.modal>

    {{-- Unsaved navigation modal --}}
    <x-permissions.modal
        id="nav-modal"
        icon="⚠"
        icon-bg="#fee2e2"
        title="Unsaved Changes"
        description="You have unsaved permission changes that will be lost."
    >
        <p class="text-sm text-slate-600">Review your changes before leaving, or discard them and continue.</p>
        <x-slot name="footer">
            <button class="btn btn-s" @click="discardAndLeave()">Discard &amp; Leave</button>
            <button class="btn btn-p" @click="closeModal('nav-modal');showModal('diff-panel')">Review Changes</button>
        </x-slot>
    </x-permissions.modal>

</div>
@endsection

@push('styles')
    @vite('resources/css/permissions.css')
@endpush

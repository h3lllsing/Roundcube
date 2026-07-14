@extends('layouts.admin')

@section('title', $focusedRole ? "Permissions — {$focusedRole->name}" : 'Module Permissions')

@section('content')
<div class="max-w-7xl mx-auto">
    @php $modulePresets = []; @endphp
    @if ($focusedRole)
    <div class="mb-6">
        <a href="{{ route('roles.show', $focusedRole->id) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">&larr; Back to {{ $focusedRole->name }}</a>
    </div>

    @php
        $sensitiveSlugs = config('permissions.sensitive_modules', []);
        $sensitivePermKeys = config('permissions.sensitive_permissions', []);
        $permKeys = config('permissions.keys', []);

        $accessibleCount = 0;
        $noAccessCount = 0;
        $customCount = 0;
        $sensitiveWithAccess = 0;

        foreach ($modules as $module) {
            $perm = $module->rolePermissions->firstWhere('role_id', $focusedRole->id);
            $preset = 0;
            $isSensitive = in_array($module->slug, $sensitiveSlugs);
            $activeSensitive = [];

            if ($perm) {
                $allFalse = true;
                foreach ($permKeys as $pk) { if ($perm->$pk) { $allFalse = false; break; } }

                if ($allFalse) {
                    $preset = 0;
                } elseif (
                    $perm->can_read && !$perm->can_create && !$perm->can_update
                    && !$perm->can_delete && !$perm->can_approve
                    && !$perm->can_export && !$perm->can_reveal && !$perm->can_import
                ) {
                    $preset = 1;
                } elseif (
                    $perm->can_read && $perm->can_create && $perm->can_update
                    && !$perm->can_delete && !$perm->can_approve
                    && !$perm->can_export && !$perm->can_reveal && !$perm->can_import
                ) {
                    $preset = 2;
                } else {
                    $preset = 3;
                }

                if ($perm->can_read) { $accessibleCount++; } else { $noAccessCount++; }

                if ($preset === 3) { $customCount++; }

                if ($isSensitive) {
                    foreach ($sensitivePermKeys as $spk) {
                        if ($perm->$spk) { $activeSensitive[] = $spk; }
                    }
                    if (!empty($activeSensitive)) { $sensitiveWithAccess++; }
                }
            } else {
                $noAccessCount++;
            }

            $modulePresets[$module->id] = [
                'preset' => $preset,
                'isSensitive' => $isSensitive,
                'activeSensitive' => $activeSensitive,
                'can_create' => $perm && $perm->can_create,
                'can_read' => $perm && $perm->can_read,
                'can_update' => $perm && $perm->can_update,
                'can_delete' => $perm && $perm->can_delete,
                'can_approve' => $perm && $perm->can_approve,
                'can_export' => $perm && $perm->can_export,
                'can_reveal' => $perm && $perm->can_reveal,
                'can_import' => $perm && $perm->can_import,
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
        <div class="grid grid-cols-4 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">With Access</span>
                <p class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $accessibleCount }}</p>
            </div>
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">No Access</span>
                <p class="text-lg font-semibold text-gray-400 dark:text-gray-500">{{ $noAccessCount }}</p>
            </div>
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Custom</span>
                <p class="text-lg font-semibold text-purple-600 dark:text-purple-400">{{ $customCount }}</p>
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
                @php $info = $modulePresets[$module->id]; @endphp
                <form method="POST" action="{{ route('module-permissions.update') }}" class="sm-row-form">
                    @csrf
                    <input type="hidden" name="updated_at" value="{{ $module->updated_at->format('Y-m-d H:i:s') }}">
                    <input type="hidden" name="module_id" value="{{ $module->id }}">
                    <input type="hidden" name="role_id" value="{{ $focusedRole->id }}">
                    <input type="hidden" name="can_create" value="0">
                    <input type="hidden" name="can_read" value="0">
                    <input type="hidden" name="can_update" value="0">
                    <input type="hidden" name="can_delete" value="0">
                    <input type="hidden" name="can_approve" value="0">
                    <input type="hidden" name="can_export" value="0">
                    <input type="hidden" name="can_reveal" value="0">
                    <input type="hidden" name="can_import" value="0">

                    <div class="sm-row flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                        <div class="sm-info flex items-center gap-2 min-w-0">
                            <span class="sm-name text-sm font-medium text-gray-900 dark:text-gray-100">{{ $module->name }}</span>
                            <span class="sm-cat text-xs text-gray-400">{{ $module->feature->name ?? '—' }}</span>
                            @if ($info['isSensitive'])
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 font-medium">sensitive</span>
                            @endif
                            @if ($info['preset'] === 1)
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-400 font-medium">View</span>
                            @elseif ($info['preset'] === 2)
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 font-medium">Manage</span>
                            @elseif ($info['preset'] === 3)
                            <span class="text-[10px] px-1.5 py-0.5 rounded bg-purple-50 dark:bg-purple-900/20 text-purple-700 dark:text-purple-400 font-medium">Custom</span>
                            @endif
                        </div>
                        <div class="sm-setting flex-shrink-0 ml-4">
                            <select class="sm-select text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 bg-white dark:bg-black cursor-pointer" onchange="onRolePresetChange(this)" data-module-id="{{ $module->id }}" data-role-id="{{ $focusedRole->id }}">
                                <option value="0" {{ $info['preset'] == 0 ? 'selected' : '' }}>No Access</option>
                                <option value="1" {{ $info['preset'] == 1 ? 'selected' : '' }}>View Only</option>
                                <option value="2" {{ $info['preset'] == 2 ? 'selected' : '' }}>Manage</option>
                                <option value="3" {{ $info['preset'] == 3 ? 'selected' : '' }}>Custom…</option>
                            </select>
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
                                    @endphp
                                    <td class="px-2 py-3 text-center">
                                        <a href="#"
                                           data-edit-perm="{{ $module->id }},{{ $role->id }}"
                                           data-updated-at="{{ $module->updated_at->format('Y-m-d H:i:s') }}"
                                           class="inline-flex items-center gap-1 text-xs {{ $perm ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}">
                                            @if ($perm)
                                                {{ ($perm->can_create ? 'C' : '') . ($perm->can_read ? 'R' : '') . ($perm->can_update ? 'U' : '') . ($perm->can_delete ? 'D' : '') . ($perm->can_approve ? 'A' : '') . ($perm->can_export ? 'E' : '') . ($perm->can_reveal ? 'Rev' : '') . ($perm->can_import ? 'I' : '') }}
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

    {{-- Edit Modal (shared between Simple Custom and Advanced) --}}
    <div id="permModal" class="fixed inset-0 z-50 hidden bg-black/50 flex items-center justify-center">
        <div class="bg-white dark:bg-black rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-semibold mb-4">Edit Permissions</h3>
            <form method="POST" action="{{ route('module-permissions.update') }}" class="space-y-3">
                @csrf
                <input type="hidden" name="updated_at" id="edit_updated_at">
                <input type="hidden" name="module_id" id="edit_module_id">
                <input type="hidden" name="role_id" id="edit_role_id">

                @foreach (['create', 'read', 'update', 'delete', 'approve', 'export', 'reveal', 'import'] as $perm)
                    <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="can_{{ $perm }}" value="1"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        Can {{ ucfirst($perm) }}
                    </label>
                @endforeach

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
                                @endphp
                                <td class="px-2 py-3 text-center">
                                    <a href="#"
                                       data-edit-perm="{{ $module->id }},{{ $role->id }}"
                                       data-updated-at="{{ $module->updated_at->format('Y-m-d H:i:s') }}"
                                       class="inline-flex items-center gap-1 text-xs {{ $perm ? 'text-green-600 dark:text-green-400' : 'text-gray-400 dark:text-gray-500' }}">
                                        @if ($perm)
                                            {{ ($perm->can_create ? 'C' : '') . ($perm->can_read ? 'R' : '') . ($perm->can_update ? 'U' : '') . ($perm->can_delete ? 'D' : '') . ($perm->can_approve ? 'A' : '') . ($perm->can_export ? 'E' : '') . ($perm->can_reveal ? 'Rev' : '') . ($perm->can_import ? 'I' : '') }}
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

<script>
var roleModulePermData = @json($focusedRole ? $modulePresets : (object)[]);

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
        var hasAccess = row.querySelector('.sm-select')?.value !== '0';
        var matchSearch = !q || name.includes(q);
        var matchFilter = !filterAccess || hasAccess;
        row.style.display = (matchSearch && matchFilter) ? '' : 'none';
    });
}

function onRolePresetChange(select) {
    var form = select.closest('form');
    var preset = parseInt(select.value);
    var moduleId = parseInt(select.dataset.moduleId);
    var roleId = parseInt(select.dataset.roleId);

    form.querySelectorAll('input[type="hidden"][name^="can_"]').forEach(function(input) {
        input.value = '0';
    });

    if (preset === 0) {
        form.submit();
    } else if (preset === 1) {
        form.querySelector('input[name="can_read"]').value = '1';
        form.submit();
    } else if (preset === 2) {
        form.querySelector('input[name="can_read"]').value = '1';
        form.querySelector('input[name="can_create"]').value = '1';
        form.querySelector('input[name="can_update"]').value = '1';
        form.submit();
    } else if (preset === 3) {
        openRoleCustomEditor(moduleId, roleId, select);
    }
}

function openRoleCustomEditor(moduleId, roleId, selectEl) {
    var form = selectEl.closest('form');
    var updatedAt = form.querySelector('input[name="updated_at"]').value;
    var perms = roleModulePermData[moduleId] || {};

    document.getElementById('edit_module_id').value = moduleId;
    document.getElementById('edit_role_id').value = roleId;
    document.getElementById('edit_updated_at').value = updatedAt;

    var permKeys = ['create', 'read', 'update', 'delete', 'approve', 'export', 'reveal', 'import'];
    permKeys.forEach(function(key) {
        var col = 'can_' + key;
        var checkbox = document.querySelector('#permModal input[name="' + col + '"]');
        if (checkbox) {
            checkbox.checked = !!perms[col];
        }
    });

    document.getElementById('permModal').classList.remove('hidden');
}

initRolePermMode();

document.querySelectorAll('[data-edit-perm]').forEach(function(el) {
    el.addEventListener('click', function(e) {
        e.preventDefault();
        var parts = this.dataset.editPerm.split(',');
        var moduleId = parseInt(parts[0]);
        var roleId = parseInt(parts[1]);
        document.getElementById('edit_module_id').value = moduleId;
        document.getElementById('edit_role_id').value = roleId;
        document.getElementById('edit_updated_at').value = this.dataset.updatedAt;
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
</script>
@endsection

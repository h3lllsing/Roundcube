@extends('layouts.admin')

@section('title', $focusedRole ? "Permissions — {$focusedRole->name}" : 'Module Permissions')

@section('content')
<div class="max-w-7xl mx-auto">
    @if ($focusedRole)
    <div class="mb-6">
        <a href="{{ route('roles.show', $focusedRole->id) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">&larr; Back to {{ $focusedRole->name }}</a>
    </div>
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
        @php
            $accessibleCount = 0;
            $noAccessCount = 0;
        @endphp
        @foreach ($modules as $module)
            @php
                $perm = $module->rolePermissions->firstWhere('role_id', $focusedRole->id);
                if ($perm && $perm->can_read) { $accessibleCount++; } else { $noAccessCount++; }
            @endphp
        @endforeach
        <div class="grid grid-cols-3 gap-4 mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Modules with Access</span>
                <p class="text-lg font-semibold text-green-600 dark:text-green-400">{{ $accessibleCount }}</p>
            </div>
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">No Access</span>
                <p class="text-lg font-semibold text-gray-400 dark:text-gray-500">{{ $noAccessCount }}</p>
            </div>
            <div>
                <span class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Modules</span>
                <p class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $modules->count() }}</p>
            </div>
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
    @endif

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

    {{-- Edit Modal --}}
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
</div>

<script>
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

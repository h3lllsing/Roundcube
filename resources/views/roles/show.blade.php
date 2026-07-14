@extends('layouts.admin')

@section('title', $role->name)
@section('breadcrumbTitle', $role->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="{{ $role->name }}" back-url="{{ route('roles.index') }}" />
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $role->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Slug</label>
                <p class="mt-1 text-sm"><code class="bg-gray-100 dark:bg-black px-1.5 py-0.5 rounded">{{ $role->slug }}</code></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Privileges</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $role->privileges->count() }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Users</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $role->users->count() }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $role->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Updated</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $role->updated_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
            @if (!in_array($role->slug, ['super-admin']))
            <x-button href="{{ route('module-permissions.index', ['role_id' => $role->id]) }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Configure Permissions
            </x-button>
            @endif
            <x-button href="{{ route('roles.edit', $role->id) }}" variant="outline" size="sm">Edit</x-button>
            @if (!in_array($role->slug, ['admin', 'super-admin']))
            <form action="{{ route('roles.destroy', $role->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            @endif
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-md font-semibold mb-4">Module Access Summary</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Total Modules</label>
                <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $moduleAccess['total_modules'] }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">With Access</label>
                <p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">{{ $moduleAccess['accessible_modules'] }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">No Access</label>
                <p class="mt-1 text-2xl font-semibold text-gray-400 dark:text-gray-500">{{ $moduleAccess['no_access_modules'] }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Sensitive Granted</label>
                <p class="mt-1 text-2xl font-semibold {{ $moduleAccess['sensitive_count'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-400 dark:text-gray-500' }}">{{ $moduleAccess['sensitive_count'] }}</p>
            </div>
        </div>
        @if ($moduleAccess['sensitive_count'] > 0)
        <div class="mt-3 text-xs text-gray-500 dark:text-gray-400">
            <span class="font-medium">Sensitive permissions granted:</span>
            @foreach ($moduleAccess['sensitive_granted'] as $sg)
                <span class="inline-block bg-amber-50 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 px-2 py-0.5 rounded-full ml-1">{{ $sg['module'] }} — {{ $sg['permission'] }}</span>
            @endforeach
        </div>
        @endif
        @if (!in_array($role->slug, ['super-admin']))
        <div class="mt-3">
            <a href="{{ route('module-permissions.index', ['role_id' => $role->id]) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">View and edit module permissions →</a>
        </div>
        @endif
    </div>

    <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-md font-semibold mb-4">Privileges ({{ $role->privileges->count() }})</h3>

        @if ($role->privileges->isNotEmpty())
        <table class="w-full text-sm mb-4">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700">
                    <th scope="col" class="text-left px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th scope="col" class="text-left px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Slug</th>
                    <th scope="col" class="text-left px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($role->privileges as $privilege)
                <tr>
                    <td class="px-4 py-2">{{ $privilege->name }}</td>
                    <td class="px-4 py-2"><code class="text-xs bg-gray-100 dark:bg-black px-1.5 py-0.5 rounded">{{ $privilege->slug }}</code></td>
                    <td class="px-4 py-2">
                        <form method="POST" action="{{ route('roles.privileges.detach', $role->id) }}" class="inline">
                            @csrf
                            <input type="hidden" name="privilege_id" value="{{ $privilege->id }}">
                            <button type="submit" data-confirm="Detach this privilege?" data-confirm-button="Detach" class="text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300 text-xs">Detach</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <p class="text-sm text-gray-400 dark:text-gray-500 mb-4">No privileges attached to this role.</p>
        @endif

        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Attach Privilege</h4>
        <form method="POST" action="{{ route('roles.privileges.attach', $role->id) }}" class="flex items-center gap-2">
            @csrf
            <select name="privilege_id" class="border border-gray-300 dark:border-gray-600 rounded-xl text-sm px-3 py-2 bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
                <option value="">Select privilege</option>
                @foreach ($allPrivileges as $privilege)
                    <option value="{{ $privilege->id }}" {{ $role->privileges->contains($privilege->id) ? 'disabled' : '' }}>
                        {{ $privilege->name }} ({{ $privilege->slug }})
                    </option>
                @endforeach
            </select>
            <x-button type="submit" variant="success" size="sm">Attach</x-button>
        </form>
    </div>

    <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-md font-semibold mb-4">Users with this role ({{ $role->users->count() }})</h3>
        @if ($role->users->isNotEmpty())
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($role->users as $user)
            <li class="py-2 flex items-center justify-between">
                <span>
                    <a href="{{ route('users.show', $user->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $user->name }}</a>
                    <span class="text-gray-400 dark:text-gray-500 text-xs ml-2">{{ $user->email }}</span>
                </span>
            </li>
            @endforeach
        </ul>
        @else
        <p class="text-sm text-gray-400 dark:text-gray-500">No users assigned to this role.</p>
        @endif
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Users')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Users" subtitle="Manage system users.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super-admin'))
            <x-button href="{{ route('export', 'users') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            <x-button href="{{ route('users.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" placeholder="Search users..." />
        <x-filter-select name="role" placeholder="All roles" :options="['super-admin' => 'Super Admin', 'admin' => 'Admin', 'user' => 'User']" />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'suspended' => 'Suspended']" />
        <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'role', 'status', 'date_from', 'date_to']))
            <x-button href="{{ route('users.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="users">
        <x-bulk-actions type="users" colspan="8" :statuses="[]" :actions="['suspend', 'unsuspend', 'delete', 'restore', 'force-delete']" :actionLabels="['suspend' => 'Suspend', 'unsuspend' => 'Unsuspend', 'delete' => 'Delete', 'restore' => 'Restore', 'force-delete' => 'Force Delete']" />
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Email</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Roles</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Last Login</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($users as $user)
                    <tr class="{{ $user->suspended_at ? 'bg-red-50 dark:bg-red-900/10 hover:bg-red-100 dark:hover:bg-red-900/20' : 'hover:bg-gray-50 dark:hover:bg-gray-700/50' }}">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $user->id }}" aria-label="Select {{ $user->name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 font-medium"><a href="{{ route('users.show', $user->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $user->name }}</a></td>
                        <td class="px-6 py-3 text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-3">
                            <div class="flex flex-wrap gap-1">
                                @forelse ($user->roles as $role)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ $role->name }}</span>
                                @empty
                                    <span class="text-gray-400 dark:text-gray-500">—</span>
                                @endforelse
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            @if ($user->suspended_at)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">Suspended</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">Active</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500">
                            @if ($user->last_login_at)
                                <span title="{{ $user->last_login_at }}">{{ \Carbon\Carbon::parse($user->last_login_at)->diffForHumans() }}</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400">Never</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('users.edit', $user->id) }}" color="amber" icon="edit" label="Edit" />
                            <div x-data="{ open: false, style: '' }" @click.away="open = false" class="relative inline-block">
                                <button type="button" @click="
                                    open = !open;
                                    if (open) {
                                        $nextTick(() => {
                                            const r = $el.getBoundingClientRect();
                                            style = 'position:fixed;left:' + r.left + 'px;top:' + (r.bottom + 4) + 'px;z-index:50';
                                        });
                                    }
                                " @keydown.escape.prevent="open = false" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl text-xs font-medium transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-gray-600 dark:text-white bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50" aria-haspopup="true" :aria-expanded="open.toString()" aria-label="More actions" title="More actions">
                                    <span>More</span>
                                    <svg class="w-3 h-3" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                                </button>
                                <div x-show="open" :style="style" x-cloak role="menu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-gray-50 dark:bg-black rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-48">
                                    <a href="{{ route('users.permissions.edit', $user->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">Permissions</a>
                                    <a href="{{ route('users.clone', $user->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">Clone</a>
                                    @if ($user->suspended_at)
                                        <form method="POST" action="{{ route('users.unsuspend', $user->id) }}" class="block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" data-confirm="Unsuspend this user?" data-confirm-button="Unsuspend" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-green-600 dark:text-green-400 hover:bg-green-50 dark:hover:bg-green-900/20 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-green-500/40" role="menuitem">Unsuspend</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('users.suspend', $user->id) }}" class="block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" data-confirm="Suspend this user?" data-confirm-button="Suspend" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-orange-600 dark:text-orange-400 hover:bg-orange-50 dark:hover:bg-orange-900/20 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-orange-500/40" role="menuitem">Suspend</button>
                                        </form>
                                    @endif
                                    <form method="POST" action="{{ route('users.destroy', $user->id) }}" class="block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-confirm="Are you sure?" data-confirm-button="Delete" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500/40" role="menuitem">Delete</button>
                                    </form>
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="7" icon="user" title="No users found." message="Invite users to get started." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection

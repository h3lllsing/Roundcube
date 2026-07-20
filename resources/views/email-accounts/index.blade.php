@extends('layouts.admin')

@section('title', 'Email Accounts')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Email Accounts" subtitle="Manage email accounts.">
        <x-slot:actions>
            <x-button href="{{ route('email_accounts.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('email_accounts.index') }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('trashed') ? 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' : 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' }}">
            Active
        </a>
        <a href="{{ route('email_accounts.index', ['trashed' => 1]) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium {{ request('trashed') ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-800' }}">
            Trash
        </a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        @if(request('trashed'))
            <input type="hidden" name="trashed" value="1">
        @endif
        <x-filter-input name="search" placeholder="Search email..." />
        <x-filter-select name="domain_id" placeholder="All domains" :options="$domains->pluck('name', 'id')->toArray()" />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'suspended' => 'Suspended']" />
        <x-button type="submit" variant="primary" size="sm" x-on:click="startLoading($el)">Filter</x-button>
        @if(request()->anyFilled(['search', 'domain_id', 'status']))
            <x-button href="{{ request('trashed') ? route('email_accounts.index', ['trashed' => 1]) : route('email_accounts.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Email</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Domain</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Sync</th>
                    @if(request('trashed'))
                        <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Deleted At</th>
                        <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Deleted By</th>
                    @endif
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($accounts as $account)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 font-medium">
                            <a href="{{ route('email_accounts.show', $account) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $account->email }}</a>
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $account->domain->name ?? 'N/A' }}</td>
                        <td class="px-6 py-3">
                            @if ($account->status?->value === 'active')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300">Active</span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300">Suspended</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-xs text-gray-500">
                            @if($account->last_sync_at && $account->last_sync_at->isToday())
                                <span class="text-green-600 dark:text-green-400">Today {{ $account->last_sync_at->format('g:i A') }}</span>
                            @elseif($account->last_sync_at)
                                {{ $account->last_sync_at->diffForHumans() }}
                            @else
                                <span class="text-gray-400">Never</span>
                            @endif
                        </td>
                        @if(request('trashed'))
                            <td class="px-6 py-3 text-gray-500">{{ $account->deleted_at?->diffForHumans() }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $account->deleter?->email ?? 'N/A' }}</td>
                        @endif
                        <td class="px-6 py-3 whitespace-nowrap">
                            @if(request('trashed'))
                                <form method="POST" action="{{ route('email-accounts.restore', $account->id) }}" class="inline">
                                    @csrf
                                    <button type="submit" data-confirm="Restore this email account?" data-confirm-button="Restore" x-on:click="startLoading($el)" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-900/20 hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors">Restore</button>
                                </form>
                                <form method="POST" action="{{ route('email-accounts.force-delete', $account->id) }}" class="inline ml-1">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" data-confirm="Permanently delete this email account? This cannot be undone." data-confirm-button="Delete Permanently" x-on:click="startLoading($el)" class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 transition-colors">Delete Permanently</button>
                                </form>
                            @else
                                <div x-data="{ open: false, style: '' }" @click.away="open = false" class="relative inline-block">
                                    <button type="button" @click="
                                        open = !open;
                                        if (open) {
                                            $nextTick(() => {
                                                const r = $el.getBoundingClientRect();
                                                style = 'position:fixed;left:' + r.left + 'px;top:' + (r.bottom + 4) + 'px;z-index:50';
                                            });
                                        }
                                    " @keydown.escape.prevent="open = false" class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50" aria-haspopup="true" :aria-expanded="open.toString()" aria-label="Account actions" title="Account actions">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                    </button>
                                    <div x-show="open" :style="style" x-cloak role="menu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-gray-50 dark:bg-black rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-48">
                                        <a href="{{ route('email_accounts.edit', $account) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors" role="menuitem">Edit</a>
                                        <form method="POST" action="{{ route('email_accounts.destroy', $account) }}" class="block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" data-confirm="Soft-delete this email account?" data-confirm-button="Delete" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors" role="menuitem">Delete</button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ request('trashed') ? 7 : 5 }}">
                            <x-empty-state icon="mail" title="{{ request('trashed') ? 'No trashed email accounts.' : 'No email accounts found.' }}" message="{{ request('trashed') ? '' : 'Create your first email account to get started.' }}" />
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $accounts->links() }}</div>
</div>
@endsection

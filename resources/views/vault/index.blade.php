@extends('layouts.admin')

@section('title', 'Vault')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Vault" subtitle="Store and manage sensitive credentials.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'vault') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('vault.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vault..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->filled('search'))
            <x-button href="{{ route('vault.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="vault">
        <x-bulk-actions type="vault" colspan="5" :statuses="[]" :actions="$bulkActions" />
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Service Name</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Module</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">URL</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Username</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($entries as $entry)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $entry->id }}" aria-label="Select {{ $entry->service_name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 font-medium"><a href="{{ route('vault.show', $entry->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $entry->service_name }}</a></td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ $entry->module->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400 max-w-xs truncate">
                            @if($entry->service_url)
                                <a href="{{ $entry->service_url }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline truncate block" title="{{ $entry->service_url }}">{{ $entry->service_url }}</a>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400">
                            @if($entry->username)
                                <div class="flex items-center gap-2">
                                    <span>{{ $entry->username }}</span>
                                    <x-copy-button :text="$entry->username" title="Copy username" />
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            @php
                                $_canEdit = auth()->user()->hasRole('super-admin') || ($entry->module && auth()->user()->canOnModule($entry->module, 'update'));
                                $_canDelete = auth()->user()->hasRole('super-admin') || ($entry->module && auth()->user()->canOnModule($entry->module, 'delete'));
                                $_canReveal = auth()->user()->canRevealCredentialsFor($entry->module);
                                $_hasPassword = (bool)$entry->encrypted_password;
                                $_hasServiceUrl = (bool)$entry->service_url;
                                $_hasOperationalShortcuts = $_hasServiceUrl;
                            @endphp
                            <div x-data="{ open: false, style: '' }" @click.away="open = false" class="relative inline-block">
                                <button type="button" @click="
                                    open = !open;
                                    if (open) { $nextTick(() => { const r = $el.getBoundingClientRect(); style = 'position:fixed;left:' + r.left + 'px;top:' + (r.bottom + 4) + 'px;z-index:50'; }); }
                                " @keydown.escape.prevent="open = false" class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50" aria-haspopup="true" :aria-expanded="open.toString()" aria-label="Vault actions" title="Vault actions">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                                <div x-show="open" :style="style" x-cloak role="menu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-gray-50 dark:bg-black rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-48">
                                    <a href="{{ route('vault.show', $entry->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">View Details</a>

                                    @if($_hasOperationalShortcuts || ($_canReveal && $_hasPassword))
                                    <div class="border-t border-gray-100 dark:border-gray-700/50 my-1"></div>
                                    @endif

                                    @if($_hasServiceUrl)
                                    <a href="{{ $entry->service_url }}" target="_blank" rel="noopener noreferrer" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">Open Website</a>
                                    @endif

                                    @if($_canReveal && $_hasPassword)
                                    <div class="flex items-center px-3 py-1.5 gap-2" role="menuitem">
                                        <span class="flex-1 min-w-0 text-sm text-gray-700 dark:text-white truncate" title="Password">Password</span>
                                        <x-copy-button :passwordRoute="route('vault.password', $entry->id)" class="shrink-0 w-6 h-6 !p-0 inline-flex items-center justify-center dark:text-gray-300" title="Copy Password" />
                                    </div>
                                    @endif

                                    @if($_canEdit)
                                    <div class="border-t border-gray-100 dark:border-gray-700/50 my-1"></div>
                                    <a href="{{ route('vault.edit', $entry->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">Edit</a>
                                    @endif
                                    @if($_canDelete)
                                    <form method="POST" action="{{ route('vault.destroy', $entry->id) }}" class="block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-confirm="Are you sure?" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500/40" role="menuitem">Delete</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="5" icon="lock" title="No vault entries found." message="Store sensitive information securely." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-4">{{ $entries->links() }}</div>
</div>
@endsection

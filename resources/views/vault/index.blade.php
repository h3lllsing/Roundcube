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
        <x-bulk-actions type="vault" colspan="7" :statuses="[]" :actions="$bulkActions" />
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
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Created</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($entries as $entry)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $entry->id }}" aria-label="Select {{ $entry->service_name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 font-medium">{{ $entry->service_name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $entry->module->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500 max-w-xs truncate">
                            @if($entry->service_url)
                                <div class="flex items-center gap-2">
                                    <a href="{{ $entry->service_url }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline truncate">{{ $entry->service_url }}</a>
                                    <x-copy-button :text="$entry->service_url" title="Copy URL" />
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500">
                            @if($entry->username)
                                <div class="flex items-center gap-2">
                                    <span>{{ $entry->username }}</span>
                                    <x-copy-button :text="$entry->username" title="Copy username" />
                                </div>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $entry->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('vault.show', $entry->id) }}" color="indigo" icon="view" label="View" />
                            <x-permission-check :module="$entry->module" action="update">
                            <x-action href="{{ route('vault.edit', $entry->id) }}" color="amber" icon="edit" label="Edit" />
                            </x-permission-check>
                            <x-permission-check :module="$entry->module" action="delete">
                            <x-action action="{{ route('vault.destroy', $entry->id) }}" color="red" icon="delete" label="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="7" icon="lock" title="No vault entries found." message="Store sensitive information securely." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-4">{{ $entries->links() }}</div>
</div>
@endsection

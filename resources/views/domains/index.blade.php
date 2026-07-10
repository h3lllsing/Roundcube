@extends('layouts.admin')

@section('title', 'Domains')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Domains" subtitle="Track registered domains.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'domains') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('domains.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search domains..." />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status']))
            <x-button href="{{ route('domains.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="domains">
        <x-bulk-actions type="domains" colspan="9" :actions="$bulkActions" />
    </form>

        <x-table>
            <x-slot:head>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Hosting</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Provider</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Expiry</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cost</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cloudflare</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
                    @forelse ($domains as $domain)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $domain->id }}" aria-label="Select {{ $domain->name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                            <td class="px-6 py-3 font-medium">{{ $domain->name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $domain->hosting->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $domain->serviceProvider->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">
                            @if($domain->expiry_date)
                                <x-date :value="$domain->expiry_date" />
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500">
                            @if($domain->cost)
                                <x-money :value="$domain->cost" />
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <x-badge :variant="$domain->status">{{ $domain->status }}</x-badge>
                        </td>
                        <td class="px-6 py-3">
                            @if($domain->cloudflare_status)
                                <x-badge :variant="$domain->cloudflare_status">{{ ucfirst($domain->cloudflare_status) }}</x-badge>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('domains.show', $domain->id) }}" color="indigo" icon="view" label="" title="View" />
                            <x-permission-check :module="$domain->module" action="update">
                            <x-action href="{{ route('domains.edit', $domain->id) }}" color="amber" icon="edit" label="" title="Edit" />
                            </x-permission-check>
                            <x-permission-check :module="$domain->module" action="delete">
                            <x-action action="{{ route('domains.destroy', $domain->id) }}" color="red" icon="delete" label="" title="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="9" icon="globe" title="No domains found." message="Register or add domains to track them." /></tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-4">{{ $domains->links() }}</div>
@endsection

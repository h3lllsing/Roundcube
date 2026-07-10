@extends('layouts.admin')

@section('title', 'VPS')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="VPS" subtitle="Track virtual private servers.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'vps') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('vps.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search VPS..." />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status']))
            <x-button href="{{ route('vps.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="vps">
        <x-bulk-actions type="vps" colspan="9" :actions="$bulkActions" />
    </form>

        <x-table>
            <x-slot:head>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">VPS</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Vendor</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">VPS IP</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">VPS Pass</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Department</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Location</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Associate</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
                    @forelse ($vpsList as $vps)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $vps->id }}" aria-label="Select {{ $vps->name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                            <td class="px-6 py-3 font-medium">{{ $vps->name }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $vps->serviceProvider->name ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @if($vps->ip_address)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm">{{ $vps->ip_address }}</span>
                                        <x-copy-button :text="$vps->ip_address" title="Copy IP" />
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($vps->password)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm text-gray-400">••••••••</span>
                                        <x-permission-check :module="$vaultModule" action="reveal">
                                        <x-copy-button password-route="{{ url('vps') }}/{{ $vps->id }}/password" title="Copy password" />
                                        </x-permission-check>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $vps->department ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $vps->location ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $vps->user->name ?? '—' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('vps.show', $vps->id) }}" color="indigo" icon="view" label="" title="View" />
                            <x-permission-check :module="$vps->module" action="update">
                            <x-action href="{{ route('vps.edit', $vps->id) }}" color="amber" icon="edit" label="" title="Edit" />
                            </x-permission-check>
                            <x-permission-check :module="$vps->module" action="delete">
                            <x-action action="{{ route('vps.destroy', $vps->id) }}" color="red" icon="delete" label="" title="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                            </td>
                        </tr>
                @empty
                    <tr><x-empty-state :colspan="9" icon="server" title="No VPS found." message="Add VPS servers to monitor them." /></tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-4">{{ $vpsList->links() }}</div>
</div>


@endsection

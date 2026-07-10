@extends('layouts.admin')

@section('title', 'Assets')

@section('content')
@php
$badgeMap = ['available' => 'success', 'assigned' => 'primary', 'lost' => 'danger', 'decommissioned' => 'default'];
@endphp
<div class="max-w-7xl mx-auto">
    <x-page-header title="Assets" subtitle="Track IT equipment and devices.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'assets') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-2m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('assets.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search asset tag, brand, model, anydesk, dept..." />
        <x-filter-select name="status" placeholder="All statuses" :options="['available' => 'Available', 'assigned' => 'Assigned', 'lost' => 'Lost', 'decommissioned' => 'Decommissioned']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status']))
            <x-button href="{{ route('assets.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="assets">
        <x-bulk-actions type="assets" colspan="9" :actions="$bulkActions" />
    </form>

        <x-table>
            <x-slot:head>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Asset ID</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Brand</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Model</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Assigned To</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Premises</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">AnyDesk</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
                @forelse ($assets as $asset)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $asset->id }}" aria-label="Select {{ $asset->asset_tag }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 font-medium">{{ $asset->asset_tag }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $asset->brand ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $asset->model ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $asset->assigned_user_name ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <x-badge :variant="$badgeMap[$asset->status] ?? 'default'">{{ ucfirst($asset->status) }}</x-badge>
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $asset->premises ?? '—' }}</td>
                        <td class="px-6 py-3 font-mono text-sm">{{ $asset->anydesk_id ?? '—' }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('assets.show', $asset->id) }}" color="indigo" icon="view" label="" title="View" />
                            <x-permission-check :module="$asset->module" action="update">
                            <x-action href="{{ route('assets.edit', $asset->id) }}" color="amber" icon="edit" label="" title="Edit" />
                            </x-permission-check>
                            <x-permission-check :module="$asset->module" action="delete">
                            <x-action action="{{ route('assets.destroy', $asset->id) }}" color="red" icon="delete" label="" title="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="9" icon="server" title="No assets found." message="Add IT assets to start tracking them." /></tr>
                @endforelse
        </x-table>

        <div class="mt-4">{{ $assets->links() }}</div>
</div>
@endsection

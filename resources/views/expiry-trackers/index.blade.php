@extends('layouts.admin')

@section('title', 'Renewals')

@php
    $sourceTypeLabels = [
        'domain' => 'Domain',
        'hosting' => 'Hosting',
        'vps' => 'VPS',
        'voip' => 'VOIP',
        'domain_email' => 'Domain Email',
        'other_service' => 'Other Service',
        'service_provider' => 'Service Provider',
    ];
@endphp

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Renewals" subtitle="Manage renewals and monitor expirations.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'expiry-trackers') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('expiry-trackers.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Standalone Renewal Item
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Cost (Visible)</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $totalCost ? '$' . number_format((float) $totalCost, 2) : '$0.00' }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Records</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $trackers->total() }}</p>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <select name="sync_type"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All renewals</option>
            <option value="linked" @selected(request('sync_type') === 'linked')>Linked</option>
            <option value="standalone" @selected(request('sync_type') === 'standalone')>Standalone</option>
        </select>
        <select name="source_type"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All source types</option>
            @foreach($sourceTypes as $val => $label)
                <option value="{{ $val }}" @selected(request('source_type') === $val)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="expired" @selected(request('status') === 'expired')>Expired</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
        </select>
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status', 'sync_type', 'source_type']))
            <x-button href="{{ route('expiry-trackers.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="expiry-trackers">
        <x-bulk-actions type="expiry-trackers" colspan="9" :actions="$bulkActions" />
    </form>

        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                        <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                        <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name / Source</th>
                        <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Service Provider</th>
                        <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cost</th>
                        <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Expiry</th>
                        <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Renewal</th>
                        <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                        <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse ($trackers as $tracker)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $tracker->id }}" aria-label="Select {{ $tracker->name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{{ $tracker->name }}</span>
                                    @if($tracker->trackable_type)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-lime-100 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300">Auto-synced</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Standalone</span>
                                    @endif
                                </div>
                                @if($tracker->trackable_type)
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">{{ $sourceTypeLabels[$tracker->trackable_type] ?? $tracker->trackable_type }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $tracker->serviceProvider?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $tracker->cost ? '$' . number_format($tracker->cost, 2) : '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $tracker->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $tracker->renewal_date?->format('Y-m-d') ?? '—' }}</td>
                            <td class="px-6 py-3">
                                <span @class([
                                    'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                    'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $tracker->status === 'active',
                                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $tracker->status === 'expired',
                                    'bg-gray-100 text-gray-700 dark:bg-black/30 dark:text-gray-300' => $tracker->status === 'cancelled',
                                ])>{{ $tracker->status }}</span>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('expiry-trackers.show', $tracker->id) }}" color="indigo" icon="view" label="View" />
                            <x-permission-check :module="$tracker->module" action="update">
                            <x-action href="{{ route('expiry-trackers.edit', $tracker->id) }}" color="amber" icon="edit" label="Edit" />
                            </x-permission-check>
                            @if($tracker->status !== 'cancelled')
                            <x-permission-check :module="$tracker->module" action="update">
                            <x-action action="{{ route('expiry-trackers.renew', $tracker->id) }}" color="emerald" icon="refresh" label="Renew" confirm="Renew this item? Expiry date will be extended by 1 year." confirm-button="Renew" method="POST" />
                            </x-permission-check>
                            @endif
                            <x-permission-check :module="$tracker->module" action="delete">
                            <x-action action="{{ route('expiry-trackers.destroy', $tracker->id) }}" color="red" icon="delete" label="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                            </td>
                        </tr>
                @empty
                    <tr><x-empty-state :colspan="9" icon="clock" title="No renewals found." message="Add standalone renewal items or link services to track renewals." /></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $trackers->links() }}</div>
</div>
@endsection

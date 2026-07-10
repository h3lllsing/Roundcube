@extends('layouts.admin')

@section('title', 'Other Services')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Other Services" subtitle="Track miscellaneous services.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'other-services') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('other-services.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search services..." />
        <x-filter-select name="service_type" placeholder="All types" :options="['saas' => 'SaaS', 'api' => 'API', 'monitoring' => 'Monitoring', 'analytics' => 'Analytics', 'cdn' => 'CDN', 'ssl' => 'SSL', 'other' => 'Other']" />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status', 'service_type']))
            <x-button href="{{ route('other-services.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="other-services">
        <x-bulk-actions type="other-services" colspan="9" :actions="$bulkActions" />
    </form>

        <x-table>
            <x-slot:head>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Type</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Login ID</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Password</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Service Provider</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cost</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Expiry</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
                    @forelse ($services as $service)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $service->id }}" aria-label="Select {{ $service->name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                            <td class="px-6 py-3 font-medium">{{ $service->name }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $service->service_type ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @if($service->username)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm max-w-[120px] truncate" title="{{ $service->username }}">{{ $service->username }}</span>
                                        <x-copy-button :text="$service->username" title="Copy login ID" />
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($service->password)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm text-gray-400">••••••••</span>
                                        <x-permission-check :module="$vaultModule" action="reveal">
                                        <x-copy-button password-route="{{ url('other-services') }}/{{ $service->id }}/password" title="Copy password" />
                                        </x-permission-check>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $service->serviceProvider?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">@if($service->cost)<x-money :value="$service->cost" />@else—@endif</td>
                            <td class="px-6 py-3 text-gray-500">@if($service->expiry_date)<x-date :value="$service->expiry_date" />@else—@endif</td>
                            <td class="px-6 py-3">
                                <x-badge :variant="$service->status">{{ $service->status }}</x-badge>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('other-services.show', $service->id) }}" color="indigo" icon="view" label="" title="View" />
                            <x-permission-check :module="$service->module" action="update">
                            <x-action href="{{ route('other-services.edit', $service->id) }}" color="amber" icon="edit" label="" title="Edit" />
                            </x-permission-check>
                            <x-permission-check :module="$service->module" action="delete">
                            <x-action action="{{ route('other-services.destroy', $service->id) }}" color="red" icon="delete" label="" title="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                            </td>
                        </tr>
                @empty
                    <tr><x-empty-state :colspan="10" icon="box" title="No other services found." message="Add other services to manage everything in one place." /></tr>
                    @endforelse
                </tbody>
        </x-table>

        <div class="mt-4">{{ $services->links() }}</div>
</div>


@endsection

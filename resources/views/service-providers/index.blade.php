@extends('layouts.admin')

@section('title', 'Service Providers')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Service Providers" subtitle="Track service provider information.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'service-providers') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('service-providers.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search providers..." />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status']))
            <x-button href="{{ route('service-providers.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="service-providers">
        <x-bulk-actions type="service-providers" colspan="9" :actions="$bulkActions" />
    </form>

        <x-table>
            <x-slot:head>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Serial</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Type</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Web URL</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Login ID</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Password</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Email</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
                    @forelse ($providers as $provider)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $provider->id }}" aria-label="Select {{ $provider->name ?? $provider->id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                            <td class="px-6 py-3 text-gray-500">{{ $provider->id }}</td>
                            <td class="px-6 py-3 font-medium">{{ $provider->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $provider->type ?? '—' }}</td>
                            <td class="px-6 py-3 max-w-[200px] truncate">
                                @if($provider->website)
                                    <div class="flex items-center gap-2">
                                        <a href="{{ $provider->website }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline truncate block" title="{{ $provider->website }}">{{ $provider->website }}</a>
                                        <x-copy-button :text="$provider->website" title="Copy URL" />
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($provider->login_id)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm max-w-[120px] truncate" title="{{ $provider->login_id }}">{{ $provider->login_id }}</span>
                                        <x-copy-button :text="$provider->login_id" title="Copy login ID" />
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($provider->password)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm text-gray-400">••••••••</span>
                                        <x-permission-check :module="$provider->module ?? $vaultModule" action="reveal">
                                        <x-copy-button password-route="{{ url('service-providers') }}/{{ $provider->id }}/password" title="Copy password" />
                                        </x-permission-check>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($provider->email)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm max-w-[160px] truncate" title="{{ $provider->email }}">{{ $provider->email }}</span>
                                        <x-copy-button :text="$provider->email" title="Copy email" />
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                <x-badge :variant="$provider->status">{{ $provider->status }}</x-badge>
                            </td>
                            <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('service-providers.show', $provider->id) }}" color="indigo" icon="view" label="" title="View" />
                            <x-permission-check :module="$provider->module" action="update">
                            <x-action href="{{ route('service-providers.edit', $provider->id) }}" color="amber" icon="edit" label="" title="Edit" />
                            </x-permission-check>
                            <x-permission-check :module="$provider->module" action="delete">
                            <x-action action="{{ route('service-providers.destroy', $provider->id) }}" color="red" icon="delete" label="" title="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                            </td>
                        </tr>
                @empty
                    <tr><x-empty-state :colspan="9" icon="box" title="No service providers found." message="Add service providers to keep track of vendors." /></tr>
                    @endforelse
                </tbody>
        </x-table>

        <div class="mt-4">{{ $providers->links() }}</div>
</div>


@endsection

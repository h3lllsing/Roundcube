@extends('layouts.admin')

@section('title', 'VoIP')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="VoIP" subtitle="Manage VoIP services.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'voip') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('voip.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search VoIP..." />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status']))
            <x-button href="{{ route('voip.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="voip">
        <x-bulk-actions type="voip" colspan="12" :actions="$bulkActions" />
    </form>

        <x-table>
            <x-slot:head>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Serial</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">EXTENSIONS</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">PASSWORDS</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">SERVER IP</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Number for Inbound</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Vendor NAME</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Number Status</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Code for OutBound</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Brand Details</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
                    @forelse ($voipList as $voip)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $voip->id }}" aria-label="Select {{ $voip->name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                            <td class="px-6 py-3 text-gray-500">{{ $voip->id }}</td>
                            <td class="px-6 py-3 font-medium">{{ $voip->extensions[0] ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @if($voip->extension_password)
                                    <span class="inline-flex items-center gap-1">
                                        <span class="text-gray-400">••••••••</span>
                                        <x-permission-check :module="$vaultModule" action="reveal">
                                        <x-copy-button password-route="{{ url('voip') }}/{{ $voip->id }}/extension-password" title="Copy password" />
                                        </x-permission-check>
                                    </span>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 font-medium">{{ $voip->name }}</td>
                            <td class="px-6 py-3">
                                @if($voip->server_ip)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm">{{ $voip->server_ip }}</span>
                                        <x-copy-button :text="$voip->server_ip" title="Copy server IP" />
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-500">{{ $voip->phone_number ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $voip->serviceProvider?->name ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $voip->number_status ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500">{{ $voip->outbound_code ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500 max-w-[200px] truncate" title="{{ $voip->team_details }}">{{ $voip->team_details ?? '—' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('voip.show', $voip->id) }}" color="indigo" icon="view" label="" title="View" />
                            <x-permission-check :module="$voip->module" action="update">
                            <x-action href="{{ route('voip.edit', $voip->id) }}" color="amber" icon="edit" label="" title="Edit" />
                            </x-permission-check>
                            <x-permission-check :module="$voip->module" action="delete">
                            <x-action action="{{ route('voip.destroy', $voip->id) }}" color="red" icon="delete" label="" title="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                            </td>
                        </tr>
                @empty
                    <tr><x-empty-state :colspan="12" icon="server" title="No VoIP entries found." message="Add VoIP services to manage them." /></tr>
                    @endforelse
                </tbody>
        </x-table>

        <div class="mt-4">{{ $voipList->links() }}</div>
</div>


@endsection

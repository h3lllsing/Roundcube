@extends('layouts.admin')

@section('title', 'Hostings')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Hostings" subtitle="Manage hosting plans and accounts.">
        <x-slot:actions>
            @if($canExport)
            <x-button href="{{ route('export', 'hostings') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('hostings.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search hostings..." />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended', 'pending_transfer' => 'Pending Transfer', 'cancelled' => 'Cancelled']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status']))
            <x-button href="{{ route('hostings.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="hostings">
        <x-bulk-actions type="hostings" colspan="10" :actions="$bulkActions" />
    </form>

        <x-table>
            <x-slot:head>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Serial</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Domain</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Domain IP</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Mail Domain IP</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cpanel Link</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cpanel ID</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cpanel PW</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cpanel IP</th>
                <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
            </x-slot:head>
                    @forelse ($hostings as $hosting)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                            <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $hosting->id }}" aria-label="Select {{ $hosting->name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                            <td class="px-6 py-3 text-gray-500">{{ $hosting->id }}</td>
                            <td class="px-6 py-3 font-medium">{{ $hosting->name }}</td>
                            <td class="px-6 py-3 text-gray-500 font-mono">{{ $hosting->domain_ip ?? '—' }}</td>
                            <td class="px-6 py-3 text-gray-500 font-mono">{{ $hosting->mail_domain_ip ?? '—' }}</td>
                            <td class="px-6 py-3">
                                @if($hosting->cpanel_url && Str::startsWith($hosting->cpanel_url, ['http://', 'https://']))
                                    <div class="flex items-center gap-2">
                                        <a href="{{ $hosting->cpanel_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 underline text-xs">{{ $hosting->cpanel_url }}</a>
                                        <x-copy-button :text="$hosting->cpanel_url" title="Copy URL" />
                                    </div>
                                @else
                                    <span class="text-gray-500">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($hosting->username)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm max-w-[120px] truncate" title="{{ $hosting->username }}">{{ $hosting->username }}</span>
                                        <x-copy-button :text="$hosting->username" title="Copy username" />
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3">
                                @if($hosting->password)
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm text-gray-400">••••••••</span>
                                        <x-permission-check :module="$vaultModule" action="reveal">
                                        <x-copy-button password-route="{{ url('hostings') }}/{{ $hosting->id }}/password" title="Copy password" />
                                        </x-permission-check>
                                    </div>
                                @else
                                    <span class="text-gray-400">—</span>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-gray-500 font-mono">{{ $hosting->cpanel_ip ?? '—' }}</td>
                            <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('hostings.show', $hosting->id) }}" color="indigo" icon="view" label="" title="View" />
                            <x-permission-check :module="$hosting->module" action="update">
                            <x-action href="{{ route('hostings.edit', $hosting->id) }}" color="amber" icon="edit" label="" title="Edit" />
                            </x-permission-check>
                            <x-permission-check :module="$hosting->module" action="delete">
                            <x-action action="{{ route('hostings.destroy', $hosting->id) }}" color="red" icon="delete" label="" title="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                            </td>
                        </tr>
                @empty
                    <tr><x-empty-state :colspan="10" icon="server" title="No hostings found." message="Add hosting accounts to manage them." /></tr>
                @endforelse
            </tbody>
        </x-table>

        <div class="mt-4">{{ $hostings->links() }}</div>
</div>


@endsection

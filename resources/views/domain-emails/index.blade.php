@extends('layouts.admin')

@section('title', 'Email Credentials')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Email Credentials" subtitle="Store and manage domain email passwords.">
        <x-slot:actions>
            @if(isset($canExport) && $canExport)
            <x-button href="{{ route('export', 'domain-emails') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            @if($canCreate)
            <x-button href="{{ route('domain-emails.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Email
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <x-filter-input name="search" value="{{ request('search') }}" placeholder="Search email or domain..." />
        <x-filter-select name="domain_id" placeholder="All domains" :options="$domains" />
        <x-filter-select name="status" placeholder="All statuses" :options="['active' => 'Active', 'expired' => 'Expired', 'suspended' => 'Suspended']" />
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'domain_id', 'status']))
            <x-button href="{{ route('domain-emails.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <x-table :bulk="false">
        <x-slot:head>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Serial</th>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Domain</th>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Email</th>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Password</th>
            <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
        </x-slot:head>
                @forelse ($emails as $email)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-gray-500">{{ $email->id }}</td>
                        <td class="px-6 py-3">
                            @if($email->domain)
                            <a href="{{ route('domain-emails.index', ['domain_id' => $email->domain_id]) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
                                {{ $email->domain->name }}
                            </a>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ $email->email }}</span>
                                <x-copy-button :text="$email->email" title="Copy email" />
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            @if($email->password)
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-sm text-gray-400">••••••••</span>
                                    <x-permission-check :module="$email->module" action="reveal">
                                    <x-copy-button password-route="{{ url('domain-emails') }}/{{ $email->id }}/password" title="Copy password" />
                                    </x-permission-check>
                                </div>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('domain-emails.show', $email->id) }}" color="indigo" icon="view" label="" title="View" />
                            <x-permission-check :module="$email->module" action="update">
                            <x-action href="{{ route('domain-emails.edit', $email->id) }}" color="amber" icon="edit" label="" title="Edit" />
                            </x-permission-check>
                            <x-permission-check :module="$email->module" action="delete">
                            <x-action action="{{ route('domain-emails.destroy', $email->id) }}" color="red" icon="delete" label="" title="Delete" confirm="Are you sure?" method="DELETE" />
                            </x-permission-check>
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="5" icon="box" title="No email credentials yet." message="Add your first email account to store its password." /></tr>
                @endforelse
            </tbody>
    </x-table>

    <div class="mt-4">{{ $emails->links() }}</div>
</div>


@endsection
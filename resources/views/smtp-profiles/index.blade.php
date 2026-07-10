@extends('layouts.admin')

@section('title', 'SMTP Profiles')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="SMTP Profiles" subtitle="Manage email sender profiles">
        <x-slot:actions>
            <x-button href="{{ route('smtp-profiles.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" action="{{ route('smtp-profiles.index') }}" class="mb-6">
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search profiles..."
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none w-48">
            </div>
            <div>
                <label class="block text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1">Status</label>
                <select name="status"
                    class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
            <div class="flex items-center gap-2">
                <x-button type="submit" variant="primary" size="sm">Filter</x-button>
                @if(request()->anyFilled(['search', 'status']))
                    <x-button href="{{ route('smtp-profiles.index') }}" variant="outline" size="sm">Clear</x-button>
                @endif
            </div>
        </div>
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Sender Email</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">SMTP Host:Port</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Default</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Priority</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">In Use</th>
                    <th class="text-center px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Last Test</th>
                    <th class="text-right px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($profiles as $profile)
                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700/50">
                    <td class="px-6 py-3">
                        <a href="{{ route('smtp-profiles.show', $profile) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                            {{ $profile->name }}
                        </a>
                    </td>
                    <td class="px-6 py-3 text-gray-600 dark:text-gray-400">
                        <div class="flex items-center gap-2">
                            <span>{{ $profile->sender_name }} <{{ $profile->sender_email }}></span>
                            <x-copy-button :text="$profile->sender_email" title="Copy sender email" />
                        </div>
                    </td>
                    <td class="px-6 py-3 text-gray-600 dark:text-gray-400 font-mono text-xs">
                        <div class="flex items-center gap-2">
                            <span>{{ $profile->smtp_host }}:{{ $profile->smtp_port }}</span>
                            <x-copy-button :text="$profile->smtp_host . ':' . $profile->smtp_port" title="Copy SMTP host:port" />
                        </div>
                    </td>
                    <td class="px-6 py-3 text-center">
                        @if($profile->is_default)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300">Yes</span>
                        @else
                            <span class="text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-center">
                        @if($profile->is_active)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300">Active</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-center text-gray-600 dark:text-gray-400">{{ $profile->priority }}</td>
                    <td class="px-6 py-3 text-center text-gray-600 dark:text-gray-400">{{ $profile->usageCount() }}</td>
                    <td class="px-6 py-3 text-center">
                        @if($profile->last_tested_at)
                            <span class="text-xs {{ $profile->last_test_status === 'success' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                {{ $profile->last_tested_at->diffForHumans() }}
                            </span>
                        @else
                            <span class="text-gray-400">Never</span>
                        @endif
                    </td>
                    <td class="px-6 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <x-action href="{{ route('smtp-profiles.show', $profile) }}" color="indigo" label="" icon="view" />
                            <x-action href="{{ route('smtp-profiles.edit', $profile) }}" color="amber" label="" icon="edit" />
                            @if(!$profile->is_default)
                            <form action="{{ route('smtp-profiles.set-default', $profile) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <x-action color="sky" label="Set Default" />
                            </form>
                            @endif
                            @if($profile->is_active)
                            <form action="{{ route('smtp-profiles.toggle-active', $profile) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <x-action color="red" label="Deactivate" confirm="Deactivate this profile?" confirm-button="Deactivate" />
                            </form>
                            @else
                            <form action="{{ route('smtp-profiles.toggle-active', $profile) }}" method="POST" class="inline">
                                @csrf @method('PATCH')
                                <x-action color="emerald" label="Activate" />
                            </form>
                            @endif
                            <form action="{{ route('smtp-profiles.duplicate', $profile) }}" method="POST" class="inline">
                                @csrf
                                <x-action color="indigo" label="Duplicate" icon="clone" />
                            </form>
                            @if(!$profile->isInUse())
                            <form action="{{ route('smtp-profiles.destroy', $profile) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <x-action color="red" label="" icon="delete" confirm="Delete this profile?" />
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <x-empty-state icon="server" title="No SMTP profiles yet" message="Create your first email sender profile to start sending emails." />
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $profiles->links() }}
    </div>
</div>
@endsection
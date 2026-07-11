@extends('layouts.admin')

@section('title', 'Login Audits')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Login Audits" subtitle="Review authentication attempts.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super-admin'))
            <x-button href="{{ route('export', 'login-audits') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search email or IP..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <select name="event"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All events</option>
            <option value="login_success" @selected(request('event') === 'login_success')>Success</option>
            <option value="login_failed" @selected(request('event') === 'login_failed')>Failed</option>
            <option value="logout" @selected(request('event') === 'logout')>Logout</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'event', 'date_from', 'date_to']))
            <x-button href="{{ route('login-audits.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="login-audits">
        <x-bulk-actions type="login-audits" colspan="7" :statuses="[]" :actions="['delete']" />
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">User</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Email</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Event</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">IP Address</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($audits as $audit)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $audit->id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 text-gray-500">{{ $audit->user->name ?? '—' }}</td>
                        <td class="px-6 py-3 font-medium">{{ $audit->email }}</td>
                        <td class="px-6 py-3">
                            <span @class([
                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $audit->event === 'login_success',
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $audit->event === 'login_failed',
                            ])>{{ str_replace('_', ' ', $audit->event) }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400 font-mono text-xs">{{ $audit->ip_address }}</td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400 text-nowrap">{{ $audit->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="6" icon="user" title="No login audits found." message="Login attempts will appear here." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-4">{{ $audits->links() }}</div>
</div>
@endsection

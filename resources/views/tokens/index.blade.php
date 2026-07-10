@extends('layouts.admin')

@section('title', 'API Tokens')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="API Tokens" subtitle="Manage API access tokens.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super-admin'))
            <x-button href="{{ route('export', 'tokens') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            <x-button href="{{ route('tokens.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if (session('plain_text'))
        <div class="mb-4 px-4 py-3 rounded-xl bg-yellow-50 dark:bg-yellow-900/30 border border-yellow-200 dark:border-yellow-700 text-sm text-yellow-700 dark:text-yellow-300">
            <strong>Token created!</strong> Copy this now — it won't be shown again:<br>
            <code class="block mt-1 p-2 bg-yellow-100 dark:bg-yellow-800/30 rounded text-xs break-all">{{ session('plain_text') }}</code>
        </div>
    @endif

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tokens..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->filled('search'))
            <x-button href="{{ route('tokens.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="tokens">
        <x-bulk-actions type="tokens" colspan="5" :statuses="[]" :actions="['delete']" />
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Created</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Last Used</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($tokens as $token)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $token->id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 font-medium">{{ $token->name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $token->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $token->last_used_at?->diffForHumans() ?? 'Never' }}</td>
                        <td class="px-6 py-3">
                            <x-action action="{{ route('tokens.destroy', $token->id) }}" color="red" icon="delete" label="Revoke" confirm="Revoke this token? This cannot be undone." confirm-button="Revoke" method="DELETE" />
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="5" icon="key" title="No API tokens found." message="Generate a token to authenticate API requests." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tokens->links() }}</div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Notes')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Notes" subtitle="Keep track of important notes.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super-admin'))
            <x-button href="{{ route('export', 'notes') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            <x-button href="{{ route('notes.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search notes..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <select name="notable_type"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All types</option>
            <option value="App\Models\Feature" @selected(request('notable_type') === 'App\Models\Feature')>Feature</option>
            <option value="App\Models\Module" @selected(request('notable_type') === 'App\Models\Module')>Module</option>
            @foreach ($notableTypes as $type)
                @if (!in_array($type, ['App\Models\Feature', 'App\Models\Module']))
                <option value="{{ $type }}" @selected(request('notable_type') === $type)>{{ class_basename($type) }}</option>
                @endif
            @endforeach
        </select>
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'notable_type']))
            <x-button href="{{ route('notes.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="notes">
        <x-bulk-actions type="notes" colspan="6" :statuses="[]" :actions="['delete']" />
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Content</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">User</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Notable Type</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Created</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($notes as $note)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $note->id }}" aria-label="Select note {{ $note->id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 max-w-md truncate">{{ Str::limit($note->content, 80) }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $note->user->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">
                            @if ($note->notable_type)
                                @php
                                    $routeKey = match ($note->notable_type) {
                                        'App\Models\Feature' => 'features.show',
                                        'App\Models\Module' => 'modules.show',
                                        default => null,
                                    };
                                @endphp
                                @if ($routeKey)
                                    <a href="{{ route($routeKey, $note->notable_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">
                                        {{ class_basename($note->notable_type) }} #{{ $note->notable_id }}
                                    </a>
                                @else
                                    {{ class_basename($note->notable_type) }} #{{ $note->notable_id }}
                                @endif
                            @else
                                <span class="text-gray-400 dark:text-gray-500">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $note->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('notes.show', $note->id) }}" color="indigo" icon="view" label="View" />
                            <x-action href="{{ route('notes.edit', $note->id) }}" color="amber" icon="edit" label="Edit" />
                            <x-action action="{{ route('notes.destroy', $note->id) }}" color="red" icon="delete" label="Delete" confirm="Are you sure?" method="DELETE" />
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="6" icon="clipboard" title="No notes found." message="Add notes to keep track of important information." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-4">{{ $notes->links() }}</div>
</div>
@endsection

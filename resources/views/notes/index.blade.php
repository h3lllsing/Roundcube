@extends('layouts.admin')

@section('title', 'Notes')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Notes</h1>
        <a href="{{ route('notes.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">+ Create</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search notes..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">Search</button>
        @if(request()->filled('search'))
            <a href="{{ route('notes.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-sm rounded-lg transition-colors">Clear</a>
        @endif
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Content</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">User</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Notable Type</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Created</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($notes as $note)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 max-w-md truncate">{{ Str::limit($note->content, 80) }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $note->user->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ class_basename($note->notable_type) }} #{{ $note->notable_id }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $note->created_at->format('Y-m-d H:i') }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <a href="{{ route('notes.show', $note->id) }}" class="text-blue-600 hover:text-blue-800 text-xs mr-2">View</a>
                            <a href="{{ route('notes.edit', $note->id) }}" class="text-amber-600 hover:text-amber-800 text-xs mr-2">Edit</a>
                            <form method="POST" action="{{ route('notes.destroy', $note->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No notes found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $notes->links() }}</div>
</div>
@endsection

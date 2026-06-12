@extends('layouts.admin')

@section('title', 'Vault')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Vault</h1>
        <a href="{{ route('vault.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">+ Create</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vault..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">Search</button>
        @if(request()->filled('search'))
            <a href="{{ route('vault.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-sm rounded-lg transition-colors">Clear</a>
        @endif
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Service Name</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Module</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">URL</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Username</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Created</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($entries as $entry)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 font-medium">{{ $entry->service_name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $entry->module->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500 max-w-xs truncate">
                            @if($entry->service_url)
                                <a href="{{ $entry->service_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $entry->service_url }}</a>
                            @else
                                —
                            @endif
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $entry->username ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $entry->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <a href="{{ route('vault.show', $entry->id) }}" class="text-blue-600 hover:text-blue-800 text-xs mr-2">View</a>
                            <a href="{{ route('vault.edit', $entry->id) }}" class="text-amber-600 hover:text-amber-800 text-xs mr-2">Edit</a>
                            <form method="POST" action="{{ route('vault.destroy', $entry->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-8 text-center text-gray-400">No vault entries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $entries->links() }}</div>
</div>
@endsection

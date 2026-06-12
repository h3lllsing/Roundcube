@extends('layouts.admin')

@section('title', 'Modules')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Modules</h1>
        <a href="{{ route('modules.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">+ Create</a>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">ID</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Feature</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Created</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($modules as $module)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-gray-500">{{ $module->id }}</td>
                        <td class="px-6 py-3 font-medium">{{ $module->name }}</td>
                        <td class="px-6 py-3">{{ $module->feature->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $module->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <a href="{{ route('modules.show', $module->id) }}" class="text-blue-600 hover:text-blue-800 text-xs mr-2">View</a>
                            <a href="{{ route('modules.edit', $module->id) }}" class="text-amber-600 hover:text-amber-800 text-xs mr-2">Edit</a>
                            <form method="POST" action="{{ route('modules.destroy', $module->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-400">No modules found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $modules->links() }}
    </div>
</div>
@endsection

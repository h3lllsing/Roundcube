@extends('layouts.admin')

@section('title', 'Tasks')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Tasks</h1>
        <a href="{{ route('tasks.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">+ Create</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        <select name="status"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
            <option value="completed" @selected(request('status') === 'completed')>Completed</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
        </select>
        <select name="priority"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">All priorities</option>
            <option value="low" @selected(request('priority') === 'low')>Low</option>
            <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
            <option value="high" @selected(request('priority') === 'high')>High</option>
            <option value="urgent" @selected(request('priority') === 'urgent')>Urgent</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">Filter</button>
        @if(request()->anyFilled(['search', 'status', 'priority']))
            <a href="{{ route('tasks.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-sm rounded-lg transition-colors">Clear</a>
        @endif
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Title</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Module</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Priority</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Assignees</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Due</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Created</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($tasks as $task)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 font-medium max-w-xs truncate">{{ $task->title }}</td>
                        <td class="px-6 py-3">{{ $task->module->name ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <span @class([
                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200' => $task->status === 'pending',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $task->status === 'in_progress',
                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $task->status === 'completed',
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $task->status === 'cancelled',
                            ])>{{ str_replace('_', ' ', $task->status) }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <span @class([
                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-gray-100 text-gray-600 dark:bg-gray-600 dark:text-gray-300' => $task->priority === 'low',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $task->priority === 'medium',
                                'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' => $task->priority === 'high',
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $task->priority === 'urgent',
                            ])>{{ $task->priority }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $task->assignees->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $task->due_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $task->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <a href="{{ route('tasks.show', $task->id) }}" class="text-blue-600 hover:text-blue-800 text-xs mr-2">View</a>
                            <a href="{{ route('tasks.edit', $task->id) }}" class="text-amber-600 hover:text-amber-800 text-xs mr-2">Edit</a>
                            <form method="POST" action="{{ route('tasks.destroy', $task->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">No tasks found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tasks->links() }}</div>
</div>
@endsection

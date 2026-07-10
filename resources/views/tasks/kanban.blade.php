@extends('layouts.admin')

@section('title', 'Task Board')

@section('content')
<div class="max-w-full mx-auto">
    <x-page-header title="Task Board">
        <x-slot:actions>
            <x-button href="{{ route('tasks.index') }}{{ request('my_tasks') ? '?my_tasks=1' : '' }}" variant="outline" size="sm">List</x-button>
            <x-button href="{{ route('tasks.create') }}" variant="primary" size="sm">+ Create</x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <select name="priority"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All priorities</option>
            <option value="low" @selected(request('priority') === 'low')>Low</option>
            <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
            <option value="high" @selected(request('priority') === 'high')>High</option>
            <option value="urgent" @selected(request('priority') === 'urgent')>Urgent</option>
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="checkbox" name="my_tasks" value="1" @checked(request()->boolean('my_tasks')) class="rounded border-gray-300 dark:border-gray-600">
            My Tasks
        </label>
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['priority']) || request()->boolean('my_tasks'))
            <x-button href="{{ route('tasks.kanban') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        @php
            $statusLabels = ['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
            $statusColors = ['pending' => 'border-gray-300 dark:border-gray-600', 'in_progress' => 'border-blue-300 dark:border-blue-600', 'completed' => 'border-green-300 dark:border-green-600', 'cancelled' => 'border-red-300 dark:border-red-600'];
        @endphp

        @foreach (['pending', 'in_progress', 'completed', 'cancelled'] as $status)
            <div class="bg-gray-50 dark:bg-black/50 rounded-xl border {{ $statusColors[$status] }} p-3">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3 px-1">{{ $statusLabels[$status] }} ({{ $columns[$status]->count() }})</h3>
                <div class="space-y-2 min-h-[200px]">
                    @forelse ($columns[$status] as $task)
                        <div class="bg-white dark:bg-black rounded-xl border border-gray-200 dark:border-gray-600 p-3 shadow-sm">
                            <div class="flex items-start justify-between gap-2 mb-1">
                                <a href="{{ route('tasks.show', $task->id) }}" class="text-sm font-medium hover:text-indigo-600 dark:hover:text-indigo-400 leading-tight">{{ $task->title }}</a>
                                <span @class([
                                    'shrink-0 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium',
                                    'bg-gray-100 text-gray-600 dark:bg-black dark:text-gray-300' => $task->priority === 'low',
                                    'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $task->priority === 'medium',
                                    'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' => $task->priority === 'high',
                                    'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $task->priority === 'urgent',
                                ])>{{ $task->priority }}</span>
                            </div>
                            @if($task->module)
                                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $task->module->name }}</p>
                            @endif
                            <div class="flex items-center justify-between text-xs text-gray-400 dark:text-gray-500">
                                <span>{{ $task->assignees->pluck('name')->implode(', ') ?: '—' }}</span>
                                @if($task->due_date)
                                    <span>{{ $task->due_date->format('M d') }}</span>
                                @endif
                            </div>
                            <form method="POST" action="{{ route('tasks.update-status', $task->id) }}" class="mt-2 pt-2 border-t border-gray-100 dark:border-gray-600">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="text-xs border-0 bg-transparent cursor-pointer focus:ring-0 w-full p-0 text-gray-500 dark:text-gray-400">
                                    <option value="pending" @selected($task->status === 'pending')>→ Pending</option>
                                    <option value="in_progress" @selected($task->status === 'in_progress')>→ In Progress</option>
                                    <option value="completed" @selected($task->status === 'completed')>→ Completed</option>
                                    <option value="cancelled" @selected($task->status === 'cancelled')>→ Cancelled</option>
                                </select>
                            </form>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 dark:text-gray-500 text-center py-8">No tasks</p>
                    @endforelse
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection

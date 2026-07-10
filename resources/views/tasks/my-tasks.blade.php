@extends('layouts.admin')

@section('title', 'My Tasks')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="My Tasks" subtitle="Your assigned tasks.">
        <x-slot:actions>
            <x-button href="{{ route('tasks.index') }}" variant="outline" size="sm">All Tasks</x-button>
            <x-button href="{{ route('tasks.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search my tasks..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <select name="status"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All statuses</option>
            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
            <option value="in_progress" @selected(request('status') === 'in_progress')>In Progress</option>
            <option value="completed" @selected(request('status') === 'completed')>Completed</option>
            <option value="cancelled" @selected(request('status') === 'cancelled')>Cancelled</option>
        </select>
        <select name="priority"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All priorities</option>
            <option value="low" @selected(request('priority') === 'low')>Low</option>
            <option value="medium" @selected(request('priority') === 'medium')>Medium</option>
            <option value="high" @selected(request('priority') === 'high')>High</option>
            <option value="urgent" @selected(request('priority') === 'urgent')>Urgent</option>
        </select>
        <select name="module_id"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All modules</option>
            @foreach ($modules as $id => $name)
                <option value="{{ $id }}" @selected(request('module_id') == $id)>{{ $name }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <select name="sort_by"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">Sort by</option>
            <option value="title" @selected(request('sort_by') === 'title')>Title</option>
            <option value="status" @selected(request('sort_by') === 'status')>Status</option>
            <option value="priority" @selected(request('sort_by') === 'priority')>Priority</option>
            <option value="due_date" @selected(request('sort_by') === 'due_date')>Due Date</option>
            <option value="created_at" @selected(request('sort_by') === 'created_at')>Created</option>
            <option value="updated_at" @selected(request('sort_by') === 'updated_at')>Updated</option>
        </select>
        <select name="sort_order"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="desc" @selected(request('sort_order', 'desc') === 'desc')>Newest</option>
            <option value="asc" @selected(request('sort_order') === 'asc')>Oldest</option>
        </select>
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status', 'priority', 'module_id', 'date_from', 'date_to', 'sort_by']))
            <x-button href="{{ route('tasks.my') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Title</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Module</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Priority</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Assignees</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Due</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Created</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($tasks as $task)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 font-medium max-w-xs truncate">{{ $task->title }}</td>
                        <td class="px-6 py-3">{{ $task->module->name ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <form method="POST" action="{{ route('tasks.update-status', $task->id) }}" class="status-update-form">
                                @csrf
                                @method('PATCH')
                                <select name="status"
                                    class="text-xs border-0 bg-transparent cursor-pointer focus:ring-0 font-medium rounded-full px-2 py-0.5 {{ match($task->status) {
                                        'pending' => 'text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-black',
                                        'in_progress' => 'text-blue-700 dark:text-blue-300 bg-blue-100 dark:bg-blue-900/30',
                                        'completed' => 'text-green-700 dark:text-green-300 bg-green-100 dark:bg-green-900/30',
                                        'cancelled' => 'text-red-700 dark:text-red-300 bg-red-100 dark:bg-red-900/30',
                                        default => 'text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-black',
                                    } }}">
                                    <option value="pending" @selected($task->status === 'pending')>Pending</option>
                                    <option value="in_progress" @selected($task->status === 'in_progress')>In Progress</option>
                                    <option value="completed" @selected($task->status === 'completed')>Completed</option>
                                    <option value="cancelled" @selected($task->status === 'cancelled')>Cancelled</option>
                                </select>
                            </form>
                        </td>
                        <td class="px-6 py-3">
                            <span @class([
                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-gray-100 text-gray-600 dark:bg-black dark:text-gray-300' => $task->priority === 'low',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $task->priority === 'medium',
                                'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' => $task->priority === 'high',
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $task->priority === 'urgent',
                            ])>{{ $task->priority }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $task->assignees->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $task->due_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $task->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('tasks.show', $task->id) }}" color="indigo" icon="view" label="View" />
                            <x-action href="{{ route('tasks.edit', $task->id) }}" color="amber" icon="edit" label="Edit" />
                            <x-action action="{{ route('tasks.destroy', $task->id) }}" color="red" icon="delete" label="Delete" confirm="Are you sure?" method="DELETE" />
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="8" icon="clipboard" title="No tasks assigned." message="You have no tasks assigned to you." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tasks->links() }}</div>
</div>
@endsection

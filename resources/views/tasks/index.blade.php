@extends('layouts.admin')

@section('title', 'Tasks')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Tasks" subtitle="Track and manage all tasks.">
        <x-slot:actions>
            <x-button href="{{ route('tasks.kanban') }}{{ request('my_tasks') ? '?my_tasks=1' : '' }}" variant="outline" size="sm">Board</x-button>
            @if(auth()->user()->hasRole('super-admin'))
            <x-button href="{{ route('export', 'tasks') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            <x-button href="{{ route('tasks.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="mb-6">
        <button type="button" id="filterToggle" class="lg:hidden flex items-center gap-2 text-sm font-medium text-gray-600 dark:text-gray-400 mb-3 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/40 rounded-lg px-2 py-1" aria-expanded="false" aria-controls="filterBar">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
            <span>Filters</span>
            <svg id="filterChevron" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
        </button>
        <form method="GET" id="filterBar" class="flex flex-wrap gap-3 max-lg:hidden">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..."
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
        <select name="assigned_to"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All assignees</option>
            @foreach ($users as $id => $name)
                <option value="{{ $id }}" @selected(request('assigned_to') == $id)>{{ $name }}</option>
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
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="checkbox" name="my_tasks" value="1" @checked(request()->boolean('my_tasks')) class="rounded border-gray-300 dark:border-gray-600">
            My Tasks
        </label>
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
            <input type="checkbox" name="trashed" value="1" @checked(request()->boolean('trashed')) class="rounded border-gray-300 dark:border-gray-600">
            Trashed
        </label>
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status', 'priority', 'module_id', 'assigned_to', 'date_from', 'date_to', 'sort_by']) || request()->boolean('my_tasks') || request()->boolean('trashed'))
            <x-button href="{{ route('tasks.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>
    </div>
    <script>
        document.getElementById('filterToggle')?.addEventListener('click', function() {
            var bar = document.getElementById('filterBar');
            var chevron = document.getElementById('filterChevron');
            var expanded = bar.classList.toggle('max-lg:hidden');
            this.setAttribute('aria-expanded', !expanded);
            if (chevron) chevron.classList.toggle('rotate-180');
        });
    </script>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="tasks">
        <x-bulk-actions type="tasks" colspan="8" :statuses="['pending', 'in_progress', 'completed', 'cancelled']" />
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Title</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Module</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Priority</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Assignees</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Due</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($tasks as $task)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $task->id }}" aria-label="Select task {{ $task->id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 font-medium max-w-xs truncate"><a href="{{ route('tasks.show', $task->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $task->title }}</a></td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ $task->module->name ?? '—' }}</td>
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
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ $task->assignees->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td class="px-6 py-3 text-gray-500 dark:text-gray-400">{{ $task->due_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            @php $_canEdit = auth()->user()->hasRole('super-admin'); @endphp
                            <div x-data="{ open: false, style: '' }" @click.away="open = false" class="relative inline-block">
                                <button type="button" @click="
                                    open = !open;
                                    if (open) { $nextTick(() => { const r = $el.getBoundingClientRect(); style = 'position:fixed;left:' + r.left + 'px;top:' + (r.bottom + 4) + 'px;z-index:50'; }); }
                                " @keydown.escape.prevent="open = false" class="inline-flex items-center justify-center w-9 h-9 rounded-xl transition-all focus:outline-none focus:ring-2 focus:ring-indigo-500/40 text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-white bg-gray-50 dark:bg-gray-800/50 hover:bg-gray-100 dark:hover:bg-gray-700/50" aria-haspopup="true" :aria-expanded="open.toString()" aria-label="Task actions" title="Task actions">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="5" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="12" cy="19" r="1.5"/></svg>
                                </button>
                                <div x-show="open" :style="style" x-cloak role="menu" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" class="bg-gray-50 dark:bg-black rounded-xl shadow-lg border border-gray-200 dark:border-gray-700 py-1 w-36">
                                    <a href="{{ route('tasks.show', $task->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">View Details</a>
                                    @if($_canEdit)
                                    <a href="{{ route('tasks.edit', $task->id) }}" class="block px-3 py-2 text-sm text-gray-700 dark:text-white hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500/40" role="menuitem">Edit</a>
                                    <form method="POST" action="{{ route('tasks.destroy', $task->id) }}" class="block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" data-confirm="Are you sure?" x-on:click="startLoading($el)" class="w-full text-left px-3 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors focus:outline-none focus:ring-2 focus:ring-inset focus:ring-red-500/40" role="menuitem">Delete</button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="8" icon="clipboard" title="No tasks found." message="Create tasks to track your work." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-4">{{ $tasks->links() }}</div>
</div>
@endsection

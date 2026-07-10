<x-card variant="glass" hover class="rounded-2xl">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Tasks</h2>
    </div>

    @if ($tasks['total_tasks'] > 0)
    <div class="grid grid-cols-2 max-sm:grid-cols-1 gap-2 mb-3">
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $tasks['total_tasks'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold {{ $tasks['overdue_tasks'] > 0 ? 'text-rose-600' : 'text-gray-900 dark:text-white' }}">{{ $tasks['overdue_tasks'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Overdue</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-emerald-600">{{ $tasks['due_this_week'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Due Week</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-indigo-600">{{ $tasks['my_pending'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">My Pend.</p>
        </div>
    </div>

    @if ($tasks['tasks_by_status']->sum() > 0)
    <div class="mt-2">
        <canvas id="tasksStatusChart"
            data-labels='{{ json_encode($tasks['tasks_by_status']->keys()) }}'
            data-values='{{ json_encode($tasks['tasks_by_status']->values()) }}'
            height="180">
        </canvas>
    </div>
    @endif
    @else
    <div class="flex flex-col items-center justify-center py-8 text-center">
        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
        <p class="text-sm text-gray-400 dark:text-gray-500">No tasks yet. Create tasks to track work items and meet deadlines.</p>
        <a href="{{ route('tasks.create') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-500 hover:text-indigo-600 font-medium">+ Create Task</a>
    </div>
    @endif

    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-end text-xs">
        <a href="{{ route('reports.category', 'tasks') }}" class="text-indigo-500 hover:text-indigo-600 font-medium hover:underline">View Full Report &rarr;</a>
    </div>
</x-card>

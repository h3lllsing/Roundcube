<x-card variant="glass" hover class="rounded-2xl">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Recent Activity</h2>
    </div>

    @if ($activity['activities']->isNotEmpty())
    <div class="divide-y divide-gray-100 dark:divide-gray-700/50 -mx-5">
        @foreach ($activity['activities'] as $entry)
        @php
            $routeName = $activity['route_map'][$entry['subject_type']] ?? null;
            $hasLink = $routeName && $routeName !== '#' && $entry['subject_id'];
        @endphp
        <a href="{{ $hasLink ? route($routeName, $entry['subject_id']) : '#' }}" class="px-5 py-3 flex items-center gap-3 text-sm row-hover transition-colors {{ $hasLink ? '' : 'pointer-events-none' }}">
            <span class="w-7 h-7 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5 text-indigo-500" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </span>
            <span class="text-gray-400 dark:text-gray-500 text-xs font-medium shrink-0 min-w-[60px]">{{ $entry['causer_name'] }}</span>
            <span class="text-gray-700 dark:text-gray-300 flex-1 truncate">{{ $entry['description'] }}</span>
            <span class="text-[11px] text-gray-400 dark:text-gray-500 shrink-0">{{ $entry['created_at'] }}</span>
        </a>
        @endforeach
    </div>
    @else
    <div class="text-center text-sm text-gray-400 dark:text-gray-500 py-10">
        No recent activity. Activity is logged automatically when records are created, updated, or deleted.
    </div>
    @endif
</x-card>

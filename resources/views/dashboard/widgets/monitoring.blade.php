<x-card variant="glass" hover class="rounded-2xl">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Monitoring</h2>
    </div>

    <div class="grid grid-cols-2 max-sm:grid-cols-1 gap-3 mb-3">
        <x-stat-card label="Monitored" :value="$monitoring['total_monitored']" icon="server" color="blue" />
        <x-stat-card label="Online" :value="$monitoring['online']" icon="check" color="emerald" />
        <x-stat-card label="Offline" :value="$monitoring['offline']" icon="x" color="red" />
        <x-stat-card label="Unchecked" :value="$monitoring['unchecked']" icon="clock" color="amber" />
    </div>

    @if(!empty($monitoring['offline_items']))
    <div class="mb-3 pt-2 border-t border-gray-100 dark:border-gray-700/50">
        <p class="text-xs font-semibold text-red-600 dark:text-red-400 mb-1.5">Offline Services (top 5)</p>
        @foreach($monitoring['offline_items'] as $item)
        <a href="{{ route($item->route, $item->id) }}" class="flex items-center justify-between py-1 text-xs hover:bg-red-50 dark:hover:bg-red-900/10 -mx-1 px-1 rounded transition-colors">
            <span class="text-gray-700 dark:text-gray-300 truncate">{{ $item->type }}: {{ $item->name }}</span>
            <span class="text-gray-400 dark:text-gray-500 shrink-0 ml-2">{{ $item->last_ping_at->diffForHumans() }}</span>
        </a>
        @endforeach
    </div>
    @endif

    <div class="flex items-center justify-end text-xs pt-3 border-t border-gray-100 dark:border-gray-700/50">
        <a href="{{ route('monitoring.index') }}" class="text-indigo-500 hover:text-indigo-600 font-medium hover:underline">View All &rarr;</a>
    </div>
</x-card>

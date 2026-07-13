<x-card variant="glass" hover class="rounded-2xl">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-sky-500 to-blue-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Asset Summary</h2>
    </div>

    @if ($assets['total_assets'] > 0)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <x-stat-card label="Total" :value="$assets['total_assets']" icon="server" color="sky" />
        <x-stat-card label="Assigned Today" :value="$assets['assigned_today']" icon="tasks" color="emerald" />
        <x-stat-card label="Returned Today" :value="$assets['returned_today']" icon="tasks" color="amber" />
        <x-stat-card label="Available" :value="$assets['assets_by_status']['available'] ?? 0" icon="server" color="emerald" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Status Breakdown</p>
            <canvas id="assetsStatusChart"
                data-labels='{{ json_encode($assets['assets_by_status']->keys()) }}'
                data-values='{{ json_encode($assets['assets_by_status']->values()) }}'
                height="180">
            </canvas>
        </div>
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Recent Assignments</p>
            @if ($assets['recent_assignments']->isNotEmpty())
            <ul class="space-y-1.5">
                @foreach ($assets['recent_assignments'] as $a)
                <li class="text-xs text-gray-500 dark:text-gray-400 flex justify-between items-center py-1.5 px-2 rounded-lg bg-gray-50 dark:bg-black/50">
                    <span class="truncate font-medium">{{ $a['asset_tag'] }}</span>
                    <span class="shrink-0 ml-2 text-gray-400">{{ $a['assignee'] }} &middot; {{ $a['assigned_at'] }}</span>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-xs text-gray-400 dark:text-gray-500 py-4 text-center">No recent assignments.</p>
            @endif
        </div>
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-8 text-center">
        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
        <p class="text-sm text-gray-400 dark:text-gray-500">No assets registered. Add laptops, headphones, mice, and network devices to track your IT inventory.</p>
        <a href="{{ route('assets.create') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-500 hover:text-indigo-600 font-medium">+ Add Asset</a>
    </div>
    @endif

    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-end text-xs">
        <a href="{{ route('reports.category', 'assets') }}" class="text-indigo-500 hover:text-indigo-600 font-medium hover:underline">View Full Report &rarr;</a>
    </div>
</x-card>

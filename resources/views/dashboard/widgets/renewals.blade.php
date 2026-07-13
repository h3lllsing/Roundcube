<x-card variant="glass" hover class="rounded-2xl">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Renewal Summary</h2>
    </div>

    @if ($renewals['total_trackers'] > 0)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <x-stat-card label="Trackers" :value="$renewals['total_trackers']" icon="clock" color="amber" />
        <x-stat-card label="Manual Today" :value="$renewals['manual_sends_today']" icon="tasks" color="emerald" />
        <x-stat-card label="Auto Today" :value="$renewals['automatic_sends_today']" icon="tasks" color="sky" />
        <x-stat-card label="Failed Today" :value="$renewals['failed_today']" icon="bell" color="rose" />
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-2">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Upcoming (30d)</p>
            @if ($renewals['upcoming_renewals']->isNotEmpty())
            <ul class="space-y-1.5">
                @foreach ($renewals['upcoming_renewals'] as $r)
                <li class="text-xs text-gray-500 dark:text-gray-400 flex justify-between items-center py-1.5 px-2 rounded-lg bg-gray-50 dark:bg-black/50">
                    <span class="truncate font-medium">{{ $r['name'] }}</span>
                    <span class="shrink-0 ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $r['days_left'] <= 7 ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400' }} text-[10px] font-semibold">{{ $r['expiry_date'] }} ({{ $r['days_left'] }}d)</span>
                </li>
                @endforeach
            </ul>
            @else
            <p class="text-xs text-gray-400 dark:text-gray-500 py-4 text-center">No renewals due in the next 30 days.</p>
            @endif
        </div>
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">30-Day Stats</p>
            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1.5 px-2 rounded-lg bg-gray-50 dark:bg-black/50">
                    <span class="text-gray-500 dark:text-gray-400">Sent</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $renewals['notifications_sent_30d'] }}</span>
                </div>
                <div class="flex justify-between py-1.5 px-2 rounded-lg bg-gray-50 dark:bg-black/50">
                    <span class="text-gray-500 dark:text-gray-400">Failed</span>
                    <span class="font-semibold {{ $renewals['notifications_failed_30d'] > 0 ? 'text-rose-600' : 'text-gray-700 dark:text-gray-300' }}">{{ $renewals['notifications_failed_30d'] }}</span>
                </div>
                @if ($renewals['total_smtp_profiles'] !== null)
                <div class="flex justify-between py-1.5 px-2 rounded-lg bg-gray-50 dark:bg-black/50">
                    <span class="text-gray-500 dark:text-gray-400">SMTP Profiles</span>
                    <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $renewals['total_smtp_profiles'] }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    @if ($renewals['renewals_expiry_chart']->isNotEmpty())
    <div class="mt-4 pt-4 border-t border-gray-100 dark:border-gray-700/50">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Expiry Forecast (6 months)</p>
        <canvas id="renewalsExpiryChart"
            data-labels='{{ json_encode($renewals['renewals_expiry_chart']->keys()) }}'
            data-values='{{ json_encode($renewals['renewals_expiry_chart']->values()) }}'
            height="180">
        </canvas>
    </div>
    @endif
    @else
    <div class="flex flex-col items-center justify-center py-8 text-center">
        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-gray-400 dark:text-gray-500">No renewal trackers found. Trackers help you never miss an expiry date.</p>
        <a href="{{ route('expiry-trackers.create') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-500 hover:text-indigo-600 font-medium">+ Add Tracker</a>
    </div>
    @endif

    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-end text-xs">
        <a href="{{ route('reports.category', 'renewals') }}" class="text-indigo-500 hover:text-indigo-600 font-medium hover:underline">View Full Report &rarr;</a>
    </div>
</x-card>

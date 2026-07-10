<x-card variant="glass" hover class="rounded-2xl">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">SMTP Profiles</h2>
    </div>

    @if ($smtp['total_profiles'] > 0)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <x-stat-card label="Total" :value="$smtp['total_profiles']" icon="server" color="rose" />
        <x-stat-card label="Active" :value="$smtp['active_profiles']" icon="tasks" color="emerald" />
        <x-stat-card label="Failed Tests" :value="$smtp['failed_profiles']" icon="bell" color="rose" />
        <x-stat-card label="In Use" :value="$smtp['usage_count']" icon="clock" color="amber" />
    </div>

    @if ($smtp['profile_statuses']->isNotEmpty())
    <div class="mt-2">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Active Profile Statuses</p>
        <ul class="space-y-1.5">
            @foreach ($smtp['profile_statuses'] as $p)
            <li class="text-xs text-gray-500 dark:text-gray-400 flex justify-between items-center py-1.5 px-2 rounded-lg bg-gray-50 dark:bg-black/50">
                <span class="truncate font-medium">{{ $p['name'] }}</span>
                <span class="shrink-0 ml-2 inline-flex items-center gap-1 px-2 py-0.5 rounded-full {{ $p['last_test_status'] === 'success' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : ($p['last_test_status'] === 'failed' ? 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' : 'bg-gray-100 dark:bg-gray-800 text-gray-500') }} text-[10px] font-semibold">
                    {{ $p['last_test_status'] ?? 'untested' }}
                </span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
    @else
    <div class="flex flex-col items-center justify-center py-8 text-center">
        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        <p class="text-sm text-gray-400 dark:text-gray-500">No SMTP profiles configured. Add an SMTP profile to enable email notifications for renewals.</p>
        <a href="{{ route('smtp-profiles.create') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-500 hover:text-indigo-600 font-medium">+ Add Profile</a>
    </div>
    @endif
</x-card>

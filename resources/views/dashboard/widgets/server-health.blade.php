<x-card variant="glass" hover class="rounded-2xl">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-gray-500 to-slate-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Server Health</h2>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">PHP</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['php_version'] }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Laravel</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['laravel_version'] }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">App</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['app_version'] }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Cache</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['cache_driver'] }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Session</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['session_driver'] }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Queue</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['queue_driver'] }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Database</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['db_connection'] }} <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-full text-[10px] {{ $server_health['db_status'] === 'Connected' ? 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-400' : 'bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400' }}">{{ $server_health['db_status'] }}</span></p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Mail</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['mail_status'] }}</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Disk Use</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['disk_used_pct'] }}% <span class="text-[11px] text-gray-400 font-normal">({{ $server_health['disk_free'] }} / {{ $server_health['disk_total'] }})</span></p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3">
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Scheduler</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $server_health['scheduler_last_run'] }}</p>
        </div>
    </div>
</x-card>

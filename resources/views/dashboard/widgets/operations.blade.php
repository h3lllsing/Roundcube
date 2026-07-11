<x-card variant="glass" hover class="rounded-2xl xl:col-span-2">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Operations Summary</h2>
    </div>

    @if ($operations['total_active_services'] > 0 || $operations['active_providers'] > 0)
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">
        <x-stat-card label="Active Services" :value="$operations['total_active_services']" icon="server" color="violet" />
        <x-stat-card label="Monthly Cost" :value="'$' . number_format($operations['total_monthly_cost'], 0)" icon="dollar" color="amber" />
        <x-stat-card label="Expiring (30d)" :value="$operations['services_expiring_30d']" icon="clock" color="rose" />
        <x-stat-card label="Providers" :value="$operations['active_providers']" icon="users" color="sky" />
    </div>

    @if (!empty($operations['services_by_type_chart']))
    <div class="mt-2">
        <canvas id="servicesTypeChart"
            data-labels='{{ json_encode(array_keys($operations['services_by_type_chart'])) }}'
            data-values='{{ json_encode(array_values($operations['services_by_type_chart'])) }}'
            height="200">
        </canvas>
    </div>
    @endif


    @else
    <div class="flex flex-col items-center justify-center py-8 text-center">
        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
        <p class="text-sm text-gray-400 dark:text-gray-500">No services configured yet. Create your first domain or hosting record to get started.</p>
        <a href="{{ route('domains.create') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-500 hover:text-indigo-600 font-medium">+ Add Domain</a>
    </div>
    @endif

    <div class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700/50 flex items-center justify-end text-xs">
        <a href="{{ route('reports.category', 'domains') }}" class="text-indigo-500 hover:text-indigo-600 font-medium hover:underline">View Full Report &rarr;</a>
    </div>
</x-card>

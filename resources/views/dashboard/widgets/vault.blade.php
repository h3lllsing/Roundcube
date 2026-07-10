<x-card variant="glass" hover class="rounded-2xl">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Vault Summary</h2>
    </div>

    @if ($vault['total_entries'] > 0)
    <div class="grid grid-cols-2 max-sm:grid-cols-1 gap-2 mb-3">
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-indigo-600">{{ $vault['total_entries'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-amber-600">{{ $vault['revealed_today'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Revealed Today</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-gray-900 dark:text-white">{{ $vault['my_entries'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">My Entries</p>
        </div>
        <div class="bg-gray-50 dark:bg-black/50 rounded-xl p-3 text-center">
            <p class="text-lg font-bold text-rose-600">{{ $vault['total_reveals_30d'] }}</p>
            <p class="text-[10px] text-gray-500 dark:text-gray-400 uppercase tracking-wider">Reveals (30d)</p>
        </div>
    </div>

    @if ($vault['recent_reveals']->isNotEmpty())
    <div class="mt-2">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-2">Recent Reveals</p>
        <ul class="space-y-1.5">
            @foreach ($vault['recent_reveals'] as $r)
            <li class="text-xs text-gray-500 dark:text-gray-400 flex justify-between items-center py-1.5 px-2 rounded-lg bg-gray-50 dark:bg-black/50">
                <span class="truncate font-medium">{{ $r['causer'] }}</span>
                <span class="shrink-0 ml-2 text-gray-400">{{ $r['created_at'] }}</span>
            </li>
            @endforeach
        </ul>
    </div>
    @endif
    @else
    <div class="flex flex-col items-center justify-center py-8 text-center">
        <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <p class="text-sm text-gray-400 dark:text-gray-500">No credentials saved in the vault. Store passwords, API keys, and other secrets securely.</p>
        <a href="{{ route('vault.create') }}" class="mt-3 inline-flex items-center gap-1 text-xs text-indigo-500 hover:text-indigo-600 font-medium">+ Add Credential</a>
    </div>
    @endif
</x-card>

<x-card variant="glass" hover class="rounded-2xl">
    <div class="flex items-center gap-2 mb-4">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
        </div>
        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Quick Actions</h2>
    </div>

    @php
        $hasAny = $quick_actions['can_manage_system']
            || $quick_actions['can_create_task']
            || $quick_actions['can_create_domain']
            || $quick_actions['can_create_hosting']
            || $quick_actions['can_create_vps']
            || $quick_actions['can_create_voip']
            || $quick_actions['can_create_vault']
            || $quick_actions['can_create_asset'];
    @endphp

    @if ($hasAny)
    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
        @if($quick_actions['can_manage_system'])
        <a href="{{ route('features.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 text-indigo-700 dark:text-indigo-300 rounded-xl text-sm font-medium hover:from-indigo-100 hover:to-purple-100 dark:hover:from-indigo-900/30 dark:hover:to-purple-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <span>Feature</span>
        </a>
        <a href="{{ route('modules.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 text-indigo-700 dark:text-indigo-300 rounded-xl text-sm font-medium hover:from-indigo-100 hover:to-purple-100 dark:hover:from-indigo-900/30 dark:hover:to-purple-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
            <span>Module</span>
        </a>
        <a href="{{ route('users.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-sky-50 to-blue-50 dark:from-sky-900/20 dark:to-blue-900/20 text-sky-700 dark:text-sky-300 rounded-xl text-sm font-medium hover:from-sky-100 hover:to-blue-100 dark:hover:from-sky-900/30 dark:hover:to-blue-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-sky-500 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M7 20.75a7 7 0 0110 0"/></svg>
            <span>User</span>
        </a>
        @endif
        @if($quick_actions['can_create_task'])
        <a href="{{ route('tasks.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm font-medium hover:from-emerald-100 hover:to-teal-100 dark:hover:from-emerald-900/30 dark:hover:to-teal-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
            <span>Task</span>
        </a>
        @endif
        @if($quick_actions['can_create_domain'])
        <a href="{{ route('domains.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-sky-50 to-blue-50 dark:from-sky-900/20 dark:to-blue-900/20 text-sky-700 dark:text-sky-300 rounded-xl text-sm font-medium hover:from-sky-100 hover:to-blue-100 dark:hover:from-sky-900/30 dark:hover:to-blue-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-sky-500 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
            <span>Domain</span>
        </a>
        @endif
        @if($quick_actions['can_create_hosting'])
        <a href="{{ route('hostings.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 text-violet-700 dark:text-violet-300 rounded-xl text-sm font-medium hover:from-violet-100 hover:to-purple-100 dark:hover:from-violet-900/30 dark:hover:to-purple-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
            <span>Hosting</span>
        </a>
        @endif
        @if($quick_actions['can_create_vps'])
        <a href="{{ route('vps.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 text-violet-700 dark:text-violet-300 rounded-xl text-sm font-medium hover:from-violet-100 hover:to-purple-100 dark:hover:from-violet-900/30 dark:hover:to-purple-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
            <span>VPS</span>
        </a>
        @endif
        @if($quick_actions['can_create_voip'])
        <a href="{{ route('voip.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 text-violet-700 dark:text-violet-300 rounded-xl text-sm font-medium hover:from-violet-100 hover:to-purple-100 dark:hover:from-violet-900/30 dark:hover:to-purple-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span>VoIP</span>
        </a>
        @endif
        @if($quick_actions['can_create_vault'])
        <a href="{{ route('vault.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-amber-900/20 text-amber-700 dark:text-amber-300 rounded-xl text-sm font-medium hover:from-amber-100 hover:to-orange-100 dark:hover:from-amber-900/30 dark:hover:to-amber-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Vault Entry</span>
        </a>
        @endif
        @if($quick_actions['can_create_asset'])
        <a href="{{ route('assets.create') }}" class="flex items-center gap-2 px-3 py-2.5 bg-gradient-to-r from-sky-50 to-blue-50 dark:from-sky-900/20 dark:to-blue-900/20 text-sky-700 dark:text-sky-300 rounded-xl text-sm font-medium hover:from-sky-100 hover:to-blue-100 dark:hover:from-sky-900/30 dark:hover:to-blue-900/30 transition-all">
            <svg class="w-5 h-5 shrink-0 text-sky-500 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            <span>Asset</span>
        </a>
        @endif
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-6 text-center">
        <p class="text-sm text-gray-400 dark:text-gray-500">No quick actions available. Contact an administrator if you need additional permissions.</p>
    </div>
    @endif
</x-card>

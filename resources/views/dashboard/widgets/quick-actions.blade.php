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
    <div class="flex flex-wrap gap-2">
        @if($quick_actions['can_manage_system'])
        <a href="{{ route('features.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 text-indigo-700 dark:text-indigo-300 rounded-xl text-sm font-medium hover:from-indigo-100 hover:to-purple-100 dark:hover:from-indigo-900/30 dark:hover:to-purple-900/30 transition-all">+ Feature</a>
        <a href="{{ route('modules.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-indigo-50 to-purple-50 dark:from-indigo-900/20 dark:to-purple-900/20 text-indigo-700 dark:text-indigo-300 rounded-xl text-sm font-medium hover:from-indigo-100 hover:to-purple-100 dark:hover:from-indigo-900/30 dark:hover:to-purple-900/30 transition-all">+ Module</a>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-sky-50 to-blue-50 dark:from-sky-900/20 dark:to-blue-900/20 text-sky-700 dark:text-sky-300 rounded-xl text-sm font-medium hover:from-sky-100 hover:to-blue-100 dark:hover:from-sky-900/30 dark:hover:to-blue-900/30 transition-all">+ User</a>
        @endif
        @if($quick_actions['can_create_task'])
        <a href="{{ route('tasks.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-emerald-50 to-teal-50 dark:from-emerald-900/20 dark:to-teal-900/20 text-emerald-700 dark:text-emerald-300 rounded-xl text-sm font-medium hover:from-emerald-100 hover:to-teal-100 dark:hover:from-emerald-900/30 dark:hover:to-teal-900/30 transition-all">+ Task</a>
        @endif
        @if($quick_actions['can_create_domain'])
        <a href="{{ route('domains.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-sky-50 to-blue-50 dark:from-sky-900/20 dark:to-blue-900/20 text-sky-700 dark:text-sky-300 rounded-xl text-sm font-medium hover:from-sky-100 hover:to-blue-100 dark:hover:from-sky-900/30 dark:hover:to-blue-900/30 transition-all">+ Domain</a>
        @endif
        @if($quick_actions['can_create_hosting'])
        <a href="{{ route('hostings.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 text-violet-700 dark:text-violet-300 rounded-xl text-sm font-medium hover:from-violet-100 hover:to-purple-100 dark:hover:from-violet-900/30 dark:hover:to-purple-900/30 transition-all">+ Hosting</a>
        @endif
        @if($quick_actions['can_create_vps'])
        <a href="{{ route('vps.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 text-violet-700 dark:text-violet-300 rounded-xl text-sm font-medium hover:from-violet-100 hover:to-purple-100 dark:hover:from-violet-900/30 dark:hover:to-purple-900/30 transition-all">+ VPS</a>
        @endif
        @if($quick_actions['can_create_voip'])
        <a href="{{ route('voip.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-violet-50 to-purple-50 dark:from-violet-900/20 dark:to-purple-900/20 text-violet-700 dark:text-violet-300 rounded-xl text-sm font-medium hover:from-violet-100 hover:to-purple-100 dark:hover:from-violet-900/30 dark:hover:to-purple-900/30 transition-all">+ VoIP</a>
        @endif
        @if($quick_actions['can_create_vault'])
        <a href="{{ route('vault.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-amber-900/20 text-amber-700 dark:text-amber-300 rounded-xl text-sm font-medium hover:from-amber-100 hover:to-orange-100 dark:hover:from-amber-900/30 dark:hover:to-amber-900/30 transition-all">+ Vault Entry</a>
        @endif
        @if($quick_actions['can_create_asset'])
        <a href="{{ route('assets.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-sky-50 to-blue-50 dark:from-sky-900/20 dark:to-blue-900/20 text-sky-700 dark:text-sky-300 rounded-xl text-sm font-medium hover:from-sky-100 hover:to-blue-100 dark:hover:from-sky-900/30 dark:hover:to-blue-900/30 transition-all">+ Asset</a>
        @endif
    </div>
    @else
    <div class="flex flex-col items-center justify-center py-6 text-center">
        <p class="text-sm text-gray-400 dark:text-gray-500">No quick actions available. Contact an administrator if you need additional permissions.</p>
    </div>
    @endif
</x-card>

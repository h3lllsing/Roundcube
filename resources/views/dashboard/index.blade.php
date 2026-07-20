@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="w-full fade-in-up" x-data="{ ready: false }" x-init="setTimeout(() => ready = true, 120)">
    <x-page-header title="Dashboard" subtitle="Overview">
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        @if(isset($total_users))
        <div class="rounded-xl bg-gradient-to-br from-indigo-500/10 to-purple-500/5 dark:from-indigo-500/15 dark:to-purple-500/5 border border-indigo-200/50 dark:border-indigo-800/30 p-4" x-show="ready" x-cloak>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Users</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_users }}</p>
        </div>
        @endif
        <div class="rounded-xl bg-gradient-to-br from-amber-500/10 to-orange-500/5 dark:from-amber-500/15 dark:to-orange-500/5 border border-amber-200/50 dark:border-amber-800/30 p-4" x-show="ready" x-cloak>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Notifications</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_notifications }}</p>
        </div>
        @if(isset($total_email_accounts))
        <div class="rounded-xl bg-gradient-to-br from-green-500/10 to-emerald-500/5 dark:from-green-500/15 dark:to-emerald-500/5 border border-green-200/50 dark:border-green-800/30 p-4" x-show="ready" x-cloak>
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Email Accounts</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_email_accounts }}</p>
        </div>
        @endif
        <template x-if="!ready">
            <div class="col-span-full grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="rounded-xl bg-gray-100 dark:bg-gray-800/50 p-4 animate-pulse"><div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div><div class="h-7 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div></div>
                <div class="rounded-xl bg-gray-100 dark:bg-gray-800/50 p-4 animate-pulse"><div class="h-3 w-20 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div><div class="h-7 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div></div>
                @if(isset($total_email_accounts))
                <div class="rounded-xl bg-gray-100 dark:bg-gray-800/50 p-4 animate-pulse"><div class="h-3 w-24 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div><div class="h-7 w-12 bg-gray-200 dark:bg-gray-700 rounded"></div></div>
                @endif
            </div>
        </template>
    </div>

    <div x-show="ready" x-cloak>
    @if(isset($audit_actions) && count($audit_actions))
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Audit Actions (Last 7 Days)</h3>
        <div class="grid grid-cols-3 gap-4">
            <div class="text-center">
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $audit_actions['soft_delete'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Soft Deletes</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-purple-600 dark:text-purple-400">{{ $audit_actions['restored'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Restores</p>
            </div>
            <div class="text-center">
                <p class="text-2xl font-bold text-gray-600 dark:text-gray-400">{{ $audit_actions['force_delete'] ?? 0 }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Force Deletes</p>
            </div>
        </div>
    </div>
    @endif

    @if(isset($failed_imap_accounts) && $failed_imap_accounts > 0)
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-red-200 dark:border-red-800/30 p-5 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">IMAP Health</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">Accounts with errors in last 24h</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $failed_imap_accounts }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">of {{ $total_email_accounts ?? '—' }} total</p>
            </div>
        </div>
    </div>
    @elseif(isset($failed_imap_accounts))
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-green-200 dark:border-green-800/30 p-5 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">IMAP Health</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">All accounts healthy in last 24h</p>
            </div>
            <div class="text-right">
                <p class="text-2xl font-bold text-green-600 dark:text-green-400">0</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">of {{ $total_email_accounts ?? '—' }} total</p>
            </div>
        </div>
    </div>
    @endif

    @if(!empty($assigned_accounts))
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">My Email Accounts</h3>
            <a href="{{ route('webmail.index') }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Open Webmail →</a>
        </div>
        <div class="space-y-2">
            @foreach($assigned_accounts as $account)
            <a href="{{ route('webmail.open', $account) }}"
               class="flex items-center gap-3 p-2.5 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group">
                <span class="w-2 h-2 rounded-full {{ $account->status?->value === 'active' ? 'bg-green-500' : 'bg-red-500' }} shrink-0"></span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $account->email }}</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                        {{ $account->domain->name }}
                        @if($account->last_sync_at)
                        · last sync {{ $account->last_sync_at->diffForHumans() }}
                        @endif
                    </p>
                </div>
                <span class="text-xs text-indigo-600 dark:text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity">Open →</span>
            </a>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($active_domains))
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Active Domains ({{ count($active_domains) }})</h3>
        <div class="flex flex-wrap gap-2">
            @foreach($active_domains as $domain)
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 dark:bg-indigo-900/20 text-indigo-700 dark:text-indigo-300 text-xs font-medium border border-indigo-200/50 dark:border-indigo-800/30">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/></svg>
                {{ $domain->name }}
            </span>
            @endforeach
        </div>
    </div>
    @endif

    @if(!empty($recent_activity))
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Recent Activity</h3>
        <div class="space-y-2">
            @foreach($recent_activity as $log)
            <div class="flex items-center gap-3 text-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 shrink-0"></span>
                <span class="text-gray-600 dark:text-gray-400">{{ $log['description'] }}</span>
                <span class="ml-auto text-xs text-gray-400 dark:text-gray-500">{{ \Carbon\Carbon::parse($log['created_at'])->diffForHumans() }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif
    </div>

    <template x-if="!ready">
        <div class="space-y-4">
            <div class="bg-gray-100 dark:bg-gray-800/50 rounded-xl p-5 animate-pulse"><div class="h-4 w-36 bg-gray-200 dark:bg-gray-700 rounded mb-4"></div><div class="flex gap-4"><div class="flex-1 text-center"><div class="h-8 w-12 bg-gray-200 dark:bg-gray-700 rounded mx-auto mb-2"></div><div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded mx-auto"></div></div><div class="flex-1 text-center"><div class="h-8 w-12 bg-gray-200 dark:bg-gray-700 rounded mx-auto mb-2"></div><div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded mx-auto"></div></div><div class="flex-1 text-center"><div class="h-8 w-12 bg-gray-200 dark:bg-gray-700 rounded mx-auto mb-2"></div><div class="h-3 w-16 bg-gray-200 dark:bg-gray-700 rounded mx-auto"></div></div></div></div>
            <div class="bg-gray-100 dark:bg-gray-800/50 rounded-xl p-5 animate-pulse"><div class="h-4 w-28 bg-gray-200 dark:bg-gray-700 rounded mb-4"></div><div class="space-y-3"><div class="h-12 bg-gray-200 dark:bg-gray-700 rounded-lg"></div><div class="h-12 bg-gray-200 dark:bg-gray-700 rounded-lg"></div></div></div>
            <div class="bg-gray-100 dark:bg-gray-800/50 rounded-xl p-5 animate-pulse"><div class="h-4 w-32 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div><div class="flex flex-wrap gap-2"><div class="h-7 w-20 bg-gray-200 dark:bg-gray-700 rounded-lg"></div><div class="h-7 w-28 bg-gray-200 dark:bg-gray-700 rounded-lg"></div><div class="h-7 w-24 bg-gray-200 dark:bg-gray-700 rounded-lg"></div></div></div>
            <div class="bg-gray-100 dark:bg-gray-800/50 rounded-xl p-5 animate-pulse"><div class="h-4 w-28 bg-gray-200 dark:bg-gray-700 rounded mb-3"></div><div class="space-y-3"><div class="h-5 bg-gray-200 dark:bg-gray-700 rounded"></div><div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div><div class="h-5 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div></div></div>
        </div>
    </template>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="w-full fade-in-up">
    <x-page-header title="Dashboard" subtitle="Overview">
    </x-page-header>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="rounded-xl bg-gradient-to-br from-indigo-500/10 to-purple-500/5 dark:from-indigo-500/15 dark:to-purple-500/5 border border-indigo-200/50 dark:border-indigo-800/30 p-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Users</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_users ?? '—' }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-amber-500/10 to-orange-500/5 dark:from-amber-500/15 dark:to-orange-500/5 border border-amber-200/50 dark:border-amber-800/30 p-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Notifications</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_notifications }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-green-500/10 to-emerald-500/5 dark:from-green-500/15 dark:to-emerald-500/5 border border-green-200/50 dark:border-green-800/30 p-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Features</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_features ?? '—' }}</p>
        </div>
        <div class="rounded-xl bg-gradient-to-br from-violet-500/10 to-purple-500/5 dark:from-violet-500/15 dark:to-purple-500/5 border border-violet-200/50 dark:border-violet-800/30 p-4">
            <p class="text-xs font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Modules</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $total_modules ?? '—' }}</p>
        </div>
    </div>

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
@endsection

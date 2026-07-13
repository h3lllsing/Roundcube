@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="w-full fade-in-up">
    <x-page-header title="Dashboard" subtitle="{{ ['super-admin' => 'Enterprise Overview', 'admin' => 'Operations Overview', 'editor' => 'Support Overview', 'user' => 'My Services', 'customer' => 'My Dashboard'][$dashboardRole] ?? 'Overview' }}">
        <x-slot:actions>
            <button type="button" id="cmd-palette-trigger" class="hidden sm:inline-flex items-center gap-1.5 text-xs text-gray-400 dark:text-gray-500 bg-white/70 dark:bg-black/70 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700 hover:border-indigo-300 dark:hover:border-indigo-600 transition-colors cursor-pointer">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Cmd+K</span>
            </button>
            <span class="text-xs text-gray-400 dark:text-gray-500 bg-white/70 dark:bg-black/70 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                {{ now()->format('l, F j, Y') }}
            </span>
        </x-slot:actions>
    </x-page-header>

    @php
        $_kpi = (!empty($renewals) || !empty($monitoring) || !empty($tasks) || !empty($operations) || !empty($vault));
    @endphp

    @if($_kpi)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        @if(!empty($renewals))
        <div class="rounded-xl bg-gradient-to-br from-amber-500/10 to-orange-500/5 dark:from-amber-500/15 dark:to-orange-500/5 border border-amber-200/50 dark:border-amber-800/30 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Failed Today</p>
            <p class="text-xl font-bold {{ $renewals['failed_today'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">{{ $renewals['failed_today'] }}</p>
        </div>
        @endif
        @if(!empty($monitoring))
        <div class="rounded-xl bg-gradient-to-br from-rose-500/10 to-pink-500/5 dark:from-rose-500/15 dark:to-pink-500/5 border border-rose-200/50 dark:border-rose-800/30 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Offline</p>
            <p class="text-xl font-bold {{ $monitoring['offline'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">{{ $monitoring['offline'] }}</p>
        </div>
        @endif
        @if(!empty($tasks))
        <div class="rounded-xl bg-gradient-to-br from-rose-500/10 to-pink-500/5 dark:from-rose-500/15 dark:to-pink-500/5 border border-rose-200/50 dark:border-rose-800/30 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Overdue Tasks</p>
            <p class="text-xl font-bold {{ $tasks['overdue_tasks'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">{{ $tasks['overdue_tasks'] }}</p>
        </div>
        @endif
        @if(!empty($operations))
        <div class="rounded-xl bg-gradient-to-br from-violet-500/10 to-purple-500/5 dark:from-violet-500/15 dark:to-purple-500/5 border border-violet-200/50 dark:border-violet-800/30 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Active Services</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white">{{ $operations['total_active_services'] }}</p>
        </div>
        @endif
        @if(!empty($vault))
        <div class="rounded-xl bg-gradient-to-br from-indigo-500/10 to-purple-500/5 dark:from-indigo-500/15 dark:to-purple-500/5 border border-indigo-200/50 dark:border-indigo-800/30 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">Reveals 30d</p>
            <p class="text-xl font-bold {{ $vault['total_reveals_30d'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-gray-900 dark:text-white' }}">{{ $vault['total_reveals_30d'] }}</p>
        </div>
        @endif
        @if(!empty($monitoring))
        <div class="rounded-xl bg-gradient-to-br from-amber-500/10 to-yellow-500/5 dark:from-amber-500/15 dark:to-yellow-500/5 border border-amber-200/50 dark:border-amber-800/30 p-3.5">
            <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">SSL ≤30d</p>
            <p class="text-xl font-bold {{ $monitoring['ssl_expiring_30d'] > 0 ? 'text-rose-600 dark:text-rose-400' : 'text-gray-900 dark:text-white' }}">{{ $monitoring['ssl_expiring_30d'] }}</p>
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
        @if(!empty($renewals))
            @include('dashboard.widgets.renewals')
        @endif
        @if(!empty($monitoring))
            @include('dashboard.widgets.monitoring')
        @endif
        @if(!empty($tasks))
            @include('dashboard.widgets.tasks')
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        @if(!empty($operations))
            @include('dashboard.widgets.operations')
        @endif
        @if(!empty($assets))
            @include('dashboard.widgets.assets')
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        @if(!empty($vault))
            @include('dashboard.widgets.vault')
        @endif
        @if(!empty($quick_actions))
            @include('dashboard.widgets.quick-actions')
        @endif
    </div>

    @if(!empty($activity))
        @include('dashboard.widgets.activity')
    @endif

    @if(!empty($smtp))
        <details class="mt-6 group rounded-xl bg-white dark:bg-black border border-gray-200 dark:border-gray-700 overflow-hidden">
            <summary class="flex items-center gap-2 px-5 py-3 cursor-pointer list-none text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <svg class="w-4 h-4 text-rose-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span class="flex-1">SMTP Profiles</span>
                <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-5 pb-4">
                @include('dashboard.widgets.smtp')
            </div>
        </details>
    @endif

    @if(!empty($server_health))
        <details class="mt-4 group rounded-xl bg-white dark:bg-black border border-gray-200 dark:border-gray-700 overflow-hidden">
            <summary class="flex items-center gap-2 px-5 py-3 cursor-pointer list-none text-sm font-semibold text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span class="flex-1">Server Health</span>
                <svg class="w-4 h-4 text-gray-400 group-open:rotate-180 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </summary>
            <div class="px-5 pb-4">
                @include('dashboard.widgets.server-health')
            </div>
        </details>
    @endif
</div>
@endsection
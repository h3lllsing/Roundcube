@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto fade-in-up">
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

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
        @if(!empty($renewals))
            @include('dashboard.widgets.renewals')
        @endif

        @if(!empty($monitoring))
            @include('dashboard.widgets.monitoring')
        @endif

        @if(!empty($tasks))
            @include('dashboard.widgets.tasks')
        @endif

        @if(!empty($vault))
            @include('dashboard.widgets.vault')
        @endif

        @if(!empty($operations))
            @include('dashboard.widgets.operations')
        @endif

        @if(!empty($assets))
            @include('dashboard.widgets.assets')
        @endif
    </div>

    @if(!empty($quick_actions))
        @include('dashboard.widgets.quick-actions')
    @endif

    @if(!empty($activity))
        <div class="mb-8">
            @include('dashboard.widgets.activity')
        </div>
    @endif

    @if(!empty($smtp))
        <details class="mb-8 group rounded-xl bg-white dark:bg-black border border-gray-200 dark:border-gray-700 overflow-hidden">
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
        <details class="mb-8 group rounded-xl bg-white dark:bg-black border border-gray-200 dark:border-gray-700 overflow-hidden">
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

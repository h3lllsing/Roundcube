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
        @if(!empty($operations))
            @include('dashboard.widgets.operations')
        @endif

        @if(!empty($renewals))
            @include('dashboard.widgets.renewals')
        @endif

        @if(!empty($tasks))
            @include('dashboard.widgets.tasks')
        @endif

        @if(!empty($assets))
            @include('dashboard.widgets.assets')
        @endif

        @if(!empty($vault))
            @include('dashboard.widgets.vault')
        @endif

        @if(!empty($monitoring))
            @include('dashboard.widgets.monitoring')
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
        <div class="mb-8">
            @include('dashboard.widgets.smtp')
        </div>
    @endif

    @if(!empty($server_health))
        <div class="mb-8">
            @include('dashboard.widgets.server-health')
        </div>
    @endif
</div>
@endsection

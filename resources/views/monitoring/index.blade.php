@extends('layouts.admin')

@section('title', 'Monitoring Overview')

@section('content')
<div class="max-w-7xl mx-auto fade-in-up">
    <x-page-header title="Monitoring Overview" subtitle="Service uptime monitoring">
    </x-page-header>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-8 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-400 dark:text-gray-500 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-300 mb-1">Monitoring</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400">Monitoring will be available after configuring email services.</p>
    </div>
</div>
@endsection

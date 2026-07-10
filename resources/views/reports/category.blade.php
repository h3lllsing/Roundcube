@extends('layouts.admin')

@section('title', $provider['label'] . ' Reports')

@section('content')
<div class="max-w-5xl mx-auto">
    <x-page-header :title="$provider['label'] . ' Reports'" :subtitle="$provider['description']">
        <x-slot:actions>
            <x-button href="{{ route('reports.index') }}" variant="outline" size="sm">
                &larr; All Categories
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach ($reports as $report)
        <a href="{{ route('reports.show', [$provider['slug'], $report['slug']]) }}"
            class="rounded-2xl p-5 card-hover group block">
            <x-card variant="glass" padding="none" class="rounded-2xl p-5 card-hover">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-4 h-4 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $provider['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $report['label'] }}</h3>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ $report['description'] }}</p>
            <div class="flex items-center justify-between">
                <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ count($report['columns']) }} columns</span>
                <span class="text-xs text-indigo-500 group-hover:translate-x-0.5 transition-transform">View Report &rarr;</span>
            </div>
            </x-card>
        </a>
        @endforeach
    </div>
</div>
@endsection

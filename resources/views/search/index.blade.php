@extends('layouts.admin')

@section('title', 'Search Results')

@php
    $filters = [
        'all' => 'All',
        'services' => 'Services',
        'assets' => 'Assets',
        'tasks' => 'Tasks',
        'vault' => 'Vault',
        'users' => 'Users',
    ];
@endphp

@section('content')
<div class="max-w-7xl mx-auto fade-in-up">
    <x-page-header title="Search" subtitle="Find items across all modules." />

    <form method="GET" action="{{ route('search') }}" class="mb-6">
        <div class="flex gap-3">
            <input type="text" name="q" value="{{ $q }}" placeholder="Search domains, assets, tasks, vault..."
                class="flex-1 px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none"
                autofocus>
            <x-button type="submit" variant="primary" size="md">Search</x-button>
            @if ($q)
            <a href="{{ route('search') }}" class="inline-flex items-center px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors">Clear</a>
            @endif
        </div>

        <div class="flex flex-wrap gap-2 mt-3">
            @foreach ($filters as $key => $label)
            <a href="{{ route('search', array_filter(['q' => $q, 'filter' => $key !== 'all' ? $key : null])) }}"
                class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                {{ ($filter ?? 'all') === $key
                    ? 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800'
                    : 'bg-gray-100 dark:bg-gray-800/50 text-gray-600 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700/50 border border-transparent' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>
    </form>

    @if ($q && strlen(trim($q)) < 2)
        <div class="text-center py-12">
            <p class="text-sm text-gray-500">Please enter at least 2 characters.</p>
        </div>
    @elseif ($q && empty($results))
        <div class="text-center py-12">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">No results found for "<strong>{{ $q }}</strong>".</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try different keywords or check your spelling.</p>
        </div>
    @elseif (empty($results) && !$q)
        <div class="text-center py-12">
            <svg class="w-12 h-12 mx-auto text-gray-300 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-sm text-gray-500 dark:text-gray-400">Enter a search term to find items across all modules.</p>
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Try searching for a domain name, asset tag, task title, or vault credential.</p>
        </div>
    @else
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
            Found results for "<strong>{{ $q }}</strong>"
            @if($filter !== 'all')
                in <strong>{{ $filters[$filter] ?? $filter }}</strong>
            @endif
        </p>

        <div class="space-y-6">
            @foreach ($results as $key => $group)
                <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
                    <div class="px-5 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-gray-700 dark:text-gray-300">{{ $group['label'] }}</h2>
                        <a href="{{ route($group['index_route']) }}?search={{ urlencode($q) }}" class="text-xs text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium">View all →</a>
                    </div>
                    <div class="divide-y divide-gray-100 dark:divide-gray-700/50">
                        @foreach ($group['items'] as $item)
                            <a href="{{ $item['url'] }}" class="flex items-center gap-3 px-5 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/30 transition-colors text-sm">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $key === 'domains' ? 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/30 dark:text-cyan-300' : '' }}{{ $key === 'hostings' ? 'bg-teal-100 text-teal-700 dark:bg-teal-900/30 dark:text-teal-300' : '' }}{{ $key === 'vps' ? 'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' : '' }}{{ $key === 'voip' ? 'bg-violet-100 text-violet-700 dark:bg-violet-900/30 dark:text-violet-300' : '' }}{{ $key === 'domain_emails' ? 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' : '' }}{{ $key === 'other_services' ? 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-300' : '' }}{{ $key === 'service_providers' ? 'bg-orange-100 text-orange-700 dark:bg-orange-900/30 dark:text-orange-300' : '' }}{{ $key === 'expiry_trackers' ? 'bg-lime-100 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300' : '' }}{{ $key === 'assets' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}{{ $key === 'tasks' ? 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' : '' }}{{ $key === 'vault' ? 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' : '' }}{{ $key === 'notes' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' : '' }}{{ $key === 'users' ? 'bg-pink-100 text-pink-700 dark:bg-pink-900/30 dark:text-pink-300' : '' }}{{ $key === 'features' ? 'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' : '' }}{{ $key === 'modules' ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : '' }}{{ $key === 'smtp_profiles' ? 'bg-gray-100 text-gray-700 dark:bg-gray-800/50 dark:text-gray-300' : '' }}">
                                    {{ $group['label'] }}
                                </span>
                                <span class="flex-1 min-w-0">
                                    <span class="text-gray-900 dark:text-gray-100 font-medium">{!! strip_tags($item['title_highlighted'], '<mark>') !!}</span>
                                    @if ($item['subtitle'])
                                    <span class="text-gray-500 dark:text-gray-400 text-xs ml-2">{!! strip_tags($item['subtitle_highlighted'], '<mark>') !!}</span>
                                    @endif
                                </span>
                                @if ($item['badge'])
                                    <span @class([
                                        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium shrink-0',
                                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $item['badge'] === 'active' || $item['badge'] === '1',
                                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $item['badge'] === 'expired',
                                        'bg-gray-100 text-gray-700 dark:bg-black/30 dark:text-gray-300' => in_array($item['badge'], ['cancelled', 'completed', 'decommissioned']),
                                        'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' => $item['badge'] === 'pending',
                                        'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $item['badge'] === 'in_progress',
                                        'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' => $item['badge'] === 'pending_renewal',
                                        'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' => $item['badge'] === 'assigned',
                                        'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' => $item['badge'] === 'inactive' || $item['badge'] === '0',
                                        'bg-sky-100 text-sky-700 dark:bg-sky-900/30 dark:text-sky-300' => $item['badge'] === 'available',
                                    ])>{{ $item['badge'] }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection

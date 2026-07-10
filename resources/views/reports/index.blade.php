@extends('layouts.admin')

@section('title', 'Reports')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Reports" subtitle="Enterprise reporting center — drill into categories below." />

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 mb-8">
        @foreach ($categories as $slug => $cat)
        <a href="{{ route('reports.category', $slug) }}"
            class="rounded-2xl p-5 card-hover group block">
            <x-card variant="glass" padding="none" class="rounded-2xl p-5 card-hover">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-500/10 to-purple-500/10 dark:from-indigo-500/20 dark:to-purple-500/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cat['icon'] }}"/>
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors">{{ $cat['label'] }}</h3>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-3 line-clamp-2">{{ $cat['description'] }}</p>
            <div class="flex items-center justify-between">
                <span class="text-[10px] font-semibold uppercase tracking-widest text-gray-400 dark:text-gray-500">{{ $cat['report_count'] }} {{ Str::plural('report', $cat['report_count']) }}</span>
                <span class="text-xs text-indigo-500 group-hover:translate-x-0.5 transition-transform">View &rarr;</span>
            </div>
            </x-card>
        </a>
        @endforeach
    </div>

    <hr class="my-8 border-gray-200 dark:border-gray-700">

    <div class="mb-6">
        <h2 class="text-lg font-semibold mb-1">Cost & Activity Overview</h2>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">Aggregated metrics across all services.</p>

        <form method="GET" class="flex flex-wrap gap-3 mb-6">
            <select name="cost_status"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
                <option value="">All statuses</option>
                <option value="active" @selected(request('cost_status') === 'active')>Active</option>
                <option value="expired" @selected(request('cost_status') === 'expired')>Expired</option>
                <option value="cancelled" @selected(request('cost_status') === 'cancelled')>Cancelled</option>
            </select>
            <select name="user_id"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
                <option value="">All users</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
            <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To"
                class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
            <x-button type="submit" variant="primary" size="sm">Filter</x-button>
            @if(request()->anyFilled(['cost_status', 'date_from', 'date_to', 'user_id']))
                <x-button href="{{ route('reports.index') }}" variant="outline" size="sm">Clear</x-button>
            @endif
        </form>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Monthly Cost @if(request()->anyFilled(['cost_status', 'date_from', 'date_to']))<span class="text-xs text-gray-400 dark:text-gray-500">(filtered)</span>@endif</p>
                <p class="text-2xl font-bold mt-1">${{ number_format($totalMonthly, 2) }}</p>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Total Tasks</p>
                <p class="text-2xl font-bold mt-1">{{ $taskSummary['total'] }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $taskSummary['pending'] }} pending, {{ $taskSummary['in_progress'] }} in progress</p>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Tasks Completed</p>
                <p class="text-2xl font-bold mt-1 text-green-600 dark:text-green-400">{{ $taskSummary['completed'] }}</p>
            </div>
            <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <p class="text-sm text-gray-500 dark:text-gray-400">Login Attempts</p>
                <p class="text-2xl font-bold mt-1">{{ $loginSummary['total'] }}</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $loginSummary['successful'] }} success, {{ $loginSummary['failed'] }} failed</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold mb-4">Cost by Type</h3>
                <div class="space-y-3">
                    @foreach ($costByType as $key => $data)
                        <div class="flex items-center justify-between">
                            <span class="text-sm capitalize">{{ str_replace('_', ' ', $key) }}</span>
                            <div class="text-right">
                                <span class="text-sm font-medium">${{ number_format($data['total'], 2) }}</span>
                                <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">({{ $data['count'] }})</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
                <h3 class="text-sm font-semibold mb-4">Top 10 Costs</h3>
                <div class="space-y-2">
                    @foreach ($topCosts as $item)
                        <div class="flex items-center justify-between text-sm">
                            <div class="min-w-0 flex-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-gray-100 dark:bg-black text-gray-600 dark:text-gray-300 mr-1">{{ $item['type'] }}</span>
                                <span class="truncate">{{ $item['name'] }}</span>
                            </div>
                            <span class="font-medium shrink-0 ml-2">${{ number_format($item['cost'], 2) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

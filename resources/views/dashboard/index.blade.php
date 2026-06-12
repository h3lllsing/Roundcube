@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-semibold mb-6">Dashboard</h1>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <x-stat-card label="Features" :value="$totalFeatures ?? 0" />
        <x-stat-card label="Modules" :value="$totalModules ?? 0" />
        <x-stat-card label="Tasks" :value="$totalTasks" />
        <x-stat-card label="Notes" :value="$totalNotes" />
        <x-stat-card label="My Notes" :value="$myNotes" />
        <x-stat-card label="Services" :value="$totalServices" />
        <x-stat-card label="Expiring Soon" :value="$expiringSoon" />
        <x-stat-card label="Unread" :value="$unreadNotifications" />
        @if ($totalUsers !== null)
            <x-stat-card label="Users" :value="$totalUsers" />
        @endif
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Tasks by Status</h2>
            <div class="max-w-xs mx-auto">
                <canvas id="tasksStatusChart"
                    data-labels='{{ json_encode($tasksByStatus->keys()) }}'
                    data-values='{{ json_encode($tasksByStatus->values()) }}'
                    height="250">
                </canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
            <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Active Services by Type</h2>
            <canvas id="servicesTypeChart"
                data-labels='{{ json_encode(array_keys($servicesByType)) }}'
                data-values='{{ json_encode(array_values($servicesByType)) }}'
                height="250">
            </canvas>
        </div>
    </div>

    @if (!empty($upcomingExpiries))
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-8">
        <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Upcoming Expiries (next 30 days)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ($upcomingExpiries as $type => $items)
                <div>
                    <p class="text-xs font-semibold text-gray-600 dark:text-gray-300 mb-2">{{ $type }}</p>
                    <ul class="space-y-1">
                        @foreach ($items as $item)
                            <li class="text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                                <span class="truncate">{{ $item['name'] }}</span>
                                <span class="shrink-0 ml-2">{{ $item['expiry'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h2 class="text-lg font-medium">Recent Activity</h2>
        </div>
        <div class="divide-y divide-gray-200 dark:divide-gray-700">
            @forelse ($recentActivity as $activity)
                <div class="px-6 py-3 flex items-center gap-3 text-sm">
                    <span class="w-2 h-2 rounded-full bg-blue-500 shrink-0"></span>
                    <span class="text-gray-500 dark:text-gray-400 shrink-0">{{ $activity->causer?->getAttribute('name') ?? 'System' }}</span>
                    <span class="text-gray-700 dark:text-gray-300">{{ $activity->description }}</span>
                    <span class="ml-auto text-xs text-gray-400">{{ $activity->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <div class="px-6 py-8 text-center text-sm text-gray-400">No recent activity.</div>
            @endforelse
        </div>
    </div>
</div>
@endsection

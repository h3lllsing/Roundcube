@extends('layouts.admin')

@section('title', 'Calendar')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Calendar">
        <x-slot:actions>
            <x-button href="{{ route('calendar', ['month' => $prevMonth, 'year' => $prevYear]) }}" variant="outline" size="sm">&larr; Prev</x-button>
            <span class="text-sm font-medium px-3">{{ Carbon\Carbon::create($year, $month, 1)->format('F Y') }}</span>
            <x-button href="{{ route('calendar', ['month' => $nextMonth, 'year' => $nextYear]) }}" variant="outline" size="sm">Next &rarr;</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full mb-6">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 w-[14.285%]">Sun</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 w-[14.285%]">Mon</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 w-[14.285%]">Tue</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 w-[14.285%]">Wed</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 w-[14.285%]">Thu</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 w-[14.285%]">Fri</th>
                    <th class="px-4 py-3 text-center font-medium text-gray-500 dark:text-gray-400 w-[14.285%]">Sat</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($calendar as $week)
                    <tr class="border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                        @foreach ($week as $day)
                            <td class="px-2 py-3 align-top min-h-[100px] {{ $day === null ? 'bg-gray-50 dark:bg-black/50' : '' }}">
                                @if ($day !== null)
                                    @php
                                        $dateStr = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                        $dayEvents = $eventsByDate[$dateStr] ?? [];
                                    @endphp
                                    <div class="text-center text-sm font-medium mb-1 {{ count($dayEvents) > 0 ? 'text-indigo-600 dark:text-indigo-400' : 'text-gray-700 dark:text-gray-300' }}">{{ $day }}</div>
                                    @foreach ($dayEvents as $event)
                                        <a href="{{ route($event['type'] . '.show', $event['id']) }}" class="block text-xs px-1 py-0.5 mb-0.5 rounded truncate
                                            @if ($event['status'] === 'active') bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300
                                            @elseif ($event['status'] === 'expired') bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300
                                            @else bg-gray-100 dark:bg-black text-gray-600 dark:text-gray-400 @endif
                                        " title="{{ $event['name'] }} ({{ $event['type_label'] }})">
                                            {{ $event['name'] }}
                                        </a>
                                    @endforeach
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if (count($events) > 0)
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5">
        <h2 class="text-sm font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">All Events This Month</h2>
        <div class="space-y-2">
            @foreach ($events as $event)
                <a href="{{ route($event['type'] . '.show', $event['id']) }}" class="flex items-center justify-between text-sm py-2 px-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700/50 transition-colors {{ $event['status'] === 'active' ? 'bg-green-50 dark:bg-green-900/10' : ($event['status'] === 'expired' ? 'bg-red-50 dark:bg-red-900/10' : 'bg-gray-50 dark:bg-black/50') }}">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-medium text-gray-500 dark:text-gray-400 w-24">{{ $event['date'] }}</span>
                        <span class="text-gray-900 dark:text-gray-100">{{ $event['name'] }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400">({{ $event['type_label'] }})</span>
                    </div>
                    <span @class([
                        'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                        'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $event['status'] === 'active',
                        'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $event['status'] === 'expired',
                        'bg-gray-100 text-gray-700 dark:bg-black/30 dark:text-gray-300' => $event['status'] === 'cancelled',
                    ])>{{ $event['status'] }}</span>
                    </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection

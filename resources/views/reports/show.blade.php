@extends('layouts.admin')

@section('title', $label)

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header :title="$label" :subtitle="$description">
        <x-slot:actions>
            <x-button href="{{ route('reports.category', $provider->slug()) }}" variant="outline" size="sm">
                &larr; Back
            </x-button>
            @if(auth()->user()->hasRole('super-admin'))
            <x-button href="{{ route('reports.export', [$provider->slug(), $report['slug']]) }}{{ http_build_query(request()->only(['search', 'date_from', 'date_to', 'status', 'user_id'])) ? '?' . http_build_query(request()->only(['search', 'date_from', 'date_to', 'status', 'user_id'])) : '' }}"
                variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    <x-report-filter-bar
        :route="route('reports.show', [$provider->slug(), $report['slug']])"
        searchPlaceholder="Search {{ strtolower($label) }}..."
        :showSearch="true"
        :showDateRange="false"
        :showStatus="false"
    />

    @if($results instanceof \Illuminate\Support\Collection || is_array($results))
        @if(count($results) > 0)
        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                            @foreach($columns as $col)
                            <th class="px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400">{{ $col }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                        @foreach($results as $row)
                        <tr class="hover:bg-gray-50 dark:hover:bg-black/30 transition-colors">
                            @foreach($columns as $colIndex => $col)
                            <td class="px-4 py-3 text-sm text-gray-700 dark:text-gray-300">
                                @php
                                    $key = \Illuminate\Support\Str::of($col)->lower()->replace(' ', '_')->toString();
                                    $val = $row instanceof \Illuminate\Database\Eloquent\Model
                                        ? $row->getAttribute($key)
                                        : ($row->$key ?? ($row[$key] ?? ''));
                                @endphp
                                @if($val instanceof \Carbon\Carbon)
                                    {{ $val->format('M d, Y') }}
                                    @if(in_array($col, ['Expiry Date', 'Due Date']) && isset($row->status) && $row->status !== 'expired')
                                        <span class="text-xs text-gray-400 dark:text-gray-500 ml-1">
                                            ({{ now()->startOfDay()->diffInDays($val, false) > 0 ? now()->startOfDay()->diffInDays($val, false) . 'd left' : 'today' }})
                                        </span>
                                    @elseif(in_array($col, ['Expiry Date', 'Due Date']) && (isset($row->status) && $row->status === 'expired'))
                                        <span class="text-xs text-rose-500 ml-1">
                                            ({{ abs(now()->startOfDay()->diffInDays($val, false)) }}d overdue)
                                        </span>
                                    @endif
                                @elseif($key === 'cost' && is_numeric($val))
                                    ${{ number_format((float)$val, 2) }}
                                @elseif($key === 'auto_renew')
                                    @if($val)
                                        <span class="text-green-600 dark:text-green-400">Yes</span>
                                    @else
                                        <span class="text-gray-400">No</span>
                                    @endif
                                @elseif($key === 'status')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                        @if(in_array($val, ['active', 'available'])) bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300
                                        @elseif(in_array($val, ['expired', 'overdue', 'failed'])) bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300
                                        @elseif($val === 'pending_renewal') bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                                        @else bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400 @endif
                                    ">{{ str_replace('_', ' ', ucwords($val ?? '')) }}</span>
                                @elseif($key === 'priority')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                        @if($val === 'high') bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300
                                        @elseif($val === 'medium') bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                                        @else bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 @endif
                                    ">{{ ucwords($val ?? '') }}</span>
                                @elseif($key === 'days_left')
                                    @if($val > 0)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold
                                            @if($val <= 7) bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-300
                                            @elseif($val <= 14) bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300
                                            @else bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 @endif
                                        ">{{ $val }}d</span>
                                    @else
                                        <span class="text-rose-500 font-semibold">Overdue</span>
                                    @endif
                                @else
                                    {{ $val ?? '—' }}
                                @endif
                            </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-gray-200 dark:border-gray-700 text-xs text-gray-500 dark:text-gray-400">
                {{ count($results) }} {{ Str::plural('record', count($results)) }}
            </div>
        </div>
        @else
            <x-empty-state title="No results" message="No records found for this report with the current filters." />
        @endif
    @else
        <x-empty-state title="No results" message="No records found for this report." />
    @endif
</div>
@endsection

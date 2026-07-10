@php
$eventIcons = [
    'created' => '<svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
    'updated' => '<svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>',
    'deleted' => '<svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>',
    'restored' => '<svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>',
    'revealed' => '<svg class="w-4 h-4" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
];

$eventColors = [
    'created' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600 dark:text-emerald-400',
    'updated' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400',
    'deleted' => 'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400',
    'restored' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400',
    'revealed' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400',
];

$eventLabels = [
    'created' => 'Created',
    'updated' => 'Updated',
    'deleted' => 'Deleted',
    'restored' => 'Restored',
    'revealed' => 'Password Revealed',
];
@endphp

@php $formatVal = fn($v) => is_null($v) ? '—' : (is_bool($v) ? ($v ? 'Yes' : 'No') : (is_array($v) || is_object($v) ? json_encode($v) : ($v instanceof \Carbon\Carbon ? $v->format('Y-m-d') : (string) $v))); @endphp

@if($activities->isNotEmpty())
<div class="mt-6 bg-white dark:bg-black rounded-2xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center gap-2 mb-5">
        <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
        </div>
        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Activity Timeline</h3>
    </div>
    <div class="space-y-3">
        @foreach($activities as $activity)
        @php
            $event = $activity->event ?? 'updated';
            $icon = $eventIcons[$event] ?? $eventIcons['updated'];
            $color = $eventColors[$event] ?? $eventColors['updated'];
            $label = $eventLabels[$event] ?? ucfirst($event);
            $props = $activity->properties;
            $attrs = $props ? $props->get('attributes') : null;
            $old = $props ? $props->get('old') : null;
            $hasChanges = $attrs && $old && is_array($attrs) && is_array($old);
        @endphp
        <div class="flex items-start gap-3 p-4 rounded-xl bg-gray-50 dark:bg-black/30 border border-gray-100 dark:border-gray-700/30">
            <div class="w-8 h-8 rounded-xl {{ $color }} flex items-center justify-center shrink-0 mt-0.5">
                {!! $icon !!}
            </div>
            <div class="flex-1 min-w-0 space-y-1.5">
                <p class="text-sm font-semibold text-gray-900 dark:text-gray-100">
                    {{ $activity->description }}
                </p>
                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-500 dark:text-gray-400">
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ $activity->causer?->getAttribute('name') ?? 'System' }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        {{ $activity->created_at->format('d M Y') }} <span class="text-gray-400 dark:text-gray-500">({{ $activity->created_at->format('l') }})</span>
                    </span>
                    <span class="inline-flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $activity->created_at->format('h:i A') }}
                    </span>
                    <span class="inline-flex items-center gap-1 text-gray-400 dark:text-gray-500">
                        <svg class="w-3.5 h-3.5" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        {{ $activity->created_at->diffForHumans() }}
                    </span>
                </div>
                @if($hasChanges)
                <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                    @foreach($attrs as $field => $newVal)
                        @if($field !== 'updated_at' && !str_ends_with($field, '_id'))
                        @php
                            $oldVal = $old[$field] ?? null;
                            $formattedOld = $formatVal($oldVal);
                            $formattedNew = $formatVal($newVal);
                        @endphp
                        @if($formattedOld !== $formattedNew)
                        <div class="inline-flex items-center gap-1 bg-white dark:bg-black/50 px-2 py-0.5 rounded-md border border-gray-200 dark:border-gray-700">
                            <span class="text-gray-500 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $field) }}:</span>
                            <span class="text-gray-400 dark:text-gray-500 line-through">{{ $formattedOld }}</span>
                            <svg class="w-3 h-3 text-gray-400" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                            <span class="font-medium text-gray-700 dark:text-gray-300">{{ $formattedNew }}</span>
                        </div>
                        @endif
                        @endif
                    @endforeach
                </div>
                @elseif($attrs && is_array($attrs) && !$old)
                <div class="mt-1.5 flex flex-wrap gap-x-4 gap-y-1 text-xs">
                    @foreach($attrs as $field => $val)
                        @if(!in_array($field, ['updated_at', 'created_at', 'id', 'deleted_at']) && !str_ends_with($field, '_id'))
                        <span class="text-gray-500 dark:text-gray-400 capitalize">{{ str_replace('_', ' ', $field) }}: <span class="font-medium text-gray-700 dark:text-gray-300">{{ $formatVal($val) }}</span></span>
                        @endif
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

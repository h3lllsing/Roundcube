@extends('layouts.admin')

@section('title', 'Activity Log Detail')
@section('breadcrumbTitle', 'Activity Log Detail')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Activity Log Detail" subtitle="View activity log entry details." back-url="{{ route('activity-logs.index') }}" back-label="Back to Activity Logs" />

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">User</p>
                <p class="font-medium">{{ $activity->causer?->getAttribute('name') ?? 'System' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Event</p>
                <p class="font-medium">{{ $activity->event }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Description</p>
                <p class="font-medium">{{ $activity->description }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Subject</p>
                <p class="font-medium">{{ $activity->subject_type ? class_basename($activity->subject_type) . ' #' . $activity->subject_id : '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                <p class="font-medium">{{ $activity->created_at->format('Y-m-d H:i:s') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Log Name</p>
                <p class="font-medium">{{ $activity->log_name ?? '—' }}</p>
            </div>
        </div>

        @php $props = $activity->properties; $attrs = $props?->get('attributes'); $oldVals = $props?->get('old'); @endphp
        @if($props && $props->count())
        <div class="mt-6">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 font-medium">Changes</p>
            <div class="space-y-1.5">
                @if($attrs && is_array($attrs))
                    @php $ignoreFields = ['id', 'created_at', 'updated_at', 'deleted_at', 'password']; @endphp
                    @foreach($attrs as $field => $newVal)
                        @if(in_array($field, $ignoreFields) || str_ends_with($field, '_id')) @continue @endif
                        @php
                            $oldVal = $oldVals[$field] ?? null;
                            $label = str_replace('_', ' ', $field);
                            $isChanged = $oldVal !== null && $oldVal !== $newVal;
                            $displayVal = is_null($newVal) ? '—' : (is_bool($newVal) ? ($newVal ? 'Yes' : 'No') : (is_array($newVal) || is_object($newVal) ? json_encode($newVal) : (string) $newVal));
                            $displayOld = is_null($oldVal) ? '—' : (is_bool($oldVal) ? ($oldVal ? 'Yes' : 'No') : (is_array($oldVal) || is_object($oldVal) ? json_encode($oldVal) : (string) $oldVal));
                        @endphp
                        <div class="flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg {{ $isChanged ? 'bg-amber-50 dark:bg-amber-900/10' : 'bg-gray-50 dark:bg-black/50' }}">
                            <span class="font-medium text-gray-700 dark:text-gray-300 capitalize min-w-[120px]">{{ $label }}</span>
                            @if($isChanged)
                                <span class="text-gray-400 dark:text-gray-500 line-through">{{ $displayOld }}</span>
                                <svg class="w-3 h-3 text-gray-400 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $displayVal }}</span>
                            @else
                                <span class="text-gray-600 dark:text-gray-400">{{ $displayVal }}</span>
                            @endif
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
        @endif
    </div>

    <x-activity-timeline subjectType="Spatie\Activitylog\Models\Activity" :subjectId="$activity->id" />
</div>
@endsection

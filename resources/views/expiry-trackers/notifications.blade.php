@extends('layouts.admin')

@section('title', 'Notification History - ' . $tracker->name)
@section('breadcrumbTitle', $tracker->name . ' Notifications')

@section('content')
<div class="max-w-5xl mx-auto">
    <x-page-header title="Notification History" subtitle="{{ $tracker->name }}">
        <x-slot:actions>
            <x-button href="{{ route('expiry-trackers.edit', $tracker->id) }}" variant="primary" size="sm">Edit Tracker</x-button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900">
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Sent At</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Recipient</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Reminder Day</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Trigger</th>
                    <th class="text-center px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Status</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Sender</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">SMTP Profile</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-400">Error</th>
                </tr>
            </thead>
            <tbody>
                @forelse($notifications as $notification)
                <tr class="border-b border-gray-100 dark:border-gray-800 hover:bg-gray-50 dark:hover:bg-gray-900/50">
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 whitespace-nowrap">
                        {{ $notification->created_at->format('Y-m-d H:i') }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="font-medium">{{ $notification->recipient_email }}</span>
                        <span class="text-xs text-gray-400 ml-1">({{ $notification->recipient_type }})</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($notification->reminder_day === 0)
                            <span class="text-xs">Expiry day</span>
                        @else
                            <span class="text-xs">{{ $notification->reminder_day }} day(s)</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $triggerStyles = [
                                'cron' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400',
                                'manual' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
                                'test' => 'bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300',
                            ];
                            $style = $triggerStyles[$notification->trigger_source] ?? $triggerStyles['cron'];
                        @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $style }}">
                            {{ ucfirst($notification->trigger_source) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($notification->status === 'sent')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300">Sent</span>
                        @elseif($notification->status === 'failed')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300">Failed</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">Queued</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                        {{ $notification->sender_email }}
                    </td>
                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400 text-xs">
                        {{ $notification->smtpProfile?->name ?? 'Default System SMTP' }}
                    </td>
                    <td class="px-4 py-3 text-xs {{ $notification->status === 'failed' ? 'text-red-500' : 'text-gray-400' }}">
                        {{ $notification->error_message ?? '—' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                        No notifications sent yet.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>

    <div class="mt-4">
        <a href="{{ route('expiry-trackers.show', $tracker->id) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">&larr; Back to tracker</a>
    </div>
</div>
@endsection

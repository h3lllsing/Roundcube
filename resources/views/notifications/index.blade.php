@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Notifications">
        <x-slot:actions>
            <form method="POST" action="{{ route('notifications.read-all') }}" class="inline">
                @csrf
                <x-button type="submit" variant="primary" size="sm">Mark All as Read</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('notifications.index') }}" class="px-3 py-1.5 text-sm rounded-xl {{ request('unread') ? 'bg-gray-100 dark:bg-black text-gray-600 dark:text-gray-300' : 'bg-indigo-600 text-white' }}">All</a>
        <a href="{{ route('notifications.index', ['unread' => 1]) }}" class="px-3 py-1.5 text-sm rounded-xl {{ request('unread') ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-black text-gray-600 dark:text-gray-300' }}">Unread</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <input type="hidden" name="unread" value="{{ request('unread') }}">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search notifications..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
        <x-button type="submit" variant="primary" size="sm">Search</x-button>
        @if(request()->filled('search'))
            <x-button href="{{ route('notifications.index', request()->only('unread')) }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('notifications.bulk-delete') }}" id="bulkForm">
        @csrf
        <div class="mb-3 flex items-center gap-2">
            <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500" data-bulk-select-all data-bulk-selector=".notif-item">
                Select All
            </label>
            <x-button type="submit" variant="danger" size="sm" data-confirm="Delete selected notifications?">Delete Selected</x-button>
            <x-button type="submit" variant="primary" size="sm" data-confirm="Mark selected as read?" data-confirm-button="Mark as Read" data-bulk-action="{{ route('notifications.bulk-read') }}">Mark Selected as Read</x-button>
        </div>

        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;
                    $type = $data['type'] ?? class_basename($notification->type);
                @endphp
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 last:border-b-0 flex items-start gap-3 {{ $notification->read_at ? '' : 'bg-indigo-50 dark:bg-indigo-900/10' }}">
                    <div class="mt-1.5">
                        <input type="checkbox" name="ids[]" value="{{ $notification->id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 notif-item">
                    </div>
                    <div class="mt-1.5 shrink-0">
                        @if ($type === 'task_assigned')
                            <span class="w-2 h-2 rounded-full bg-indigo-500 inline-block"></span>
                        @elseif ($type === 'note_added')
                            <span class="w-2 h-2 rounded-full bg-green-500 inline-block"></span>
                        @elseif ($type === 'expiring_soon')
                            <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span>
                        @elseif ($type === 'vault_password_revealed')
                            <span class="w-2 h-2 rounded-full bg-purple-500 inline-block"></span>
                        @elseif ($type === 'monitor_check_failed')
                            <span class="w-2 h-2 rounded-full bg-red-500 inline-block"></span>
                        @else
                            <span class="w-2 h-2 rounded-full {{ $notification->read_at ? 'bg-gray-300 dark:bg-black' : 'bg-indigo-500' }} inline-block"></span>
                        @endif
                    </div>
                    <div class="flex-1 min-w-0">
                        @if ($type === 'task_assigned')
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                Task <a href="{{ route('tasks.show', $data['task_id']) }}" class="text-indigo-600 hover:text-indigo-800 font-medium">{{ $data['title'] }}</a> assigned to you
                                @if (!empty($data['assigned_by_name'])) by {{ $data['assigned_by_name'] }}@endif
                            </p>
                            <div class="flex gap-2 mt-1">
                                @if (!empty($data['priority']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-black dark:text-gray-300">{{ $data['priority'] }}</span>
                                @endif
                                @if (!empty($data['status']))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300">{{ str_replace('_', ' ', $data['status']) }}</span>
                                @endif
                                @if (!empty($data['due_date']))
                                    <span class="text-xs text-gray-500">Due {{ \Carbon\Carbon::parse($data['due_date'])->format('M j') }}</span>
                                @endif
                            </div>
                        @elseif ($type === 'note_added')
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                @if (!empty($data['added_by_name'])){{ $data['added_by_name'] }}@else{{ 'A user' }}@endif added a note
                                @if (!empty($data['notable_type']) && !empty($data['notable_id']))
                                    on <a href="{{ route('notes.show', $data['note_id']) }}" class="text-indigo-600 hover:text-indigo-800">Note #{{ $data['note_id'] }}</a>
                                @endif
                            </p>
                            @if (!empty($data['content']))
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1 truncate">{{ Str::limit($data['content'], 120) }}</p>
                            @endif
                        @elseif ($type === 'expiring_soon')
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                <span class="font-medium">{{ $data['entity_type'] ?? 'Service' }}</span>
                                <span class="font-medium">{{ $data['name'] }}</span>
                                @if (($data['days_remaining'] ?? 0) < 0)
                                    expired {{ abs($data['days_remaining']) }} day(s) ago
                                @elseif (($data['days_remaining'] ?? 0) === 0)
                                    expires today
                                @else
                                    expires in {{ $data['days_remaining'] }} day(s)
                                @endif
                                @if (!empty($data['expiry_date']))
                                    ({{ \Carbon\Carbon::parse($data['expiry_date'])->format('M j, Y') }})
                                @endif
                            </p>
                        @elseif ($type === 'vault_password_revealed')
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                Vault password for <span class="font-medium">{{ $data['service'] }}</span> revealed by {{ $data['revealed_by'] }}
                            </p>
                        @elseif ($type === 'monitor_check_failed')
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                Monitor check failed for <span class="font-medium">{{ $data['resource_name'] }}</span>
                            </p>
                            @if (!empty($data['error']))
                                <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $data['error'] }}</p>
                            @endif
                        @else
                            <p class="text-sm text-gray-900 dark:text-gray-100">{{ $data['message'] ?? $type }}</p>
                        @endif
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if (! $notification->read_at)
                            <form method="POST" action="{{ route('notifications.read', $notification->id) }}" class="inline">
                                @csrf
                                <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800">Read</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('notifications.destroy', $notification->id) }}" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" data-confirm="Delete this notification?" class="text-xs text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">Delete</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="py-12 text-center">
                    <x-empty-state :colspan="1" icon="bell" title="No notifications found." message="Notifications will appear here when something happens." />
                </div>
            @endforelse
        </div>
    </form>

    <div class="mt-4">{{ $notifications->links() }}</div>
</div>
@endsection

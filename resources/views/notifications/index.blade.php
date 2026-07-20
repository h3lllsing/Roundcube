@extends('layouts.admin')

@section('title', 'Notifications')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Notifications">
        <x-slot:actions>
            <form method="POST" action="{{ route('notifications.read-all') }}" class="inline">
                @csrf
                <x-button type="submit" variant="primary" size="sm" x-on:click="startLoading($el)">Mark All as Read</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="flex gap-2 mb-4">
        <a href="{{ route('notifications.index') }}" class="px-3 py-1.5 text-sm rounded-xl {{ request('unread') ? 'bg-gray-100 dark:bg-black text-gray-600 dark:text-gray-300' : 'bg-indigo-600 text-white' }}">All</a>
        <a href="{{ route('notifications.index', ['unread' => 1]) }}" class="px-3 py-1.5 text-sm rounded-xl {{ request('unread') ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-black text-gray-600 dark:text-gray-300' }}">Unread</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <input type="hidden" name="unread" value="{{ request('unread') }}">
        <x-filter-input name="search" placeholder="Search notifications..." />
        <x-button type="submit" variant="primary" size="sm" x-on:click="startLoading($el)">Search</x-button>
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
                        <span class="w-2 h-2 rounded-full {{ $notification->read_at ? 'bg-gray-300 dark:bg-black' : 'bg-indigo-500' }} inline-block"></span>
                    </div>
                    <div class="flex-1 min-w-0">
                        @if ($type === 'email_sync_failed')
                            <p class="text-sm text-gray-900 dark:text-gray-100">
                                Email sync failed for <span class="font-medium">{{ $data['email'] ?? 'N/A' }}</span>
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
                                <button type="submit" x-on:click="startLoading($el)" class="text-xs text-indigo-600 hover:text-indigo-800">Read</button>
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

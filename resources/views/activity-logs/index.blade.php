@extends('layouts.admin')

@section('title', 'Activity Logs')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Activity Logs</h1>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        <select name="event"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">All events</option>
            <option value="created" @selected(request('event') === 'created')>Created</option>
            <option value="updated" @selected(request('event') === 'updated')>Updated</option>
            <option value="deleted" @selected(request('event') === 'deleted')>Deleted</option>
            <option value="revealed" @selected(request('event') === 'revealed')>Revealed</option>
            <option value="login" @selected(request('event') === 'login')>Login</option>
            <option value="logout" @selected(request('event') === 'logout')>Logout</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">Filter</button>
        @if(request()->anyFilled(['search', 'event']))
            <a href="{{ route('activity-logs.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-sm rounded-lg transition-colors">Clear</a>
        @endif
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">User</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Event</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Description</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Subject</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($activities as $activity)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-gray-500">{{ $activity->causer?->getAttribute('name') ?? 'System' }}</td>
                        <td class="px-6 py-3">
                            <span @class([
                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $activity->event === 'created',
                                'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300' => $activity->event === 'updated',
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $activity->event === 'deleted',
                                'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' => $activity->event === 'revealed',
                                'bg-gray-100 text-gray-700 dark:bg-gray-600 dark:text-gray-200' => !in_array($activity->event, ['created','updated','deleted','revealed']),
                            ])>{{ $activity->event }}</span>
                        </td>
                        <td class="px-6 py-3 max-w-sm truncate">{{ $activity->description }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $activity->subject_type ? class_basename($activity->subject_type) . ' #' . $activity->subject_id : '—' }}</td>
                        <td class="px-6 py-3 text-gray-500 text-nowrap">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400">No activity logs found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $activities->links() }}</div>
</div>
@endsection

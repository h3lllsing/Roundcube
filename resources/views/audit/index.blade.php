@extends('layouts.admin')

@section('title', 'Audit Trail')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Audit Trail" subtitle="View system activity history." />

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search description..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <select name="action"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All actions</option>
            @foreach ($actions as $a)
                <option value="{{ $a }}" @selected(request('action') === $a || request('event') === $a)>{{ ucfirst($a) }}</option>
            @endforeach
        </select>
        <select name="causer_id"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All users</option>
            @foreach ($users as $id => $name)
                <option value="{{ $id }}" @selected(request('causer_id') == $id)>{{ $name }}</option>
            @endforeach
        </select>
        <select name="subject_type"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All subjects</option>
            <option value="App\Models\Domain" @selected(request('subject_type') === 'App\Models\Domain')>Domain</option>
            <option value="App\Models\EmailAccount" @selected(request('subject_type') === 'App\Models\EmailAccount')>Email Account</option>
            <option value="App\Models\User" @selected(request('subject_type') === 'App\Models\User')>User</option>
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" placeholder="From"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <input type="date" name="date_to" value="{{ request('date_to') }}" placeholder="To"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'event', 'action', 'causer_id', 'subject_type', 'date_from', 'date_to']))
            <x-button href="{{ route('audit.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">User</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Event</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Description</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Subject</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Date</th>
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
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => in_array($activity->event, ['deleted', 'soft_delete', 'force_delete']),
                                'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300' => $activity->event === 'revealed',
                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' => $activity->event === 'restored',
                                'bg-gray-100 text-gray-700 dark:bg-black dark:text-gray-200' => !in_array($activity->event, ['created','updated','deleted','soft_delete','force_delete','revealed','restored']),
                            ])>{{ $activity->event }}</span>
                        </td>
                        <td class="px-6 py-3 max-w-sm truncate">{{ $activity->description }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $activity->subject_type ? class_basename($activity->subject_type) . ' #' . $activity->subject_id : '—' }}</td>
                        <td class="px-6 py-3 text-gray-500 text-nowrap">{{ $activity->created_at->format('Y-m-d H:i') }}</td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="5" icon="activity" title="No audit records found." message="Activity will appear as users interact with the system." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $activities->links() }}</div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Monitoring Overview')

@section('content')
<div class="max-w-7xl mx-auto fade-in-up">
    <x-page-header title="Monitoring Overview" subtitle="Uptime status across all services">
    </x-page-header>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $stats['total'] }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Online</p>
            <p class="text-2xl font-bold text-emerald-600 dark:text-emerald-400 mt-1">{{ $stats['online'] }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Offline</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1">{{ $stats['offline'] }}</p>
        </div>
        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Unchecked</p>
            <p class="text-2xl font-bold text-amber-600 dark:text-amber-400 mt-1">{{ $stats['unchecked'] }}</p>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search name or URL..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <select name="type"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All types</option>
            @foreach($sourceTypes as $key => $label)
                <option value="{{ $key }}" @selected(request('type') === $key)>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All statuses</option>
            <option value="online" @selected(request('status') === 'online')>Online</option>
            <option value="offline" @selected(request('status') === 'offline')>Offline</option>
            <option value="unchecked" @selected(request('status') === 'unchecked')>Unchecked</option>
        </select>
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'type', 'status']))
            <x-button href="{{ route('monitoring.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Type</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">URL</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Last Check</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 text-gray-500">{{ $item->type_label }}</td>
                        <td class="px-6 py-3 font-medium">{{ $item->name }}</td>
                        <td class="px-6 py-3 text-gray-500 max-w-[200px] truncate" title="{{ $item->url }}">{{ $item->url }}</td>
                        <td class="px-6 py-3">
                            <span @class([
                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300' => $item->status === 'online',
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $item->status === 'offline',
                                'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300' => $item->status === 'unchecked',
                            ])>{{ ucfirst($item->status) }}</span>
                        </td>
                        <td class="px-6 py-3 text-gray-500">{{ $item->last_ping_at?->format('Y-m-d H:i') ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <x-action href="{{ route($item->route, $item->id) }}" color="indigo" icon="view" label="View" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            <p class="text-sm text-gray-400 dark:text-gray-500">No services with monitoring configured.</p>
                            <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Add a monitoring URL to any service to start tracking uptime.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $items->total() }} total service(s)
        @if($items->total() > $items->perPage())
            &middot;
            @php $lastPage = $items->lastPage(); @endphp
            @for ($i = 1; $i <= $lastPage; $i++)
                <a href="{{ request()->fullUrlWithQuery(['page' => $i]) }}" class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-xs font-medium {{ $items->currentPage() === $i ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-300' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}">{{ $i }}</a>
            @endfor
        @endif
    </div>
</div>
@endsection

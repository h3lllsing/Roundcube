@extends('layouts.admin')

@section('title', 'VPS')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">VPS</h1>
        <a href="{{ route('vps.create') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg">+ Create</a>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search VPS..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
        <select name="status"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-sm bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="expired" @selected(request('status') === 'expired')>Expired</option>
            <option value="suspended" @selected(request('status') === 'suspended')>Suspended</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm rounded-lg transition-colors">Filter</button>
        @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('vps.index') }}" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 hover:bg-gray-300 dark:hover:bg-gray-500 text-sm rounded-lg transition-colors">Clear</a>
        @endif
    </form>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50">
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Module</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Provider</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Plan</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Cost</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Expiry</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Status</th>
                    <th class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($vpsList as $vps)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-6 py-3 font-medium">{{ $vps->name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $vps->module->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $vps->provider }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $vps->plan ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $vps->cost ? '$' . number_format($vps->cost, 2) : '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $vps->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-6 py-3">
                            <span @class([
                                'inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium',
                                'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300' => $vps->status === 'active',
                                'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300' => $vps->status === 'expired',
                                'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300' => $vps->status === 'suspended',
                            ])>{{ $vps->status }}</span>
                        </td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <a href="{{ route('vps.show', $vps->id) }}" class="text-blue-600 hover:text-blue-800 text-xs mr-2">View</a>
                            <a href="{{ route('vps.edit', $vps->id) }}" class="text-amber-600 hover:text-amber-800 text-xs mr-2">Edit</a>
                            <form method="POST" action="{{ route('vps.destroy', $vps->id) }}" class="inline" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-xs">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-8 text-center text-gray-400">No VPS found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $vpsList->links() }}</div>
</div>
@endsection

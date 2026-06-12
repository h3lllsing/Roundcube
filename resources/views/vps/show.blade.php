@extends('layouts.admin')

@section('title', $vps->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ $vps->name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('vps.edit', $vps->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Edit</a>
            <form action="{{ route('vps.destroy', $vps->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Delete</button>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Name</p>
                <p class="font-medium">{{ $vps->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Provider</p>
                <p class="font-medium">{{ $vps->provider ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Plan</p>
                <p class="font-medium">{{ $vps->plan ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">IP Address</p>
                <p class="font-medium">{{ $vps->ip_address ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">OS</p>
                <p class="font-medium">{{ $vps->os ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">RAM</p>
                <p class="font-medium">{{ $vps->ram_mb ? $vps->ram_mb . ' MB' : '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Disk</p>
                <p class="font-medium">{{ $vps->disk_gb ? $vps->disk_gb . ' GB' : '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">CPU Cores</p>
                <p class="font-medium">{{ $vps->cpu_cores ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Cost</p>
                <p class="font-medium">{{ $vps->cost ? '$' . number_format($vps->cost, 2) : '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="font-medium">{{ $vps->status }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Start Date</p>
                <p class="font-medium">{{ $vps->start_date?->format('Y-m-d') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Expiry Date</p>
                <p class="font-medium">{{ $vps->expiry_date?->format('Y-m-d') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Module</p>
                <p class="font-medium">{{ $vps->module->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">User</p>
                <p class="font-medium">{{ $vps->user->name ?? '—' }}</p>
            </div>
        </div>

        @if($vps->notes)
        <div class="mt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Notes</p>
            <p class="text-sm whitespace-pre-wrap">{{ $vps->notes }}</p>
        </div>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('vps.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">&larr; Back to VPS</a>
    </div>
</div>
@endsection

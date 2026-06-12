@extends('layouts.admin')

@section('title', $provider->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ $provider->name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('service-providers.edit', $provider->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Edit</a>
            <form action="{{ route('service-providers.destroy', $provider->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
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
                <p class="font-medium">{{ $provider->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Type</p>
                <p class="font-medium">{{ $provider->type ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Provider</p>
                <p class="font-medium">{{ $provider->provider ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Website</p>
                <p class="font-medium">@if($provider->website)<a href="{{ $provider->website }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $provider->website }}</a>@else—@endif</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Cost</p>
                <p class="font-medium">{{ $provider->cost ? '$' . number_format($provider->cost, 2) : '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="font-medium">{{ $provider->status }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Start Date</p>
                <p class="font-medium">{{ $provider->start_date?->format('Y-m-d') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Expiry Date</p>
                <p class="font-medium">{{ $provider->expiry_date?->format('Y-m-d') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Module</p>
                <p class="font-medium">{{ $provider->module->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">User</p>
                <p class="font-medium">{{ $provider->user->name ?? '—' }}</p>
            </div>
        </div>

        @if($provider->notes)
        <div class="mt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Notes</p>
            <p class="text-sm whitespace-pre-wrap">{{ $provider->notes }}</p>
        </div>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('service-providers.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">&larr; Back to Service Providers</a>
    </div>
</div>
@endsection

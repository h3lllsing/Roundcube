@extends('layouts.admin')

@section('title', $domain->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ $domain->name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('domains.edit', $domain->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Edit</a>
            <form action="{{ route('domains.destroy', $domain->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
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
                <p class="font-medium">{{ $domain->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Registrar</p>
                <p class="font-medium">{{ $domain->registrar ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Registration Date</p>
                <p class="font-medium">{{ $domain->registration_date?->format('Y-m-d') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Expiry Date</p>
                <p class="font-medium">{{ $domain->expiry_date?->format('Y-m-d') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Auto Renew</p>
                <p class="font-medium">{{ $domain->auto_renew ? 'Yes' : 'No' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Cost</p>
                <p class="font-medium">{{ $domain->cost ? '$' . number_format($domain->cost, 2) : '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Status</p>
                <p class="font-medium">{{ $domain->status }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Module</p>
                <p class="font-medium">{{ $domain->module->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">User</p>
                <p class="font-medium">{{ $domain->user->name ?? '—' }}</p>
            </div>
        </div>

        @if($domain->dns_servers)
        <div class="mt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">DNS Servers</p>
            <div class="flex flex-wrap gap-2">
                @foreach(Arr::wrap($domain->dns_servers) as $dns)
                    <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 dark:bg-gray-700 text-sm font-mono">{{ $dns }}</span>
                @endforeach
            </div>
        </div>
        @endif

        @if($domain->notes)
        <div class="mt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Notes</p>
            <p class="text-sm whitespace-pre-wrap">{{ $domain->notes }}</p>
        </div>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('domains.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">&larr; Back to Domains</a>
    </div>
</div>
@endsection

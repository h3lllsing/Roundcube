@extends('layouts.admin')

@section('title', $entry->service_name)

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">{{ $entry->service_name }}</h1>
        <div class="flex items-center gap-2">
            <a href="{{ route('vault.edit', $entry->id) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Edit</a>
            <form action="{{ route('vault.destroy', $entry->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">Delete</button>
            </form>
        </div>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Service Name</p>
                <p class="font-medium">{{ $entry->service_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Service URL</p>
                <p class="font-medium">@if($entry->service_url)<a href="{{ $entry->service_url }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">{{ $entry->service_url }}</a>@else—@endif</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Username</p>
                <p class="font-medium">{{ $entry->username ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Password</p>
                <div class="flex items-center gap-2">
                    <span id="passwordDisplay" class="font-mono text-sm">••••••••</span>
                    <button type="button" onclick="togglePassword()" class="text-xs text-blue-600 hover:text-blue-800">Show</button>
                </div>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Module</p>
                <p class="font-medium">{{ $entry->module->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">User</p>
                <p class="font-medium">{{ $entry->user->name ?? '—' }}</p>
            </div>
        </div>

        @if($entry->description)
        <div class="mt-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-1">Description</p>
            <p class="text-sm whitespace-pre-wrap">{{ $entry->description }}</p>
        </div>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ route('vault.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">&larr; Back to Vault</a>
    </div>
</div>

<script>
function togglePassword() {
    const el = document.getElementById('passwordDisplay');
    const btn = event.currentTarget;
    if (el.dataset.revealed === 'true') {
        el.textContent = '••••••••';
        el.dataset.revealed = 'false';
        btn.textContent = 'Show';
    } else {
        el.textContent = '{{ $entry->decryptPassword() }}';
        el.dataset.revealed = 'true';
        btn.textContent = 'Hide';
    }
}
</script>
@endsection

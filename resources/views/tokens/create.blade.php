@extends('layouts.admin')

@section('title', 'Create API Token')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-page-header title="Create API Token" subtitle="Generate a new API token" />

    <form method="POST" action="{{ route('tokens.store') }}" class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf

        <div>
            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Token Name</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}" required
                class="w-full px-3 py-2.5 border rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none placeholder:text-gray-400 dark:placeholder:text-gray-500 @error('name') border-red-400 dark:border-red-500 @else border-gray-300 dark:border-gray-600 @enderror"
                placeholder="e.g. CI/CD Pipeline">
            @error('name')
                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">Create Token</x-button>
            <x-button href="{{ route('tokens.index') }}" variant="outline" size="sm">Cancel</x-button>
        </div>
    </form>
</div>
@endsection

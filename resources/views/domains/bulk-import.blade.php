@extends('layouts.admin')

@section('title', 'Bulk Import Accounts - ' . $domain->name)

@section('content')
<x-page-header title="Bulk Import" subtitle="{{ $domain->name }}" backUrl="{{ route('domains.show', $domain) }}" backLabel="Back to Domain">
</x-page-header>

<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('domains.bulk-import', $domain) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Accounts (one per line, format: <code>username:password</code>)</label>
                <textarea name="entries" rows="10" required
                    class="w-full rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-black px-4 py-3 text-sm font-mono focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="user1:Pass@123&#10;user2:Pass456&#10;user3:Secret789">{{ old('entries') }}</textarea>
                <p class="text-xs text-gray-400 mt-1.5">Full email also works: <code>user1@domain.com:Pass123</code></p>
                @error('entries') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center gap-3">
                <x-button type="submit" variant="primary">Import Accounts</x-button>
                <x-button href="{{ route('domains.show', $domain) }}" variant="outline">Cancel</x-button>
            </div>
        </form>
    </x-card>
</div>
@endsection
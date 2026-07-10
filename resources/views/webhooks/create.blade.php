@extends('layouts.admin')

@section('title', 'Create Webhook')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Create Webhook" subtitle="Add a new webhook endpoint" />

    <form action="{{ route('webhooks.store') }}" method="POST" class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf

        <x-form.input name="name" label="Name" :value="old('name')" required />
        <x-form.input name="url" label="URL" :value="old('url')" required />

        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Events</label>
            <div class="space-y-1.5">
                @php $selectedEvents = old('events', []); @endphp
                @foreach(['vault.revealed', 'task.created', 'task.updated', 'expiring_soon'] as $ev)
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="events[]" value="{{ $ev }}" {{ in_array($ev, $selectedEvents) ? 'checked' : '' }}
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500/40">
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $ev }}</span>
                    </label>
                @endforeach
            </div>
            @error('events')
                <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
            @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-gray-300 dark:border-gray-600">
                Active
            </label>
            <x-form.select name="user_id" label="User" :options="$users" :value="old('user_id')" placeholder="Select user..." />
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">Save</x-button>
            <x-button href="{{ route('webhooks.index') }}" variant="outline" size="sm">Cancel</x-button>
        </div>
    </form>
</div>
@endsection

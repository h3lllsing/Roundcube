@extends('layouts.admin')

@section('title', $webhook->name)
@section('breadcrumbTitle', $webhook->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $webhook->name }}" back-url="{{ route('webhooks.index') }}" back-label="Back to Webhooks">
        <x-slot:actions>
            <form action="{{ route('webhooks.test', $webhook->id) }}" method="POST" class="inline">
                @csrf
                <x-button type="submit" variant="success" size="sm">Test</x-button>
            </form>
            <x-button href="{{ route('webhooks.edit', $webhook->id) }}" variant="primary" size="sm">Edit</x-button>
            <form action="{{ route('webhooks.destroy', $webhook->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Name" value="{{ $webhook->name }}" />
            <x-field label="URL">
                @if($webhook->url)
                    <div class="flex items-center gap-2">
                        <a href="{{ $webhook->url }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $webhook->url }}</a>
                        <x-copy-button :text="$webhook->url" title="Copy URL" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Events" value="{{ $webhook->events ? implode(', ', $webhook->events) : '—' }}" />
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Status">
                <x-badge :variant="$webhook->is_active ? 'active' : 'inactive'">{{ $webhook->is_active ? 'Active' : 'Inactive' }}</x-badge>
            </x-field>
            <x-field label="Last Fired">
                @if($webhook->last_fired_at)
                    {{ $webhook->last_fired_at->format('Y-m-d H:i:s') }}
                @else
                    —
                @endif
            </x-field>
            <x-field label="User" value="{{ $webhook->user->name ?? '—' }}" />
        </div>
    </x-card>

    <x-activity-timeline subjectType="App\Models\Webhook" :subjectId="$webhook->id" />
</div>
@endsection

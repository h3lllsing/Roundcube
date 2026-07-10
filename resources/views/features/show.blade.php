@extends('layouts.admin')

@section('title', $feature->name)
@section('breadcrumbTitle', $feature->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="{{ $feature->name }}" back-url="{{ route('features.index') }}" />
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $feature->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Name</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $feature->name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Slug</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $feature->slug }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Icon</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $feature->icon ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $feature->description ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Active</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $feature->is_active ? 'Yes' : 'No' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Modules</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $feature->modules->count() }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $feature->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Updated</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $feature->updated_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
            <x-button href="{{ route('features.edit', $feature->id) }}" variant="primary" size="sm">Edit</x-button>
            <form action="{{ route('features.destroy', $feature->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
        </div>
    </div>

    <x-notes-thread :model="$feature" notable-type="App\Models\Feature" />

    <x-activity-timeline subjectType="App\Models\Feature" :subjectId="$feature->id" />
</div>
@endsection

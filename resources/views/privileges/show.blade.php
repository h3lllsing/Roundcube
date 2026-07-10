@extends('layouts.admin')

@section('title', $privilege->name)
@section('breadcrumbTitle', $privilege->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="{{ $privilege->name }}" back-url="{{ route('privileges.index') }}" />
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $privilege->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Slug</label>
                <p class="mt-1 text-sm"><code class="bg-gray-100 dark:bg-black px-1.5 py-0.5 rounded">{{ $privilege->slug }}</code></p>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $privilege->description ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Roles</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $privilege->roles->count() }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $privilege->created_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
            <x-button href="{{ route('privileges.edit', $privilege->id) }}" variant="primary" size="sm">Edit</x-button>
            <form action="{{ route('privileges.destroy', $privilege->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
        </div>
    </div>

    <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-md font-semibold mb-4">Roles with this privilege ({{ $privilege->roles->count() }})</h3>
        @if ($privilege->roles->isNotEmpty())
        <ul class="divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($privilege->roles as $role)
            <li class="py-2 flex items-center justify-between">
                <a href="{{ route('roles.show', $role->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $role->name }}</a>
            </li>
            @endforeach
        </ul>
        @else
        <p class="text-sm text-gray-400 dark:text-gray-500">No roles have this privilege.</p>
        @endif
    </div>
</div>
@endsection

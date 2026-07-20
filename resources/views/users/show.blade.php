@extends('layouts.admin')

@section('title', $user->name)
@section('breadcrumbTitle', $user->name)

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="{{ $user->name }}" subtitle="Enterprise Permission Inspector" back-url="{{ route('users.index') }}">
            <x-slot:actions>
                <span class="text-xs text-gray-400 dark:text-gray-500 bg-white/70 dark:bg-black/70 px-3 py-1.5 rounded-xl border border-gray-200 dark:border-gray-700">
                    {{ now()->format('Y-m-d H:i') }}
                </span>
            </x-slot:actions>
        </x-page-header>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Email</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Role</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($user->role) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                <p class="mt-1 text-sm">
                    @if ($user->suspended_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Suspended</span>
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                    @endif
                </p>
            </div>
            @if ($lastLogin)
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Last Login</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $lastLogin->created_at->format('Y-m-d H:i') }}</p>
            </div>
            @endif
        </div>
        <div class="flex items-center gap-3 pt-4 mt-4 border-t border-gray-200 dark:border-gray-700">
            <x-button href="{{ route('users.edit', $user->id) }}" variant="primary" size="sm">Edit</x-button>
            <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
        </div>
    </div>

    <x-activity-timeline subjectType="App\Models\User" :subjectId="$user->id" />
</div>
@endsection
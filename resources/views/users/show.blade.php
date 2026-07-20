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
            <x-field label="ID" value="{{ $user->id }}" />
            <x-field label="Email" value="{{ $user->email }}" />
            <x-field label="Role" value="{{ ucfirst($user->role) }}" />
            <x-field label="Status">
                @if ($user->suspended_at)
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Suspended</span>
                @else
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Active</span>
                @endif
            </x-field>
            @if ($lastLogin)
            <x-field label="Last Login" value="{{ $lastLogin->created_at->format('Y-m-d H:i') }}" />
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
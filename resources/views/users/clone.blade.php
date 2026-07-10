@extends('layouts.admin')

@section('title', 'Clone User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Clone User" subtitle="Create a new user by cloning an existing user" />

        <div class="mb-6 p-4 rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 text-sm text-amber-700 dark:text-amber-300">
            <strong>Cloning from:</strong> {{ $sourceUser->name }} ({{ $sourceUser->email }})
            @if ($sourceRoles->isNotEmpty())
                <span class="block mt-1 text-xs text-amber-600 dark:text-amber-400">
                    Roles: {{ $sourceRoles->pluck('name')->implode(', ') }}
                </span>
            @endif
            @if ($overrideCount > 0)
                <span class="block mt-1 text-xs text-amber-600 dark:text-amber-400">
                    Module overrides: {{ $overrideCount }} module(s)
                </span>
            @endif
        </div>

        <form action="{{ route('users.clone.store', $sourceUser->id) }}" method="POST" class="space-y-4">
            @csrf

            <x-form.input name="name" label="Name" :value="old('name')" required />
            <x-form.input type="email" name="email" label="Email" :value="old('email')" required />
            <x-form.input type="password" name="password" label="Password" required autocomplete="new-password" />
            <x-form.input type="password" name="password_confirmation" label="Confirm Password" required autocomplete="new-password" />

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
                <h3 class="text-sm font-semibold mb-3">Clone Options</h3>
                <div class="space-y-2">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="copy_roles" value="1" {{ old('copy_roles', '1') === '1' ? 'checked' : '' }}
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        Copy Roles
                    </label>
                    <br>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="copy_overrides" value="1" {{ old('copy_overrides', '1') === '1' ? 'checked' : '' }}
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        Copy Module Overrides
                    </label>
                    <br>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="copy_status" value="1" {{ old('copy_status') === '1' ? 'checked' : '' }}
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                        Copy Status
                    </label>
                </div>
            </div>

            @if ($sourceUser->hasRole('super-admin'))
            <div class="p-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-sm text-red-700 dark:text-red-300">
                <strong>Warning:</strong> This user is a Super Admin. Cloning will grant full unrestricted access to the new user.
                <label class="inline-flex items-center gap-2 mt-2 text-sm font-medium">
                    <input type="checkbox" name="confirm_super_admin" value="1" {{ old('confirm_super_admin') === '1' ? 'checked' : '' }}
                        class="rounded border-gray-300 dark:border-gray-600 text-red-600 focus:ring-red-500">
                    I confirm — Clone super-admin role
                </label>
                @error('confirm_super_admin')
                    <p class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>
            @endif

            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Clone User</x-button>
                <x-button href="{{ route('users.show', $sourceUser->id) }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection
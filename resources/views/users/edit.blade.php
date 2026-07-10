@extends('layouts.admin')

@section('title', 'Edit User')
@section('breadcrumbTitle', $user->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Edit User" subtitle="Update user details" />
        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $user->updated_at->format('Y-m-d H:i:s') }}">
            <x-form.input name="name" label="Name" :value="old('name', $user->name)" required />
            <x-form.input type="email" name="email" label="Email" :value="old('email', $user->email)" required />
            <x-form.input type="password" name="password" label="New Password" autocomplete="new-password" />
            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-3">Leave blank to keep current password.</p>
            <x-form.input type="password" name="password_confirmation" label="Confirm Password" autocomplete="new-password" />
            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-3">Only required when setting a new password.</p>
            <x-form.input type="date" name="suspended_at" label="Suspended At" :value="old('suspended_at', $user->suspended_at?->format('Y-m-d'))" />
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Roles</label>
                <div class="flex flex-wrap gap-4">
                    @foreach ($roles as $role)
                        <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="roles[]" value="{{ $role->id }}" {{ in_array($role->id, old('roles', $user->roles->pluck('id')->toArray())) ? 'checked' : '' }}
                                class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
                            {{ $role->name }}
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-4 mt-6">
                <h3 class="text-md font-semibold mb-1">Permission Overrides</h3>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">Per-user overrides are optional and should only be used for exceptions to role permissions.</p>
                <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            @if($overrideCount > 0)
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $overrideCount }} override(s) configured.</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">These permissions override role defaults.</p>
                            @else
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">No overrides configured.</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">User will inherit permissions from assigned roles.</p>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <x-button href="{{ route('users.permissions.edit', $user->id) }}" variant="primary" size="sm">Configure Overrides</x-button>
                            <x-button href="{{ route('users.show', $user->id) }}" variant="outline" size="sm">View Effective Permissions</x-button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('users.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

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
            <x-form.select name="role" label="Role" :options="[
                '' => '— Select a role —',
                'super-admin' => 'Super Admin',
                'admin' => 'Admin',
                'user' => 'User',
            ]" :value="old('role', $user->role)" />

            <x-button href="{{ route('users.show', $user->id) }}" variant="outline" size="sm">View Profile</x-button>

            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm" x-on:click="startLoading($el)">Save</x-button>
                <x-button href="{{ route('users.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

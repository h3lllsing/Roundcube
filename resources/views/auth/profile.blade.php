@extends('layouts.admin')

@section('title', 'Profile')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Profile" subtitle="Update your account details and password" />

    <form action="{{ route('profile.update') }}" method="POST" class="rounded-2xl p-6 lg:p-8 space-y-5">
        <x-card variant="glass" padding="none" class="rounded-2xl p-6 lg:p-8">
        @csrf
        @method('PUT')

        <input type="hidden" name="updated_at" value="{{ old('updated_at', $user->updated_at) }}">
        <x-form.input name="name" label="Name" :value="old('name', $user->name)" required />
        <x-form.input name="email" label="Email" type="email" :value="old('email', $user->email)" required />

        <hr class="border-gray-200 dark:border-gray-700/50">

        <p class="text-sm text-gray-500 dark:text-gray-400">Leave blank to keep current password.</p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input type="password" name="password" label="New Password" autocomplete="new-password" />
            <x-form.input type="password" name="password_confirmation" label="Confirm Password" autocomplete="new-password" />
        </div>

        <x-form.input type="password" name="current_password" label="Current Password" autocomplete="current-password" placeholder="Required to set a new password" />

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Save
            </x-button>
        </div>
        </x-card>
    </form>
</div>
@endsection

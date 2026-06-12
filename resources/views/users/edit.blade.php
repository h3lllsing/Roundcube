@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold mb-6">Edit User</h2>
        <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <x-form.input name="name" label="Name" :value="old('name', $user->name)" required />
            <x-form.input type="email" name="email" label="Email" :value="old('email', $user->email)" required />
            <x-form.input type="password" name="password" label="New Password" />
            <x-form.input type="password" name="password_confirmation" label="Confirm New Password" />
            <x-form.input type="date" name="suspended_at" label="Suspended At" :value="old('suspended_at', $user->suspended_at?->format('Y-m-d'))" />
            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Save</button>
                <a href="{{ route('users.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

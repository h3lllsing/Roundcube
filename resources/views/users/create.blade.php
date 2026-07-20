@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
<div class="max-w-3xl">
    <x-page-header title="Create User" subtitle="Add a new system user." backUrl="{{ route('users.index') }}" backLabel="Back to Users" />

    <x-card>
        <form method="POST" action="{{ route('users.store') }}">
            @csrf

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.input name="name" label="Full Name" value="{{ old('name') }}" required />
                    <x-form.input type="email" name="email" label="Email Address" value="{{ old('email') }}" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.password name="password" label="Password" required />
                    <x-form.password name="password_confirmation" label="Confirm Password" required />
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.select name="role" label="Role" :options="['user' => 'User', 'admin' => 'Admin']" value="{{ old('role', 'user') }}" />
                    <x-form.select name="status" label="Status" :options="['active' => 'Active', 'suspended' => 'Suspended']" value="{{ old('status', 'active') }}" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary" x-on:click="startLoading($el)">Create User</x-button>
                    <x-button href="{{ route('users.index') }}" variant="outline">Cancel</x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection

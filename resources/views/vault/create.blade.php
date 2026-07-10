@extends('layouts.admin')

@section('title', 'Create Vault Entry')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Create Vault Entry" subtitle="Store a new password entry" />

    <form action="{{ route('vault.store') }}" method="POST" class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="service_name" label="Service Name" :value="old('service_name')" required />
            <x-form.input name="service_url" label="Service URL" :value="old('service_url')" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="username" label="Username" :value="old('username')" />
            <x-form.input name="encrypted_password" label="Password" type="password" :value="old('encrypted_password')" autocomplete="new-password" />
        </div>

        <x-form.select name="module_id" label="Module" :options="$modules" :value="old('module_id')" placeholder="Select module..." />
        <x-form.select name="user_id" label="User" :options="$users" :value="old('user_id')" placeholder="Select user..." />

        <x-form.textarea name="description" label="Description" :value="old('description')" />

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">Save</x-button>
            <x-button href="{{ route('vault.index') }}" variant="outline" size="sm">Cancel</x-button>
        </div>
    </form>
</div>
@endsection

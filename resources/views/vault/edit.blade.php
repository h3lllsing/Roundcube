@extends('layouts.admin')

@section('title', 'Edit Vault Entry')
@section('breadcrumbTitle', $entry->service_name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit Vault Entry" subtitle="Update password entry details" />

    <form action="{{ route('vault.update', $entry->id) }}" method="POST" class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="updated_at" value="{{ $entry->updated_at->format('Y-m-d H:i:s') }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="service_name" label="Service Name" :value="old('service_name', $entry->service_name)" required />
            <x-form.input name="service_url" label="Service URL" :value="old('service_url', $entry->service_url)" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="username" label="Username" :value="old('username', $entry->username)" />
            <x-form.input name="encrypted_password" label="New Password (leave blank to keep current)" type="password" :value="old('encrypted_password')" autocomplete="new-password" />
            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-3">Leave blank to keep current value.</p>
        </div>

        <x-form.select name="module_id" label="Module" :options="$modules" :value="old('module_id', $entry->module_id)" placeholder="Select module..." />
        <x-form.select name="user_id" label="User" :options="$users" :value="old('user_id', $entry->user_id)" placeholder="Select user..." />

        <x-form.textarea name="description" label="Description" :value="old('description', $entry->description)" />

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">Save</x-button>
            <x-button href="{{ route('vault.index') }}" variant="outline" size="sm">Cancel</x-button>
        </div>
    </form>
</div>
@endsection

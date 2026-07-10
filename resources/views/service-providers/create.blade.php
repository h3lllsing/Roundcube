@extends('layouts.admin')

@section('title', 'Create Service Provider')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Create Service Provider" subtitle="Add a new service provider" />

    <form action="{{ route('service-providers.store') }}" method="POST">
        <x-card class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="name" label="Name" :value="old('name')" required />
                <x-form.input name="type" label="Type" :value="old('type')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="website" label="Web URL" :value="old('website')" />
                <x-form.input name="login_id" label="Login ID" :value="old('login_id')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="password" name="password" label="Password" :value="old('password')" autocomplete="new-password" />
                <x-form.input name="email" label="Email Address" type="email" :value="old('email')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="provider" label="Provider" :value="old('provider')" />
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="start_date" label="Start Date" :value="old('start_date')" />
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended']" :value="old('status')" required />
            </div>

            <x-form.textarea name="description" label="Notes" :value="old('description')" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('service-providers.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

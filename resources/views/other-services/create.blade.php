@extends('layouts.admin')

@section('title', 'Create Other Service')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Create Other Service" subtitle="Add a new other service" />

    <form action="{{ route('other-services.store') }}" method="POST">
        <x-card class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="name" label="Name" :value="old('name')" required />
                <x-form.select name="service_type" label="Type" :options="['saas' => 'SaaS', 'api' => 'API', 'monitoring' => 'Monitoring', 'analytics' => 'Analytics', 'cdn' => 'CDN', 'ssl' => 'SSL', 'other' => 'Other']" :value="old('service_type')" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id')" placeholder="Select provider..." />
                <x-form.input name="username" label="Username" :value="old('username')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="password" name="password" label="Password" :value="old('password')" autocomplete="new-password" />
                <x-form.input name="login_url" label="Login URL" :value="old('login_url')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="website" label="Website" :value="old('website')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost')" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', 12)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="start_date" label="Start Date" :value="old('start_date')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date')" />
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled']" :value="old('status')" />
            </div>

            <x-form.textarea name="description" label="Description" :value="old('description')" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('other-services.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

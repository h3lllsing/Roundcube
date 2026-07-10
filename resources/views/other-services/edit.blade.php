@extends('layouts.admin')

@section('title', 'Edit Other Service')
@section('breadcrumbTitle', $service->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit Other Service" subtitle="Update other service details" />

    <form action="{{ route('other-services.update', $service->id) }}" method="POST">
        <x-card class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $service->updated_at->format('Y-m-d H:i:s') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="name" label="Name" :value="old('name', $service->name)" required />
                <x-form.select name="service_type" label="Type" :options="['saas' => 'SaaS', 'api' => 'API', 'monitoring' => 'Monitoring', 'analytics' => 'Analytics', 'cdn' => 'CDN', 'ssl' => 'SSL', 'other' => 'Other']" :value="old('service_type', $service->service_type)" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id', $service->service_provider_id)" placeholder="Select provider..." />
                <x-form.input name="username" label="Username" :value="old('username', $service->username)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="password" name="password" label="Password" value="" autocomplete="new-password" />
                <p class="text-xs text-gray-500 dark:text-gray-400 -mt-3">Leave blank to keep current value.</p>
                <x-form.input name="login_url" label="Login URL" :value="old('login_url', $service->login_url)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="website" label="Website" :value="old('website', $service->website)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost', $service->cost)" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', $service->billing_period_months ?? 12)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="start_date" label="Start Date" :value="old('start_date', $service->start_date?->format('Y-m-d'))" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date', $service->expiry_date?->format('Y-m-d'))" />
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'expired' => 'Expired', 'cancelled' => 'Cancelled']" :value="old('status', $service->status)" />
            </div>

            <x-form.select name="user_id" label="User" :options="$users" :value="old('user_id', $service->user_id)" placeholder="Select user..." />

            <x-form.textarea name="description" label="Notes" :value="old('description', $service->description)" />

            <x-notes-thread :model="$service" notable-type="App\Models\OtherService" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('other-services.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Create Domain Email')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Add Email Credential" subtitle="Store email account and password." />

    <form action="{{ route('domain-emails.store') }}" method="POST">
        <x-card class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="email" label="Email Address" :value="old('email')" required placeholder="admin@yourdomain.com" />
                <x-form.input type="password" name="password" label="Password" :value="old('password')" placeholder="Enter email password" autocomplete="new-password" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id')" placeholder="Select provider..." />
                <x-form.select name="domain_id" label="Domain" :options="$domains" :value="old('domain_id')" placeholder="Select domain..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost')" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', 12)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" name="storage_mb" label="Storage (MB)" :value="old('storage_mb')" />
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date')" />
            </div>

            <x-form.textarea name="description" label="Description" :value="old('description')" placeholder="Any additional info about this email account..." />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('domain-emails.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

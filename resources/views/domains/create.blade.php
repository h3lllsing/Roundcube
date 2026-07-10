@extends('layouts.admin')

@section('title', 'Create Domain')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Create Domain" subtitle="Add a new domain" />

    <form action="{{ route('domains.store') }}" method="POST">
        <x-card class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="name" label="Name" :value="old('name')" required />
                <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id')" placeholder="Select provider..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="registration_date" label="Registration Date" :value="old('registration_date')" />
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended']" :value="old('status')" required />
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost')" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', 12)" />
            </div>

            <x-form.select name="cloudflare_status" label="Cloudflare Status" :options="[null => 'Not configured', 'enabled' => 'Enabled', 'disabled' => 'Disabled', 'unknown' => 'Unknown']" :value="old('cloudflare_status')" />

            <x-form.select name="hosting_id" label="Hosting" :options="$hostings" :value="old('hosting_id')" placeholder="Select hosting..." />

            <x-form.checkbox name="auto_renew" label="Auto Renew" :checked="old('auto_renew')" />

            <x-form.textarea name="dns_servers" label="DNS Servers (comma-separated)" :value="old('dns_servers')" />

            <x-form.textarea name="description" label="Description" :value="old('description')" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('domains.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

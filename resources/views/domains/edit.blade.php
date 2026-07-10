@extends('layouts.admin')

@section('title', 'Edit Domain')
@section('breadcrumbTitle', $domain->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit Domain" subtitle="Update domain details" />

    <form action="{{ route('domains.update', $domain->id) }}" method="POST">
        <x-card class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $domain->updated_at->format('Y-m-d H:i:s') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="name" label="Name" :value="old('name', $domain->name)" required />
                <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id', $domain->service_provider_id)" placeholder="Select provider..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="registration_date" label="Registration Date" :value="old('registration_date', $domain->registration_date?->format('Y-m-d'))" />
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date', $domain->expiry_date?->format('Y-m-d'))" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended']" :value="old('status', $domain->status)" required />
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost', $domain->cost)" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', $domain->billing_period_months ?? 12)" />
            </div>

            <x-form.select name="cloudflare_status" label="Cloudflare Status" :options="[null => 'Not configured', 'enabled' => 'Enabled', 'disabled' => 'Disabled', 'unknown' => 'Unknown']" :value="old('cloudflare_status', $domain->cloudflare_status)" />

            <x-form.select name="hosting_id" label="Hosting" :options="$hostings" :value="old('hosting_id', $domain->hosting_id)" placeholder="Select hosting..." />
            <x-form.checkbox name="auto_renew" label="Auto Renew" :checked="old('auto_renew', $domain->auto_renew)" />

            <x-form.textarea name="dns_servers" label="DNS Servers (comma-separated)" :value="old('dns_servers', $domain->dns_servers ? implode(', ', $domain->dns_servers) : '')" />

            <x-form.textarea name="description" label="Notes" :value="old('description', $domain->description)" />

            <x-notes-thread :model="$domain" notable-type="App\Models\Domain" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('domains.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

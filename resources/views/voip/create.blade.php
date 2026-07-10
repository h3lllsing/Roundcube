@extends('layouts.admin')

@section('title', 'Create VoIP')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Create VoIP" subtitle="Add a new VoIP service" />

    <form action="{{ route('voip.store') }}" method="POST">
        <x-card class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="extension" label="Extension" :value="old('extension')" placeholder="e.g. 101" />
                <x-form.input name="name" label="Name" :value="old('name')" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="service_provider_id" label="Vendor" :options="$serviceProviders" :value="old('service_provider_id')" placeholder="Select vendor..." />
                <x-form.input name="phone_number" label="Phone" :value="old('phone_number')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="server_ip" label="Server IP" :value="old('server_ip')" placeholder="e.g. 192.168.1.100" />
                <x-form.select name="direction" label="Inbound/Out" :options="['inbound' => 'Inbound', 'outbound' => 'Outbound', 'both' => 'Both']" :value="old('direction')" placeholder="Select direction..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="number_status" label="Number Status" :value="old('number_status')" placeholder="e.g. active, blocked, forwarding" />
                <x-form.input name="outbound_code" label="Code for Outbound" :value="old('outbound_code')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost')" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', 12)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date')" />
                <x-form.input type="password" name="extension_password" label="Extension Password" :value="old('extension_password')" autocomplete="new-password" />
            </div>

            <x-form.textarea name="team_details" label="Team Details" :value="old('team_details')" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('voip.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection
@extends('layouts.admin')

@section('title', 'Edit VoIP')
@section('breadcrumbTitle', $voip->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit VoIP" subtitle="Update VoIP details" />

    <form action="{{ route('voip.update', $voip->id) }}" method="POST">
        <x-card class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $voip->updated_at->format('Y-m-d H:i:s') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="extension" label="Extension" :value="old('extension', $voip->extensions[0] ?? '')" placeholder="e.g. 101" />
                <x-form.input name="name" label="Name" :value="old('name', $voip->name)" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="service_provider_id" label="Vendor" :options="$serviceProviders" :value="old('service_provider_id', $voip->service_provider_id)" placeholder="Select vendor..." />
                <x-form.input name="phone_number" label="Phone" :value="old('phone_number', $voip->phone_number)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="server_ip" label="Server IP" :value="old('server_ip', $voip->server_ip)" placeholder="e.g. 192.168.1.100" />
                <x-form.select name="direction" label="Inbound/Out" :options="['inbound' => 'Inbound', 'outbound' => 'Outbound', 'both' => 'Both']" :value="old('direction', $voip->direction)" placeholder="Select direction..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="number_status" label="Number Status" :value="old('number_status', $voip->number_status)" placeholder="e.g. active, blocked, forwarding" />
                <x-form.input name="outbound_code" label="Code for Outbound" :value="old('outbound_code', $voip->outbound_code)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost', $voip->cost)" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', $voip->billing_period_months ?? 12)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date', $voip->expiry_date?->format('Y-m-d'))" />
                <x-form.input type="password" name="extension_password" label="Extension Password" value="" autocomplete="new-password" />
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-3">Leave blank to keep current value.</p>

            <x-form.textarea name="team_details" label="Team Details" :value="old('team_details', $voip->team_details)" />

            <x-form.textarea name="description" label="Description" :value="old('description', $voip->description)" />

            <x-notes-thread :model="$voip" notable-type="App\Models\Voip" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('voip.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection
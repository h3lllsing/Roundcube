@extends('layouts.admin')

@section('title', 'Create VPS')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Create VPS" subtitle="Add a new VPS server" />

    <form action="{{ route('vps.store') }}" method="POST">
        <x-card class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="name" label="Name" :value="old('name')" required />
                <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id')" placeholder="Select provider..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="plan" label="Plan" :value="old('plan')" />
                <x-form.input name="ip_address" label="IP Address" :value="old('ip_address')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="password" name="password" label="Password" :value="old('password')" autocomplete="new-password" />
                <x-form.input name="os" label="OS" :value="old('os')" />
                <x-form.input type="number" name="ram_mb" label="RAM (MB)" :value="old('ram_mb')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" name="disk_gb" label="Disk (GB)" :value="old('disk_gb')" />
                <x-form.input type="number" name="cpu_cores" label="CPU Cores" :value="old('cpu_cores')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="department" label="Department" :value="old('department')" />
                <x-form.input name="location" label="Location" :value="old('location')" />
            </div>
            <x-form.textarea name="login_ids" label="Login IDs (JSON array)" :value="old('login_ids')" />
            <x-form.textarea name="additional_ips" label="Additional IPs (JSON array)" :value="old('additional_ips')" />
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost')" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', 12)" />
                <x-form.input type="date" name="start_date" label="Start Date" :value="old('start_date')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date')" />
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended']" :value="old('status')" required />
            </div>

            <x-form.textarea name="description" label="Description" :value="old('description')" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('vps.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

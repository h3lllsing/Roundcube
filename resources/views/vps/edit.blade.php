@extends('layouts.admin')

@section('title', 'Edit VPS')
@section('breadcrumbTitle', $vps->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit VPS" subtitle="Update VPS details" />

    <form action="{{ route('vps.update', $vps->id) }}" method="POST">
        <x-card class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $vps->updated_at->format('Y-m-d H:i:s') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="name" label="Name" :value="old('name', $vps->name)" required />
                <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id', $vps->service_provider_id)" placeholder="Select provider..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="plan" label="Plan" :value="old('plan', $vps->plan)" />
                <x-form.input name="ip_address" label="IP Address" :value="old('ip_address', $vps->ip_address)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="password" name="password" label="Password" value="" autocomplete="new-password" />
                <p class="text-xs text-gray-500 dark:text-gray-400 -mt-3">Leave blank to keep current value.</p>
                <x-form.input name="os" label="OS" :value="old('os', $vps->os)" />
                <x-form.input type="number" name="ram_mb" label="RAM (MB)" :value="old('ram_mb', $vps->ram_mb)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" name="disk_gb" label="Disk (GB)" :value="old('disk_gb', $vps->disk_gb)" />
                <x-form.input type="number" name="cpu_cores" label="CPU Cores" :value="old('cpu_cores', $vps->cpu_cores)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="department" label="Department" :value="old('department', $vps->department)" />
                <x-form.input name="location" label="Location" :value="old('location', $vps->location)" />
            </div>
            <x-form.textarea name="login_ids" label="Login IDs (JSON array)" :value="old('login_ids', $vps->login_ids ? json_encode($vps->login_ids) : '')" />
            <x-form.textarea name="additional_ips" label="Additional IPs (JSON array)" :value="old('additional_ips', $vps->additional_ips ? json_encode($vps->additional_ips) : '')" />
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost', $vps->cost)" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', $vps->billing_period_months ?? 12)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="start_date" label="Start Date" :value="old('start_date', $vps->start_date?->format('Y-m-d'))" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date', $vps->expiry_date?->format('Y-m-d'))" />
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended']" :value="old('status', $vps->status)" required />
            </div>

            <x-form.textarea name="description" label="Notes" :value="old('description', $vps->description)" />

            <x-notes-thread :model="$vps" notable-type="App\Models\Vps" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('vps.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

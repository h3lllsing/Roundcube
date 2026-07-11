@extends('layouts.admin')

@section('title', 'Edit Hosting')
@section('breadcrumbTitle', $hosting->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit Hosting" subtitle="Update hosting details" />

    <form action="{{ route('hostings.update', $hosting->id) }}" method="POST">
        <x-card class="space-y-6">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $hosting->updated_at->format('Y-m-d H:i:s') }}">

            {{-- Overview --}}
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Overview</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="name" label="Name" :value="old('name', $hosting->name)" required />
                    <x-form.input name="domain" label="Domain" :value="old('domain', $hosting->domain)" />
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                    <x-form.input name="plan" label="Plan" :value="old('plan', $hosting->plan)" />
                    <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id', $hosting->service_provider_id)" placeholder="Select provider..." />
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            {{-- Access --}}
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Access</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="username" label="Username" :value="old('username', $hosting->username)" />
                    <x-form.input name="cpanel_url" label="cPanel URL" :value="old('cpanel_url', $hosting->cpanel_url)" />
                </div>
                <div class="mt-4">
                    <x-form.password name="password" label="Password" placeholder="Leave empty to keep current" autocomplete="new-password" />
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            {{-- Infrastructure / Technical --}}
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Infrastructure / Technical</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="cpanel_ip" label="cPanel IP" :value="old('cpanel_ip', $hosting->cpanel_ip)" />
                    <x-form.input name="domain_ip" label="Domain IP" :value="old('domain_ip', $hosting->domain_ip)" />
                </div>
                <div class="mt-4">
                    <x-form.input name="mail_domain_ip" label="Mail Domain IP" :value="old('mail_domain_ip', $hosting->mail_domain_ip)" />
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            {{-- Schedule --}}
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Schedule</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input type="date" name="start_date" label="Start Date" :value="old('start_date', $hosting->start_date?->format('Y-m-d'))" />
                    <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date', $hosting->expiry_date?->format('Y-m-d'))" />
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            {{-- Financial --}}
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Financial</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost', $hosting->cost)" />
                    <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', $hosting->billing_period_months ?? 12)" />
                </div>
            </div>

            <hr class="border-gray-200 dark:border-gray-700">

            {{-- Status & Notes --}}
            <div>
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100 mb-3">Status &amp; Notes</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended', 'pending_transfer' => 'Pending Transfer', 'cancelled' => 'Cancelled']" :value="old('status', $hosting->status)" required />
                </div>
                <div class="mt-4">
                    <x-form.textarea name="description" label="Notes" :value="old('description', $hosting->description)" />
                </div>
            </div>

            <x-notes-thread :model="$hosting" notable-type="App\Models\Hosting" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('hostings.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

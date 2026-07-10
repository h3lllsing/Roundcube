@extends('layouts.admin')

@section('title', 'Add Standalone Renewal Item')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Add Standalone Renewal Item" subtitle="Create a manually managed renewal record" />

    <form action="{{ route('expiry-trackers.store') }}" method="POST" class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="name" label="Name" :value="old('name')" required />
            <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id')" placeholder="Select provider..." />
        </div>

        <x-form.input name="username" label="Username" :value="old('username')" />

        <x-form.input name="login_url" label="Login URL" :value="old('login_url')" />

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost')" />
            <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', 12)" />
            <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date')" />
            <x-form.input type="date" name="renewal_date" label="Renewal Date" :value="old('renewal_date')" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select name="status" label="Status" :options="['active' => 'Active', 'expired' => 'Expired', 'pending_renewal' => 'Pending Renewal', 'cancelled' => 'Cancelled']" :value="old('status')" />
        </div>

        <x-form.textarea name="description" label="Description" :value="old('description')" />

        @include('expiry-trackers._notification-form')

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">Save</x-button>
            <x-button href="{{ route('expiry-trackers.index') }}" variant="outline" size="sm">Cancel</x-button>
        </div>
    </form>
</div>
@endsection

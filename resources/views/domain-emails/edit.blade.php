@extends('layouts.admin')

@section('title', 'Edit Domain Email')
@section('breadcrumbTitle', $email->email)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit Email Credential" subtitle="Update email account details." />

    <form action="{{ route('domain-emails.update', $email->id) }}" method="POST">
        <x-card class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $email->updated_at->format('Y-m-d H:i:s') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="email" label="Email Address" :value="old('email', $email->email)" required />
                <x-form.input type="password" name="password" label="New Password" :value="old('password')" placeholder="Leave empty to keep current" autocomplete="new-password" />
                <p class="text-xs text-gray-500 dark:text-gray-400 -mt-3">Leave blank to keep current value.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id', $email->service_provider_id)" placeholder="Select provider..." />
                <x-form.select name="domain_id" label="Domain" :options="$domains" :value="old('domain_id', $email->domain_id)" placeholder="Select domain..." />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost', $email->cost)" />
                <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', $email->billing_period_months ?? 12)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="number" name="storage_mb" label="Storage (MB)" :value="old('storage_mb', $email->storage_mb)" />
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date', $email->expiry_date?->format('Y-m-d'))" />
            </div>

            <x-form.textarea name="description" label="Notes" :value="old('description', $email->description)" />

            <x-notes-thread :model="$email" notable-type="App\Models\DomainEmail" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('domain-emails.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

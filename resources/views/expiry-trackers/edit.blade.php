@extends('layouts.admin')

@php $isLinked = !is_null($tracker->trackable_type); @endphp

@section('title', $isLinked ? 'Renewal Item' : 'Edit Renewal Item')
@section('breadcrumbTitle', $tracker->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $isLinked ? 'Renewal Item' : 'Edit Renewal Item' }}" subtitle="{{ $isLinked ? 'Linked renewal — edit notification settings' : 'Update renewal item details' }}" />

    @if($isLinked)
    <div class="bg-lime-50 dark:bg-lime-900/20 border border-lime-200 dark:border-lime-800 rounded-xl p-4 mb-6">
        <p class="text-sm text-lime-800 dark:text-lime-300"><strong>Renewal Linked to Source Service</strong></p>
        <p class="text-sm text-lime-700 dark:text-lime-400 mt-0.5">This renewal is linked to a source service. Name and expiry date are managed by the source record. Notification settings can be configured here.</p>
    </div>
    @endif

    <form action="{{ route('expiry-trackers.update', $tracker->id) }}" method="POST" class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf
        @method('PUT')
        <input type="hidden" name="updated_at" value="{{ $tracker->updated_at->format('Y-m-d H:i:s') }}">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="name" label="Name" :value="old('name', $tracker->name)" required :disabled="$isLinked" />
            <x-form.select name="service_provider_id" label="Service Provider" :options="$serviceProviders" :value="old('service_provider_id', $tracker->service_provider_id)" placeholder="Select provider..." />
        </div>

        <x-form.input name="username" label="Username" :value="old('username', $tracker->username)" />

        <x-form.input name="login_url" label="Login URL" :value="old('login_url', $tracker->login_url)" />

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost', $tracker->cost)" />
            <x-form.select name="billing_period_months" label="Billing Period" :options="[1 => 'Monthly', 3 => 'Quarterly (3 months)', 6 => 'Semi-Annual (6 months)', 12 => 'Annual (12 months)', 24 => 'Biennial (24 months)']" :value="old('billing_period_months', $tracker->billing_period_months ?? 12)" />
            <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date', $tracker->expiry_date?->format('Y-m-d'))" :disabled="$isLinked" />
            <x-form.input type="date" name="renewal_date" label="Renewal Date" :value="old('renewal_date', $tracker->renewal_date?->format('Y-m-d'))" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.select name="status" label="Status" :options="['active' => 'Active', 'expired' => 'Expired', 'pending_renewal' => 'Pending Renewal', 'cancelled' => 'Cancelled']" :value="old('status', $tracker->status)" />
        </div>

        <x-form.select name="user_id" label="User" :options="$users" :value="old('user_id', $tracker->user_id)" placeholder="Select user..." />
        <x-form.textarea name="description" label="Notes" :value="old('description', $tracker->description)" />

        <x-notes-thread :model="$tracker" notable-type="App\Models\ExpiryTracker" />

        @include('expiry-trackers._notification-form', ['tracker' => $tracker])

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">Save</x-button>
            <x-button href="{{ route('expiry-trackers.index') }}" variant="outline" size="sm">Cancel</x-button>
        </div>
    </form>
</div>
@endsection

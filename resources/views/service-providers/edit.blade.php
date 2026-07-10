@extends('layouts.admin')

@section('title', 'Edit Service Provider')
@section('breadcrumbTitle', $provider->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit Service Provider" subtitle="Update service provider details" />

    <form action="{{ route('service-providers.update', $provider->id) }}" method="POST">
        <x-card class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $provider->updated_at->format('Y-m-d H:i:s') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="name" label="Name" :value="old('name', $provider->name)" required />
                <x-form.input name="type" label="Type" :value="old('type', $provider->type)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="website" label="Web URL" :value="old('website', $provider->website)" />
                <x-form.input name="login_id" label="Login ID" :value="old('login_id', $provider->login_id)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="password" name="password" label="Password" value="" autocomplete="new-password" />
                <x-form.input name="email" label="Email Address" type="email" :value="old('email', $provider->email)" />
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-3">Leave password blank to keep current value.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="provider" label="Provider" :value="old('provider', $provider->provider)" />
                <x-form.input type="number" step="0.01" name="cost" label="Monthly Cost" :value="old('cost', $provider->cost)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input type="date" name="start_date" label="Start Date" :value="old('start_date', $provider->start_date?->format('Y-m-d'))" />
                <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date', $provider->expiry_date?->format('Y-m-d'))" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended']" :value="old('status', $provider->status)" required />
            </div>

            <x-form.textarea name="description" label="Notes" :value="old('description', $provider->description)" />

            <x-notes-thread :model="$provider" notable-type="App\Models\ServiceProvider" />

            <div class="flex items-center gap-3 pt-2">
                <x-button href="{{ route('service-providers.show', $provider->id) }}" variant="outline" size="sm">View</x-button>
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('service-providers.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

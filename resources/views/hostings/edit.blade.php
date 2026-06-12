@extends('layouts.admin')

@section('title', 'Edit Hosting')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold">Edit Hosting</h1>
    </div>

    <form action="{{ route('hostings.update', $hosting->id) }}" method="POST" class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="name" label="Name" :value="old('name', $hosting->name)" required />
            <x-form.input name="provider" label="Provider" :value="old('provider', $hosting->provider)" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="plan" label="Plan" :value="old('plan', $hosting->plan)" />
            <x-form.input name="domain" label="Domain" :value="old('domain', $hosting->domain)" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input type="number" step="0.01" name="cost" label="Cost" :value="old('cost', $hosting->cost)" />
            <x-form.input type="date" name="start_date" label="Start Date" :value="old('start_date', $hosting->start_date?->format('Y-m-d'))" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input type="date" name="expiry_date" label="Expiry Date" :value="old('expiry_date', $hosting->expiry_date?->format('Y-m-d'))" />
            <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'expired' => 'Expired', 'suspended' => 'Suspended']" :value="old('status', $hosting->status)" required />
        </div>

        <x-form.select name="module_id" label="Module" :options="$modules" :value="old('module_id', $hosting->module_id)" placeholder="Select module..." />
        <x-form.select name="user_id" label="User" :options="$users" :value="old('user_id', $hosting->user_id)" placeholder="Select user..." />

        <x-form.textarea name="notes" label="Notes" :value="old('notes', $hosting->notes)" />

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Save</button>
            <a href="{{ route('hostings.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900">Cancel</a>
        </div>
    </form>
</div>
@endsection

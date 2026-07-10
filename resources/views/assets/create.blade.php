@extends('layouts.admin')

@section('title', 'Create Asset')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Create Asset" subtitle="Add a new IT asset" />

    <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <x-card>
            <div class="space-y-4">
                <x-form.input name="asset_tag" label="Asset ID (leave blank for auto-generate)" :value="old('asset_tag')" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="brand" label="Brand" :value="old('brand')" />
                    <x-form.input name="model" label="Model" :value="old('model')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="processor" label="Processor" :value="old('processor')" />
                    <x-form.input name="ram" label="RAM" :value="old('ram')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="storage" label="Storage" :value="old('storage')" />
                    <x-form.input name="os" label="Operating System" :value="old('os')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="serial_number" label="Serial Number" :value="old('serial_number')" />
                    <x-form.select name="status" label="Status" :options="['available' => 'Available', 'assigned' => 'Assigned', 'lost' => 'Lost', 'decommissioned' => 'Decommissioned']" :value="old('status', 'available')" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="headphone" label="Headphone" :value="old('headphone')" />
                    <x-form.input name="additional_equipments" label="Additional Equipments" :value="old('additional_equipments')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="assigned_user_name" label="Assigned Employee Name" :value="old('assigned_user_name')" />
                    <x-form.input name="reporting_authority" label="Reporting Authority" :value="old('reporting_authority')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="department" label="Department" :value="old('department')" />
                    <x-form.input name="premises" label="Premises" :value="old('premises')" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="anydesk_id" label="AnyDesk ID" :value="old('anydesk_id')" />
                    <x-form.input name="anydesk_password" label="AnyDesk Password" :value="old('anydesk_password')" />
                </div>

                <x-form.textarea name="additional_comments" label="Additional Comments" :value="old('additional_comments')" />

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Primary Image</label>
                    <input type="file" name="primary_image" accept="image/jpeg,image/png,image/webp" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300" />
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary" size="sm">Save</x-button>
                    <x-button href="{{ route('assets.index') }}" variant="outline" size="sm">Cancel</x-button>
                </div>
            </div>
        </x-card>
    </form>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Edit Asset')
@section('breadcrumbTitle', $asset->asset_tag)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit Asset" subtitle="Update asset details" />

    <form action="{{ route('assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <input type="hidden" name="updated_at" value="{{ $asset->updated_at->format('Y-m-d H:i:s') }}">
        <x-card>
            <div class="space-y-4">
                <x-form.input name="asset_tag" label="Asset ID" :value="old('asset_tag', $asset->asset_tag)" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="brand" label="Brand" :value="old('brand', $asset->brand)" />
                    <x-form.input name="model" label="Model" :value="old('model', $asset->model)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="processor" label="Processor" :value="old('processor', $asset->processor)" />
                    <x-form.input name="ram" label="RAM" :value="old('ram', $asset->ram)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="storage" label="Storage" :value="old('storage', $asset->storage)" />
                    <x-form.input name="os" label="Operating System" :value="old('os', $asset->os)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="serial_number" label="Serial Number" :value="old('serial_number', $asset->serial_number)" />
                    <x-form.select name="status" label="Status" :options="['available' => 'Available', 'assigned' => 'Assigned', 'lost' => 'Lost', 'decommissioned' => 'Decommissioned']" :value="old('status', $asset->status)" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="headphone" label="Headphone" :value="old('headphone', $asset->headphone)" />
                    <x-form.input name="additional_equipments" label="Additional Equipments" :value="old('additional_equipments', $asset->additional_equipments)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="assigned_user_name" label="Assigned Employee Name" :value="old('assigned_user_name', $asset->assigned_user_name)" />
                    <x-form.input name="reporting_authority" label="Reporting Authority" :value="old('reporting_authority', $asset->reporting_authority)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="department" label="Department" :value="old('department', $asset->department)" />
                    <x-form.input name="premises" label="Premises" :value="old('premises', $asset->premises)" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-form.input name="anydesk_id" label="AnyDesk ID" :value="old('anydesk_id', $asset->anydesk_id)" />
                    <x-form.input name="anydesk_password" label="AnyDesk Password" :value="old('anydesk_password', $asset->anydesk_password)" />
                </div>

                <x-form.textarea name="additional_comments" label="Additional Comments" :value="old('additional_comments', $asset->additional_comments)" />

                <x-notes-thread :model="$asset" notable-type="App\Models\Asset" />

                @if($asset->primary_image)
                <div>
                    <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Current Image</p>
                    <img src="{{ asset('storage/' . $asset->primary_image) }}" alt="" role="presentation" loading="lazy" class="w-48 h-auto rounded-lg mb-2">
                </div>
                @endif

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $asset->primary_image ? 'Replace Image' : 'Primary Image' }}</label>
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

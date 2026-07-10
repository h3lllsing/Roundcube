@extends('layouts.admin')

@section('title', 'Create G-Mail')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Create G-Mail" subtitle="Add a new G-Mail account" />

    <form action="{{ route('g-mails.store') }}" method="POST">
        <x-card class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="user_name" label="User Name" :value="old('user_name')" />
                <x-form.input name="pseudo" label="PSEUDO" :value="old('pseudo')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="emails_address" label="EMAILS ADDRESS" :value="old('emails_address')" />
                <x-form.input type="password" name="password" label="PASSWORDS" autocomplete="new-password" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="security_number" label="Security number" :value="old('security_number')" />
                <x-form.input name="security_number_person" label="Security number person" :value="old('security_number_person')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="recovery_email" label="Recovery Email" :value="old('recovery_email')" />
                <x-form.input name="department" label="Department" :value="old('department')" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended']" :value="old('status')" />
                <x-form.input name="assigned" label="ASSIGNED" :value="old('assigned')" />
            </div>

            <x-form.textarea name="user_remarks" label="USER REMARKS" :value="old('user_remarks')" />
            <x-form.textarea name="comments" label="COMMENTS" :value="old('comments')" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('g-mails.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

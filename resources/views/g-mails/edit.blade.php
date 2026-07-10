@extends('layouts.admin')

@section('title', 'Edit G-Mail')
@section('breadcrumbTitle', $gMail->user_name ?? $gMail->id)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Edit G-Mail" subtitle="Update G-Mail details" />

    <form action="{{ route('g-mails.update', $gMail->id) }}" method="POST">
        <x-card class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $gMail->updated_at->format('Y-m-d H:i:s') }}">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="user_name" label="User Name" :value="old('user_name', $gMail->user_name)" />
                <x-form.input name="pseudo" label="PSEUDO" :value="old('pseudo', $gMail->pseudo)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="emails_address" label="EMAILS ADDRESS" :value="old('emails_address', $gMail->emails_address)" />
                <x-form.input type="password" name="password" label="PASSWORDS" placeholder="Leave empty to keep current" autocomplete="new-password" />
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 -mt-3">Leave blank to keep current value.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="security_number" label="Security number" :value="old('security_number', $gMail->security_number)" />
                <x-form.input name="security_number_person" label="Security number person" :value="old('security_number_person', $gMail->security_number_person)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="recovery_email" label="Recovery Email" :value="old('recovery_email', $gMail->recovery_email)" />
                <x-form.input name="department" label="Department" :value="old('department', $gMail->department)" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended']" :value="old('status', $gMail->status)" />
                <x-form.input name="assigned" label="ASSIGNED" :value="old('assigned', $gMail->assigned)" />
            </div>

            <x-form.textarea name="user_remarks" label="USER REMARKS" :value="old('user_remarks', $gMail->user_remarks)" />
            <x-form.textarea name="comments" label="COMMENTS" :value="old('comments', $gMail->comments)" />

            <div class="flex items-center gap-3 pt-2">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('g-mails.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </x-card>
    </form>
</div>
@endsection

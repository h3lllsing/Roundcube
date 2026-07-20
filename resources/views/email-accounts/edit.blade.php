@extends('layouts.admin')

@section('title', 'Edit Email Account')

@section('content')
<x-page-header title="Edit Email Account" subtitle="{{ $emailAccount->email }}" backUrl="{{ route('email_accounts.show', $emailAccount) }}" backLabel="Back to Email Account" />

<div class="max-w-3xl">
    <x-card>
        <form method="POST" action="{{ route('email_accounts.update', $emailAccount) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $emailAccount->updated_at }}" />

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.select name="domain_id" label="Domain" :options="$domains->pluck('name', 'id')->toArray()" value="{{ $emailAccount->domain_id }}" required />

                    <x-form.input name="email" label="Email Address" value="{{ $emailAccount->email }}" required />
                </div>

                <x-form.password name="password" label="New Password" placeholder="Leave blank to keep current" />

                <hr class="border-gray-200 dark:border-gray-700">

                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">IMAP Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.input name="imap_host" label="IMAP Host" value="{{ $emailAccount->imap_host }}" required />
                    <x-form.input name="imap_port" label="IMAP Port" type="number" value="{{ $emailAccount->imap_port }}" required />
                </div>

                <x-form.select name="imap_encryption" label="IMAP Encryption" :options="['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None']" value="{{ $emailAccount->imap_encryption }}" required />

                <hr class="border-gray-200 dark:border-gray-700">

                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">SMTP Settings</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.input name="smtp_host" label="SMTP Host" value="{{ $emailAccount->smtp_host }}" />
                    <x-form.input name="smtp_port" label="SMTP Port" type="number" value="{{ $emailAccount->smtp_port }}" />
                </div>

                <x-form.select name="smtp_encryption" label="SMTP Encryption" :options="['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None']" value="{{ $emailAccount->smtp_encryption }}" />

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.input name="smtp_username" label="SMTP Username" value="{{ $emailAccount->smtp_username }}" />
                    <x-form.password name="smtp_password" label="New SMTP Password" placeholder="Leave blank to keep current" />
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <x-form.checkbox name="sync_enabled" label="Enable Sync" {{ $emailAccount->sync_enabled ? 'checked' : '' }} />

                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'suspended' => 'Suspended']" value="{{ $emailAccount->status }}" required />

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary" x-on:click="startLoading($el)">Update Email Account</x-button>
                    <x-button href="{{ route('email_accounts.show', $emailAccount) }}" variant="outline">Cancel</x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection

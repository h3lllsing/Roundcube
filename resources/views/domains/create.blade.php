@extends('layouts.admin')

@section('title', 'Create Domain')

@section('content')
<x-page-header title="Create Domain" subtitle="Add a new email domain." backUrl="{{ route('domains.index') }}" backLabel="Back to Domains" />

<div class="max-w-2xl">
    <x-card>
        <form method="POST" action="{{ route('domains.store') }}">
            @csrf

            <div class="space-y-5">
                <x-form.input name="name" label="Domain Name" placeholder="example.com" required />

                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'suspended' => 'Suspended', 'expired' => 'Expired']" value="active" required />

                <x-form.textarea name="notes" label="Notes" rows="3" />

                <details class="text-sm text-gray-500 dark:text-gray-400 cursor-pointer">
                    <summary class="hover:text-indigo-600 font-medium">Mail Server Settings (optional)</summary>
                    <div class="mt-4 space-y-5 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">IMAP Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-form.input name="imap_host" label="IMAP Host" placeholder="mail.example.com" />
                            <x-form.input name="imap_port" label="IMAP Port" type="number" value="993" />
                        </div>
                        <x-form.select name="imap_encryption" label="IMAP Encryption" :options="['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None']" value="ssl" />

                        <hr class="border-gray-200 dark:border-gray-700">

                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">SMTP Settings</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-form.input name="smtp_host" label="SMTP Host" placeholder="mail.example.com" />
                            <x-form.input name="smtp_port" label="SMTP Port" type="number" value="587" />
                        </div>
                        <x-form.select name="smtp_encryption" label="SMTP Encryption" :options="['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None']" value="tls" />
                        <x-form.input name="smtp_username" label="SMTP Username" placeholder="Same as email" />
                    </div>
                </details>

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary" x-on:click="startLoading($el)">Create Domain</x-button>
                    <x-button href="{{ route('domains.index') }}" variant="outline">Cancel</x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection

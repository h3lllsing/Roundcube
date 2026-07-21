@extends('layouts.admin')

@section('title', 'Create Email Account')

@section('content')
<x-page-header title="Create Email Account" subtitle="IMAP/SMTP settings auto-detected from email domain." backUrl="{{ route('email_accounts.index') }}" backLabel="Back to Email Accounts" />

<div class="max-w-3xl">
    <x-card>
        <form method="POST" action="{{ route('email_accounts.store') }}">
            @csrf

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.select name="domain_id" label="Domain" :options="$domains->pluck('name', 'id')->toArray()" value="{{ request('domain_id') }}" required />

                    <x-form.input name="email" label="Email Address" id="email-input" placeholder="user@example.com" required />
                </div>

                <x-form.password name="password" label="Password" required />

                <hr class="border-gray-200 dark:border-gray-700">

                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">IMAP Settings <span id="imap-status" class="text-xs font-normal text-green-600 dark:text-green-400"></span> <span id="imap-error" class="text-xs font-normal text-red-600 dark:text-red-400 hidden"></span></h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.input name="imap_host" id="imap_host" label="IMAP Host" placeholder="Auto-detected" required />
                    <x-form.input name="imap_port" id="imap_port" label="IMAP Port" type="number" value="993" required />
                </div>

                <x-form.select name="imap_encryption" id="imap_encryption" label="IMAP Encryption" :options="['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None']" value="ssl" required />

                <hr class="border-gray-200 dark:border-gray-700">

                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">SMTP Settings <span id="smtp-status" class="text-xs font-normal text-green-600 dark:text-green-400"></span> <span id="smtp-error" class="text-xs font-normal text-red-600 dark:text-red-400 hidden"></span></h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.input name="smtp_host" id="smtp_host" label="SMTP Host" placeholder="Auto-detected" />
                    <x-form.input name="smtp_port" id="smtp_port" label="SMTP Port" type="number" value="587" />
                </div>

                <x-form.select name="smtp_encryption" id="smtp_encryption" label="SMTP Encryption" :options="['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None']" value="tls" />

                <div class="grid grid-cols-1 gap-5">
                    <x-form.input name="smtp_username" id="smtp_username" label="SMTP Username" placeholder="Auto-detected (same as email)" />
                </div>
                <details class="text-sm text-gray-500 dark:text-gray-400 cursor-pointer">
                    <summary class="hover:text-indigo-600">Advanced SMTP settings</summary>
                    <div class="mt-3 grid grid-cols-1 md:grid-cols-2 gap-5">
                        <x-form.password name="smtp_password" label="SMTP Password (leave empty to use IMAP password)" />
                    </div>
                </details>

                <hr class="border-gray-200 dark:border-gray-700">

                <x-form.checkbox name="sync_enabled" label="Enable Sync" checked />

                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'suspended' => 'Suspended']" value="active" required />

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary" x-on:click="startLoading($el)">Create Email Account</x-button>
                    <x-button href="{{ route('email_accounts.index') }}" variant="outline">Cancel</x-button>
                </div>
            </div>
        </form>
    </x-card>
</div>
@endsection

@push('scripts')
<script>
(function() {
    var emailInput = document.getElementById('email-input');
    if (!emailInput) return;
    var debounceTimer;

    function isFieldEmpty(id) {
        var el = document.getElementById(id);
        return !el || !el.value || el.value === el.getAttribute('data-default') || el.placeholder === el.value;
    }

    function setVal(id, val) {
        var el = document.getElementById(id);
        if (el) el.value = val;
    }

    emailInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(doAutoDiscover, 600);
    });

    function doAutoDiscover() {
        var email = emailInput.value.trim();
        if (!email || !email.includes('@')) return;

        var imapFields = ['imap_host','imap_port','imap_encryption'];
        var smtpFields = ['smtp_host','smtp_port','smtp_encryption','smtp_username'];

        if (imapFields.some(function(f) { return !isFieldEmpty(f); })) return;

        var statusEls = {imap: document.getElementById('imap-status'), smtp: document.getElementById('smtp-status')};
        var errorEls = {imap: document.getElementById('imap-error'), smtp: document.getElementById('smtp-error')};

        Object.values(errorEls).forEach(function(el) { if (el) { el.textContent = ''; el.classList.add('hidden'); } });
        Object.values(statusEls).forEach(function(el) { if (el) el.textContent = 'detecting...'; });

        var controller = new AbortController();
        var timeoutId = setTimeout(function() { controller.abort(); }, 15000);

        fetch('{{ route("email_accounts.auto-discover") }}?email=' + encodeURIComponent(email), { signal: controller.signal })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                clearTimeout(timeoutId);
                if (data.imap_host && isFieldEmpty('imap_host')) {
                    setVal('imap_host', data.imap_host);
                    setVal('imap_port', data.imap_port);
                    setVal('imap_encryption', data.imap_encryption);
                    if (statusEls.imap) statusEls.imap.textContent = '\u2713 ' + data.imap_host + ':' + data.imap_port;
                }
                if (data.smtp_host && isFieldEmpty('smtp_host')) {
                    setVal('smtp_host', data.smtp_host);
                    setVal('smtp_port', data.smtp_port);
                    setVal('smtp_encryption', data.smtp_encryption);
                    setVal('smtp_username', email);
                    if (statusEls.smtp) statusEls.smtp.textContent = '\u2713 ' + data.smtp_host + ':' + data.smtp_port;
                }
            })
            .catch(function() {
                clearTimeout(timeoutId);
                Object.values(statusEls).forEach(function(el) { if (el) el.textContent = ''; });
            });
    }
})();
</script>
@endpush

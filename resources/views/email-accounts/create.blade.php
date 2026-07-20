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

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.input name="smtp_username" id="smtp_username" label="SMTP Username" placeholder="Auto-detected" />
                    <x-form.password name="smtp_password" label="SMTP Password" />
                </div>

                <hr class="border-gray-200 dark:border-gray-700">

                <x-form.checkbox name="sync_enabled" label="Enable Sync" checked />

                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'suspended' => 'Suspended']" value="active" required />

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary">Create Email Account</x-button>
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
    var originalValues = {};
    emailInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(doAutoDiscover, 600);
    });
    function doAutoDiscover() {
        var email = emailInput.value.trim();
        if (!email || !email.includes('@')) return;
        var statusEls = {imap: document.getElementById('imap-status'), smtp: document.getElementById('smtp-status')};
        var errorEls = {imap: document.getElementById('imap-error'), smtp: document.getElementById('smtp-error')};
        var fields = ['imap_host','imap_port','imap_encryption','smtp_host','smtp_port','smtp_encryption','smtp_username'];
        originalValues = {};
        fields.forEach(function(f) {
            var el = document.getElementById(f);
            if (el) { originalValues[f] = el.value; el.value = ''; el.disabled = true; }
        });
        Object.values(errorEls).forEach(function(el) { if (el) { el.textContent = ''; el.classList.add('hidden'); } });
        Object.values(statusEls).forEach(function(el) { if (el) el.textContent = 'detecting...'; });
        var controller = new AbortController();
        var timeoutId = setTimeout(function() { controller.abort(); }, 15000);
        fetch('{{ route("email-accounts.auto-discover") }}?email=' + encodeURIComponent(email), { signal: controller.signal })
            .then(function(r) {
                if (!r.ok) throw new Error('HTTP ' + r.status);
                return r.json();
            })
            .then(function(data) {
                if (data.imap_host) {
                    document.getElementById('imap_host').value = data.imap_host;
                    document.getElementById('imap_port').value = data.imap_port;
                    document.getElementById('imap_encryption').value = data.imap_encryption;
                    if (statusEls.imap) statusEls.imap.textContent = '\u2713 ' + data.imap_host + ':' + data.imap_port;
                }
                if (data.smtp_host) {
                    document.getElementById('smtp_host').value = data.smtp_host;
                    document.getElementById('smtp_port').value = data.smtp_port;
                    document.getElementById('smtp_encryption').value = data.smtp_encryption;
                    document.getElementById('smtp_username').value = email;
                    if (statusEls.smtp) statusEls.smtp.textContent = '\u2713 ' + data.smtp_host + ':' + data.smtp_port;
                }
                clearTimeout(timeoutId);
            })
            .catch(function() {
                clearTimeout(timeoutId);
                Object.keys(originalValues).forEach(function(f) {
                    var el = document.getElementById(f);
                    if (el) el.value = originalValues[f];
                });
                Object.values(statusEls).forEach(function(el) { if (el) el.textContent = ''; });
                if (errorEls.imap) { errorEls.imap.textContent = 'Auto-detect failed'; errorEls.imap.classList.remove('hidden'); }
                if (errorEls.smtp) { errorEls.smtp.textContent = 'Auto-detect failed'; errorEls.smtp.classList.remove('hidden'); }
            })
            .finally(function() {
                fields.forEach(function(f) {
                    var el = document.getElementById(f);
                    if (el) el.disabled = false;
                });
            });
    }
})();
</script>
@endpush

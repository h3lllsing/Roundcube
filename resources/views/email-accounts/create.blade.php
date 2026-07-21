@extends('layouts.admin')

@section('title', 'Create Email Account')

@section('content')
<x-page-header title="Create Email Account" subtitle="Enter email and password, then test the connection." backUrl="{{ route('email_accounts.index') }}" backLabel="Back to Email Accounts" />

<div class="max-w-3xl">
    <x-card>
        <form method="POST" action="{{ route('email_accounts.store') }}" id="create-form">
            @csrf

            <div class="space-y-5">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <x-form.select name="domain_id" label="Domain" :options="$domains->pluck('name', 'id')->toArray()" value="{{ request('domain_id') }}" required />

                    <x-form.input name="email" label="Email Address" id="email-input" placeholder="user@example.com" required />
                </div>

                <x-form.password name="password" label="Password" required />

                <div id="test-result" class="hidden text-sm"></div>

                <div class="flex items-center gap-3">
                    <x-button type="button" variant="primary" id="test-btn">
                        <span id="test-btn-text">Test Connection</span>
                        <svg id="test-spinner" class="hidden animate-spin h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </x-button>
                </div>

                <details class="text-sm text-gray-500 dark:text-gray-400 cursor-pointer">
                    <summary class="hover:text-indigo-600 font-medium">Advanced mail server settings</summary>
                    <div class="mt-4 space-y-5 border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">IMAP Settings <span id="imap-status" class="text-xs font-normal text-green-600 dark:text-green-400"></span></h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-form.input name="imap_host" id="imap_host" label="IMAP Host" placeholder="Auto-filled after test" />
                            <x-form.input name="imap_port" id="imap_port" label="IMAP Port" type="number" value="993" />
                        </div>

                        <x-form.select name="imap_encryption" id="imap_encryption" label="IMAP Encryption" :options="['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None']" value="ssl" />

                        <hr class="border-gray-200 dark:border-gray-700">

                        <h3 class="text-sm font-semibold text-gray-900 dark:text-white">SMTP Settings <span id="smtp-status" class="text-xs font-normal text-green-600 dark:text-green-400"></span></h3>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <x-form.input name="smtp_host" id="smtp_host" label="SMTP Host" placeholder="Auto-filled after test" />
                            <x-form.input name="smtp_port" id="smtp_port" label="SMTP Port" type="number" value="587" />
                        </div>

                        <x-form.select name="smtp_encryption" id="smtp_encryption" label="SMTP Encryption" :options="['ssl' => 'SSL', 'tls' => 'TLS', 'none' => 'None']" value="tls" />

                        <div class="grid grid-cols-1 gap-5">
                            <x-form.input name="smtp_username" id="smtp_username" label="SMTP Username" placeholder="Same as email" />
                        </div>

                        <x-form.password name="smtp_password" label="SMTP Password (leave empty to use IMAP password)" />
                    </div>
                </details>

                <x-form.checkbox name="sync_enabled" label="Enable Sync" checked />

                <hr class="border-gray-200 dark:border-gray-700">

                <x-form.select name="status" label="Status" :options="['active' => 'Active', 'suspended' => 'Suspended']" value="active" required />

                <div class="flex items-center gap-3 pt-2">
                    <x-button type="submit" variant="primary" id="save-btn" disabled x-on:click="startLoading($el)">Create Email Account</x-button>
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
    var emailInput = document.getElementById('email');
    var passwordInput = document.querySelector('input[name="password"]');
    var testBtn = document.getElementById('test-btn');
    var testResult = document.getElementById('test-result');
    var saveBtn = document.getElementById('save-btn');
    var testSpinner = document.getElementById('test-spinner');
    var testBtnText = document.getElementById('test-btn-text');

    function testConnection() {
        var email = emailInput.value.trim();
        var password = passwordInput.value;

        if (!email || !password) {
            testResult.className = 'text-sm text-red-600';
            testResult.textContent = 'Please enter email and password first.';
            testResult.classList.remove('hidden');
            return;
        }

        testBtn.disabled = true;
        testBtnText.textContent = 'Testing...';
        testSpinner.classList.remove('hidden');
        testResult.className = 'hidden';
        saveBtn.disabled = true;

        fetch('{{ route("email_accounts.test-connection") }}', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value },
            body: JSON.stringify({ email: email, password: password })
        })
        .then(function(r) { return r.json().then(function(d) { return { status: r.status, data: d }; }); })
        .then(function(resp) {
            testBtn.disabled = false;
            testBtnText.textContent = 'Test Connection';
            testSpinner.classList.add('hidden');

            if (resp.data.success) {
                testResult.className = 'text-sm text-green-600 font-medium';
                testResult.textContent = '✓ Connected — ' + resp.data.message;
                testResult.classList.remove('hidden');
                saveBtn.disabled = false;

                var s = resp.data.settings;
                if (s.imap_host) {
                    document.getElementById('imap_host').value = s.imap_host;
                    document.getElementById('imap_port').value = s.imap_port;
                    document.getElementById('imap_encryption').value = s.imap_encryption;
                }
                if (s.smtp_host) {
                    document.getElementById('smtp_host').value = s.smtp_host;
                    document.getElementById('smtp_port').value = s.smtp_port;
                    document.getElementById('smtp_encryption').value = s.smtp_encryption;
                    document.getElementById('smtp_username').value = email;
                }
            } else {
                testResult.className = 'text-sm text-red-600';
                testResult.textContent = '✗ ' + (resp.data.message || 'Connection failed');
                testResult.classList.remove('hidden');
            }
        })
        .catch(function() {
            testBtn.disabled = false;
            testBtnText.textContent = 'Test Connection';
            testSpinner.classList.add('hidden');
            testResult.className = 'text-sm text-red-600';
            testResult.textContent = '✗ Network error. Try again.';
            testResult.classList.remove('hidden');
        });
    }

    window.testConnection = testConnection;
    testBtn.addEventListener('click', testConnection);
})();
</script>
@endpush

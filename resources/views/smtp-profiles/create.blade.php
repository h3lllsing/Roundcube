@extends('layouts.admin')

@section('title', 'Create SMTP Profile')

@section('content')
<div class="max-w-2xl mx-auto">
    <x-page-header title="Create SMTP Profile" subtitle="Add a new email sender profile" />

    <form action="{{ route('smtp-profiles.store') }}" method="POST" class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf

        <x-form.input name="name" label="Profile Name" :value="old('name')" required />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="sender_name" label="Sender Name" :value="old('sender_name')" required />
            <x-form.input type="email" name="sender_email" label="Sender Email" :value="old('sender_email')" required />
        </div>

        <x-form.input type="email" name="reply_to_email" label="Reply-To Email (optional)" :value="old('reply_to_email')" />

        <div class="flex items-center justify-end">
            <button type="button" id="btn-auto-discover"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 rounded-lg transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                Auto-Discover SMTP
            </button>
        </div>

        <hr class="border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="smtp_host" label="SMTP Host" :value="old('smtp_host')" required />
            <x-form.input type="number" name="smtp_port" label="Port Number" :value="old('smtp_port', '587')" required />
        </div>

        <x-form.select name="smtp_encryption" label="Encryption" :options="['' => 'None', 'tls' => 'TLS', 'ssl' => 'SSL']" :value="old('smtp_encryption')" />

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-form.input name="smtp_username" label="SMTP Username" :value="old('smtp_username')" required />
            <x-form.input type="password" name="smtp_password" label="SMTP Password" :value="old('smtp_password')" required autocomplete="new-password" />
        </div>

        <hr class="border-gray-200 dark:border-gray-700">

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <x-form.checkbox name="is_default" label="Set as default" :checked="old('is_default')" />
            <x-form.checkbox name="is_active" label="Active" :checked="old('is_active', true)" />
            <x-form.input type="number" name="priority" label="Priority" :value="old('priority', '100')" />
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">Save</x-button>
            <x-button href="{{ route('smtp-profiles.index') }}" variant="outline" size="sm">Cancel</x-button>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.getElementById('btn-auto-discover').addEventListener('click', function() {
    const email = document.querySelector('[name="sender_email"]').value;
    if (!email) { alert('Enter sender email first.'); return; }

    const btn = this;
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = 'Searching...';

    fetch('{{ route("smtp-profiles.auto-discover") }}?email=' + encodeURIComponent(email))
        .then(r => r.json())
        .then(data => {
            if (data.error) { alert(data.error); return; }
            document.querySelector('[name="smtp_host"]').value = data.host;
            document.querySelector('[name="smtp_port"]').value = data.port;
            document.querySelector('[name="smtp_encryption"]').value = data.encryption;
            document.querySelector('[name="smtp_username"]').value = data.username;
        })
        .catch(() => alert('Auto-discover failed. Fill settings manually.'))
        .finally(() => { btn.disabled = false; btn.innerHTML = orig; });
});
</script>
@endpush
@endsection

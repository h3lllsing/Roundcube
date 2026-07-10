@extends('layouts.admin')

@section('title', $entry->service_name)
@section('breadcrumbTitle', $entry->service_name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $entry->service_name }}" back-url="{{ route('vault.index') }}" back-label="Back to Vault">
        <x-slot:actions>
            <x-permission-check :module="$entry->module" action="update">
            <x-button href="{{ route('vault.edit', $entry->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$entry->module" action="delete">
            <form action="{{ route('vault.destroy', $entry->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Service Name" value="{{ $entry->service_name }}" />
            <x-field label="Service URL">
                @if($entry->service_url)
                    <div class="flex items-center gap-2">
                        <a href="{{ $entry->service_url }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $entry->service_url }}</a>
                        <x-copy-button :text="$entry->service_url" title="Copy URL" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Username">
                @if($entry->username)
                    <div class="flex items-center gap-2">
                        <span>{{ $entry->username }}</span>
                        <x-copy-button :text="$entry->username" title="Copy username" />
                    </div>
                @else
                    —
                @endif
            </x-field>
        </div>

        @if($entry->encrypted_password)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Access</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Password">
                <div class="flex items-center gap-2">
                    <span id="passwordDisplay" class="font-mono text-sm">{{ session('revealed_password') ?? '••••••••' }}</span>
                    @unless(session('revealed_password'))
                    <form action="{{ route('vault.reveal', $entry->id) }}" method="POST" class="inline reveal-form">
                        @csrf
                        <button type="submit" class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20">Show</button>
                    </form>
                    @else
                    <button type="button" data-vault-action="hide" class="px-2 py-1 text-xs font-medium text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-800 rounded-lg hover:bg-yellow-50 dark:hover:bg-yellow-900/20">Hide</button>
                    <button type="button" data-vault-action="copy" class="px-2 py-1 text-xs font-medium text-green-600 dark:text-green-400 border border-green-200 dark:border-green-800 rounded-lg hover:bg-green-50 dark:hover:bg-green-900/20">Copy</button>
                    @endif
                </div>
            </x-field>
        </div>
        @endif

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Module" value="{{ $entry->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $entry->user->name ?? '—' }}" />
        </div>

        @if($entry->description)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="mt-4">
            <x-field label="Description">
                <p class="text-sm whitespace-pre-wrap">{{ $entry->description }}</p>
            </x-field>
        </div>
        @endif
    </x-card>

    <x-activity-timeline subjectType="App\Models\VaultEntry" :subjectId="$entry->id" />
</div>

@push('scripts')
<script>
(function() {
    var hideBtn = document.querySelector('[data-vault-action="hide"]');
    var copyBtn = document.querySelector('[data-vault-action="copy"]');
    var displayEl = document.getElementById('passwordDisplay');
    if (!displayEl) return;
    if (hideBtn) {
        hideBtn.addEventListener('click', function() {
            window.location.reload();
        });
    }
    if (copyBtn && displayEl.textContent !== '••••••••') {
        copyBtn.addEventListener('click', function() {
            navigator.clipboard.writeText(displayEl.textContent);
            var orig = copyBtn.textContent;
            copyBtn.textContent = 'Copied!';
            setTimeout(function() { copyBtn.textContent = orig; }, 2000);
        });
    }
})();
</script>
@endpush
@endsection

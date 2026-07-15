@extends('layouts.admin')

@section('title', $email->email)
@section('breadcrumbTitle', $email->email)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $email->email }}" back-url="{{ route('domain-emails.index') }}" back-label="Back to Email Credentials">
        <x-slot:actions>
            <x-permission-check :module="$email->module" action="update">
            <x-button href="{{ route('domain-emails.edit', $email->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$email->module" action="delete">
            <form action="{{ route('domain-emails.destroy', $email->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Email">
                <div class="flex items-center gap-2">
                    <p class="font-medium">{{ $email->email }}</p>
                    <x-copy-button :text="$email->email" title="Copy email" />
                </div>
            </x-field>
            <x-field label="Password">
                @if($email->password)
                    <div class="flex items-center gap-2">
                        <span class="font-mono password-mask" data-password="{{ route('domain-emails.password', $email->id) }}">••••••••</span>
                        <x-permission-check :module="$email->module" action="reveal">
                        <x-copy-button password-route="{{ route('domain-emails.password', $email->id) }}" title="Copy password" />
                        <button type="button" aria-label="Toggle password visibility" class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 toggle-password">Show</button>
                        </x-permission-check>
                    </div>
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Relationships</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Service Provider" value="{{ $email->serviceProvider?->name ?? '—' }}" />
            <x-field label="Domain" value="{{ $email->domain->name ?? '—' }}" />
        </div>

        @if($email->cost || $email->storage_mb)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Financial</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Monthly Cost">
                @if($email->cost)
                    <x-money :value="$email->cost" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Billing Period">
                @if($email->billing_period_months)
                    {{ $email->billing_period_months }} {{ Str::plural('month', $email->billing_period_months) }}
                @else
                    —
                @endif
            </x-field>
            <x-field label="Storage">
                @if($email->storage_mb)
                    {{ $email->storage_mb }} MB
                @else
                    —
                @endif
            </x-field>
        </div>
        @endif

        @if($email->expiry_date)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Dates</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Expiry Date">
                <x-date :value="$email->expiry_date" />
            </x-field>
        </div>
        @endif

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Status">
                @if($email->status)
                    <x-badge :variant="$email->status">{{ ucfirst($email->status) }}</x-badge>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Module" value="{{ $email->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $email->user->name ?? '—' }}" />
        </div>

        @if($email->description)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="mt-4">
            <x-field label="Notes">
                <p class="text-sm whitespace-pre-wrap">{{ $email->description }}</p>
            </x-field>
        </div>
        @endif

        <x-notes-thread :model="$email" notable-type="App\Models\DomainEmail" />
    </x-card>

    <x-activity-timeline subjectType="App\Models\DomainEmail" :subjectId="$email->id" />
</div>

@push('scripts')
<script>
(function() {
    var cachedPassword = null;
    var passwordUrl = '{{ route('domain-emails.password', $email->id) }}';
    var maskEl = document.querySelector('.password-mask');
    var toggleBtn = document.querySelector('.toggle-password');

    if (maskEl && toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (cachedPassword) {
                maskEl.textContent = maskEl.textContent === '••••••••' ? cachedPassword : '••••••••';
                toggleBtn.textContent = maskEl.textContent === '••••••••' ? 'Show' : 'Hide';
                return;
            }
            fetch(passwordUrl)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    cachedPassword = data.password;
                    maskEl.textContent = data.password;
                    toggleBtn.textContent = 'Hide';
                })
                .catch(function() { alert('Failed to fetch password.'); });
        });
    }
})();
</script>
@endpush
@endsection

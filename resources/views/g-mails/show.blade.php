@extends('layouts.admin')

@section('title', $gMail->user_name ?? 'G-Mail #'.$gMail->id)
@section('breadcrumbTitle', $gMail->user_name ?? $gMail->id)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $gMail->user_name ?? 'G-Mail #'.$gMail->id }}" back-url="{{ route('g-mails.index') }}" back-label="Back to G-Mails">
        <x-slot:actions>
            <x-permission-check :module="$gMail->module" action="update">
            <x-button href="{{ route('g-mails.edit', $gMail->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$gMail->module" action="delete">
            <form action="{{ route('g-mails.destroy', $gMail->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="User Name">
                @if($gMail->user_name)
                    <div class="flex items-center gap-2">
                        <span>{{ $gMail->user_name }}</span>
                        <x-copy-button :text="$gMail->user_name" title="Copy user name" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Email Address">
                @if($gMail->emails_address)
                    <div class="flex items-center gap-2">
                        <span>{{ $gMail->emails_address }}</span>
                        <x-copy-button :text="$gMail->emails_address" title="Copy email" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Password">
                @if($gMail->password)
                    <div class="flex items-center gap-2">
                        <span class="font-mono password-mask" data-password="{{ route('g-mails.password', $gMail->id) }}">••••••••</span>
                        <x-permission-check :module="$gMail->module" action="reveal">
                        <x-copy-button password-route="{{ route('g-mails.password', $gMail->id) }}" title="Copy password" />
                        <button type="button" aria-label="Toggle password visibility" class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 toggle-password">Show</button>
                        </x-permission-check>
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Recovery Email">
                @if($gMail->recovery_email)
                    <div class="flex items-center gap-2">
                        <span>{{ $gMail->recovery_email }}</span>
                        <x-copy-button :text="$gMail->recovery_email" title="Copy recovery email" />
                    </div>
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Technical</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="PSEUDO" value="{{ $gMail->pseudo ?? '—' }}" />
            <x-field label="Security Number" value="{{ $gMail->security_number ?? '—' }}" />
            <x-field label="Security Number Person" value="{{ $gMail->security_number_person ?? '—' }}" />
            <x-field label="Department" value="{{ $gMail->department ?? '—' }}" />
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Status">
                <x-badge :variant="$gMail->status ?? 'inactive'">{{ ucfirst($gMail->status ?? 'inactive') }}</x-badge>
            </x-field>
            <x-field label="ASSIGNED" value="{{ $gMail->assigned ?? '—' }}" />
            <x-field label="Module" value="{{ $gMail->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $gMail->user->name ?? '—' }}" />
        </div>

        @if($gMail->user_remarks)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="User Remarks">
                <p class="text-sm whitespace-pre-wrap">{{ $gMail->user_remarks }}</p>
            </x-field>
            @if($gMail->comments)
            <x-field label="Comments">
                <p class="text-sm whitespace-pre-wrap">{{ $gMail->comments }}</p>
            </x-field>
            @endif
        </div>
        @endif
    </x-card>

    <x-activity-timeline subjectType="App\Models\GMail" :subjectId="$gMail->id" />
</div>

@push('scripts')
<script>
(function() {
    var cachedPassword = null;
    var passwordUrl = '{{ route('g-mails.password', $gMail->id) }}';
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

@extends('layouts.admin')

@section('title', $voip->name)
@section('breadcrumbTitle', $voip->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $voip->name }}" back-url="{{ route('voip.index') }}" back-label="Back to VoIP">
        <x-slot:actions>
            <x-monitor-button type="voip" :id="$voip->id" />
            <x-permission-check :module="$voip->module" action="update">
            <x-button href="{{ route('voip.edit', $voip->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$voip->module" action="delete">
            <form action="{{ route('voip.destroy', $voip->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Name" value="{{ $voip->name }}" />
            <x-field label="Extension" value="{{ $voip->extensions[0] ?? '—' }}" />
            <x-field label="Vendor">
                @if($voip->serviceProvider)
                    <a href="{{ route('service-providers.show', $voip->serviceProvider->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $voip->serviceProvider->name }}</a>
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Access</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Phone">
                @if($voip->phone_number)
                    <div class="flex items-center gap-2">
                        <span>{{ $voip->phone_number }}</span>
                        <x-copy-button :text="$voip->phone_number" title="Copy phone number" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Server IP">
                @if($voip->server_ip)
                    <div class="flex items-center gap-2">
                        <span class="font-mono">{{ $voip->server_ip }}</span>
                        <x-copy-button :text="$voip->server_ip" title="Copy server IP" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Extension Password">
                @if($voip->extension_password)
                    <div class="flex items-center gap-2">
                        <span class="font-mono ext-password-mask" data-password="{{ route('voip.extension-password', $voip->id) }}">••••••••</span>
                        <x-permission-check :module="$voip->module" action="reveal">
                        <x-copy-button password-route="{{ route('voip.extension-password', $voip->id) }}" title="Copy extension password" />
                        <button type="button" aria-label="Toggle password visibility" class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 toggle-ext-password">Show</button>
                        </x-permission-check>
                    </div>
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Technical</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Direction" value="{{ $voip->direction ?? '—' }}" />
            <x-field label="Number Status" value="{{ $voip->number_status ?? '—' }}" />
            <x-field label="Outbound Code" value="{{ $voip->outbound_code ?? '—' }}" />
            <x-field label="Team Details">
                @if($voip->team_details)
                    <p class="text-sm whitespace-pre-wrap">{{ $voip->team_details }}</p>
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Financial</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Monthly Cost">
                @if($voip->cost)
                    <x-money :value="$voip->cost" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Billing Period">
                @if($voip->billing_period_months)
                    {{ $voip->billing_period_months }} {{ Str::plural('month', $voip->billing_period_months) }}
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Dates</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Start Date">
                @if($voip->start_date)
                    <x-date :value="$voip->start_date" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Expiry Date">
                @if($voip->expiry_date)
                    <x-date :value="$voip->expiry_date" />
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Status">
                <x-badge :variant="$voip->status">{{ ucfirst($voip->status) }}</x-badge>
            </x-field>
            <x-field label="Module" value="{{ $voip->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $voip->user->name ?? '—' }}" />
        </div>

        @if($renewals->count())
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Renewals</h3>
        <div class="space-y-2">
            @foreach($renewals as $renewal)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div>
                        <a href="{{ route('expiry-trackers.show', $renewal->id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $renewal->name }}</a>
                        <span class="text-xs text-gray-500 ml-2">Expires: {{ $renewal->expiry_date?->format('Y-m-d') ?? '—' }}</span>
                    </div>
                    <x-badge :variant="$renewal->status">{{ $renewal->status }}</x-badge>
                </div>
            @endforeach
        </div>
        @endif

        @if($voip->description)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="mt-4">
            <x-field label="Notes">
                <p class="text-sm whitespace-pre-wrap">{{ $voip->description }}</p>
            </x-field>
        </div>
        @endif

        <x-notes-thread :model="$voip" notable-type="App\Models\Voip" />
    </x-card>

    <x-monitor-result type="voip" :id="$voip->id" />

    <x-activity-timeline subjectType="App\Models\Voip" :subjectId="$voip->id" />
</div>

@push('scripts')
<script>
(function() {
    var cached = null;
    var url = '{{ route('voip.extension-password', $voip->id) }}';
    var maskEl = document.querySelector('.ext-password-mask');
    var toggleBtn = document.querySelector('.toggle-ext-password');

    if (maskEl && toggleBtn) {
        toggleBtn.addEventListener('click', function() {
            if (cached) {
                maskEl.textContent = maskEl.textContent === '••••••••' ? cached : '••••••••';
                toggleBtn.textContent = maskEl.textContent === '••••••••' ? 'Show' : 'Hide';
                return;
            }
            fetch(url)
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    cached = data.extension_password;
                    maskEl.textContent = cached;
                    toggleBtn.textContent = 'Hide';
                })
                .catch(function() { alert('Failed to fetch password.'); });
        });
    }
})();
</script>
@endpush
@endsection

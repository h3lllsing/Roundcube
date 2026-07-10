@extends('layouts.admin')

@section('title', $vps->name)
@section('breadcrumbTitle', $vps->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $vps->name }}" back-url="{{ route('vps.index') }}" back-label="Back to VPS">
        <x-slot:actions>
            <x-monitor-button type="vps" :id="$vps->id" />
            <x-permission-check :module="$vps->module" action="update">
            <x-button href="{{ route('vps.edit', $vps->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$vps->module" action="delete">
            <form action="{{ route('vps.destroy', $vps->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Name" value="{{ $vps->name }}" />
            <x-field label="Service Provider">
                @if($vps->serviceProvider)
                    <a href="{{ route('service-providers.show', $vps->serviceProvider->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $vps->serviceProvider->name }}</a>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Plan" value="{{ $vps->plan ?? '—' }}" />
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Access</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="IP Address">
                @if($vps->ip_address)
                    <div class="flex items-center gap-2">
                        <span class="font-mono">{{ $vps->ip_address }}</span>
                        <x-copy-button :text="$vps->ip_address" title="Copy IP address" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Password">
                @if($vps->password)
                    <div class="flex items-center gap-2">
                        <span class="font-mono password-mask" data-password="{{ route('vps.password', $vps->id) }}">••••••••</span>
                        <x-permission-check :module="$vaultModule" action="reveal">
                        <x-copy-button password-route="{{ route('vps.password', $vps->id) }}" title="Copy password" />
                        <button type="button" aria-label="Toggle password visibility" class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 toggle-password">Show</button>
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
            <x-field label="OS" value="{{ $vps->os ?? '—' }}" />
            <x-field label="RAM">
                @if($vps->ram_mb)
                    {{ $vps->ram_mb }} MB
                @else
                    —
                @endif
            </x-field>
            <x-field label="Disk">
                @if($vps->disk_gb)
                    {{ $vps->disk_gb }} GB
                @else
                    —
                @endif
            </x-field>
            <x-field label="CPU Cores" value="{{ $vps->cpu_cores ?? '—' }}" />
            <x-field label="Location" value="{{ $vps->location ?? '—' }}" />
            <x-field label="Department" value="{{ $vps->department ?? '—' }}" />
            <x-field label="Login IDs">
                @if($vps->login_ids && is_array($vps->login_ids))
                    <span class="text-sm">{{ implode(', ', array_filter($vps->login_ids)) }}</span>
                @elseif($vps->login_ids)
                    <span class="text-sm">{{ $vps->login_ids }}</span>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Additional IPs">
                @if($vps->additional_ips && is_array($vps->additional_ips))
                    <span class="text-sm">{{ implode(', ', array_filter($vps->additional_ips)) }}</span>
                @elseif($vps->additional_ips)
                    <span class="text-sm">{{ $vps->additional_ips }}</span>
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Financial</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Monthly Cost">
                @if($vps->cost)
                    <x-money :value="$vps->cost" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Billing Period">
                @if($vps->billing_period_months)
                    {{ $vps->billing_period_months }} {{ Str::plural('month', $vps->billing_period_months) }}
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Dates</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Start Date">
                @if($vps->start_date)
                    <x-date :value="$vps->start_date" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Expiry Date">
                @if($vps->expiry_date)
                    <x-date :value="$vps->expiry_date" />
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Status">
                <x-badge :variant="$vps->status">{{ ucfirst($vps->status) }}</x-badge>
            </x-field>
            <x-field label="Module" value="{{ $vps->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $vps->user->name ?? '—' }}" />
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

        @if($vps->description)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="mt-4">
            <x-field label="Notes">
                <p class="text-sm whitespace-pre-wrap">{{ $vps->description }}</p>
            </x-field>
        </div>
        @endif

        <x-notes-thread :model="$vps" notable-type="App\Models\Vps" />
    </x-card>

    <x-monitor-result type="vps" :id="$vps->id" />

    <x-activity-timeline subjectType="App\Models\Vps" :subjectId="$vps->id" />
</div>

@push('scripts')
<script>
(function() {
    var cachedPassword = null;
    var passwordUrl = '{{ route('vps.password', $vps->id) }}';
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

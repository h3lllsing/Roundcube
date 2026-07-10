@extends('layouts.admin')

@section('title', $service->name)
@section('breadcrumbTitle', $service->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $service->name }}" back-url="{{ route('other-services.index') }}" back-label="Back to Other Services">
        <x-slot:actions>
            <x-monitor-button type="other-services" :id="$service->id" />
            <x-permission-check :module="$service->module" action="update">
            <x-button href="{{ route('other-services.edit', $service->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$service->module" action="delete">
            <form action="{{ route('other-services.destroy', $service->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Name" value="{{ $service->name }}" />
            <x-field label="Type" value="{{ $service->service_type ?? '—' }}" />
            <x-field label="Service Provider">
                @if($service->serviceProvider)
                    <a href="{{ route('service-providers.show', $service->serviceProvider->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $service->serviceProvider->name }}</a>
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Access</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Website">
                @if($service->website)
                    <div class="flex items-center gap-2">
                        <a href="{{ $service->website }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $service->website }}</a>
                        <x-copy-button :text="$service->website" title="Copy URL" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Login URL">
                @if($service->login_url)
                    <div class="flex items-center gap-2">
                        <a href="{{ $service->login_url }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $service->login_url }}</a>
                        <x-copy-button :text="$service->login_url" title="Copy login URL" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Username">
                @if($service->username)
                    <div class="flex items-center gap-2">
                        <span>{{ $service->username }}</span>
                        <x-copy-button :text="$service->username" title="Copy username" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Password">
                @if($service->password)
                    <div class="flex items-center gap-2">
                        <span class="font-mono password-mask" data-password="{{ route('other-services.password', $service->id) }}">••••••••</span>
                        <x-permission-check :module="$vaultModule" action="reveal">
                        <x-copy-button password-route="{{ route('other-services.password', $service->id) }}" title="Copy password" />
                        <button type="button" aria-label="Toggle password visibility" class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 toggle-password">Show</button>
                        </x-permission-check>
                    </div>
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Financial</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Monthly Cost">
                @if($service->cost)
                    <x-money :value="$service->cost" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Billing Period">
                @if($service->billing_period_months)
                    {{ $service->billing_period_months }} {{ Str::plural('month', $service->billing_period_months) }}
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Dates</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Start Date">
                @if($service->start_date)
                    <x-date :value="$service->start_date" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Expiry Date">
                @if($service->expiry_date)
                    <x-date :value="$service->expiry_date" />
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Status">
                <x-badge :variant="$service->status">{{ ucfirst($service->status ?? '—') }}</x-badge>
            </x-field>
            <x-field label="Module" value="{{ $service->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $service->user->name ?? '—' }}" />
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

        @if($service->description)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="mt-4">
            <x-field label="Notes">
                <p class="text-sm whitespace-pre-wrap">{{ $service->description }}</p>
            </x-field>
        </div>
        @endif

        <x-notes-thread :model="$service" notable-type="App\Models\OtherService" />
    </x-card>

    <x-monitor-result type="other-services" :id="$service->id" />

    <x-activity-timeline subjectType="App\Models\OtherService" :subjectId="$service->id" />
</div>

@push('scripts')
<script>
(function() {
    var cachedPassword = null;
    var passwordUrl = '{{ route('other-services.password', $service->id) }}';
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

@extends('layouts.admin')

@section('title', $provider->name)
@section('breadcrumbTitle', $provider->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $provider->name }}" back-url="{{ route('service-providers.index') }}" back-label="Back to Service Providers">
        <x-slot:actions>
            <x-monitor-button type="service-providers" :id="$provider->id" />
            <x-permission-check :module="$provider->module" action="update">
            <x-button href="{{ route('service-providers.edit', $provider->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$provider->module" action="delete">
            <form action="{{ route('service-providers.destroy', $provider->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    {{-- OVERVIEW --}}
    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Name" value="{{ $provider->name }}" />
            <x-field label="Type" value="{{ $provider->type ?? '—' }}" />
        </div>

        {{-- ACCESS --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Access</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Web URL">
                @if($provider->website)
                    <div class="flex items-center gap-2">
                        <a href="{{ $provider->website }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $provider->website }}</a>
                        <x-copy-button :text="$provider->website" title="Copy URL" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Login ID">
                @if($provider->login_id)
                    <div class="flex items-center gap-2">
                        <span>{{ $provider->login_id }}</span>
                        <x-copy-button :text="$provider->login_id" title="Copy login ID" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Password">
                @if($provider->password)
                    <div class="flex items-center gap-2">
                        <span class="font-mono password-mask" data-password="{{ route('service-providers.password', $provider->id) }}">••••••••</span>
                        <x-permission-check :module="$provider->module" action="reveal">
                        <x-copy-button password-route="{{ route('service-providers.password', $provider->id) }}" title="Copy password" />
                        <button type="button" aria-label="Toggle password visibility" class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 toggle-password">Show</button>
                        </x-permission-check>
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Email Address">
                @if($provider->email)
                    <div class="flex items-center gap-2">
                        <a href="mailto:{{ $provider->email }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $provider->email }}</a>
                        <x-copy-button :text="$provider->email" title="Copy email" />
                    </div>
                @else
                    —
                @endif
            </x-field>
        </div>

        {{-- FINANCIAL --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Financial</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Monthly Cost">
                @if($provider->cost)
                    <x-money :value="$provider->cost" />
                @else
                    —
                @endif
            </x-field>
        </div>

        {{-- RELATIONSHIPS: Hosting --}}
        @if($provider->hostings->count())
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Linked Hosting</h3>
        <div class="space-y-2">
            @foreach($provider->hostings as $item)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div>
                        <a href="{{ route('hostings.show', $item->id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $item->name }}</a>
                        <span class="text-xs text-gray-500 ml-2">{{ $item->plan ?? '—' }}</span>
                    </div>
                    <x-badge :variant="$item->status">{{ $item->status }}</x-badge>
                </div>
            @endforeach
        </div>
        @endif

        {{-- RELATIONSHIPS: Domains --}}
        @if($provider->domains->count())
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Linked Domains</h3>
        <div class="space-y-2">
            @foreach($provider->domains as $item)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div>
                        <a href="{{ route('domains.show', $item->id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $item->name }}</a>
                        <span class="text-xs text-gray-500 ml-2">Expires: {{ $item->expiry_date?->format('Y-m-d') ?? '—' }}</span>
                    </div>
                    <x-badge :variant="$item->status">{{ $item->status }}</x-badge>
                </div>
            @endforeach
        </div>
        @endif

        {{-- RELATIONSHIPS: VPS --}}
        @if($provider->vps->count())
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Linked VPS</h3>
        <div class="space-y-2">
            @foreach($provider->vps as $item)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div>
                        <a href="{{ route('vps.show', $item->id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $item->name }}</a>
                        <span class="text-xs text-gray-500 ml-2">{{ $item->ip_address ?? '—' }}</span>
                    </div>
                    <x-badge :variant="$item->status">{{ $item->status }}</x-badge>
                </div>
            @endforeach
        </div>
        @endif

        {{-- RELATIONSHIPS: VoIP --}}
        @if($provider->voip->count())
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Linked VoIP</h3>
        <div class="space-y-2">
            @foreach($provider->voip as $item)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div>
                        <a href="{{ route('voip.show', $item->id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $item->name }}</a>
                        <span class="text-xs text-gray-500 ml-2">{{ $item->phone_number ?? '—' }}</span>
                    </div>
                    <x-badge :variant="$item->status">{{ $item->status }}</x-badge>
                </div>
            @endforeach
        </div>
        @endif

        {{-- RELATIONSHIPS: Other Services --}}
        @if($provider->otherServices->count())
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Linked Other Services</h3>
        <div class="space-y-2">
            @foreach($provider->otherServices as $item)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div>
                        <a href="{{ route('other-services.show', $item->id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $item->name }}</a>
                        <span class="text-xs text-gray-500 ml-2">{{ $item->service_type ?? '—' }}</span>
                    </div>
                    <x-badge :variant="$item->status">{{ $item->status }}</x-badge>
                </div>
            @endforeach
        </div>
        @endif

        {{-- DATES --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Dates</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Start Date">
                @if($provider->start_date)
                    <x-date :value="$provider->start_date" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Expiry Date">
                @if($provider->expiry_date)
                    <x-date :value="$provider->expiry_date" />
                @else
                    —
                @endif
            </x-field>
        </div>

        {{-- STATUS --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Status">
                <x-badge :variant="$provider->status">{{ ucfirst($provider->status) }}</x-badge>
            </x-field>
            <x-field label="Module" value="{{ $provider->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $provider->user->name ?? '—' }}" />
        </div>

        {{-- NOTES --}}
        @if($provider->description)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="mt-4">
            <x-field label="Notes">
                <p class="text-sm whitespace-pre-wrap">{{ $provider->description }}</p>
            </x-field>
        </div>
        @endif

        <x-notes-thread :model="$provider" notable-type="App\Models\ServiceProvider" />
    </x-card>

    <x-monitor-result type="service-providers" :id="$provider->id" />

    <x-activity-timeline subjectType="App\Models\ServiceProvider" :subjectId="$provider->id" />
</div>

@push('scripts')
<script>
(function() {
    var cachedPassword = null;
    var passwordUrl = '{{ route('service-providers.password', $provider->id) }}';
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

@extends('layouts.admin')

@section('title', $hosting->name)
@section('breadcrumbTitle', $hosting->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $hosting->name }}" back-url="{{ route('hostings.index') }}" back-label="Back to Hostings">
        <x-slot:actions>
            <x-monitor-button type="hostings" :id="$hosting->id" />
            <x-permission-check :module="$hosting->module" action="update">
            <x-button href="{{ route('hostings.edit', $hosting->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$hosting->module" action="delete">
            <form action="{{ route('hostings.destroy', $hosting->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        {{-- OVERVIEW --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Name" value="{{ $hosting->name }}" />
            <x-field label="Provider">
                @if($hosting->serviceProvider)
                    <a href="{{ route('service-providers.show', $hosting->serviceProvider->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $hosting->serviceProvider->name }}</a>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Plan" value="{{ $hosting->plan ?? '—' }}" />
            <x-field label="Domain" value="{{ $hosting->domain ?? '—' }}" />
        </div>

        {{-- ACCESS --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Access</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="cPanel URL">
                @if($hosting->cpanel_url && Str::startsWith($hosting->cpanel_url, ['http://', 'https://']))
                    <div class="flex items-center gap-2">
                        <a href="{{ $hosting->cpanel_url }}" target="_blank" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 underline">{{ $hosting->cpanel_url }}</a>
                        <x-copy-button :text="$hosting->cpanel_url" title="Copy URL" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Username">
                @if($hosting->username)
                    <div class="flex items-center gap-2">
                        <span>{{ $hosting->username }}</span>
                        <x-copy-button :text="$hosting->username" title="Copy username" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Password">
                @if($hosting->password)
                    <div class="flex items-center gap-2">
                        <span class="font-mono password-mask" data-password="{{ route('hostings.password', $hosting->id) }}">••••••••</span>
                        <x-permission-check :module="$vaultModule" action="reveal">
                        <x-copy-button password-route="{{ route('hostings.password', $hosting->id) }}" title="Copy password" />
                        <button type="button" aria-label="Toggle password visibility" class="px-2 py-1 text-xs font-medium text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-800 rounded-lg hover:bg-indigo-50 dark:hover:bg-indigo-900/20 toggle-password">Show</button>
                        </x-permission-check>
                    </div>
                @else
                    —
                @endif
            </x-field>
        </div>

        {{-- RELATIONSHIPS: Linked Domain Emails --}}
        @php $hostingDomainEmails = $hosting->domains->flatMap->domainEmails->filter(); @endphp
        @if($hostingDomainEmails->count())
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Linked Domain Emails</h3>
        <div class="space-y-2">
            @foreach($hostingDomainEmails as $email)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('domain-emails.show', $email->id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $email->email }}</a>
                        <x-copy-button :text="$email->email" title="Copy email" />
                    </div>
                    <span class="text-xs text-gray-500">{{ $email->domain?->name ?? '—' }}</span>
                </div>
            @endforeach
        </div>
        @endif

        {{-- TECHNICAL --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Technical</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Domain IP" value="{{ $hosting->domain_ip ?? '—' }}" />
            <x-field label="Mail Domain IP" value="{{ $hosting->mail_domain_ip ?? '—' }}" />
            <x-field label="cPanel IP" value="{{ $hosting->cpanel_ip ?? '—' }}" />
        </div>

        {{-- RELATIONSHIPS --}}
        @if($hosting->domains->count())
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Linked Domains</h3>
        <div class="space-y-2">
            @foreach($hosting->domains as $domain)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div>
                        <a href="{{ route('domains.show', $domain->id) }}" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">{{ $domain->name }}</a>
                        <span class="text-xs text-gray-500 ml-2">Expires: {{ $domain->expiry_date?->format('Y-m-d') ?? '—' }}</span>
                        @if($domain->cloudflare_status)
                            <span class="text-xs text-green-600 dark:text-green-400 ml-1">· CF: {{ Str::title($domain->cloudflare_status) }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @php $emailCount = $domain->domainEmails->count(); @endphp
                        @if($emailCount)
                            <span class="text-xs text-gray-500">{{ $emailCount }} {{ Str::plural('email', $emailCount) }}</span>
                        @endif
                        <x-badge :variant="$domain->status">{{ $domain->status }}</x-badge>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-2">
            <a href="{{ route('domains.index') }}?search={{ urlencode($hosting->name) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">View all domains →</a>
        </div>
        @endif

        {{-- FINANCIAL --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Financial</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Monthly Cost">
                @if($hosting->cost)
                    <x-money :value="$hosting->cost" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Billing Period">
                @if($hosting->billing_period_months)
                    {{ $hosting->billing_period_months }} month{{ $hosting->billing_period_months > 1 ? 's' : '' }}
                @else
                    —
                @endif
            </x-field>
        </div>

        {{-- DATES --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Dates</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Start Date">
                @if($hosting->start_date)
                    <x-date :value="$hosting->start_date" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Expiry Date">
                @if($hosting->expiry_date)
                    <x-date :value="$hosting->expiry_date" />
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
                <x-badge :variant="$hosting->status">{{ ucfirst($hosting->status) }}</x-badge>
            </x-field>
            <x-field label="Module" value="{{ $hosting->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $hosting->user->name ?? '—' }}" />
        </div>

        {{-- RENEWALS --}}
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

        {{-- NOTES --}}
        @if($hosting->description)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="mt-4">
            <x-field label="Notes">
                <p class="text-sm whitespace-pre-wrap">{{ $hosting->description }}</p>
            </x-field>
        </div>
        @endif

        <x-notes-thread :model="$hosting" notable-type="App\Models\Hosting" />
    </x-card>

    <x-monitor-result type="hostings" :id="$hosting->id" />

    <x-activity-timeline subjectType="App\Models\Hosting" :subjectId="$hosting->id" />
</div>

@push('scripts')
<script>
(function() {
    var cachedPassword = null;
    var passwordUrl = '{{ route('hostings.password', $hosting->id) }}';
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

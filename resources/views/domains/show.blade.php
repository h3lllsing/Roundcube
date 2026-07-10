@extends('layouts.admin')

@section('title', $domain->name)
@section('breadcrumbTitle', $domain->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $domain->name }}" back-url="{{ route('domains.index') }}" back-label="Back to Domains">
        <x-slot:actions>
            <x-monitor-button type="domains" :id="$domain->id" />
            <x-permission-check :module="$domain->module" action="update">
            <x-button href="{{ route('domains.edit', $domain->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$domain->module" action="delete">
            <form action="{{ route('domains.destroy', $domain->id) }}" method="POST">
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
            <x-field label="Name" value="{{ $domain->name }}" />
            <x-field label="Service Provider">
                @if($domain->serviceProvider)
                    <a href="{{ route('service-providers.show', $domain->serviceProvider->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $domain->serviceProvider->name }}</a>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Hosting">
                @if($domain->hosting)
                    <a href="{{ route('hostings.show', $domain->hosting->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $domain->hosting->name }}</a>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Cloudflare">
                @if($domain->cloudflare_status)
                    <x-badge :variant="$domain->cloudflare_status === 'proxied' ? 'success' : 'warning'">{{ ucfirst($domain->cloudflare_status) }}</x-badge>
                @else
                    <span class="text-sm text-gray-400">No</span>
                @endif
            </x-field>
        </div>

        {{-- HOSTING DETAILS --}}
        @if($domain->hosting)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Hosting Details</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Plan" value="{{ $domain->hosting->plan ?? '—' }}" />
            <x-field label="Server IP" value="{{ $domain->hosting->domain_ip ?? '—' }}" />
            <x-field label="cPanel IP" value="{{ $domain->hosting->cpanel_ip ?? '—' }}" />
            <x-field label="cPanel URL">
                @if($domain->hosting->cpanel_url && Str::startsWith($domain->hosting->cpanel_url, ['http://', 'https://']))
                    <div class="flex items-center gap-2">
                        <a href="{{ $domain->hosting->cpanel_url }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $domain->hosting->cpanel_url }}</a>
                        <x-copy-button :text="$domain->hosting->cpanel_url" title="Copy cPanel URL" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Status">
                <x-badge :variant="$domain->hosting->status">{{ $domain->hosting->status }}</x-badge>
            </x-field>
        </div>
        @endif

        {{-- TECHNICAL --}}
        @if($domain->dns_servers)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Technical</h3>
        <div class="mt-2">
            <x-field label="DNS Servers">
                <div class="flex flex-wrap gap-2">
                    @foreach(Arr::wrap($domain->dns_servers) as $dns)
                        <span class="inline-flex items-center px-2 py-1 rounded-md bg-gray-100 dark:bg-black text-sm font-mono">{{ $dns }}</span>
                    @endforeach
                </div>
            </x-field>
        </div>
        @endif

        {{-- RELATIONSHIPS --}}
        @if($domain->domainEmails->count())
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Linked Emails</h3>
        <div class="space-y-2">
            @foreach($domain->domainEmails as $email)
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 dark:bg-gray-800/50">
                    <div class="flex items-center gap-2">
                        <span class="font-medium">{{ $email->email }}</span>
                        <x-copy-button :text="$email->email" title="Copy email" />
                    </div>
                    <span class="text-xs text-gray-500">{{ $email->domain?->name ?? '—' }}</span>
                </div>
            @endforeach
        </div>
        @endif

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

        {{-- FINANCIAL --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Financial</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Monthly Cost">
                @if($domain->cost)
                    <x-money :value="$domain->cost" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Billing Period">
                @if($domain->billing_period_months)
                    {{ $domain->billing_period_months }} month{{ $domain->billing_period_months > 1 ? 's' : '' }}
                @else
                    —
                @endif
            </x-field>
        </div>

        {{-- DATES --}}
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Dates</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Registration Date">
                @if($domain->registration_date)
                    <x-date :value="$domain->registration_date" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Expiry Date">
                @if($domain->expiry_date)
                    <x-date :value="$domain->expiry_date" />
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
                <x-badge :variant="$domain->status">{{ ucfirst($domain->status) }}</x-badge>
            </x-field>
            <x-field label="Auto Renew" value="{{ $domain->auto_renew ? 'Yes' : 'No' }}" />
            <x-field label="Module" value="{{ $domain->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $domain->user->name ?? '—' }}" />
        </div>

        {{-- NOTES --}}
        @if($domain->description)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="mt-4">
            <x-field label="Notes">
                <p class="text-sm whitespace-pre-wrap">{{ $domain->description }}</p>
            </x-field>
        </div>
        @endif

        <x-notes-thread :model="$domain" notable-type="App\Models\Domain" />
    </x-card>

    <x-monitor-result type="domains" :id="$domain->id" />

    <x-activity-timeline subjectType="App\Models\Domain" :subjectId="$domain->id" />
</div>
@endsection

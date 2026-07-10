@extends('layouts.admin')

@section('title', $tracker->name)
@section('breadcrumbTitle', $tracker->name)

@php
    $sourceTypeLabels = [
        'domain' => 'Domain',
        'hosting' => 'Hosting',
        'vps' => 'VPS',
        'voip' => 'VOIP',
        'domain_email' => 'Domain Email',
        'other_service' => 'Other Service',
        'service_provider' => 'Service Provider',
    ];
    $isLinked = !is_null($tracker->trackable_type);
@endphp

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $tracker->name }}" back-url="{{ route('expiry-trackers.index') }}" back-label="Back to Renewals">
        <x-slot:actions>
            <x-monitor-button type="expiry-trackers" :id="$tracker->id" />
            <x-permission-check :module="$tracker->module" action="update">
            <x-button href="{{ route('expiry-trackers.edit', $tracker->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$tracker->module" action="delete">
            <form action="{{ route('expiry-trackers.destroy', $tracker->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    @if($isLinked)
    <div class="bg-lime-50 dark:bg-lime-900/20 border border-lime-200 dark:border-lime-800 rounded-xl p-4 mb-6">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-lime-600 dark:text-lime-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
            <div>
                <p class="text-sm font-medium text-lime-800 dark:text-lime-300">Renewal Linked to Source Service</p>
                <p class="text-sm text-lime-700 dark:text-lime-400 mt-0.5">This renewal is linked to a source service. Update expiry date from the source record.</p>
                @if($tracker->trackable)
                    <a href="{{ $tracker->trackable instanceof \App\Models\Domain ? route('domains.edit', $tracker->trackable_id) : '' }}{{ $tracker->trackable instanceof \App\Models\Hosting ? route('hostings.edit', $tracker->trackable_id) : '' }}{{ $tracker->trackable instanceof \App\Models\Vps ? route('vps.edit', $tracker->trackable_id) : '' }}{{ $tracker->trackable instanceof \App\Models\Voip ? route('voip.edit', $tracker->trackable_id) : '' }}{{ $tracker->trackable instanceof \App\Models\DomainEmail ? route('domain-emails.edit', $tracker->trackable_id) : '' }}{{ $tracker->trackable instanceof \App\Models\OtherService ? route('other-services.edit', $tracker->trackable_id) : '' }}{{ $tracker->trackable instanceof \App\Models\ServiceProvider ? route('service-providers.edit', $tracker->trackable_id) : '' }}" class="inline-flex items-center gap-1.5 mt-2 px-3 py-1.5 text-xs font-medium text-lime-700 dark:text-lime-300 bg-lime-100 dark:bg-lime-900/40 rounded-lg hover:bg-lime-200 dark:hover:bg-lime-900/60 transition-colors">
                        View Source Service
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    <x-card>
        <div class="flex items-center gap-2 mb-4">
            @if($isLinked)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-lime-100 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300">Linked</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">Standalone</span>
            @endif
            @if($tracker->status)
                <x-badge :variant="$tracker->status">{{ ucfirst($tracker->status) }}</x-badge>
            @endif
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Name" value="{{ $tracker->name }}" />
            <x-field label="Login URL">
                @if($tracker->login_url)
                    <a href="{{ $tracker->login_url }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $tracker->login_url }}</a>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Username" value="{{ $tracker->username ?? '—' }}" />
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Relationships</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Service Provider" value="{{ $tracker->serviceProvider?->name ?? '—' }}" />
            @if($isLinked)
            <x-field label="Source Type" value="{{ $sourceTypeLabels[$tracker->trackable_type] ?? $tracker->trackable_type }}" />
            @endif
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Financial</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Cost">
                @if($tracker->cost)
                    <x-money :value="$tracker->cost" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Billing Period">
                @if($tracker->billing_period_months)
                    {{ $tracker->billing_period_months }} {{ Str::plural('month', $tracker->billing_period_months) }}
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Dates</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Expiry Date">
                @if($tracker->expiry_date)
                    <x-date :value="$tracker->expiry_date" />
                @else
                    —
                @endif
            </x-field>
            <x-field label="Renewal Date">
                @if($tracker->renewal_date)
                    <x-date :value="$tracker->renewal_date" />
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Module" value="{{ $tracker->module->name ?? '—' }}" />
            <x-field label="User" value="{{ $tracker->user->name ?? '—' }}" />
        </div>

        @if($tracker->email_notifications_enabled !== null)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notifications</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Email Notifications">
                @if($tracker->email_notifications_enabled)
                    <span class="text-emerald-600 dark:text-emerald-400">Enabled</span>
                @else
                    <span class="text-red-600 dark:text-red-400">Disabled</span>
                @endif
            </x-field>
            @if($tracker->email_notifications_enabled)
            <x-field label="SMTP Profile" value="{{ $tracker->smtpProfile?->name ?? 'Default System SMTP' }}" />
            <x-field label="Recipients" value="{{ count($recipientPreview ?? []) }}" />
            <x-field label="Last Notification" value="{{ $tracker->last_notification_sent_at?->diffForHumans() ?? 'Never' }}" />
            <x-field label="Next Due">
                @if($tracker->next_notification_due_at)
                    {{ $tracker->next_notification_due_at->format('Y-m-d') }}
                @else
                    —
                @endif
            </x-field>
            @else
            <x-field label="Disabled By" value="{{ $tracker->disabledByUser?->name ?? '—' }}" />
            <x-field label="Disabled At">
                @if($tracker->disabled_at)
                    {{ $tracker->disabled_at->format('Y-m-d H:i') }}
                @else
                    —
                @endif
            </x-field>
            <x-field label="Reason" value="{{ $tracker->disable_reason ?? '—' }}" />
            @endif
        </div>
        @endif

        @if($tracker->description)
        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Notes</h3>
        <div class="mt-4">
            <x-field label="Notes">
                <p class="text-sm whitespace-pre-wrap">{{ $tracker->description }}</p>
            </x-field>
        </div>
        @endif

        <x-notes-thread :model="$tracker" notable-type="App\Models\ExpiryTracker" />

        @if($tracker->email_notifications_enabled !== null)
        <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
            <a href="{{ route('expiry-trackers.notification-history', $tracker->id) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View notification history &rarr;</a>
        </div>

        @include('expiry-trackers._recipient-preview', [
            'recipients' => $recipientPreview ?? [],
            'smtpProfileName' => $tracker->smtpProfile?->name ?? 'Default System SMTP',
            'enabled' => $tracker->email_notifications_enabled,
            'senderEmail' => $senderEmail ?? '',
            'senderName' => $senderName ?? '',
            'userLookup' => $userLookup ?? [],
        ])
        @endif
    </x-card>

    <x-monitor-result type="expiry-trackers" :id="$tracker->id" />

    <x-activity-timeline subjectType="App\Models\ExpiryTracker" :subjectId="$tracker->id" />
</div>
@endsection

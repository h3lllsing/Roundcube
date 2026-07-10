@extends('layouts.admin')

@section('title', $smtpProfile->name)
@section('breadcrumbTitle', $smtpProfile->name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $smtpProfile->name }}" back-url="{{ route('smtp-profiles.index') }}" back-label="Back to SMTP Profiles">
        <x-slot:actions>
            <form action="{{ route('smtp-profiles.test', $smtpProfile) }}" method="POST" class="inline">
                @csrf
                <x-button type="submit" variant="success" size="sm">Test SMTP</x-button>
            </form>
            <x-button href="{{ route('smtp-profiles.edit', $smtpProfile) }}" variant="primary" size="sm">Edit</x-button>
        </x-slot:actions>
    </x-page-header>

    <x-card>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Profile Name" value="{{ $smtpProfile->name }}" />
            <x-field label="SMTP Host">
                <div class="flex items-center gap-2">
                    <span class="font-mono text-sm">{{ $smtpProfile->smtp_host }}:{{ $smtpProfile->smtp_port }}</span>
                    <x-copy-button :text="$smtpProfile->smtp_host . ':' . $smtpProfile->smtp_port" title="Copy SMTP host:port" />
                </div>
            </x-field>
            <x-field label="Encryption" value="{{ $smtpProfile->smtp_encryption ?? 'None' }}" />
            <x-field label="Username">
                @if($smtpProfile->smtp_username)
                    <div class="flex items-center gap-2">
                        <span>{{ $smtpProfile->smtp_username }}</span>
                        <x-copy-button :text="$smtpProfile->smtp_username" title="Copy username" />
                    </div>
                @else
                    —
                @endif
            </x-field>
            <x-field label="Password">
                <span class="text-gray-400 dark:text-gray-500 text-sm">Encrypted at rest</span>
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Sender</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Sender">
                <div class="flex items-center gap-2">
                    <span>{{ $smtpProfile->sender_name }} &lt;{{ $smtpProfile->sender_email }}&gt;</span>
                    <x-copy-button :text="$smtpProfile->sender_email" title="Copy sender email" />
                </div>
            </x-field>
            <x-field label="Reply-To">
                @if($smtpProfile->reply_to_email)
                    <div class="flex items-center gap-2">
                        <span>{{ $smtpProfile->reply_to_email }}</span>
                        <x-copy-button :text="$smtpProfile->reply_to_email" title="Copy reply-to email" />
                    </div>
                @else
                    —
                @endif
            </x-field>
        </div>

        <hr class="my-4 border-gray-200 dark:border-gray-700">
        <h3 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <x-field label="Status">
                <x-badge :variant="$smtpProfile->is_active ? 'active' : 'inactive'">{{ $smtpProfile->is_active ? 'Active' : 'Inactive' }}</x-badge>
            </x-field>
            <x-field label="Default">
                {{ $smtpProfile->is_default ? 'Yes' : 'No' }}
            </x-field>
            <x-field label="Priority" value="{{ $smtpProfile->priority }}" />
            <x-field label="Created By" value="{{ $smtpProfile->creator?->name ?? '—' }}" />
            <x-field label="Last Test">
                @if($smtpProfile->last_tested_at)
                    <span class="{{ $smtpProfile->last_test_status === 'success' ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ ucfirst($smtpProfile->last_test_status) }}
                        ({{ $smtpProfile->last_tested_at->diffForHumans() }})
                    </span>
                    @if($smtpProfile->last_test_error)
                        <p class="text-xs text-red-500 mt-1">{{ $smtpProfile->last_test_error }}</p>
                    @endif
                @else
                    <span class="text-gray-400">Not tested yet</span>
                @endif
            </x-field>
        </div>
    </x-card>

    @if(!empty($usage))
    <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">Usage</h3>
        <div class="space-y-2">
            @foreach($usage as $label => $count)
            <div class="flex items-center justify-between">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ $label }}</span>
                <span class="text-sm font-medium">{{ $count }}</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <x-activity-timeline subjectType="App\Models\SmtpProfile" :subjectId="$smtpProfile->id" />
</div>
@endsection

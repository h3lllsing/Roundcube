@extends('layouts.admin')

@section('title', $emailAccount->email)

@section('content')
<x-page-header title="{{ $emailAccount->email }}" subtitle="Email account details." backUrl="{{ route('email_accounts.index') }}" backLabel="Back to Email Accounts">
    <x-slot:actions>
        <x-button href="{{ route('email_accounts.edit', $emailAccount) }}" variant="outline" size="sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
        </x-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <x-card>
        <div class="space-y-3">
            <x-field label="Email" value="{{ $emailAccount->email }}" />
            <x-field label="Domain">
                <a href="{{ route('domains.show', $emailAccount->domain) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $emailAccount->domain->name ?? 'N/A' }}</a>
            </x-field>
            <x-field label="Status">
                <x-badge variant="{{ $emailAccount->status === 'active' ? 'success' : 'danger' }}">{{ ucfirst($emailAccount->status) }}</x-badge>
            </x-field>
            <x-field label="Sync Enabled">
                <x-badge variant="{{ $emailAccount->sync_enabled ? 'success' : 'default' }}">{{ $emailAccount->sync_enabled ? 'Yes' : 'No' }}</x-badge>
            </x-field>
            <x-field label="Last Sync" value="{{ $emailAccount->last_sync_at?->format('M d, Y g:i A') ?? 'Never' }}" />
            <x-field label="Created" value="{{ $emailAccount->created_at->format('M d, Y g:i A') }}" />
            <x-field label="Created By" value="{{ $emailAccount->creator?->email ?? 'N/A' }}" />
        </div>
    </x-card>

    <div class="lg:col-span-2 space-y-6">
        <x-card>
            <x-slot:header>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">IMAP Settings</h3>
            </x-slot:header>
            <div class="space-y-3">
                <x-field label="Host" value="{{ $emailAccount->imap_host }}" />
                <x-field label="Port" value="{{ $emailAccount->imap_port }}" />
                <x-field label="Encryption" value="{{ strtoupper($emailAccount->imap_encryption) }}" />
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">SMTP Settings</h3>
            </x-slot:header>
            <div class="space-y-3">
                <x-field label="Host" value="{{ $emailAccount->smtp_host ?? 'N/A' }}" />
                <x-field label="Port" value="{{ $emailAccount->smtp_port ?? 'N/A' }}" />
                <x-field label="Encryption" value="{{ $emailAccount->smtp_encryption ? strtoupper($emailAccount->smtp_encryption) : 'N/A' }}" />
                <x-field label="Username" value="{{ $emailAccount->smtp_username ?? 'N/A' }}" />
            </div>
        </x-card>

        <x-card>
            <x-slot:header>
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Assigned Users</h3>
            </x-slot:header>
            @if ($emailAccount->assignedUsers->count())
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($emailAccount->assignedUsers as $user)
                        <div class="py-2 flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $user->email }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">
                                    Can {{ $user->pivot->can_send ? 'Send' : '' }} {{ $user->pivot->can_receive ? '& Receive' : '' }}
                                </p>
                            </div>
                            <form method="POST" action="{{ route('email_accounts.assign.revoke', [$emailAccount, $user]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" data-confirm="Revoke access for {{ $user->email }}?" data-confirm-button="Revoke" x-on:click="startLoading($el)" class="text-xs text-red-600 dark:text-red-400 hover:underline">Revoke</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <x-empty-state message="No users assigned." />
            @endif

            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <form method="POST" action="{{ route('email_accounts.assign', $emailAccount) }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="flex-1 min-w-[200px]">
                        <x-form.select name="user_id" label="Assign User" :options="\App\Models\User::pluck('email', 'id')->toArray()" placeholder="Select user..." />
                    </div>
                    <div class="flex items-center gap-3 pb-0.5">
                        <x-form.checkbox name="can_send" label="Can Send" checked />
                        <x-form.checkbox name="can_receive" label="Can Receive" checked />
                    </div>
                    <x-button type="submit" variant="primary" size="sm">Assign</x-button>
                </form>
            </div>
        </x-card>
    </div>
</div>
@endsection

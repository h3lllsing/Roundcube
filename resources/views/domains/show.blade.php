@extends('layouts.admin')

@section('title', $domain->name)

@section('content')
<x-page-header title="{{ $domain->name }}" subtitle="Domain details and email accounts." backUrl="{{ route('domains.index') }}" backLabel="Back to Domains">
    <x-slot:actions>
        <x-button href="{{ route('domains.edit', $domain) }}" variant="outline" size="sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit
        </x-button>
    </x-slot:actions>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
    <x-card>
        <div class="space-y-3">
            <x-field label="Name" value="{{ $domain->name }}" />
            <x-field label="Status">
                <x-badge variant="{{ $domain->status?->value }}">{{ ucfirst($domain->status?->value) }}</x-badge>
            </x-field>
            @if($domain->notes)
                <x-field label="Notes" value="{{ $domain->notes }}" />
            @endif
            <x-field label="Created" value="{{ $domain->created_at->format('M d, Y g:i A') }}" />
            <x-field label="Created By" value="{{ $domain->creator?->email ?? 'N/A' }}" />
        </div>
    </x-card>

    <div class="lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Email Accounts</h2>
            <x-button href="{{ route('email_accounts.create', ['domain_id' => $domain->id]) }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Account
            </x-button>
        </div>

        <x-card padding="none">
            <x-table>
                <x-slot:head>
                    <th>Email</th>
                    <th>Status</th>
                    <th>Action</th>
                </x-slot:head>
                @forelse ($emailAccounts as $account)
                    <tr>
                        <td>
                            <a href="{{ route('email_accounts.show', $account) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">{{ $account->email }}</a>
                        </td>
                        <td>
                            <x-badge variant="{{ $account->status?->value === 'active' ? 'success' : 'danger' }}">{{ ucfirst($account->status?->value) }}</x-badge>
                        </td>
                        <td>
                            <a href="{{ route('email_accounts.show', $account) }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">
                            <x-empty-state message="No email accounts for this domain." />
                        </td>
                    </tr>
                @endforelse
            </x-table>
            </x-card>

            @if ($emailAccounts->hasPages())
                <div class="mt-4">
                    {{ $emailAccounts->links() }}
                </div>
            @endif
    </div>
</div>
@endsection

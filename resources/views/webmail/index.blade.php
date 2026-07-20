@extends('layouts.admin')

@section('title', 'Webmail')

@section('content')
<x-page-header title="Webmail" subtitle="Access your email accounts" />

@php
$grouped = $accounts->groupBy(fn($a) => $a->domain->name);
@endphp

<div class="space-y-6">
    @forelse ($grouped as $domainName => $domainAccounts)
    @php $domain = $domainAccounts->first()->domain; @endphp
    <x-card>
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $domainName }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $domainAccounts->count() }} {{ Str::plural('account', $domainAccounts->count()) }}</p>
            </div>
            <div class="flex items-center gap-2">
                <a href="https://{{ $domainName }}" target="_blank" rel="noopener noreferrer"
                   class="inline-flex items-center gap-1.5 text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Visit Website
                </a>
            </div>
        </div>

        <div class="divide-y divide-gray-100 dark:divide-gray-800">
            @foreach($domainAccounts as $account)
            <a href="{{ route('webmail.open', $account) }}"
               class="flex items-center gap-4 py-3 px-2 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors group -mx-2">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm shrink-0">
                    {{ strtoupper(substr($account->email, 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 truncate">{{ $account->email }}</p>
                    <div class="flex items-center gap-2 mt-0.5">
                        <x-badge variant="{{ $account->status?->value === 'active' ? 'success' : 'danger' }}" class="text-[10px] px-1.5 py-0">{{ $account->status?->value }}</x-badge>
                        @if($account->imap_host)
                        <span class="text-[10px] text-gray-400 dark:text-gray-500">{{ $account->imap_host }}:{{ $account->imap_port }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-indigo-600 dark:text-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity font-medium">Open Webmail</span>
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-indigo-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
            @endforeach
        </div>
    </x-card>
    @empty
    <x-card>
        <x-empty-state message="No email accounts available." />
    </x-card>
    @endforelse
</div>
@endsection

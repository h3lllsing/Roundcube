@extends('layouts.admin')

@section('title', 'Login Audit Detail')
@section('breadcrumbTitle', 'Login Audit Detail')

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="Login Audit Detail" subtitle="View audit entry details." back-url="{{ route('login-audits.index') }}" back-label="Back to Login Audits" />

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">User</p>
                <p class="font-medium">{{ $audit->user->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                <p class="font-medium">{{ $audit->email }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Event</p>
                <p class="font-medium">{{ str_replace('_', ' ', $audit->event?->value ?? '') }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">IP Address</p>
                <p class="font-medium font-mono text-sm">{{ $audit->ip_address }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">User Agent</p>
                <p class="font-medium text-sm break-all">{{ $audit->user_agent ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Date</p>
                <p class="font-medium">{{ $audit->created_at->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>

    <div class="mt-6">
        <form action="{{ route('login-audits.destroy', $audit->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this login audit entry? This cannot be undone.')">
            @csrf
            @method('DELETE')
            <x-button type="submit" variant="danger" size="sm">Delete Audit</x-button>
        </form>
    </div>

    <x-activity-timeline subjectType="App\Models\LoginAudit" :subjectId="$audit->id" />
</div>
@endsection

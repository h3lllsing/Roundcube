@extends('layouts.admin')

@section('title', $asset->asset_tag)
@section('breadcrumbTitle', $asset->asset_tag)

@section('content')
@php
$badgeMap = ['available' => 'success', 'assigned' => 'primary', 'lost' => 'danger', 'decommissioned' => 'default'];
@endphp
<div class="max-w-4xl mx-auto">
    <x-page-header title="{{ $asset->asset_tag }}" back-url="{{ route('assets.index') }}" back-label="Back to Assets">
        <x-slot:actions>
            <x-permission-check :module="$asset->module" action="update">
            <x-button href="{{ route('assets.edit', $asset->id) }}" variant="primary" size="sm">Edit</x-button>
            </x-permission-check>
            <x-permission-check :module="$asset->module" action="delete">
            <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" class="inline">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
            </x-permission-check>
        </x-slot:actions>
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <x-card>
            <h2 class="text-lg font-semibold mb-4">Details</h2>
            <div class="grid grid-cols-1 gap-4">
                <x-field label="Asset ID" value="{{ $asset->asset_tag }}" />
                <x-field label="Brand" value="{{ $asset->brand ?? '—' }}" />
                <x-field label="Model" value="{{ $asset->model ?? '—' }}" />
                <x-field label="Processor" value="{{ $asset->processor ?? '—' }}" />
                <x-field label="RAM" value="{{ $asset->ram ?? '—' }}" />
                <x-field label="Storage" value="{{ $asset->storage ?? '—' }}" />
                <x-field label="Operating System" value="{{ $asset->os ?? '—' }}" />
                <x-field label="Serial Number">{{ $asset->serial_number ?? '—' }}</x-field>
                <x-field label="Status">
                    <x-badge :variant="$badgeMap[$asset->status] ?? 'default'">{{ ucfirst($asset->status) }}</x-badge>
                </x-field>
                <x-field label="Headphone" value="{{ $asset->headphone ?? '—' }}" />
                <x-field label="Additional Equipments" value="{{ $asset->additional_equipments ?? '—' }}" />
                <x-field label="Assigned Employee" value="{{ $asset->assigned_user_name ?? '—' }}" />
                <x-field label="Reporting Authority" value="{{ $asset->reporting_authority ?? '—' }}" />
                <x-field label="Department" value="{{ $asset->department ?? '—' }}" />
                <x-field label="Premises" value="{{ $asset->premises ?? '—' }}" />
                <x-field label="AnyDesk ID" value="{{ $asset->anydesk_id ?? '—' }}" />
                <x-field label="AnyDesk Password" value="{{ $asset->anydesk_password ? '********' : '—' }}" />
                <x-field label="Created By" value="{{ $asset->user->name ?? '—' }}" />
            </div>

            @if($asset->additional_comments)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-field label="Additional Comments">
                    <p class="text-sm whitespace-pre-wrap">{{ $asset->additional_comments }}</p>
                </x-field>
            </div>
            @endif

            <x-notes-thread :model="$asset" notable-type="App\Models\Asset" />

            @if($asset->vault_entry_id)
            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-700">
                <x-field label="Vault Credentials">
                    @if($canAccessVault)
                        <a href="{{ route('vault.show', $asset->vault_entry_id) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline text-sm">
                            {{ $asset->vaultEntry->service_name ?? 'View Credentials' }}
                        </a>
                    @else
                        <span class="text-gray-400 text-sm">Restricted</span>
                    @endif
                </x-field>
            </div>
            @endif
        </x-card>

        <div class="space-y-6">
            @if($asset->primary_image)
            <x-card>
                <h2 class="text-lg font-semibold mb-4">Primary Image</h2>
                <img src="{{ asset('storage/' . $asset->primary_image) }}" alt="{{ $asset->asset_tag }}" loading="lazy" class="rounded-lg max-w-full h-auto">
            </x-card>
            @endif

            @if($asset->attachments_count > 0)
            <x-card>
                <h2 class="text-lg font-semibold mb-4">Attachments ({{ $asset->attachments_count }})</h2>
                <div class="grid grid-cols-2 gap-3">
                    @foreach($asset->attachments as $attachment)
                        <a href="{{ route('attachments.download', $attachment->id) }}" class="block p-3 rounded-lg bg-gray-50 dark:bg-black border border-gray-200 dark:border-gray-700 hover:border-indigo-300">
                            <p class="text-sm font-medium truncate">{{ $attachment->original_name }}</p>
                            <p class="text-xs text-gray-500">{{ number_format($attachment->size / 1024, 1) }} KB</p>
                        </a>
                    @endforeach
                </div>
            </x-card>
            @endif

            @if($asset->status === 'available')
            <x-permission-check :module="$asset->module" action="update">
            <x-card>
                <h2 class="text-lg font-semibold mb-4">Assign Asset</h2>
                <form action="{{ route('assets.assign', $asset->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <x-form.select name="assigned_to" label="Assign To" :options="\App\Models\User::orderBy('name')->pluck('name', 'id')" :value="old('assigned_to')" required placeholder="Select user..." />
                    <x-form.input name="department" label="Department" :value="old('department', $asset->department)" />
                    <x-form.input type="date" name="issue_date" label="Issue Date" :value="old('issue_date', now()->format('Y-m-d'))" />
                    <x-form.input type="date" name="expected_return_at" label="Expected Return" :value="old('expected_return_at')" />
                    <x-form.select name="assignment_reason" label="Reason" :options="['' => 'Select reason...', 'New Employee' => 'New Employee', 'Replacement' => 'Replacement', 'Temporary' => 'Temporary', 'Loan' => 'Loan', 'Other' => 'Other']" :value="old('assignment_reason')" />
                    <x-form.textarea name="note" label="Note" :value="old('note')" />
                    <x-button type="submit" variant="primary" size="sm">Assign</x-button>
                </form>
            </x-card>
            </x-permission-check>
            @endif

            @if($asset->status === 'assigned')
            <x-permission-check :module="$asset->module" action="update">
            <x-card>
                <h2 class="text-lg font-semibold mb-4">Return Asset</h2>
                <form action="{{ route('assets.return', $asset->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <x-form.select name="condition_on_return" label="Condition on Return" :options="['' => 'Select...', 'new' => 'New', 'good' => 'Good', 'fair' => 'Fair', 'poor' => 'Poor', 'damaged' => 'Damaged']" :value="old('condition_on_return')" />
                    <x-form.textarea name="note" label="Note" :value="old('note')" />
                    <x-button type="submit" variant="primary" size="sm">Return</x-button>
                </form>
            </x-card>
            </x-permission-check>
            @endif
        </div>
    </div>

    @if($asset->assignments->count() > 0)
    <x-card class="mt-6">
        <h2 class="text-lg font-semibold mb-4">Assignment History</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                        <th class="text-left px-4 py-2 font-medium text-gray-500">Assigned To</th>
                        <th class="text-left px-4 py-2 font-medium text-gray-500">By</th>
                        <th class="text-left px-4 py-2 font-medium text-gray-500">Date</th>
                        <th class="text-left px-4 py-2 font-medium text-gray-500">Returned</th>
                        <th class="text-left px-4 py-2 font-medium text-gray-500">Reason</th>
                        <th class="text-left px-4 py-2 font-medium text-gray-500">Note</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach($asset->assignments as $assignment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-2">{{ $assignment->assignee->name ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $assignment->assigner->name ?? '—' }}</td>
                        <td class="px-4 py-2"><x-date :value="$assignment->assigned_at" format="Y-m-d H:i" /></td>
                        <td class="px-4 py-2">{{ $assignment->returned_at ? \Carbon\Carbon::parse($assignment->returned_at)->format('Y-m-d') : 'Current' }}</td>
                        <td class="px-4 py-2">{{ $assignment->assignment_reason ?? '—' }}</td>
                        <td class="px-4 py-2 text-gray-500 max-w-xs truncate">{{ $assignment->note ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </x-card>
    @endif

    <div class="mt-6">
        <x-activity-timeline subjectType="App\Models\Asset" :subjectId="$asset->id" />
    </div>

</div>
@endsection

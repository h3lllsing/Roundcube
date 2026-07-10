@extends('layouts.admin')

@section('title', 'Attachments')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Attachments" subtitle="Manage file attachments.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super-admin'))
            <x-button href="{{ route('export', 'attachments') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            <x-button href="{{ route('attachments.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search filename..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->filled('search'))
            <x-button href="{{ route('attachments.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="attachments">
        <x-bulk-actions type="attachments" colspan="8" :statuses="[]" :actions="['delete']" />
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Filename</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Type</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Size</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Attached To</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">User</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Uploaded</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($attachments as $attachment)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $attachment->id }}" aria-label="Select attachment {{ $attachment->id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 font-medium max-w-xs truncate">{{ $attachment->original_name }}</td>
                        <td class="px-6 py-3 text-gray-500 text-xs">{{ $attachment->mime_type ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $attachment->size ? number_format($attachment->size / 1024, 1) . ' KB' : '—' }}</td>
                        <td class="px-6 py-3 text-gray-500 text-xs">{{ $attachment->notable_type ? class_basename($attachment->notable_type) . ' #' . $attachment->notable_id : '—' }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $attachment->user->name ?? '—' }}</td>
                        <td class="px-6 py-3 text-gray-500 text-nowrap">{{ $attachment->created_at->format('Y-m-d') }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('attachments.show', $attachment->id) }}" color="indigo" icon="view" label="View" />
                            <x-action href="{{ route('attachments.download', $attachment->id) }}" color="green" icon="download" label="Download" />
                            <x-action action="{{ route('attachments.destroy', $attachment->id) }}" color="red" icon="delete" label="Delete" confirm="Are you sure?" method="DELETE" />
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="8" icon="box" title="No attachments found." message="Upload files to attach them to items." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-4">{{ $attachments->links() }}</div>
</div>
@endsection

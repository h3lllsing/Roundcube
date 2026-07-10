@extends('layouts.admin')

@section('title', 'Note #' . $note->id)
@section('breadcrumbTitle', 'Note #' . $note->id)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Note #{{ $note->id }}" back-url="{{ route('notes.index') }}" />
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $note->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">User</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $note->user->name ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Content</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100 whitespace-pre-wrap">{{ $note->content }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Notable</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">
                    @if ($note->notable_type)
                        {{ class_basename($note->notable_type) }} #{{ $note->notable_id }}
                    @else
                        <span class="text-gray-400 dark:text-gray-500">—</span>
                    @endif
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $note->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Updated</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $note->updated_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
            <x-button href="{{ route('notes.edit', $note->id) }}" variant="primary" size="sm">Edit</x-button>
            <x-button href="{{ route('attachments.create', ['notable_type' => 'App\Models\Note', 'notable_id' => $note->id]) }}" variant="success" size="sm">Upload Attachment</x-button>
            <form action="{{ route('notes.destroy', $note->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
        </div>
    </div>

    @if ($note->attachments->isNotEmpty())
        <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Attachments ({{ $note->attachments->count() }})</h3>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($note->attachments as $attachment)
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $attachment->original_name }}</span>
                        <a href="{{ route('attachments.download', $attachment->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800">Download</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-activity-timeline subjectType="App\Models\Note" :subjectId="$note->id" />
</div>
@endsection

@extends('layouts.admin')

@section('title', $attachment->original_name)
@section('breadcrumbTitle', $attachment->original_name)

@section('content')
<div class="max-w-3xl mx-auto">
    <x-page-header title="{{ $attachment->original_name }}" back-url="{{ route('attachments.index') }}" back-label="Back to Attachments">
        <x-slot:actions>
            <x-button href="{{ route('attachments.download', $attachment->id) }}" variant="primary" size="sm">Download</x-button>
            <form action="{{ route('attachments.destroy', $attachment->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Filename</p>
                <p class="font-medium">{{ $attachment->original_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">MIME Type</p>
                <p class="font-medium">{{ $attachment->mime_type ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Size</p>
                <p class="font-medium">{{ $attachment->size ? number_format($attachment->size / 1024, 1) . ' KB' : '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Attached To</p>
                <p class="font-medium">{{ $attachment->notable_type ? class_basename($attachment->notable_type) . ' #' . $attachment->notable_id : '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Uploaded By</p>
                <p class="font-medium">{{ $attachment->user->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500 dark:text-gray-400">Uploaded At</p>
                <p class="font-medium">{{ $attachment->created_at->format('Y-m-d H:i:s') }}</p>
            </div>
        </div>
    </div>

    <x-activity-timeline subjectType="App\Models\Attachment" :subjectId="$attachment->id" />
</div>
@endsection

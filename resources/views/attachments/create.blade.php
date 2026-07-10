@extends('layouts.admin')

@section('title', 'Upload Attachment')

@section('content')
<div class="max-w-lg mx-auto">
    <x-page-header title="Upload Attachment" subtitle="Attach a file to a resource" />

    <form action="{{ route('attachments.store') }}" method="POST" enctype="multipart/form-data"
        class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 space-y-4">
        @csrf

        @if ($notableType && $notableId)
            <input type="hidden" name="notable_type" value="{{ $notableType }}">
            <input type="hidden" name="notable_id" value="{{ $notableId }}">
            <p class="text-sm text-gray-500 dark:text-gray-400">Attaching file to {{ class_basename($notableType) }} #{{ $notableId }}</p>
        @endif

        <div>
            <label for="file" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">File (max 10 MB)</label>
            <input id="file" type="file" name="file" required
                class="w-full text-sm text-gray-500 dark:text-gray-400 file:mr-3 file:py-1.5 file:px-3.5 file:rounded-xl file:border-0 file:text-sm file:font-medium file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-300 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-900/50 transition-all cursor-pointer">
            @error('file')
                <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div class="flex items-center gap-3 pt-2">
            <x-button type="submit" variant="primary" size="sm">Upload</x-button>
            <x-button href="{{ route('attachments.index') }}" variant="outline" size="sm">Cancel</x-button>
        </div>
    </form>
</div>
@endsection

@extends('layouts.admin')

@section('title', $task->title)
@section('breadcrumbTitle', $task->title)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="{{ $task->title }}" back-url="{{ route('tasks.index') }}" />
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">ID</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Module</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->module->name ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Feature</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->module->feature->name ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Status</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"><span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$task->status] ?? 'bg-gray-100 dark:bg-gray-900/30 text-gray-600 dark:text-gray-400' }}">{{ str_replace('_', ' ', ucfirst($task->status)) }}</span></p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Priority</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ ucfirst($task->priority) }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Due Date</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->due_date?->format('Y-m-d') ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Assignees</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->assignees->pluck('name')->implode(', ') ?: '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Creator</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->creator->name ?? '—' }}</p>
            </div>
            <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Description</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->description ?? '—' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Created</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->created_at->format('Y-m-d H:i') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-500 dark:text-gray-400">Updated</label>
                <p class="mt-1 text-sm text-gray-900 dark:text-gray-100">{{ $task->updated_at->format('Y-m-d H:i') }}</p>
            </div>
        </div>
        <div class="flex items-center gap-3 pt-6 mt-6 border-t border-gray-200 dark:border-gray-700">
            <x-button href="{{ route('tasks.edit', $task->id) }}" variant="primary" size="sm">Edit</x-button>
            <x-button href="{{ route('attachments.create', ['notable_type' => 'App\Models\Task', 'notable_id' => $task->id]) }}" variant="success" size="sm">Upload Attachment</x-button>
            <form action="{{ route('tasks.destroy', $task->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <x-button type="submit" variant="danger" size="sm" data-confirm="Are you sure?">Delete</x-button>
            </form>
        </div>
    </div>

    @if ($task->attachments->isNotEmpty())
        <div class="mt-6 bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Attachments ({{ $task->attachments->count() }})</h3>
            <ul class="divide-y divide-gray-200 dark:divide-gray-700">
                @foreach ($task->attachments as $attachment)
                    <li class="py-2 flex items-center justify-between">
                        <span class="text-sm text-gray-900 dark:text-gray-100">{{ $attachment->original_name }}</span>
                        <a href="{{ route('attachments.download', $attachment->id) }}" class="text-xs text-indigo-600 hover:text-indigo-800">Download</a>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <x-activity-timeline subjectType="App\Models\Task" :subjectId="$task->id" />
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Edit Task')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold mb-6">Edit Task</h2>
        <form action="{{ route('tasks.update', $task->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <x-form.input name="title" label="Title" :value="old('title', $task->title)" required />
            <x-form.textarea name="description" label="Description" :value="old('description', $task->description)" />
            <x-form.select name="module_id" label="Module" :options="$modules" :value="old('module_id', $task->module_id)" required />
            <x-form.select name="status" label="Status" :options="['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled']" :value="old('status', $task->status)" required />
            <x-form.select name="priority" label="Priority" :options="['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent']" :value="old('priority', $task->priority)" required />
            <x-form.input type="date" name="due_date" label="Due Date" :value="old('due_date', $task->due_date?->format('Y-m-d'))" />
            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Save</button>
                <a href="{{ route('tasks.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

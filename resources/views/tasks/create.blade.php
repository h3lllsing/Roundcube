@extends('layouts.admin')

@section('title', 'Create Task')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Create Task" subtitle="Add a new task" />
        <form action="{{ route('tasks.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <x-form.input name="title" label="Title" :value="old('title')" required />
            <x-form.textarea name="description" label="Description" :value="old('description')" />
            <x-form.select name="module_id" label="Module" :options="$modules" :value="old('module_id')" required />
            <x-form.select name="status" label="Status" :options="['pending' => 'Pending', 'in_progress' => 'In Progress', 'completed' => 'Completed', 'cancelled' => 'Cancelled']" :value="old('status', 'pending')" required />
            <x-form.select name="priority" label="Priority" :options="['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent']" :value="old('priority', 'medium')" required />
            <x-form.input type="date" name="due_date" label="Due Date" :value="old('due_date')" />
            <div>
                <label for="assignee_ids" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">Assignees</label>
                <select name="assignee_ids[]" id="assignee_ids" multiple
                    class="w-full rounded-xl border bg-white dark:bg-black text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm input-focus outline-none placeholder:text-gray-400 dark:placeholder:text-gray-500 border-gray-300 dark:border-gray-600">
                    @foreach ($users as $key => $label)
                        <option value="{{ $key }}" {{ collect(old('assignee_ids', []))->contains($key) ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Hold Ctrl (or Cmd) to select multiple users</p>
            </div>
            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('tasks.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

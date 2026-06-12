@extends('layouts.admin')

@section('title', 'Create Note')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold mb-6">Create Note</h2>
        <form action="{{ route('notes.store') }}" method="POST" class="space-y-4">
            @csrf
            <x-form.textarea name="content" label="Content" :value="old('content')" required />
            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Save</button>
                <a href="{{ route('notes.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'Edit Module')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold mb-6">Edit Module</h2>
        <form action="{{ route('modules.update', $module->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <x-form.select name="feature_id" label="Feature" :options="$features" :value="old('feature_id', $module->feature_id)" required />
            <x-form.input name="name" label="Name" :value="old('name', $module->name)" required />
            <x-form.input name="slug" label="Slug" :value="old('slug', $module->slug)" required />
            <x-form.textarea name="description" label="Description" :value="old('description', $module->description)" />
            <x-form.checkbox name="is_active" label="Active" :checked="old('is_active', $module->is_active)" />
            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Save</button>
                <a href="{{ route('modules.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

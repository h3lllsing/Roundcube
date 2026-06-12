@extends('layouts.admin')

@section('title', 'Create Feature')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-lg font-semibold mb-6">Create Feature</h2>
        <form action="{{ route('features.store') }}" method="POST" class="space-y-4">
            @csrf
            <x-form.input name="name" label="Name" :value="old('name')" required />
            <x-form.input name="slug" label="Slug" :value="old('slug')" required />
            <x-form.textarea name="description" label="Description" :value="old('description')" />
            <x-form.input name="icon" label="Icon" :value="old('icon')" />
            <x-form.checkbox name="is_active" label="Active" :checked="old('is_active', true)" />
            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">Save</button>
                <a href="{{ route('features.index') }}" class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

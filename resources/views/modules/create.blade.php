@extends('layouts.admin')

@section('title', 'Create Module')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Create Module" subtitle="Add a new module to a feature" />
        <form action="{{ route('modules.store') }}" method="POST" class="space-y-4">
            @csrf
            <x-form.select name="feature_id" label="Feature" :options="$features" :value="old('feature_id')" required />
            <x-form.input name="name" label="Name" :value="old('name')" required />
            <x-form.input name="slug" label="Slug" :value="old('slug')" required />
            <x-form.textarea name="description" label="Description" :value="old('description')" />
            <x-form.checkbox name="is_active" label="Active" :checked="old('is_active', true)" />
            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('modules.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

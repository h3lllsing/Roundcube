@extends('layouts.admin')

@section('title', 'Create Feature')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Create Feature" subtitle="Add a new feature to the system" />
        <form action="{{ route('features.store') }}" method="POST" class="space-y-4">
            @csrf
            <x-form.input name="name" label="Name" :value="old('name')" required />
            <x-form.input name="slug" label="Slug" :value="old('slug')" required />
            <x-form.textarea name="description" label="Description" :value="old('description')" />
            <x-form.input name="icon" label="Icon" :value="old('icon')" />
            <x-form.checkbox name="is_active" label="Active" :checked="old('is_active', true)" />
            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('features.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

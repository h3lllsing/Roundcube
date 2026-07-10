@extends('layouts.admin')

@section('title', 'Edit Module')
@section('breadcrumbTitle', $module->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Edit Module" subtitle="Update module details" />
        <form action="{{ route('modules.update', $module->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $module->updated_at->format('Y-m-d H:i:s') }}">
            <x-form.select name="feature_id" label="Feature" :options="$features" :value="old('feature_id', $module->feature_id)" required />
            <x-form.input name="name" label="Name" :value="old('name', $module->name)" required />
            <x-form.input name="slug" label="Slug" :value="old('slug', $module->slug)" required />
            <x-form.textarea name="description" label="Description" :value="old('description', $module->description)" />
            <x-form.checkbox name="is_active" label="Active" :checked="old('is_active', $module->is_active)" />
            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('modules.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

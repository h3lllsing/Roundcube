@extends('layouts.admin')

@section('title', 'Edit Feature')
@section('breadcrumbTitle', $feature->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Edit Feature" subtitle="Update feature details" />
        <form action="{{ route('features.update', $feature->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $feature->updated_at->format('Y-m-d H:i:s') }}">
            <x-form.input name="name" label="Name" :value="old('name', $feature->name)" required />
            <x-form.input name="slug" label="Slug" :value="old('slug', $feature->slug)" required />
            <x-form.textarea name="description" label="Description" :value="old('description', $feature->description)" />
            <x-form.input name="icon" label="Icon" :value="old('icon', $feature->icon)" />
            <x-form.checkbox name="is_active" label="Active" :checked="old('is_active', $feature->is_active)" />
            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('features.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

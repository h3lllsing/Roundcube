@extends('layouts.admin')

@section('title', 'Edit Privilege')
@section('breadcrumbTitle', $privilege->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Edit Privilege" subtitle="Update privilege details" />
        <form action="{{ route('privileges.update', $privilege->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $privilege->updated_at->format('Y-m-d H:i:s') }}">
            <x-form.input name="name" label="Name" :value="old('name', $privilege->name)" required />
            <x-form.input name="slug" label="Slug" :value="old('slug', $privilege->slug)" required />
            <x-form.textarea name="description" label="Description" :value="old('description', $privilege->description)" />
            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('privileges.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

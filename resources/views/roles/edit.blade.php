@extends('layouts.admin')

@section('title', 'Edit Role')
@section('breadcrumbTitle', $role->name)

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Edit Role" subtitle="Update role details" />
        <form action="{{ route('roles.update', $role->id) }}" method="POST" class="space-y-4">
            @csrf
            @method('PUT')
            <input type="hidden" name="updated_at" value="{{ $role->updated_at->format('Y-m-d H:i:s') }}">
            <x-form.input name="name" label="Name" :value="old('name', $role->name)" required />
            <x-form.input name="slug" label="Slug" :value="old('slug', $role->slug)" required />
            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('roles.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

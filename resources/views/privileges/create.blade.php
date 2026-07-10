@extends('layouts.admin')

@section('title', 'Create Privilege')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <x-page-header title="Create Privilege" subtitle="Add a new privilege" />
        <form action="{{ route('privileges.store') }}" method="POST" class="space-y-4">
            @csrf
            <x-form.input name="name" label="Name" :value="old('name')" required />
            <x-form.input name="slug" label="Slug" :value="old('slug')" required />
            <x-form.textarea name="description" label="Description" :value="old('description')" />
            <div class="flex items-center gap-3 pt-4">
                <x-button type="submit" variant="primary" size="sm">Save</x-button>
                <x-button href="{{ route('privileges.index') }}" variant="outline" size="sm">Cancel</x-button>
            </div>
        </form>
    </div>
</div>
@endsection

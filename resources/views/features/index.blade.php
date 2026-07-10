@extends('layouts.admin')

@section('title', 'Features')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Features" subtitle="Manage application features and modules.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super-admin'))
            <x-button href="{{ route('export', 'features') }}" variant="success" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export CSV
            </x-button>
            @endif
            <x-button href="{{ route('features.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search features..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
        <select name="status"
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <input type="checkbox" name="trashed" value="1" @checked(request('trashed')) class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500">
            Trashed
        </label>
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'status', 'trashed']))
            <x-button href="{{ route('features.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="features">
        <x-bulk-actions type="features" colspan="6" :statuses="[]" :actions="['delete', 'restore', 'force-delete']" />
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">ID</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Slug</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Modules</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($features as $feature)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $feature->id }}" aria-label="Select {{ $feature->name }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 text-gray-500">{{ $feature->id }}</td>
                        <td class="px-6 py-3 font-medium">{{ $feature->name }}</td>
                        <td class="px-6 py-3 text-gray-500">{{ $feature->slug }}</td>
                        <td class="px-6 py-3">{{ $feature->modules->count() }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('features.show', $feature->id) }}" color="indigo" icon="view" label="View" />
                            <x-action href="{{ route('features.edit', $feature->id) }}" color="amber" icon="edit" label="Edit" />
                            <x-action action="{{ route('features.destroy', $feature->id) }}" color="red" icon="delete" label="Delete" confirm="Are you sure?" method="DELETE" />
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="6" icon="box" title="No features found." message="Create features to manage functionality." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-4">
        {{ $features->links() }}
    </div>
</div>
@endsection

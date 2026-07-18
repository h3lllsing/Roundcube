@extends('layouts.admin')

@section('title', 'Privileges')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Privileges" subtitle="Manage user privileges.">
        <x-slot:actions>
            @if(auth()->user()->hasRole('super-admin'))
            @endif
            <x-button href="{{ route('privileges.create') }}" variant="primary" size="sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Create
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <form method="GET" class="flex flex-wrap gap-3 mb-6">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search privileges..."
            class="px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none">
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->filled('search'))
            <x-button href="{{ route('privileges.index') }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </form>

    <form method="POST" action="{{ route('bulk-action') }}" class="mb-6" id="bulk-form">
        @csrf
        <input type="hidden" name="type" value="privileges">
        <x-bulk-actions type="privileges" colspan="6" :statuses="[]" :actions="['delete']" />
    </form>

    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                    <th scope="col" class="text-left px-4 py-3 w-10"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all" data-bulk-select-all></th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">ID</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Name</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Slug</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Roles</th>
                    <th scope="col" class="text-left px-6 py-3 font-medium text-gray-500 dark:text-gray-400">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                @forelse ($privileges as $privilege)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50">
                        <td class="px-4 py-3"><input type="checkbox" name="ids[]" value="{{ $privilege->id }}" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-item" form="bulk-form"></td>
                        <td class="px-6 py-3 text-gray-500">{{ $privilege->id }}</td>
                        <td class="px-6 py-3 font-medium">{{ $privilege->name }}</td>
                        <td class="px-6 py-3"><code class="text-xs bg-gray-100 dark:bg-black px-1.5 py-0.5 rounded">{{ $privilege->slug }}</code></td>
                        <td class="px-6 py-3">{{ $privilege->roles_count }}</td>
                        <td class="px-6 py-3 whitespace-nowrap">
                            <x-action href="{{ route('privileges.show', $privilege->id) }}" color="indigo" icon="view" label="View" />
                            <x-action href="{{ route('privileges.edit', $privilege->id) }}" color="amber" icon="edit" label="Edit" />
                            <x-action action="{{ route('privileges.destroy', $privilege->id) }}" color="red" icon="delete" label="Delete" confirm="Are you sure?" method="DELETE" />
                        </td>
                    </tr>
                @empty
                    <tr><x-empty-state :colspan="6" icon="lock" title="No privileges found." message="Create privileges to define access rights." /></tr>
                @endforelse
            </tbody>
        </table>
    </div>


    <div class="mt-4">
        {{ $privileges->links() }}
    </div>
</div>
@endsection

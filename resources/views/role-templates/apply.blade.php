@extends('layouts.admin')

@section('title', 'Apply Template: ' . $template->name)

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header :title="'Apply: ' . $template->name" :subtitle="'Target role: ' . $role->name . ' (' . $role->slug . ')'">
        <x-slot:actions>
            <x-button href="{{ route('role-templates.show', $template->id) }}" variant="outline" size="sm">
                &larr; Back
            </x-button>
        </x-slot:actions>
    </x-page-header>

    @if($template->is_dangerous)
    <div class="mb-6 px-4 py-3 rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 dark:text-red-400 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <div>
                <p class="font-semibold text-red-700 dark:text-red-400">Dangerous Template Warning</p>
                <p class="text-sm text-red-600 dark:text-red-400 mt-1">This template grants extensive permissions. It should not normally be applied to non-super-admin roles. Please review the diff carefully before proceeding.</p>
            </div>
        </div>
    </div>
    @endif

    @if(count($diff['added']) > 0)
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <h3 class="text-md font-semibold mb-2 text-green-700 dark:text-green-400">
            Modules to be Added ({{ count($diff['added']) }})
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">These modules currently have no permissions for this role and will be created.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                        <th class="text-left px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Module</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Read</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Create</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Update</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Delete</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Approve</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Export</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Reveal</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Import</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($diff['added'] as $item)
                    <tr class="bg-green-50/50 dark:bg-green-900/10">
                        <td class="px-4 py-2 font-medium">{{ $item['module']->name }}</td>
                        @foreach (config('permissions.keys') as $key)
                        <td class="px-3 py-2 text-center">
                            @if($item['template_values'][$key])
                                <span class="text-green-600 font-bold">&#10003;</span>
                            @else
                                <span class="text-red-400">&#10005;</span>
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(count($diff['changed']) > 0)
    <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5 mb-6">
        <h3 class="text-md font-semibold mb-2 text-amber-700 dark:text-amber-400">
            Modules to be Overwritten ({{ count($diff['changed']) }})
        </h3>
        <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">These modules already have permissions that will be replaced.</p>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                        <th class="text-left px-4 py-2 font-medium text-gray-500 dark:text-gray-400">Module</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Read</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Create</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Update</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Delete</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Approve</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Export</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Reveal</th>
                        <th class="text-center px-3 py-2 font-medium text-gray-500 dark:text-gray-400">Import</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($diff['changed'] as $item)
                    <tr class="bg-amber-50/50 dark:bg-amber-900/10">
                        <td class="px-4 py-2 font-medium">{{ $item['module']->name }}</td>
                        @foreach (config('permissions.keys') as $key)
                        <td class="px-3 py-2 text-center">
                            @php
                                $old = $item['current_values'][$key];
                                $new = $item['template_values'][$key];
                            @endphp
                            @if($old !== $new)
                                <span class="text-red-400 line-through mr-1">{{ $old ? '&#10003;' : '&#10005;' }}</span>
                                <span class="text-green-600 font-bold">{{ $new ? '&#10003;' : '&#10005;' }}</span>
                            @else
                                @if($old)
                                    <span class="text-green-600">&#10003;</span>
                                @else
                                    <span class="text-red-400">&#10005;</span>
                                @endif
                            @endif
                        </td>
                        @endforeach
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    @if(count($diff['unchanged']) > 0)
    <div class="mb-6 px-4 py-3 rounded-xl bg-gray-50 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <span class="font-medium">{{ count($diff['unchanged']) }}</span> module(s) already match the template and will remain unchanged.
        </p>
    </div>
    @endif

    @if(count($diff['added']) === 0 && count($diff['changed']) === 0)
    <div class="mb-6 px-4 py-3 rounded-xl bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
        <p class="text-sm text-blue-700 dark:text-blue-400">All module permissions for this role already match the template. No changes needed.</p>
    </div>
    @endif

    <div class="flex items-center gap-4 mt-6">
        <form method="POST" action="{{ route('role-templates.apply', $template->id) }}" class="flex flex-wrap items-center gap-4">
            @csrf
            <input type="hidden" name="role_id" value="{{ $role->id }}">
            <input type="hidden" name="confirmed" value="1">

            @if($template->is_dangerous)
            <label class="flex items-center gap-2 text-sm text-red-700 dark:text-red-400 font-medium">
                <input type="checkbox" name="confirm_dangerous" value="1" required class="rounded border-red-300 text-red-600 focus:ring-red-500">
                I understand this template grants extensive permissions.
            </label>
            @endif

            @if(count($diff['added']) > 0 || count($diff['changed']) > 0)
            <x-button type="submit" variant="primary">
                Confirm &amp; Apply
            </x-button>
            @else
            <x-button type="submit" variant="outline" disabled>
                No Changes to Apply
            </x-button>
            @endif
        </form>
    </div>
</div>
@endsection

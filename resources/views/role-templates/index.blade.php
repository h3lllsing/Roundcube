@extends('layouts.admin')

@section('title', 'Role Templates')

@section('content')
<div class="max-w-7xl mx-auto">
    <x-page-header title="Role Templates" subtitle="Pre-configured permission blueprints that can be applied to any role." />

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse ($templates as $template)
        <div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
            <div class="p-5">
                <div class="flex items-start justify-between mb-3">
                    <div>
                        <h3 class="text-lg font-semibold">{{ $template->name }}</h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5">
                            v{{ $template->version }} &middot; {{ $template->module_count }} module(s)
                        </p>
                    </div>
                    <div class="flex gap-1.5">
                        @if($template->is_protected)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-400">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            Protected
                        </span>
                        @endif
                        @if($template->is_dangerous)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            Dangerous
                        </span>
                        @endif
                    </div>
                </div>

                @if($template->description)
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4 line-clamp-2">{{ $template->description }}</p>
                @endif

                <div class="flex gap-2">
                    <x-button href="{{ route('role-templates.show', $template->id) }}" variant="primary" size="sm">
                        View Details
                    </x-button>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-2">
            <x-empty-state icon="key" title="No role templates found." message="Role templates provide pre-configured permission blueprints." />
        </div>
        @endforelse
    </div>
</div>
@endsection

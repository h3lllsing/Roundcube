@props(['name', 'label', 'type' => 'text', 'value' => '', 'required' => false, 'disabled' => false, 'icon' => null])

@php
$hasError = $errors->has($name);
$icons = [
    'user' => '<svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>',
    'email' => '<svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/></svg>',
    'lock' => '<svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 dark:text-gray-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
];
$iconHtml = $icon && isset($icons[$icon]) ? $icons[$icon] : '';
@endphp

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ $label }}@if($required) <span class="text-red-500">*</span>@endif</label>
    @if ($iconHtml)
    <div class="relative">
        {!! $iconHtml !!}
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'w-full pl-10 pr-3 py-2.5 rounded-xl border bg-white dark:bg-black text-gray-900 dark:text-gray-100 text-sm input-focus outline-none disabled:opacity-60 disabled:cursor-not-allowed placeholder:text-gray-400 dark:placeholder:text-gray-500 ' . ($hasError ? 'border-red-400 dark:border-red-500' : 'border-gray-300 dark:border-gray-600')]) }}
        />
    </div>
    @else
    <input
        type="{{ $type }}"
        name="{{ $name }}"
        id="{{ $name }}"
        value="{{ old($name, $value) }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'w-full rounded-xl border bg-white dark:bg-black text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm input-focus outline-none disabled:opacity-60 disabled:cursor-not-allowed placeholder:text-gray-400 dark:placeholder:text-gray-500 ' . ($hasError ? 'border-red-400 dark:border-red-500' : 'border-gray-300 dark:border-gray-600')]) }}
    />
    @endif
    @error($name)
        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

@props(['name', 'label', 'options' => [], 'value' => '', 'required' => false, 'placeholder' => 'Select...', 'disabled' => false])

@php $hasError = $errors->has($name); @endphp

<div>
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ $label }}@if($required) <span class="text-red-500">*</span>@endif</label>
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        {{ $required ? 'required' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'w-full rounded-xl border bg-white dark:bg-black text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm input-focus outline-none placeholder:text-gray-400 dark:placeholder:text-gray-500 ' . ($hasError ? 'border-red-400 dark:border-red-500' : 'border-gray-300 dark:border-gray-600') . ($disabled ? ' opacity-60 cursor-not-allowed' : '')]) }}
    >
        <option value="">{{ $placeholder }}</option>
        @foreach ($options as $key => $label)
            <option value="{{ $key }}" {{ (string) old($name, $value) === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
        @endforeach
    </select>
    @error($name)
        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

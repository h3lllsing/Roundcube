@props(['name', 'label', 'required' => false, 'disabled' => false, 'placeholder' => null, 'value' => null])

@php $hasError = $errors->has($name); @endphp

<div x-data="{ show: false }">
    <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5">{{ $label }}@if($required) <span class="text-red-500">*</span>@endif</label>
    <div class="relative">
        <input
            :type="show ? 'text' : 'password'"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            autocomplete="new-password"
            {{ $required ? 'required' : '' }}
            {{ $disabled ? 'disabled' : '' }}
            {{ $attributes->merge(['class' => 'w-full rounded-xl border bg-white dark:bg-black text-gray-900 dark:text-gray-100 px-3 py-2.5 text-sm input-focus outline-none disabled:opacity-60 disabled:cursor-not-allowed placeholder:text-gray-400 dark:placeholder:text-gray-500 pr-10 ' . ($hasError ? 'border-red-400 dark:border-red-500' : 'border-gray-300 dark:border-gray-600')]) }}
        />
        <button type="button" @click="show = !show" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors" aria-label="Toggle password visibility">
            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
            </svg>
        </button>
    </div>
    @error($name)
        <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ $message }}
        </p>
    @enderror
</div>

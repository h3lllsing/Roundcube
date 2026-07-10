@props(['name', 'label', 'value' => '1', 'checked' => false, 'disabled' => false])

<label class="flex items-center gap-2 {{ $disabled ? 'opacity-60 cursor-not-allowed' : 'cursor-pointer' }}">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ old($name, $checked) ? 'checked' : '' }}
        {{ $disabled ? 'disabled' : '' }}
        {{ $attributes->merge(['class' => 'rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500/40']) }}
    />
    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
</label>
@error($name)
    <p class="mt-1.5 text-xs text-red-500 flex items-center gap-1">
        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ $message }}
    </p>
@enderror

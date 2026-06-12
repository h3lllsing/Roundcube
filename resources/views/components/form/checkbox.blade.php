@props(['name', 'label', 'value' => '1', 'checked' => false])

<label class="flex items-center gap-2 cursor-pointer">
    <input
        type="checkbox"
        name="{{ $name }}"
        value="{{ $value }}"
        {{ old($name, $checked) ? 'checked' : '' }}
        {{ $attributes->merge(['class' => 'rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500']) }}
    />
    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $label }}</span>
</label>
@error($name)
    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
@enderror

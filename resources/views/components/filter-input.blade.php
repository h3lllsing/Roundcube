@props([
    'name' => 'search',
    'value' => null,
    'placeholder' => 'Search...',
])
@php
    $value = $value ?? request($name);
@endphp
<input type="text" name="{{ $name }}" value="{{ $value }}" placeholder="{{ $placeholder }}"
    {{ $attributes->class(['px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl input-focus outline-none']) }}>

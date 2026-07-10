@props([
    'name' => 'status',
    'value' => null,
    'placeholder' => 'All',
    'options' => [],
])
@php
    $value = $value ?? request($name);
@endphp
<select name="{{ $name }}"
    {{ $attributes->class(['px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none']) }}>
    <option value="">{{ $placeholder }}</option>
    @foreach($options as $optValue => $optLabel)
        <option value="{{ $optValue }}" @selected($value == $optValue)>{{ $optLabel }}</option>
    @endforeach
</select>

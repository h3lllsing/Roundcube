@props([
    'value' => null,
    'format' => 'Y-m-d',
    'empty' => '—',
])

@php
    $formatted = $value ? (is_string($value) ? \Carbon\Carbon::parse($value)->format($format) : $value->format($format)) : $empty;
@endphp

<span {{ $attributes->class(['text-gray-900 dark:text-gray-100']) }}>
    {{ $formatted }}
</span>

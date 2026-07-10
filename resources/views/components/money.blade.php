@props([
    'value' => null,
    'currency' => '$',
    'precision' => 2,
    'empty' => '—',
])

@php
    $formatted = $value !== null && $value !== '' ? $currency . number_format((float) $value, $precision) : $empty;
@endphp

<span {{ $attributes->class(['text-gray-900 dark:text-gray-100 tabular-nums']) }}>
    {{ $formatted }}
</span>

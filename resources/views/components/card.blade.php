@props([
    'padding' => 'md',
    'variant' => 'default',
    'hover' => false,
    'header' => null,
    'footer' => null,
])

@php
$paddings = [
    'none' => '',
    'sm' => 'p-4',
    'md' => 'p-6',
    'lg' => 'p-8',
];
$variants = [
    'default' => 'bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700',
    'glass' => 'glass-card',
    'bordered' => 'border border-gray-200 dark:border-gray-700 rounded-xl bg-transparent',
    'flat' => 'bg-gray-50 dark:bg-gray-900 rounded-xl',
];
@endphp

<div {{ $attributes->class([
    $variants[$variant] ?? $variants['default'],
    $hover ? 'card-hover' : '',
]) }}>
    @if($header)
        <div class="{{ $paddings[$padding] ?? $paddings['md'] }} border-b border-gray-200 dark:border-gray-700">
            {{ $header }}
        </div>
    @endif
    <div class="{{ !$header && !$footer ? ($paddings[$padding] ?? $paddings['md']) : '' }}">
        @if($header || $footer)
            <div class="{{ $paddings[$padding] ?? $paddings['md'] }}">
                {{ $slot }}
            </div>
        @else
            {{ $slot }}
        @endif
    </div>
    @if($footer)
        <div class="{{ $paddings[$padding] ?? $paddings['md'] }} border-t border-gray-200 dark:border-gray-700">
            {{ $footer }}
        </div>
    @endif
</div>

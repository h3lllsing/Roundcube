@props([
    'variant' => 'primary',
    'size' => 'md',
    'href' => null,
    'type' => 'submit',
    'disabled' => false,
])

@php
$base = 'inline-flex items-center justify-center gap-1.5 font-medium transition-all duration-200 whitespace-nowrap';
$variants = [
    'primary' => 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-sm shadow-indigo-500/20 hover:shadow-indigo-500/30 active:scale-[0.98]',
    'danger' => 'bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-sm shadow-red-500/20 hover:shadow-red-500/30 active:scale-[0.98]',
    'success' => 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white shadow-sm shadow-emerald-500/20 hover:shadow-emerald-500/30 active:scale-[0.98]',
    'outline' => 'border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-400 dark:hover:border-gray-500 active:scale-[0.98]',
    'ghost' => 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800 active:scale-[0.98]',
];
$sizes = [
    'sm' => 'px-3 py-1.5 text-xs rounded-xl',
    'md' => 'px-4 py-2 text-sm rounded-xl',
    'lg' => 'px-6 py-2.5 text-sm rounded-xl font-semibold',
];
$classes = $base . ' ' . $variants[$variant] . ' ' . $sizes[$size];
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $disabled ? 'disabled' : '' }} {{ $attributes->class([$classes, 'opacity-50 cursor-not-allowed' => $disabled, 'cursor-pointer' => !$disabled]) }}>{{ $slot }}</button>
@endif
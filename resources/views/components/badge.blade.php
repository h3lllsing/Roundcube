@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false,
])

@php
$variants = [
    'default' => 'bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300',
    'active' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
    'expired' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
    'suspended' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
    'enabled' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
    'disabled' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300',
    'cancelled' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300',
    'inactive' => 'bg-gray-100 dark:bg-gray-800 text-gray-600 dark:text-gray-300',
    'pending_transfer' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300',
    'unknown' => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
    'primary' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300',
    'success' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300',
    'warning' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300',
    'danger' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
    'info' => 'bg-sky-100 dark:bg-sky-900/30 text-sky-700 dark:text-sky-300',
];
$sizes = [
    'sm' => 'px-1.5 py-0.5 text-[10px]',
    'md' => 'px-2 py-0.5 text-xs',
    'lg' => 'px-2.5 py-1 text-sm',
];
@endphp

<span {{ $attributes->class([
    'inline-flex items-center gap-1 font-medium rounded-full',
    $variants[$variant] ?? $variants['default'],
    $sizes[$size] ?? $sizes['md'],
]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
    @endif
    {{ $slot }}
</span>

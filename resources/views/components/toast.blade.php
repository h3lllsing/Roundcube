@props(['message' => null, 'type' => 'success'])

@php
$icons = [
    'success' => 'M5 13l4 4L19 7',
    'error' => 'M6 18L18 6M6 6l12 12',
    'info' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
];
$styles = [
    'success' => 'border-l-emerald-500 shadow-emerald-500/10 dark:shadow-emerald-500/5',
    'error' => 'border-l-red-500 shadow-red-500/10 dark:shadow-red-500/5',
    'info' => 'border-l-sky-500 shadow-sky-500/10 dark:shadow-sky-500/5',
    'warning' => 'border-l-amber-500 shadow-amber-500/10 dark:shadow-amber-500/5',
];
$bgColors = [
    'success' => 'bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400',
    'error' => 'bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400',
    'info' => 'bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400',
    'warning' => 'bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400',
];
@endphp

<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100 translate-x-0" x-transition:leave-end="opacity-0 translate-x-4" class="toast pointer-events-auto flex items-start gap-3.5 px-4 py-3.5 rounded-2xl glass-card border-l-4 text-sm shadow-lg {{ $styles[$type] ?? $styles['success'] }}">
    <div class="w-7 h-7 rounded-xl {{ $bgColors[$type] ?? $bgColors['success'] }} flex items-center justify-center shrink-0 mt-0.5">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $icons[$type] ?? $icons['success'] }}"/>
        </svg>
    </div>
    <span class="flex-1 pt-1 text-gray-800 dark:text-gray-200 font-medium">{{ $message ?? $slot }}</span>
    <button @click="show = false" class="w-5 h-5 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700/50 shrink-0 transition-all focus:outline-none focus:ring-2 focus:ring-gray-400/40">&times;</button>
</div>

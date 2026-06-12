<div {{ $attributes->merge(['class' => 'bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-5']) }}>
    <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ $label }}</p>
    <p class="text-3xl font-bold text-gray-900 dark:text-white">{{ $value }}</p>
</div>

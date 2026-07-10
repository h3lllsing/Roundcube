@props([
    'label' => null,
    'value' => null,
    'inline' => false,
])

@if($inline)
    <div {{ $attributes->class(['flex items-baseline gap-2']) }}>
        @if($label)
            <p class="text-sm text-gray-500 dark:text-gray-400 shrink-0">{{ $label }}:</p>
        @endif
        <p class="font-medium text-gray-900 dark:text-gray-100">
            {{ $value ?? $slot }}
        </p>
    </div>
@else
    <div {{ $attributes->class(['space-y-0.5']) }}>
        @if($label)
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ $label }}</p>
        @endif
        <p class="font-medium text-gray-900 dark:text-gray-100">
            {{ $value ?? $slot }}
        </p>
    </div>
@endif

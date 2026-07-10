@props(['title', 'subtitle' => null, 'backUrl' => null, 'backLabel' => null])

<div class="flex items-start justify-between mb-6 flex-wrap gap-2">
    <div>
        @if ($backUrl)
        <a href="{{ $backUrl }}" class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors mb-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            {{ $backLabel ?? 'Back' }}
        </a>
        @endif
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $title }}</h1>
        @if ($subtitle)
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>
    @if (isset($actions))
    <div class="flex items-center gap-2">{{ $actions }}</div>
    @endif
</div>
<a {{ $attributes->merge(['href' => $href]) }}
    class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm transition-colors
        @if ($active ?? false) bg-blue-50 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 font-medium
        @else text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 @endif
    "
>{{ $slot }}</a>

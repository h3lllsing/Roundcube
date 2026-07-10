<a {{ $attributes->merge(['href' => $href]) }}
    class="nav-link relative flex items-center gap-3 px-3 py-2 rounded-xl text-sm transition-all duration-200
        @if ($active ?? false) bg-gradient-to-r from-indigo-50 to-purple-50/70 dark:from-indigo-900/30 dark:to-purple-900/20 text-indigo-700 dark:text-indigo-300 font-semibold shadow-sm shadow-indigo-500/10 nav-link-active
        @else text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 @endif
    "
    @if ($active ?? false) aria-current="page" @endif
>{{ $slot }}
@if ($active ?? false)
    <span class="absolute left-0 top-1/2 -translate-y-1/2 w-0.5 h-5 rounded-full bg-gradient-to-b from-indigo-500 to-purple-600 shadow-sm shadow-indigo-500/30" aria-hidden="true"></span>
@endif
</a>
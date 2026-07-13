@props(['label', 'value', 'icon' => null, 'color' => 'indigo'])

@php
$colors = [
    'indigo' => 'from-indigo-500/10 to-purple-500/5 dark:from-indigo-500/15 dark:to-purple-500/5 border-indigo-200/50 dark:border-indigo-800/30',
    'emerald' => 'from-emerald-500/10 to-teal-500/5 dark:from-emerald-500/15 dark:to-teal-500/5 border-emerald-200/50 dark:border-emerald-800/30',
    'amber' => 'from-amber-500/10 to-orange-500/5 dark:from-amber-500/15 dark:to-orange-500/5 border-amber-200/50 dark:border-amber-800/30',
    'rose' => 'from-rose-500/10 to-pink-500/5 dark:from-rose-500/15 dark:to-pink-500/5 border-rose-200/50 dark:border-rose-800/30',
    'sky' => 'from-sky-500/10 to-blue-500/5 dark:from-sky-500/15 dark:to-blue-500/5 border-sky-200/50 dark:border-sky-800/30',
    'violet' => 'from-violet-500/10 to-purple-500/5 dark:from-violet-500/15 dark:to-purple-500/5 border-violet-200/50 dark:border-violet-800/30',
];
$accent = $colors[$color] ?? $colors['indigo'];

$numeric = is_numeric($value) ? (float) $value : null;
$sid = 'stat-' . Str::slug($label);

$icons = [
    'features' => '<svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>',
    'tasks' => '<svg class="w-5 h-5 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
    'users' => '<svg class="w-5 h-5 text-sky-500 dark:text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M7 20.75a7 7 0 0110 0"/></svg>',
    'dollar' => '<svg class="w-5 h-5 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'bell' => '<svg class="w-5 h-5 text-rose-500 dark:text-rose-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
    'server' => '<svg class="w-5 h-5 text-violet-500 dark:text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>',
    'clock' => '<svg class="w-5 h-5 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
    'shield' => '<svg class="w-5 h-5 text-indigo-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>',
    'document' => '<svg class="w-5 h-5 text-amber-500 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>',
];
@endphp

<div class="stat-card rounded-xl bg-gradient-to-br {{ $accent }} border p-5 card-hover">
    <div class="grid grid-cols-[1fr_auto] items-start gap-2">
        <div class="min-w-0 overflow-hidden">
            <p class="text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1.5">{{ $label }}</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                @if ($numeric !== null)
                <span class="number-pulse" id="{{ $sid }}" data-count="{{ $numeric }}">0</span>
                @else
                {{ $value }}
                @endif
            </p>
        </div>
        @if ($icon && isset($icons[$icon]))
        <div class="w-9 h-9 rounded-xl bg-white/60 dark:bg-black/40 flex items-center justify-center shrink-0 shadow-xs">
            {!! $icons[$icon] !!}
        </div>
        @endif
    </div>
</div>

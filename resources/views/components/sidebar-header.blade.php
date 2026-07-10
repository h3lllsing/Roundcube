<div class="sidebar-header h-14 flex items-center justify-between px-4 bg-gradient-to-r from-indigo-600 to-purple-600">
    <a href="{{ route('dashboard') }}" id="appTitle" class="text-lg font-semibold tracking-tight truncate text-white flex items-center gap-2">
        <svg class="w-6 h-6 shrink-0" aria-hidden="true" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
        </svg>
        <span>{{ config('app.name', 'Dashboard') }}</span>
    </a>
    <button type="button" id="sidebarToggle" class="text-white/80 hover:text-white shrink-0 lg:hidden" aria-label="Open sidebar" title="Toggle sidebar">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
    <button type="button" id="sidebarDesktopToggle" class="text-white/80 hover:text-white shrink-0 max-lg:hidden" aria-label="Toggle sidebar" title="Toggle sidebar">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
        </svg>
    </button>
</div>

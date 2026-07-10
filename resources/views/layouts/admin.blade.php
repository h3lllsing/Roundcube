<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', config('app.name') . ' - Admin Dashboard')">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="base-url" content="{{ url('') }}">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <script>
        (function() {
            var dm = localStorage.getItem('darkMode');
            if (dm === '1' || (dm !== '0' && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            }
        })();
    </script>
    <x-fonts />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 dark:text-gray-100">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:top-4 focus:left-4 focus:z-[300] focus:px-4 focus:py-2 focus:bg-white dark:focus:bg-gray-800 focus:text-indigo-600 focus:rounded-xl focus:shadow-lg focus:outline-none focus:ring-2 focus:ring-indigo-500">Skip to main content</a>

    <div class="flex h-screen overflow-hidden">
        <aside id="sidebar" class="w-64 glass border-r border-gray-200/50 dark:border-gray-800/50 flex flex-col shrink-0 transition-all duration-300 max-lg:-translate-x-full max-lg:fixed max-lg:z-50 max-lg:h-full overflow-hidden">
            <x-sidebar-header />
            <div id="sidebarContents" class="flex flex-col flex-1 min-h-0 overflow-y-auto">
            <x-sidebar-search />
            <x-sidebar-nav-groups
                :show-providers="$showProviders"
                :show-hostings="$showHostings"
                :show-domains="$showDomains"
                :show-emails="$showEmails"
                :show-vps="$showVps"
                :show-voip="$showVoip"
                :show-other-services="$showOtherServices"
                :show-expiry-trackers="$showExpiryTrackers"
                :show-assets="$showAssets"
                :show-g-mails="$showGMails"
                :show-monitoring="$showMonitoring"
                :show-vault="$showVault"
                :show-my-vault="$showMyVault"
            />
            <x-user-card />
            </div>
        </aside>
        <div id="sidebarOverlay" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm z-40 max-lg:block lg:hidden"></div>

        <main id="main-content" class="flex-1 overflow-y-auto min-w-0 bg-grid">
            <div class="sticky top-0 z-30 lg:hidden flex items-center gap-3 px-4 h-12 bg-white/80 dark:bg-black/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800">
                <button type="button" id="mobileMenuBtn" class="p-1.5 text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-500/40" aria-label="Open sidebar menu" title="Open menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <span class="text-sm font-semibold text-gray-700 dark:text-gray-200 truncate">@yield('title', config('app.name', 'Dashboard'))</span>
            </div>
            <div class="p-6 lg:p-8 fade-in">
                <div id="toastContainer" class="fixed top-4 right-4 z-[100] space-y-2.5 max-w-sm w-full pointer-events-none">
                    @if(session('success'))
                    <x-toast message="{{ session('success') }}" type="success" />
                    @endif
                    @if(session('error'))
                    <x-toast message="{{ session('error') }}" type="error" />
                    @endif
                </div>
                <x-breadcrumbs :title="trim($__env->yieldContent('breadcrumbTitle'))" />
                @yield('content')
            </div>
        </main>
    </div>

    <x-loading-overlay />

    <x-confirm-dialog />

    <x-command-palette />


    @vite('resources/js/help-center.js')
    @stack('scripts')
</body>
</html>
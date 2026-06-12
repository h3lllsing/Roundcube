<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-900 dark:text-gray-100">
    <div class="flex h-screen overflow-hidden">
        <aside class="w-64 bg-white dark:bg-gray-800 border-r border-gray-200 dark:border-gray-700 flex flex-col shrink-0">
            <div class="h-14 flex items-center px-5 border-b border-gray-200 dark:border-gray-700">
                <a href="{{ route('dashboard') }}" class="text-lg font-semibold tracking-tight">{{ config('app.name', 'Dashboard') }}</a>
            </div>
            <nav class="flex-1 overflow-y-auto p-3 space-y-1">
                <x-nav-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">Dashboard</x-nav-link>
                <x-nav-link href="{{ route('features.index') }}" :active="request()->routeIs('features.*')">Features</x-nav-link>
                <x-nav-link href="{{ route('modules.index') }}" :active="request()->routeIs('modules.*')">Modules</x-nav-link>
                <x-nav-link href="{{ route('tasks.index') }}" :active="request()->routeIs('tasks.*')">Tasks</x-nav-link>
                <x-nav-link href="{{ route('domains.index') }}" :active="request()->routeIs('domains.*')">Domains</x-nav-link>
                <x-nav-link href="{{ route('hostings.index') }}" :active="request()->routeIs('hostings.*')">Hostings</x-nav-link>
                <x-nav-link href="{{ route('vps.index') }}" :active="request()->routeIs('vps.*')">VPS</x-nav-link>
                <x-nav-link href="{{ route('voip.index') }}" :active="request()->routeIs('voip.*')">VoIP</x-nav-link>
                <x-nav-link href="{{ route('vault.index') }}" :active="request()->routeIs('vault.*')">Vault</x-nav-link>
                <x-nav-link href="{{ route('notes.index') }}" :active="request()->routeIs('notes.*')">Notes</x-nav-link>
                <x-nav-link href="{{ route('service-providers.index') }}" :active="request()->routeIs('service-providers.*')">Providers</x-nav-link>
                <x-nav-link href="{{ route('activity-logs.index') }}" :active="request()->routeIs('activity-logs.*')">Activity</x-nav-link>
                <x-nav-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')">Users</x-nav-link>
            </nav>
            <div class="p-3 border-t border-gray-200 dark:border-gray-700">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-medium shrink-0">
                        {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">{{ Auth::user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300" title="Sign out">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <main class="flex-1 overflow-y-auto">
            <div class="p-6 lg:p-8">
                @if (session('success'))
                    <div class="mb-4 px-4 py-3 rounded-lg bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-700 text-sm text-green-700 dark:text-green-300">
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-700 text-sm text-red-700 dark:text-red-300">
                        {{ session('error') }}
                    </div>
                @endif
                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Login</title>
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
    <style>
        body {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
        }
        @media (max-width: 767px) {
            body {
                background-attachment: scroll;
            }
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center lg:justify-end px-4 lg:px-16"
      style="background-image: url('{{ asset('images/login/dark.jpg') }}');">
    <div class="fixed inset-0 bg-black/50 lg:bg-transparent pointer-events-none"></div>
    <div class="w-full max-w-sm relative fade-in-up z-10">
        <div class="bg-white dark:bg-black rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-gray-900/50 p-8">
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mx-auto shadow-lg shadow-indigo-500/20 mb-4">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ config('app.name', 'Dashboard') }}</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Enterprise IT Operations Platform</p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-4 max-w-xs mx-auto leading-relaxed">Centralize infrastructure, domains, hosting, VPS, assets, credentials, renewals, and security from a single enterprise workspace.</p>
            </div>
            @if ($errors->any())
                <div class="mb-5 p-3 text-sm text-red-700 bg-red-50 dark:bg-red-900/20 dark:text-red-400 rounded-xl border border-red-100 dark:border-red-800/30 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $errors->first('email') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf

                <x-form.input name="email" label="Email" type="email" icon="email" :value="old('email')" required placeholder="you@example.com" class="bg-white dark:bg-black text-gray-900 dark:text-white" />

                <x-form.input name="password" label="Password" type="password" icon="lock" required placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" class="bg-white dark:bg-black text-gray-900 dark:text-white" />

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer group">
                        <input type="checkbox" name="remember"
                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
                        <span class="group-hover:text-gray-700 dark:group-hover:text-gray-300">Remember me</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 transition-colors">Forgot password?</a>
                </div>

                <x-button type="submit" variant="primary" size="lg" class="w-full" x-on:click="startLoading($el)">Sign in</x-button>
            </form>
        </div>
    </div>
</body>
</html>
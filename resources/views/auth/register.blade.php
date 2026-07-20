<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} - Register</title>
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

</head>
<body class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 rounded-full bg-indigo-500/10 blur-3xl"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 rounded-full bg-purple-500/10 blur-3xl"></div>
    </div>
    <div class="w-full max-w-sm relative fade-in-up">
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center mx-auto shadow-lg shadow-indigo-500/20 mb-4">
                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ config('app.name', 'Dashboard') }}</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Create a new account</p>
        </div>

        <div class="bg-white dark:bg-black rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-gray-900/50 p-8">
            @if ($errors->any())
                <div class="mb-5 p-3 text-sm text-red-700 bg-red-50 dark:bg-red-900/20 dark:text-red-400 rounded-xl border border-red-100 dark:border-red-800/30 flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" class="space-y-5">
                @csrf

                <x-form.input name="name" label="Name" icon="user" :value="old('name')" required placeholder="Your name" class="bg-white dark:bg-black text-gray-900 dark:text-white" />

                <x-form.input name="email" label="Email" type="email" icon="email" :value="old('email')" required placeholder="you@example.com" class="bg-white dark:bg-black text-gray-900 dark:text-white" />

                <x-form.input name="password" label="Password" type="password" icon="lock" required placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" class="bg-white dark:bg-black text-gray-900 dark:text-white" />

                <x-form.input name="password_confirmation" label="Confirm Password" type="password" icon="lock" required placeholder="&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;" class="bg-white dark:bg-black text-gray-900 dark:text-white" />

                <x-button type="submit" variant="primary" size="lg" class="w-full" x-on:click="startLoading($el)">Create account</x-button>

                <p class="text-sm text-center text-gray-500 dark:text-gray-400">
                    Already have an account?
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 dark:hover:text-indigo-300 font-medium transition-colors">Sign in</a>
                </p>
            </form>
        </div>
    </div>
</body>
</html>
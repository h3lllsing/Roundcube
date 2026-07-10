<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>419 - Session Expired</title>
    <x-fonts />
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="text-8xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">419</div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Session Expired</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">Your session has expired. Please sign in again to continue.</p>
        <x-button href="{{ route('login') }}" variant="primary" size="md">Sign In</x-button>
    </div>
</body>
</html>
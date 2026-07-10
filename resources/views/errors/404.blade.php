<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Not Found</title>
    <x-fonts />
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-gray-50 dark:bg-gray-900 flex items-center justify-center p-4">
    <div class="text-center max-w-md">
        <div class="text-8xl font-bold text-indigo-600 dark:text-indigo-400 mb-4">404</div>
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">Page Not Found</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">The page you're looking for doesn't exist or has been moved.</p>
        <x-button href="{{ route('dashboard') }}" variant="primary" size="md">Back to Dashboard</x-button>
    </div>
</body>
</html>
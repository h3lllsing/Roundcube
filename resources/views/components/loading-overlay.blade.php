@props(['id' => 'loadingOverlay', 'message' => 'Loading...'])

<div id="{{ $id }}" class="hidden fixed inset-0 z-[300] flex items-center justify-center bg-black/20 backdrop-blur-sm">
    <div class="bg-white dark:bg-black rounded-2xl shadow-2xl p-6 flex items-center gap-3">
        <div class="w-5 h-5 border-2 border-indigo-500/30 border-t-indigo-600 rounded-full animate-spin"></div>
        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ $message }}</span>
    </div>
</div>

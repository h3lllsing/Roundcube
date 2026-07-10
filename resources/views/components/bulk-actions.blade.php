@props(['type', 'colspan', 'statuses' => ['active', 'expired', 'cancelled', 'suspended'], 'actions' => ['update-status', 'delete', 'restore', 'force-delete'], 'actionLabels' => []])

<div class="mb-4" x-data="{ selectAll: false }">
    <div class="flex items-center gap-3 bg-gradient-to-r from-gray-50 to-indigo-50/50 dark:from-gray-800/50 dark:to-indigo-900/10 px-4 py-3 rounded-2xl border border-gray-200 dark:border-gray-700">
        <label class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400 cursor-pointer select-none">
            <input type="checkbox" x-model="selectAll" @change="document.querySelectorAll('.bulk-item').forEach(cb => cb.checked = selectAll)" aria-label="Select all" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 cursor-pointer">
            Select All
        </label>
        <select name="action" required class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">Action...</option>
            @foreach ($actions as $a)
                <option value="{{ $a }}">{{ $actionLabels[$a] ?? ucfirst(str_replace('-', ' ', $a)) }}</option>
            @endforeach
        </select>
        @if (in_array('update-status', $actions))
        <select name="status" class="px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">Status...</option>
            @foreach ($statuses as $s)
                <option value="{{ $s }}">{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        @endif
        <x-button type="submit" variant="primary" size="sm" data-confirm="Apply bulk action?" data-confirm-button="Apply">Apply</x-button>
    </div>
</div>

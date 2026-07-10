@props([
    'bulk' => true,
])

<div {{ $attributes->class(['bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full']) }}>
    <table class="w-full text-sm">
        <thead>
            <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
                @if($bulk)
                <th scope="col" class="text-left px-4 py-3 w-10">
                    <input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 focus:ring-indigo-500 bulk-select-all"
                           x-data x-on:change="document.querySelectorAll('.bulk-item').forEach(cb => cb.checked = $event.target.checked)">
                </th>
                @endif
                {{ $head }}
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
            {{ $slot }}
        </tbody>
    </table>
</div>

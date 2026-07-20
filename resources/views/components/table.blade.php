<table {{ $attributes->merge(['class' => 'w-full text-sm']) }}>
    <thead>
        <tr class="border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-black/50">
            {{ $head }}
        </tr>
    </thead>
    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
        {{ $slot }}
    </tbody>
</table>
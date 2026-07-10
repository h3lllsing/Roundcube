@props([
    'route' => null,
    'searchPlaceholder' => 'Search...',
    'showSearch' => true,
    'showDateRange' => true,
    'showStatus' => false,
    'statusOptions' => [],
    'showUser' => false,
    'users' => [],
])

<form method="GET" @if($route) action="{{ $route }}" @endif class="flex flex-wrap items-end gap-3 mb-6">
    @if($showSearch)
    <div>
        <label class="block text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1">Search</label>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ $searchPlaceholder }}"
            class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none w-48">
    </div>
    @endif

    @if($showDateRange)
    <div>
        <label class="block text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1">From</label>
        <input type="date" name="date_from" value="{{ request('date_from') }}"
            class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
    </div>
    <div>
        <label class="block text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1">To</label>
        <input type="date" name="date_to" value="{{ request('date_to') }}"
            class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
    </div>
    @endif

    @if($showStatus && !empty($statusOptions))
    <div>
        <label class="block text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1">Status</label>
        <select name="status"
            class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All</option>
            @foreach($statusOptions as $value => $label)
                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
            @endforeach
        </select>
    </div>
    @endif

    @if($showUser && !empty($users))
    <div>
        <label class="block text-[11px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400 mb-1">User</label>
        <select name="user_id"
            class="px-3 py-2.5 border border-gray-300 dark:border-gray-600 rounded-xl text-sm bg-white dark:bg-black text-gray-900 dark:text-white input-focus outline-none">
            <option value="">All users</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>
    @endif

    <div class="flex items-center gap-2">
        <x-button type="submit" variant="primary" size="sm">Filter</x-button>
        @if(request()->anyFilled(['search', 'date_from', 'date_to', 'status', 'user_id']))
            <x-button href="{{ $route ?? request()->url() }}" variant="outline" size="sm">Clear</x-button>
        @endif
    </div>
</form>

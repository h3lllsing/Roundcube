@props(['type', 'id'])

@if(session('monitor_result') && session('monitor_type') === $type && session('monitor_id') === (int) $id)
    @php
        $result = session('monitor_result');
        $ping = $result['ping'] ?? [];
        $ssl = $result['ssl'] ?? [];
    @endphp
    <div class="mt-4 p-4 rounded-xl border {{ $ping['success'] ?? false ? 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800' }}">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-semibold {{ $ping['success'] ?? false ? 'text-green-700 dark:text-green-300' : 'text-red-700 dark:text-red-300' }}">
                {{ $ping['success'] ?? false ? 'Online' : 'Offline' }}
            </span>
            <span class="text-xs text-gray-500">{{ $result['checked_at'] ?? '' }}</span>
        </div>
        <div class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
            @if(isset($ping['status_code']))
                <p>HTTP {{ $ping['status_code'] }} — {{ $ping['response_time_ms'] }}ms</p>
            @endif
            @if(isset($ping['error']))
                <p class="text-red-600 dark:text-red-400">{{ $ping['error'] }}</p>
            @endif
            @if(isset($ssl['success']))
                <p class="{{ $ssl['valid'] ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                    SSL: {{ $ssl['valid'] ? 'Valid' : 'Invalid' }}
                    @if(isset($ssl['days_remaining']))
                        ({{ $ssl['days_remaining'] }} days)
                    @endif
                    @if(isset($ssl['issuer']))
                        — {{ $ssl['issuer'] }}
                    @endif
                </p>
                @if(isset($ssl['error']))
                    <p class="text-red-600 dark:text-red-400">SSL Error: {{ $ssl['error'] }}</p>
                @endif
            @endif
        </div>
    </div>
@endif

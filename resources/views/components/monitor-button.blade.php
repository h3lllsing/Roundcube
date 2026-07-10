@props(['type', 'id'])

<form action="{{ route('monitor.check', [$type, $id]) }}" method="GET" class="inline">
    <x-button type="submit" variant="success" size="sm">Check Now</x-button>
</form>

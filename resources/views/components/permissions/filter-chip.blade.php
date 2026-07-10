@props(['filter' => 'all', 'label' => '', 'count' => 0])

<span
    class="chip {{ $filter === 'all' ? 'on' : '' }}"
    role="tab"
    aria-selected="{{ $filter === 'all' ? 'true' : 'false' }}"
    tabindex="0"
    @click="$dispatch('filter-change', '{{ $filter }}')"
    @keydown.enter.prevent="$dispatch('filter-change', '{{ $filter }}')"
>
    {{ $label }}
    <span style="font-weight:400;opacity:.7" x-text="filterCounts.{{ $filter }}">0</span>
</span>

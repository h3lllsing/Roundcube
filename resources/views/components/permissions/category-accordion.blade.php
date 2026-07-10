@props(['name' => '', 'moduleCount' => 0, 'modifiedCount' => 0])

<div class="cat" data-category="{{ Str::slug($name) }}">
    <div
        class="cat-hd"
        role="button"
        tabindex="0"
        aria-expanded="true"
        :aria-expanded="expandedCategories.{{ $name }}"
        @click="toggleCategory('{{ $name }}')"
        @keydown.enter.prevent="toggleCategory('{{ $name }}')"
    >
        <div class="l">
            <span class="arr o" :class="{ o: expandedCategories.{{ $name }} }">▶</span>
            <h3>{{ $name }}</h3>
            <span class="ct" x-text="getCategoryCount('{{ $name }}')">({{ $moduleCount }} modules)</span>
        </div>
        <div class="r">
            <button
                class="bk-btn"
                @click.stop="$dispatch('open-bulk-apply', { category: '{{ $name }}' })"
                aria-label="Bulk apply to {{ $name }}"
            >⚡ Bulk Apply ▾</button>
        </div>
    </div>
    <div class="cat-bd" x-show="expandedCategories.{{ $name }}">
        <table class="mt" role="grid" aria-label="Modules in {{ $name }}">
            <thead>
                <tr>
                    <th style="width:34%">Module</th>
                    <th style="width:34%">Access Level</th>
                    <th style="width:32%">Status</th>
                </tr>
            </thead>
            <tbody>
                {{ $slot }}
            </tbody>
        </table>
    </div>
</div>

@props(['module', 'baseline', 'overrides'])

@php
$hasOverride = collect($overrides)->filter(fn($v) => $v !== null)->isNotEmpty();
$presetNames = ['no_access' => 'No Access', 'view_only' => 'View Only', 'manage' => 'Manage', 'custom' => 'Custom…'];
$currentPreset = $overrides['_preset'] ?? $baseline;
$isCustom = $currentPreset === 'custom';
$colClasses = $hasOverride ? 'overridden' : 'inherited';
$isSensitive = $module->is_sensitive ?? false;
@endphp

<tr
    class="{{ $colClasses }}"
    data-module="{{ $module->name }}"
    data-sensitive="{{ $isSensitive ? 'true' : 'false' }}"
    data-module-id="{{ $module->id }}"
    x-data
    @click="if(!$event.target.closest('select,button,a,.sen-tag')){ $dispatch('open-editor', {id: {{ $module->id }} })}"
    role="button"
    tabindex="0"
    :aria-label="'Edit permissions for {{ $module->name }}'"
    @keydown.enter.prevent="$dispatch('open-editor', {id: {{ $module->id }} })"
>
    <td>
        <span class="mn">
            {{ $module->name }}
            @if($isSensitive)
                <span class="sen-tag" data-tip="{{ $module->sensitive_tip ?? 'Contains sensitive permissions' }}" role="button" tabindex="0" aria-label="Sensitive module">sensitive</span>
            @endif
        </span>
    </td>
    <td>
        <select
            class="al-select {{ $isCustom ? 'custom' : '' }}"
            aria-label="Access level for {{ $module->name }}"
            x-model.number="modules[{{ $module->id }}].preset"
            @change="$dispatch('preset-change', {id: {{ $module->id }}})"
        >
            <option value="0">No Access</option>
            <option value="1">View Only</option>
            <option value="2">Manage</option>
            <option value="3">Custom…</option>
        </select>
    </td>
    <td>
        <span class="sb {{ $hasOverride ? 'sb-m' : 'sb-i' }}" x-text="modules[{{ $module->id }}]?.status || 'Inherited'">Inherited</span>
    </td>
</tr>

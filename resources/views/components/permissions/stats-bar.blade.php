@props(['stats' => ['modified' => 0, 'sensitive' => 0, 'inherited' => 0]])

<div class="stats" role="status" aria-live="polite" aria-label="Permission statistics">
    <span class="stat">
        <span class="dot dot-m" aria-hidden="true"></span>
        <strong x-text="stats.modified">0</strong> Modified
    </span>
    <span class="stat">
        <span class="dot dot-s" aria-hidden="true"></span>
        <strong x-text="stats.sensitive">0</strong> Sensitive
    </span>
    <span class="stat">
        <span class="dot dot-i" aria-hidden="true"></span>
        <strong x-text="stats.inherited">0</strong> Inherited
    </span>
    <span
        class="inheritance-note"
        data-tip="Inherited permissions automatically follow role updates. User overrides always take precedence. Overrides are preserved when the role changes (unless explicitly reset)."
        tabindex="0"
        role="tooltip"
        aria-label="Inheritance help"
    >ⓘ <span class="hl">Inheritance:</span> role updates apply → overrides win</span>
    <button class="reset-all-btn" @click="$dispatch('open-modal', 'reset-all-modal')" aria-label="Reset all module overrides">↺ Reset All Overrides</button>
</div>

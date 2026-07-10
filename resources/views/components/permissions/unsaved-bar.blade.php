<div
    class="unsaved-bar"
    id="unsaved-bar"
    x-show="hasUnsavedChanges"
    x-cloak
    role="alert"
    aria-live="polite"
>
    <span class="dot-pulse" aria-hidden="true"></span>
    <span>
        You have unsaved changes.
        <a
            href="#"
            class="text-amber-800 dark:text-amber-400 font-semibold"
            @click.prevent="$dispatch('open-modal', 'diff-panel')"
        >Review changes</a>
        or save before leaving.
    </span>
</div>

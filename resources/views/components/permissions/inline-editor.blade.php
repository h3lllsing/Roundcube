<div
    class="ie h"
    id="editor"
    x-show="openEditor !== null"
    x-cloak
    x-transition
    role="region"
    aria-label="Custom permissions editor"
>
    <div class="ie-hd">
        <h3>Custom Permissions — <span id="editor-module" x-text="openEditor?.name || ''"></span></h3>
        <button class="ie-close" @click="closeEditor()" aria-label="Close editor">✕ Close</button>
    </div>
    <div class="ie-bd">
        <div class="ie-s">
            <div class="t">Standard Permissions <span class="font-normal text-slate-400 dark:text-slate-500">(low business impact)</span></div>
            <div class="ie-tgl">
                <template x-for="perm in ['view', 'create', 'edit']" :key="perm">
                    <label>
                        <input type="checkbox" x-model="editorPerms[perm]" @change="markUnsaved()">
                        <span x-text="perm.charAt(0).toUpperCase() + perm.slice(1)"></span>
                        <span class="hc" :data-tip="'Grant ' + perm + ' permission'" tabindex="0" role="button">ⓘ</span>
                    </label>
                </template>
            </div>
        </div>
        <hr class="ie-hr">
        <div class="ie-s">
            <div class="t text-amber-800 dark:text-amber-400">⚠ Sensitive Permissions <span class="font-normal text-amber-700 dark:text-amber-500">(requires confirmation)</span></div>
            <div class="ie-tgl">
                <template x-for="perm in ['delete', 'reveal', 'approve', 'export', 'import']" :key="perm">
                    <label>
                        <input type="checkbox" x-model="editorPerms[perm]" @change="markUnsaved()">
                        <span x-text="perm.charAt(0).toUpperCase() + perm.slice(1)"></span>
                        <span class="hc" :data-tip="'Grant ' + perm + ' permission'" tabindex="0" role="button">ⓘ</span>
                    </label>
                </template>
            </div>
        </div>
        <div class="ie-warn">⚠ Enabling sensitive permissions requires confirmation on save.</div>
        <button class="ie-rs" @click="$dispatch('open-modal', 'reset-editor-modal')">↺ Reset to role default</button>
    </div>
    <div class="ie-fa">
        <button class="btn btn-s" @click="closeEditor()">Cancel</button>
        <button class="btn btn-p" @click="applyEditor()">Apply</button>
    </div>
</div>

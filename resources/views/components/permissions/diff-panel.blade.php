<div class="dp h" id="diff-panel" x-show="openModal === 'diff-panel'" x-cloak>
    <div class="dp-hd">
        <h3>Review Changes</h3>
        <span class="text-xs text-slate-500 dark:text-slate-400" x-text="changes.length + ' modules will be modified'"></span>
    </div>
    <table class="dt" role="grid" aria-label="Permission changes">
        <thead>
            <tr>
                <th>Module</th>
                <th>From (Baseline)</th>
                <th></th>
                <th>To (New)</th>
                <th>Sensitivity</th>
            </tr>
        </thead>
        <tbody>
            <template x-for="change in changes" :key="change.id">
                <tr>
                    <td><strong x-text="change.name"></strong></td>
                    <td class="fr" x-text="change.fromLabel"></td>
                    <td class="ar">→</td>
                    <td class="to" x-text="change.toLabel"></td>
                    <td>
                        <span x-show="change.isSensitive" class="text-amber-600 dark:text-amber-400">⚠ <span x-text="change.sensitivePerms.join(', ')"></span></span>
                        <span x-show="!change.isSensitive">—</span>
                    </td>
                </tr>
            </template>
        </tbody>
    </table>
    <div class="dt-f">
        <button class="btn btn-s" @click="closeModal('diff-panel')">← Go back</button>
        <button class="btn btn-p" @click="showModal('sen-modal')">Confirm &amp; Save</button>
    </div>
</div>

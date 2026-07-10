<div class="warn-banner" id="role-warn-edit" x-show="showRoleWarning" x-cloak>
    <strong>⚠ Role changed</strong>
    <span class="text-slate-500 dark:text-slate-400 mx-1">from</span>
    <strong x-text="previousRoleName"></strong>
    <span class="text-slate-500 dark:text-slate-400 mx-1">to</span>
    <strong x-text="selectedRoleName"></strong>
    <span style="margin-left:auto;font-size:12px">
        <a href="#" class="text-blue-500 hover:text-blue-700 dark:text-blue-400" @click.prevent="showModal('role-modal')">Manage overrides</a>
    </span>
</div>

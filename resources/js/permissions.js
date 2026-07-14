const presetLabels = { 0: 'No Access', 1: 'View Only', 2: 'Manage', 3: 'Custom…' };
const presetClasses = { 0: 'na', 1: 'vo', 2: 'mg', 3: 'cu' };
const defaultToggleToColumn = {
    view: 'can_read', create: 'can_create', edit: 'can_update',
    delete: 'can_delete', approve: 'can_approve',
    export: 'can_export', reveal: 'can_reveal', import: 'can_import',
};

export default function (initData = {}) {
    return {
        modules: {},
        categories: [],
        searchQuery: '',
        activeFilter: 'all',
        openEditor: null,
        editorPerms: {},
        openModal: null,
        hasUnsavedChanges: false,
        summaryOpen: false,
        expandedCategories: {},
        showRoleWarning: false,
        bulkCategory: '',
        bulkTargetPreset: 1,
        saveUrl: '',
        backUrl: '',
        userName: '',
        userRole: '',
        userId: null,
        sensitivePermNames: ['can_delete', 'can_reveal', 'can_approve', 'can_import'],
        isSimple: true,
        filterOverrideOnly: false,

        init() {
            this.toggleToColumn = initData.toggleToColumn || defaultToggleToColumn;
            this.columnToToggle = Object.fromEntries(Object.entries(this.toggleToColumn).map(([k, v]) => [v, k]));
            this.loadFromServer(initData);
            this.setupBeforeUnload();
            const stored = localStorage.getItem('perm_mode');
            if (stored === 'advanced') this.isSimple = false;
        },

        loadFromServer(data) {
            this.modules = data.modules || {};
            this.categories = data.categories || [];
            this.saveUrl = data.saveUrl || '';
            this.backUrl = data.backUrl || '';
            this.userName = data.userName || '';
            this.userRole = data.userRole || '';
            this.userId = data.userId || null;
            if (data.sensitivePermNames) {
                this.sensitivePermNames = data.sensitivePermNames;
            }
            if (data.toggleToColumn) {
                this.toggleToColumn = data.toggleToColumn;
                this.columnToToggle = Object.fromEntries(Object.entries(this.toggleToColumn).map(([k, v]) => [v, k]));
            }
            this.categories.forEach(cat => { this.expandedCategories[cat] = true; });
            this.setupEventListeners();
        },

        setupEventListeners() {
            this.$el.addEventListener('open-editor', (e) => {
                const mod = this.modules[e.detail.id];
                if (mod) this.openEditor = mod;
            });
            this.$el.addEventListener('preset-change', (e) => {
                this.markUnsaved();
            });
            this.$el.addEventListener('filter-change', (e) => {
                this.activeFilter = e.detail;
                document.querySelectorAll('.chip').forEach(ch => {
                    ch.setAttribute('aria-selected', ch.__x && ch.__x.$el && ch.__x.$el.textContent && ch.__x.$el.textContent.includes(this.activeFilter) ? 'true' : 'false');
                });
            });
            this.$el.addEventListener('open-modal', (e) => {
                this.openModal = e.detail;
            });
            this.$el.addEventListener('open-bulk-apply', (e) => {
                this.bulkCategory = e.detail.category;
                this.showModal('bulk-modal');
            });
        },

        showModal(id) {
            this.openModal = id;
            document.body.style.overflow = 'hidden';
            this.$nextTick(() => {
                const modal = document.getElementById(id);
                if (modal) {
                    const firstBtn = modal.querySelector('button');
                    if (firstBtn) firstBtn.focus();
                }
            });
        },
        closeModal(id) {
            this.openModal = null;
            document.body.style.overflow = '';
        },

        toggleCategory(name) {
            this.expandedCategories[name] = !this.expandedCategories[name];
        },

        toggleSummary() {
            this.summaryOpen = !this.summaryOpen;
        },

        toggleSimpleMode(enterSimple) {
            this.isSimple = enterSimple;
            localStorage.setItem('perm_mode', enterSimple ? 'simple' : 'advanced');
            this.searchQuery = '';
            this.activeFilter = 'all';
            this.filterOverrideOnly = false;
        },

        closeEditor() {
            this.openEditor = null;
            this.editorPerms = {};
        },
        applyEditor() {
            if (this.openEditor) {
                Object.assign(this.openEditor.toggles, this.editorPerms);
                this.recomputePreset(this.openEditor);
                this.markUnsaved();
            }
            this.closeEditor();
        },

        recomputePreset(mod) {
            const t = mod.toggles;
            if (!t.view && !t.create && !t.edit && !t.delete && !t.approve && !t.export && !t.reveal && !t.import) { mod.preset = 0; }
            else if (t.view && !t.create && !t.edit && !t.delete && !t.approve && !t.export && !t.reveal && !t.import) { mod.preset = 1; }
            else if (t.view && t.create && t.edit && !t.delete && !t.approve && !t.reveal && !t.import) { mod.preset = 2; }
            else { mod.preset = 3; }
        },

        resetModuleToBaseline(modId) {
            const mod = this.modules[modId];
            if (!mod) return;
            mod.preset = mod.baseline;
            this.markUnsaved();
            this.closeEditor();
        },

        markUnsaved() {
            this.hasUnsavedChanges = true;
        },
        clearUnsaved() {
            this.hasUnsavedChanges = false;
        },
        setupBeforeUnload() {
            window.addEventListener('beforeunload', (e) => {
                if (this.hasUnsavedChanges) {
                    e.preventDefault();
                    e.returnValue = '';
                }
            });
        },
        discardAndLeave() {
            this.clearUnsaved();
            this.closeModal('nav-modal');
            window.location.href = document.referrer || '/';
        },
        goBack() {
            window.location.href = this.backUrl;
        },

        bulkApply() {
            Object.values(this.modules)
                .filter(m => m.category === this.bulkCategory)
                .forEach(mod => {
                    mod.preset = this.bulkTargetPreset;
                    const t = mod.toggles;
                    if (this.bulkTargetPreset === 0) {
                        t.view = t.create = t.edit = t.delete = t.approve = t.export = t.reveal = t.import = false;
                    } else if (this.bulkTargetPreset === 1) {
                        t.view = true; t.create = t.edit = t.delete = t.approve = t.export = t.reveal = t.import = false;
                    } else if (this.bulkTargetPreset === 2) {
                        t.view = t.create = t.edit = true; t.delete = t.approve = t.export = t.reveal = t.import = false;
                    }
                });
            this.closeModal('bulk-modal');
            this.markUnsaved();
        },

        save() {
            try {
            const permissions = {};
            Object.values(this.modules).forEach(mod => {
                let targetToggles;
                if (mod.preset === 0) {
                    targetToggles = { view: false, create: false, edit: false, delete: false, approve: false, export: false, reveal: false, import: false };
                } else if (mod.preset === 1) {
                    targetToggles = { view: true, create: false, edit: false, delete: false, approve: false, export: false, reveal: false, import: false };
                } else if (mod.preset === 2) {
                    targetToggles = { view: true, create: true, edit: true, delete: false, approve: false, export: false, reveal: false, import: false };
                } else {
                    targetToggles = { ...mod.toggles };
                }

                const colPerms = {};
                Object.entries(targetToggles).forEach(([key, val]) => {
                    colPerms[this.toggleToColumn[key]] = val;
                });

                if (mod.preset !== mod.baseline || mod.preset === 3) {
                    permissions[mod.id] = colPerms;
                }
            });

            const hasSensitive = this.sensitiveChanges.length > 0;
            const doSave = () => {
                this.clearUnsaved();
                fetch(this.saveUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ permissions }),
                })
                .then(res => {
                    if (res.redirected) { window.location.href = res.url; return; }
                    if (!res.ok) throw new Error('Save failed');
                    window.location.href = this.backUrl;
                })
                .catch(err => {
                    alert('Failed to save permissions: ' + err.message);
                });
            };

            if (hasSensitive) {
                this.showModal('sen-modal');
                const confirmBtn = document.querySelector('#sen-modal .btn-d');
                if (confirmBtn) {
                    confirmBtn.onclick = () => {
                        this.closeModal('sen-modal');
                        doSave();
                    };
                }
            } else {
                doSave();
            }
            } catch (e) {
                alert('Save error: ' + (e && e.message ? e.message : e));
            }
        },

        get modList() {
            return Object.values(this.modules);
        },

        get filteredModules() {
            const q = this.searchQuery.toLowerCase().trim();
            return this.modList.filter(mod => {
                const matchSearch = !q || mod.name.toLowerCase().includes(q);
                let matchFilter = true;
                if (this.activeFilter === 'modified') {
                    matchFilter = mod.preset !== mod.baseline;
                } else if (this.activeFilter === 'sensitive') {
                    matchFilter = mod.isSensitive;
                } else if (this.activeFilter === 'inherited') {
                    matchFilter = mod.preset === mod.baseline;
                } else if (['0', '1', '2', '3'].includes(this.activeFilter)) {
                    matchFilter = mod.preset === parseInt(this.activeFilter);
                }
                return matchSearch && matchFilter;
            });
        },

        get categorizedModules() {
            const result = {};
            this.categories.forEach(cat => {
                result[cat] = this.filteredModules.filter(m => m.category === cat);
            });
            return result;
        },

        get stats() {
            const mods = this.modList;
            const total = mods.length;
            const modified = mods.filter(m => m.preset !== m.baseline).length;
            const sensitive = mods.filter(m => {
                if (m.preset !== m.baseline && m.isSensitive) return true;
                if (m.preset === 3) {
                    const hasSensitive = Object.entries(m.toggles).some(([k, v]) =>
                        v && ['delete', 'reveal', 'approve', 'import'].includes(k)
                    );
                    return hasSensitive;
                }
                return false;
            }).length;
            const inherited = total - modified;
            return { modified, sensitive, inherited };
        },

        get filterCounts() {
            const mods = this.modList;
            const total = mods.length;
            const modified = mods.filter(m => m.preset !== m.baseline).length;
            const sensitive = mods.filter(m => m.isSensitive).length;
            const inherited = mods.filter(m => m.preset === m.baseline).length;
            const presetCounts = {};
            [0, 1, 2, 3].forEach(p => {
                presetCounts[p] = mods.filter(m => m.preset === p).length;
            });
            return {
                all: total,
                modified,
                sensitive,
                inherited,
                ViewOnly: presetCounts[1],
                Manage: presetCounts[2],
                Custom: presetCounts[3],
            };
        },

        get changes() {
            return this.modList
                .filter(m => m.preset !== m.baseline)
                .map(m => {
                    const isSensitive = m.isSensitive || (m.preset === 3 && this.moduleHasSensitiveToggles(m));
                    const sensitivePerms = isSensitive ? ['delete', 'reveal', 'approve', 'import'].filter(p => m.toggles[p]) : [];
                    return {
                        id: m.id,
                        name: m.name,
                        fromLabel: presetLabels[m.baseline] || 'No Access',
                        toLabel: m.preset === 3 ? 'Custom' : (presetLabels[m.preset] || 'No Access'),
                        isSensitive,
                        sensitivePerms,
                    };
                });
        },

        get summaryText() {
            return '— ' + this.categories.map(cat => {
                const count = this.modList.filter(m => m.category === cat && m.preset !== m.baseline).length;
                return cat + ' (' + count + ' modified)';
            }).join(' · ');
        },

        getCategoryCount(catName) {
            const mods = this.modList;
            const count = mods.filter(m => m.category === catName).length;
            const visible = this.filteredModules.filter(m => m.category === catName).length;
            const q = this.searchQuery.trim();
            if (q) {
                return `(${visible} of ${count} modules)`;
            }
            return `(${count} modules)`;
        },

        moduleHasSensitiveToggles(mod) {
            if (mod.preset !== 3) return false;
            return ['delete', 'reveal', 'approve', 'import'].some(p => mod.toggles[p]);
        },

        get overriddenModules() {
            return this.modList
                .filter(m => m.preset !== m.baseline)
                .map(m => ({
                    id: m.id,
                    name: m.name,
                    currentLabel: presetLabels[m.preset],
                    baselineLabel: presetLabels[m.baseline],
                }));
        },

        get bulkAffectedModules() {
            return this.modList.filter(m => m.category === this.bulkCategory)
                .map(m => ({
                    id: m.id,
                    name: m.name,
                    currentLabel: presetLabels[m.preset],
                    currentClass: presetClasses[m.preset],
                    targetLabel: presetLabels[this.bulkTargetPreset] || presetLabels[1],
                    targetClass: presetClasses[this.bulkTargetPreset] || 'vo',
                }));
        },

        get sensitiveChanges() {
            return this.changes.filter(c => c.isSensitive);
        },

        get overridesCount() {
            return this.modList.filter(m => m.preset !== m.baseline).length;
        },

        get simpleModuleList() {
            if (this.filterOverrideOnly) {
                return this.modList.filter(m => m.preset !== m.baseline);
            }
            const q = this.searchQuery.toLowerCase().trim();
            return this.modList.filter(mod => {
                return !q || mod.name.toLowerCase().includes(q);
            });
        },
    };
}

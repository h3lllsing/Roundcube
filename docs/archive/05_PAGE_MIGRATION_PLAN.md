# 5. Page Migration Plan

> Safe, incremental migration from current Blade markup to Design System components.

---

## Guiding Principles

| Principle | Rule |
|---|---|
| **One module at a time** | Never migrate multiple modules simultaneously |
| **No backend changes** | Controllers, routes, validation, permissions untouched |
| **No visual regression** | Dark mode, responsive, all states must match or exceed current |
| **PHPUnit must pass** | Run full suite after each migrated module |
| **Playwright must pass** | Run E2E tests after each migration phase |
| **Rollback via git** | Each migration is a single commit. Rollback = revert commit. |
| **Incremental shipping** | Each migrated module can be deployed independently |

---

## Phase 1: Design System Foundation (2-3 weeks)

### Goal
Create and deploy the core Design System components WITHOUT migrating any pages.

### Deliverables
- Design tokens integrated into `app.css` `@theme`
- All Phase 1 components created: `x-card`, `x-badge`, `x-field`, `x-date`, `x-table`, `x-empty-state`, `x-modal`, `x-confirm-dialog`, `x-form.input/select/textarea/checkbox`, `x-form.password`, `x-alert`, `x-toast`, `x-stat-card`, `x-password-reveal`, `x-page-header`
- Component documentation written

### Files Created
```
resources/views/components/
  card.blade.php
  badge.blade.php
  field.blade.php
  date.blade.php
  table.blade.php
  empty-state.blade.php      (overwrite existing)
  modal.blade.php
  confirm-dialog.blade.php
  alert.blade.php
  page-header.blade.php      (overwrite existing)
  form/
    input.blade.php           (overwrite existing)
    select.blade.php          (overwrite existing)
    textarea.blade.php        (overwrite existing)
    checkbox.blade.php        (overwrite existing)
    password.blade.php        (NEW)
```

### Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| New component breaks existing page | Low | Medium | Test each component in isolation before page migration |
| Component API incompatible with existing usage | Medium | High | Review all existing usage sites before finalizing props |
| Dark mode not handled in new component | Low | Medium | Dark mode is already in Tailwind config. Test in both modes. |
| Overwriting existing components breaks JS behavior | Low | High | Check for any Alpine/JS that depends on current component HTML structure |
| Pulse animation for loading not present | Medium | Low | Add `.skeleton-pulse` animation to app.css |

### Tests to Run
```
phpunit                                   # 1951 tests, 0 failures expected
npx playwright test                       # All E2E tests
```

### Rollback
```
git checkout -- resources/views/components/
git checkout -- resources/css/app.css
```

### Visual QA Checklist
- [ ] `<x-card>` renders correctly with all padding variants
- [ ] `<x-card>` glass variant looks correct
- [ ] `<x-card>` dark mode correct
- [ ] `<x-badge>` shows correct color for: active, expired, suspended, cancelled
- [ ] `<x-badge>` dark mode correct
- [ ] `<x-field>` renders label+value correctly
- [ ] `<x-field>` shows '—' for null values
- [ ] `<x-date>` formats dates correctly
- [ ] `<x-date>` shows '—' for null dates
- [ ] `<x-table>` renders columns correctly
- [ ] `<x-table>` empty state works
- [ ] `<x-modal>` opens/closes with keyboard trap
- [ ] `<x-confirm-dialog>` fires confirm/cancel events
- [ ] `<x-form.password>` mask/reveal works
- [ ] `<x-alert>` success/error/warning/info all render correctly
- [ ] All components pass dark mode check

---

## Phase 2: Core CRUD Modules (3-4 weeks)

### Goal
Migrate all core CRUD module pages to use Design System components.

### Migration Order (by complexity)

| Order | Module | Files | Risk | Effort |
|---|---|---|---|---|
| 1 | Domains | index, show, create, edit | Low | 2 days |
| 2 | Hosting | index, show, create, edit | Low | 2 days |
| 3 | VPS | index, show, create, edit | Low | 1 day |
| 4 | VoIP | index, show, create, edit | Low | 1 day |
| 5 | Domain Emails | index, show, create, edit | Low | 1 day |
| 6 | Other Services | index, show, create, edit | Low | 1 day |
| 7 | Service Providers | index, show, create, edit | Low | 1 day |

### Migration Pattern (per module)

1. `index.blade.php`:
   - Replace `<div class="bg-white dark:bg-black ... overflow-x-auto">` with `<x-card padding="none">`
   - Replace `<thead>` + `<tbody>` with `<x-table :columns="..." :rows="$records">`
   - Replace status badges with `<x-badge :value="$record->status">`
   - Replace date/cost formatting with `<x-date>` / `<x-money>`
   - Add `<x-empty-state>` in `@empty` block (remove if table handles it)
   - Add `<x-pagination>` below table

2. `show.blade.php`:
   - Replace container div with `<x-card>`
   - Replace all label-value pairs with `<x-field>`
   - Replace all date displays with `<x-date>`
   - Replace all cost displays with `<x-money>`
   - Keep activity timeline, monitoring, notes, attachments at bottom

3. `create.blade.php` / `edit.blade.php`:
   - Replace container div with `<x-card>`
   - Replace `<x-form.input>` calls (already exist, may need prop updates)
   - Add `<x-form.password>` where password fields exist
   - Keep form sections, validation pattern

### Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| `<x-table>` column config wrong | Medium | Medium | Test column prop shape in Phase 1 with real data |
| Date formatting changes subtly | Low | Medium | Check existing `->format('Y-m-d')` matches `<x-date>` default |
| Bulk checkbox behavior breaks | Low | High | Must test bulk select + form submission after migration |
| Action buttons positioning changes | Medium | Low | Visual review per page |
| Responsive layout breaks on mobile | Low | Medium | Test on mobile viewport |

### Tests to Run (per module)
```
phpunit --filter=DomainTest          # Domain-specific tests
phpunit --filter=HostingTest         # Hosting-specific tests
npx playwright test domains          # E2E tests for domains
npx playwright test hosting          # E2E tests for hosting
```

### Rollback (per module)
```
git checkout -- resources/views/{module}/
```

---

## Phase 3: Renewal Center & Permissions (2-3 weeks)

### Goal
Migrate the two most complex feature areas.

### Renewal Center

| Page | Current Issues | DS Migration |
|---|---|---|
| Index | Manual table, duplicated badges, source type labels | `<x-table>` + `<x-badge>` + `<x-renewal-badge>` |
| Show | Linked source card, sync fields, complex route building | `<x-renewal-source-card>` + `<x-field>` |
| Create/Edit | Linked vs standalone form logic | `<x-form.*>` components |

**Special considerations:**
- The `$sourceTypeLabels` array (duplicated across 2+ views) must be moved to a shared location (model accessor or service).
- The route-building logic in show.blade.php (lines 44-48) must be encapsulated in `<x-renewal-source-card>`.
- The `$isLinked` boolean is critical — must preserve detection logic.

### Permissions

| Page | Current Issues | DS Migration |
|---|---|---|
| Module permissions | 186 lines of prototype CSS | Convert to Tailwind classes within `<x-card>` + `<x-table>` |
| 11 Alpine components | Hardcoded prototype class names | Replace `.card`, `.ch`, `.cb`, `.btn`, `.mt`, etc. with Tailwind |
| Inline editor | `.ie`, `.ie-hd`, `.ie-bd` classes | Replace with `<x-card>` + Tailwind |
| Diff panel | `.dp`, `.dp-hd`, `.dt` classes | Replace with `<x-card>` |
| Modals | `.mo`, `.mo-in`, `.mo-hd` classes | Replace with `<x-modal>` |

**Special considerations:**
- The Alpine data (`editPerms`) and JS (353 lines) must continue working.
- This is a CSS-only migration of the permission page. Do NOT touch the JS.
- The prototype CSS must be deleted from `app.css` AFTER confirmation that no elements reference those classes.

### Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| CSS class removal breaks Alpine components | Medium | High | Test ALL permission interactions after migration |
| Renewal source route building incorrect | Medium | High | Test all 7 trackable types (domain, hosting, vps, voip, domain-email, other-service, service-provider) |
| Linked/standalone detection wrong | Low | High | Test both tracker types with real data |
| Prototype CSS deletion breaks hidden UI | Medium | Medium | Use grep to verify no remaining references |

### Tests to Run
```
phpunit --filter=ExpiryTrackerTest
phpunit --filter=PermissionTest
# Manual test: Create linked renewal, edit, complete, delete
# Manual test: All permission preset changes, save, verify
```

---

## Phase 4: Remaining Modules (2-3 weeks)

### Goal
Migrate secondary modules.

| Module | Files | Effort |
|---|---|---|
| Vault | index, show, create, edit | 1 day |
| Assets | index, show, create, edit | 1 day |
| Tasks | index, show, create, edit + kanban | 2 days |
| Notes | index, create | 0.5 day |
| Attachments | index, create | 0.5 day |
| Activity Logs | index, show | 0.5 day |
| Login Audits | index | 0.5 day |
| Webhooks | index, show, create, edit | 1 day |
| Users | index, show, create, edit | 1 day |
| Roles | index, show, create, edit | 1 day |
| Features | index | 0.5 day |
| Modules | index | 0.5 day |

**Vault special considerations:**
- Password reveal is the primary interaction — ensure `<x-password-reveal>` works correctly.
- The `VaultService` encrypt/decrypt flow must remain untouched.

**Tasks special considerations:**
- Kanban board view is standalone. Only the task index/show need DS migration.
- The kanban board uses Alpine.js for drag-and-drop — leave intact.

**Activity/Login Audits:**
- Read-only pages. Simple table view.

---

## Phase 5: Final Polish (2-3 weeks)

### Goal
Migrate complex structural components and auth pages.

### Dashboard

| Task | Effort | Risk |
|---|---|---|
| Replace `@include` widgets with `<x-widget-card>` | 2 days | Low |
| Replace stat cards with `<x-stat-card>` | 0.5 day | Low |
| Add `<x-chart-card>` for chart canvases | 1 day | Low |
| Add `<x-renewal-widget>` for upcoming renewals | 0.5 day | Low |
| Add `<x-activity-widget>` for recent activity | 0.5 day | Low |

### Admin Layout (App Shell)

| Task | Effort | Risk |
|---|---|---|
| Create `<x-app-shell>` component | 3 days | High |
| Move inline JS to modules | 3 days | Medium |
| Replace toast system with `<x-toast>` | 1 day | Low |
| Replace confirm dialog with `<x-confirm-dialog>` | 1 day | Low |
| Replace command palette with `<x-command-palette>` | 2 days | Low |
| Replace notification bell with `<x-notification-dropdown>` | 1 day | Low |

**Risk:** The admin layout is ~783 lines with ~300 lines of inline JS. Replacing it is the highest-risk migration.

**Mitigation:** Keep the old layout as a fallback. Deploy `<x-app-shell>` alongside it. Migrate page by page.

### Auth Pages

| Page | Effort | Risk |
|---|---|---|
| Login | 2 days | Medium |
| Register | 1 day | Low |
| Forgot Password | 0.5 day | Low |
| Reset Password | 1 day | Low |
| Profile | 0.5 day | Low |

**Note:** Auth pages are standalone HTML files. They don't use the admin layout, so they need their own shell component or can use the same `<x-app-shell>` with `auth` variant (no sidebar).

---

## Total Migration Effort

| Phase | Duration | Files Affected | Risk |
|---|---|---|---|
| Phase 1: DS Foundation | 2-3 weeks | ~18 files created | Very Low |
| Phase 2: Core CRUD | 3-4 weeks | ~28 files modified | Low |
| Phase 3: Renewal/Perms | 2-3 weeks | ~15 files modified | Medium |
| Phase 4: Remaining | 2-3 weeks | ~24 files modified | Low |
| Phase 5: Polish | 2-3 weeks | ~15 files modified + ~5 JS modules | Medium |

**Total: 11-16 weeks for one full-time developer.**

This is the most conservative estimate. Actual time may be less because:
- Many migrations are simple find-and-replace patterns
- Phase 4 modules are nearly identical to Phase 2 modules
- The Design System reduces migration time for each subsequent module

---

## End State

After all 5 phases:

| Metric | Before | After |
|---|---|---|
| Duplicated status badge code | ~30 locations | 0 (one `<x-badge>` component) |
| Manual table implementations | ~16 | 0 (one `<x-table>` component) |
| Manual field/date markup | ~240+ locations | 0 (one `<x-field>` + `<x-date>`) |
| Prototype CSS classes | ~70 custom classes | 0 (all Tailwind) |
| Dashboard widget code | `@include` with no contract | Proper `<x-widget-*>` components |
| Admin layout inline JS | ~300 lines inline | Modular JS files |
| Total Blade component count | 27 | ~50 (23 new) |
| UI consistency | Inconsistent | Uniform across all modules |
| Page CSS burden | Duplicated per page | Centralized in components |

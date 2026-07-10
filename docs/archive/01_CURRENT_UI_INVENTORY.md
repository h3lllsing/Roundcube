# 1. Current UI Inventory

> Complete audit of every existing UI element in the codebase.

---

## 1.1 Existing Blade Components

### Structural Components (12 files)

| # | Component | Path | Lines | Alpine | Usage Count | Duplication | DS? | Priority |
|---|---|---|---|---|---|---|---|---|
| 1 | action | `components/action.blade.php` | 45 | No | ~150+ (all index pages) | Low (only one impl) | No | Low |
| 2 | activity-timeline | `components/activity-timeline.blade.php` | 111 | No | ~16+ (every show page) | Low | Yes-refine | Medium |
| 3 | breadcrumbs | `components/breadcrumbs.blade.php` | 66 | No | 1 (admin layout) | None | Yes-refine | Low |
| 4 | bulk-actions | `components/bulk-actions.blade.php` | 25 | Yes | ~16+ (every index page) | Low | Yes-refine | Medium |
| 5 | button | `components/button.blade.php` | 30 | No | ~200+ | Low | Yes-refine | Medium |
| 6 | empty-state | `components/empty-state.blade.php` | 37 | No | ~16+ (index pages) | Low (single impl) | Yes-expand | High |
| 7 | monitor-button | `components/monitor-button.blade.php` | 5 | No | ~16+ | Low | Merge into action | Low |
| 8 | monitor-result | `components/monitor-result.blade.php` | 39 | No | ~16+ | Low | Keep | Low |
| 9 | nav-link | `components/nav-link.blade.php` | 11 | No | ~20 (sidebar) | None | Keep | Low |
| 10 | page-header | `components/page-header.blade.php` | 13 | No | ~35 (all pages) | None | Yes-refine | Medium |
| 11 | report-filter-bar | `components/report-filter-bar.blade.php` | 66 | No | ~3 (reports only) | Low | Merge into filter-bar | Low |
| 12 | stat-card | `components/stat-card.blade.php` | 48 | No | ~8 (dashboard) | Low | Yes-refine | Medium |

### Form Components (4 files)

| # | Component | Path | Lines | Alpine | Usage Count | Duplication | DS? | Priority |
|---|---|---|---|---|---|---|---|---|
| 13 | form.checkbox | `components/form/checkbox.blade.php` | 18 | No | ~30 | Low | Yes-refine | High |
| 14 | form.input | `components/form/input.blade.php` | 22 | No | ~200+ | Low | Yes-refine | High |
| 15 | form.select | `components/form/select.blade.php` | 25 | No | ~100+ | Low | Yes-refine | High |
| 16 | form.textarea | `components/form/textarea.blade.php` | 20 | No | ~30 | Low | Yes-refine | High |

### Permission Components (11 files)

| # | Component | Path | Lines | Alpine | Usage | DS? | Priority |
|---|---|---|---|---|---|---|---|
| 17 | category-accordion | `components/permissions/category-accordion.blade.php` | 40 | Yes | 1 page | Yes-refine | Medium |
| 18 | diff-panel | `components/permissions/diff-panel.blade.php` | 35 | Yes | 1 page | Yes-refine | Medium |
| 19 | filter-chip | `components/permissions/filter-chip.blade.php` | 13 | Yes | 1 page | Yes-refine | Low |
| 20 | inline-editor | `components/permissions/inline-editor.blade.php` | 47 | Yes | 1 page | Yes-refine | Medium |
| 21 | modal | `components/permissions/modal.blade.php` | 33 | Yes | 1 page | Generalize | High |
| 22 | module-row | `components/permissions/module-row.blade.php` | 48 | Yes | 1 page | Yes-refine | Medium |
| 23 | role-warning | `components/permissions/role-warning.blade.php` | 10 | Yes | 1 page | Keep | Low |
| 24 | sensitive-criteria | `components/permissions/sensitive-criteria.blade.php` | 10 | No | 1 page | Keep | Low |
| 25 | stats-bar | `components/permissions/stats-bar.blade.php` | 24 | Yes | 1 page | Yes-refine | Medium |
| 26 | summary-collapsible | `components/permissions/summary-collapsible.blade.php` | 27 | Yes | 1 page | Keep | Low |
| 27 | unsaved-bar | `components/permissions/unsaved-bar.blade.php` | 19 | Yes | 1 page | Generalize | Medium |

---

## 1.2 Repeated Table Patterns

Every module index page manually writes:

```
resources/views/domains/index.blade.php     (lines 46-105)
resources/views/hostings/index.blade.php    (similar)
resources/views/vps/index.blade.php         (similar)
resources/views/voip/index.blade.php        (similar)
resources/views/domain-emails/index.blade.php (similar)
resources/views/other-services/index.blade.php (similar)
resources/views/expiry-trackers/index.blade.php (lines 71-129)
resources/views/service-providers/index.blade.php (similar)
resources/views/vault/index.blade.php       (similar)
resources/views/assets/index.blade.php      (similar)
resources/views/tasks/index.blade.php       (similar)
resources/views/notes/index.blade.php       (similar)
resources/views/attachments/index.blade.php (similar)
resources/views/webhooks/index.blade.php    (similar)
resources/views/users/index.blade.php       (similar)
resources/views/roles/index.blade.php       (similar)
resources/views/activity-logs/index.blade.php (similar)
```

**Estimated: 16+ manual table implementations.**

Each one includes:
- Wrapper div with `bg-white dark:bg-black rounded-xl shadow-sm border overflow-x-auto`
- `<thead>` with hardcoded column headers and `bg-gray-50 dark:bg-black/50`
- `<tbody>` with `@forelse` + `<tr class="hover:bg-gray-50...">`
- `<x-empty-state>` for empty state
- `<x-action>` buttons for view/edit/delete
- Manual `<input type="checkbox">` for bulk select
- Status badge inline markup
- Date/cost inline formatting

**Duplication level: EXTREME (16+ implementations, 90% identical)**

**Priority: HIGHEST**

---

## 1.3 Repeated Badge Patterns

Status badges are duplicated across approximately 30 locations:

```
Active status (green):    domains, hostings, vps, voip, other-services,
                          domain-emails, expiry-trackers, service-providers, assets,
                          tasks, users

Expired status (red):     domains, hostings, vps, voip, expiry-trackers

Suspended status (yellow): domains, hostings

Cancelled status (gray):  expiry-trackers, other-services

Cloudflare status:        domains index + show

Renewal status:           expiry-trackers

Permission levels:        auth/my-permissions.blade.php
```

Each badge is 8-12 lines of Blade with `@class()` and inline Tailwind classes for each color variant.

**Duplication level: HIGH (~30 locations, identical logic)**

**Priority: HIGH**

---

## 1.4 Repeated Field/Date Patterns

Every show page duplicates:

```blade
<div>
    <p class="text-sm text-gray-500 dark:text-gray-400">Field Label</p>
    <p class="font-medium">{{ $entity->field ?? '—' }}</p>
</div>
```

And for dates:

```blade
<div>
    <p class="text-sm text-gray-500 dark:text-gray-400">Date Label</p>
    <p class="font-medium">{{ $entity->date_field?->format('Y-m-d') ?? '—' }}</p>
</div>
```

Locations: Every show page across all 16+ modules. ~15 fields per page.

**Estimated: 240+ label-value pairs, 16 show pages.**

**Duplication level: VERY HIGH**

**Priority: HIGH**

---

## 1.5 Repeated Card Patterns

Every show page wraps detail content in:

```blade
<div class="bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
```

This appears on every show page (16+), every create page (16+), and every edit page (16+).

**Estimated: 48+ card wrappers.**

**Duplication level: HIGH (48+ identical divs)**

**Priority: MEDIUM**

---

## 1.6 Duplicated CSS Patterns

### Permissions Prototype CSS (lines 196-382 of app.css)

~186 lines of CSS copied from `PHASE0_PROTOTYPE.html`:

| CSS Classes | Tailwind Equivalent | Status |
|---|---|---|
| `.hd`, `.pt`, `.ps` | Tailwind spacing/typography | DELETE |
| `.card`, `.ch`, `.cb` | `bg-white rounded-xl shadow-sm` | DELETE |
| `.stats`, `.stat` | `flex gap-*` | DELETE |
| `.dot-m`, `.dot-s`, `.dot-i` | `w-2 h-2 rounded-full bg-*` | DELETE |
| `.btn`, `.btn-s`, `.btn-p`, `.btn-d` | `<x-button>` component | DELETE |
| `.chip` | `rounded-full border px-*` | DELETE |
| `.mt`, `.mn`, `.sen-tag` | Tailwind table classes | DELETE |
| `.ie`, `.ie-hd`, `.ie-bd` | Tailwind card + border | DELETE |
| `.mo`, `.mo-in`, `.mo-hd` | `<x-modal>` component | DELETE |
| `.fg`, `.fw`, `.f` | Tailwind grid classes | DELETE |

**Estimated: 70+ custom class names, all redundant with Tailwind or existing components.**

**Duplication level: MEDIUM (one location but large file)**

**Priority: HIGH (migrate to Tailwind, delete from app.css)**

### Glass Effects (lines 61-87 of app.css)

Two separate but very similar glass classes (`.glass` and `.glass-card`) with nearly identical blur/background values.

**Duplication level: LOW (2 classes, but could be 1)**

### Table Header Styling (lines 146-161 of app.css)

`thead th` is global CSS overriding Tailwind. Every table inherits this style even if it's not in an admin context.

**Recommendation: Remove global thead th, use `<x-table>` component instead.**

### Input Focus Styling (lines 163-173 of app.css)

`.input-focus` class used alongside Tailwind's `focus:` utilities. Consider migrating entirely to `focus:ring-indigo-500 focus:border-indigo-500`.

---

## 1.7 JS-Driven UI Behaviors

### Admin Layout Inline Script (admin.blade.php ~lines 480-780)

~300 lines of vanilla JavaScript handling:

| Behavior | Lines | Currently Testable? | Replace With |
|---|---|---|---|
| Dark mode toggle + localStorage | ~15 | No | Extracted JS module |
| Sidebar collapse + persist | ~25 | No | Extracted JS module |
| Sidebar search/filter | ~35 | No | Extracted JS module |
| Toast show/hide + auto-dismiss | ~40 | No | Extracted JS module |
| Confirm modal keyboard trap | ~25 | No | Extracted JS module |
| Loading overlay | ~15 | No | Extracted JS module |
| Cmd+K command palette | ~80 | No | Extracted JS module |
| Animated stat counters | ~25 | No | Extracted JS module |
| Notification bell | ~10 | No | Extracted JS module |
| Password reveal toggle | ~10 | No | Extracted JS module |
| Select all checkboxes | ~10 | No | Extracted JS module |
| Submit button loading state | ~10 | No | Extracted JS module |

---

## 1.8 Alpine.js Components

| Alpine Data | File | Lines | Purpose |
|---|---|---|---|
| `Alpine.data('editPerms', ...)` | `resources/js/permissions.js` | 353 | Full permissions management SPA |
| `x-data="{ selectAll: false }"` | `bulk-actions.blade.php` | 25 | Select-all checkbox |
| `x-data`, `x-show`, `x-for` | 11 permissions components | ~350 combined | Permission UI interactivity |

**Alpine is used exclusively in:**
- Permissions page (11 components, 353-line JS module)
- Bulk actions component

**Alpine is NOT used in:**
- Admin layout (all vanilla JS)
- Dashboard
- Index pages
- Show pages
- Forms
- Renewal Center
- Vault
- Any other module

**This is a significant architectural observation: Alpine is underutilized.**
The admin layout's ~300 lines of inline JS could benefit from Alpine's state management, but doesn't use it.

---

## 1.9 Dashboard Widgets

Dashboard `index.blade.php` uses `@include` pattern:

```blade
@if(!empty($operations))
    @include('dashboard.widgets.operations')
@endif
```

Widgets found (NEEDS CONFIRMATION on exact file list):
- `dashboard/widgets/operations.blade.php`
- `dashboard/widgets/renewals.blade.php`
- `dashboard/widgets/tasks.blade.php`
- `dashboard/widgets/assets.blade.php`
- `dashboard/widgets/vault.blade.php`

These are `@include` partials, not Blade components. They have no prop contract — they implicitly depend on controller variables.

---

## 1.10 Auth Pages (Standalone HTML)

These do NOT use the admin layout:

| Page | Path | Lines | Notes |
|---|---|---|---|
| Login | `auth/login.blade.php` | 116 | Full `<html>` document |
| Register | `auth/register.blade.php` | 104 | Full `<html>` document |
| Forgot Password | `auth/forgot-password.blade.php` | 79 | Full `<html>` document |
| Reset Password | `auth/reset-password.blade.php` | 89 | Full `<html>` document |
| Profile | `auth/profile.blade.php` | 33 | Uses admin layout |
| My Permissions | `auth/my-permissions.blade.php` | 64 | Uses admin layout |

Auth pages duplicate:
- Dark mode detection script
- Font loading (Instrument Sans from bunny.net)
- Vite asset loading
- CSS class conventions
- Error/success message styling

---

## 1.11 Pagination

Customized pagination template at `vendor/pagination/tailwind.blade.php` (96 lines).

Overrides Laravel's default Tailwind pagination with custom gradient buttons, dark mode, and "Showing X to Y of Z results" text.

---

## 1.12 Charts

`resources/js/charts.js` (100 lines) initializes:
- Tasks status doughnut chart
- Services type bar chart
- Assets status doughnut chart
- Renewals expiry bar chart

Charts are initialized by finding DOM elements by ID and reading data from `data-` attributes.

---

## 1.13 Empty States

Existing `<x-empty-state>` component (37 lines) supports:
- 12 icons (box, search, user, bell, lock, key, clipboard, globe, server, calendar, activity, clock)
- Title, message, colspan props
- Renders as `<td>` (in tables) or `<div>` (standalone)

**Used in: ~16 index pages (inside `@forelse @empty`).**
**Missing: action button support, illustration variants, compact mode.**

---

## 1.14 Loading States

**None exist.** There are no skeleton screens, no loading spinners for tables, no placeholder states. Every page either renders full content or shows nothing.

---

## 1.15 Custom CSS Summary

| Source | Lines | Type | Action |
|---|---|---|---|
| `app.css` Tailwind directives | 1-12 | Configuration | KEEP |
| `app.css` dark mode + scrollbar | 13-26 | Custom CSS | KEEP (but use tokens) |
| `app.css` keyframes | 28-54 | Animations | KEEP (extend) |
| `app.css` utility classes | 56-193 | Custom CSS | Mostly KEEP (refine) |
| `app.css` permissions prototype | 196-382 | Prototype CSS | DELETE (migrate to Tailwind) |
| Auth pages inline styles | ~10 per page | Custom CSS | MINIMIZE |

---

## 1.16 Files Needing Migration (Total)

| Category | Count | Total Files |
|---|---|---|
| Module view directories | 16+ | ~96+ (index/show/create/edit) |
| Blade components | 27 | 27 |
| Permissions CSS | 1 | 1 (app.css block) |
| Dashboard widgets | ~5 | 5 |
| Auth pages | 6 | 6 |
| Pagination template | 1 | 1 |
| JS files | 4 | 4 |
| CSS | 1 | 1 |
| Layout | 1 | 1 |
| **Total** | | **~142 files** |

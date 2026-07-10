# 6. UI Quality Standards

> Design and code quality rules every developer must follow.

---

## 6.1 Table Design Standards

| Rule | Standard |
|---|---|
| Header style | `text-xs font-semibold uppercase tracking-wider text-gray-500` |
| Header background | `bg-gray-50` light, `bg-black/50` dark |
| Cell padding | `py-3 px-6` comfortable, `py-1.5 px-4` compact |
| Row hover | `hover:bg-gray-50` light, `hover:bg-gray-700/50` dark |
| Row border | `border-b border-gray-200` light, `border-gray-700` dark |
| Column alignment | Text left by default. Numbers right. Actions right. |
| Actions column | Last column. Minimum width. `whitespace-nowrap`. |
| Empty state | MUST show `<x-empty-state>` when no rows. |
| Loading state | Show `<x-skeleton type="table">` when loading. |
| Bulk checkbox | First column. 10px width. |
| Responsive | Horizontal scroll on overflow. Column visibility via breakpoints. |
| Sort indicator | Show arrow on sortable column headers. |
| Text truncation | Long text truncated with `truncate` class + tooltip on hover. |
| Badges in cells | Use `<x-badge>` — NEVER raw Tailwind badge classes. |
| Dates in cells | Use `<x-date format="Y-m-d">` — NEVER manual formatting. |
| Money in cells | Use `<x-money>` — NEVER `number_format()`. |

---

## 6.2 Form Design Standards

| Rule | Standard |
|---|---|
| Label style | `text-sm font-medium text-gray-700 dark:text-gray-300 mb-1` |
| Input style | `w-full rounded-xl border border-gray-300 dark:border-gray-600 px-3 py-2.5 text-sm` |
| Input focus | `focus:border-indigo-500 focus:ring-3 focus:ring-indigo-500/10` |
| Input error | `border-red-500 focus:border-red-500 focus:ring-red-500/10` |
| Input disabled | `opacity-50 cursor-not-allowed bg-gray-50 dark:bg-gray-900` |
| Error message | `text-sm text-red-600 mt-1` |
| Helper text | `text-xs text-gray-500 mt-1` |
| Required asterisk | Red `*` after label text |
| Form spacing | `space-y-5` between fields |
| Form sections | Separated by `<hr class="my-6 border-gray-200 dark:border-gray-700">` |
| Grid columns | 2 columns on `md:`, 1 column on mobile |
| Submit button | Right-aligned or left-aligned with Cancel button |
| Cancel button | Links to index route, not `javascript:history.back()` |
| Validation summary | Show at top of form AND inline per field |
| Password fields | Use `<x-form.password>` only |
| Date fields | Use `<x-form.date>` only |
| Select with null option | Always include `<option value="">None</option>` |
| Boolean fields | Use `<x-form.toggle>` for simple switches, `<x-form.checkbox>` for standalone |

---

## 6.3 Modal Design Standards

| Rule | Standard |
|---|---|
| Backdrop | `fixed inset-0 bg-overlay z-50` |
| Content | `bg-surface-elevated rounded-xl shadow-modal max-w-md w-full mx-4` |
| Animation | `scale-in` on open, fade out on close |
| Header | `flex items-center justify-between p-6 pb-0` |
| Title | `text-lg font-semibold` |
| Body | `p-6` |
| Footer | `flex justify-end gap-3 p-6 pt-0 border-t border-gray-200 dark:border-gray-700` |
| Close button | X icon in header, + Escape key |
| Focus trap | Tab cycles within modal, first focusable element auto-focused |
| Body scroll lock | `overflow: hidden` on `<body>` when modal open |
| Persistent modal | No close on backdrop click. Only close via explicit action. |
| Confirmation variant | Danger: red confirm button. Warning: amber. Info: blue. |
| Form in modal | After modal close, clear form state. |

---

## 6.4 Badge Design Standards

| Rule | Standard |
|---|---|
| Base style | `inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium` |
| Active | `bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300` |
| Expired | `bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300` |
| Suspended | `bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300` |
| Cancelled | `bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400` |
| Pending | `bg-amber-50 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300` |
| Enabled | `bg-blue-50 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300` |
| Disabled | `bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400` |
| Linked (auto-synced) | `bg-lime-50 text-lime-700 dark:bg-lime-900/30 dark:text-lime-300` |
| Standalone | `bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400` |
| Dot variant | Show colored dot before text. Use for: compact lists, sidebar counts. |
| Outline variant | Border only, no background. Use for: filter chips, unselected states. |

---

## 6.5 Card Design Standards

| Rule | Standard |
|---|---|
| Default variant | `bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700` |
| Glass variant | `glass-card` class (existing from app.css, preserve) |
| Bordered variant | `border border-gray-200 dark:border-gray-700 rounded-xl` (no shadow) |
| Hover effect | `card-hover` class (existing from app.css, preserve) |
| Padding | `p-6` comfortable, `p-4` compact, `p-0` none |
| Header | Optional. `border-b border-gray-200 dark:border-gray-700 px-6 py-4` |
| Footer | Optional. `border-t border-gray-200 dark:border-gray-700 px-6 py-4` |
| Stacking | Cards in a column: `space-y-4` |
| Grid | Cards in a grid: `grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6` |
| Nested cards | Avoid. Use `<x-section>` instead. |

---

## 6.6 Dashboard Design Standards

| Rule | Standard |
|---|---|
| Stat card grid | `grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6` |
| Widget grid | `grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6` |
| Stat icon | Colored circular background. `w-10 h-10 rounded-lg`. |
| Stat value | `text-2xl font-bold` |
| Stat label | `text-sm text-gray-500` |
| Chart dimensions | Doughnut: max 200px. Bar: full width. |
| Widget header | Widget title + optional badge/action |
| Widget loading | Show `<x-skeleton type="text" :rows="3">` while loading |
| Widget empty | Show compact `<x-empty-state>` |
| Card hover | Optional `card-hover` class on stat cards |

---

## 6.7 Spacing Standards

| Context | Rule |
|---|---|
| Page padding | `p-6 max-w-7xl mx-auto` |
| Between sections | `mt-6` or `space-y-6` |
| Between cards | `gap-6` in grid, `space-y-4` in stack |
| Between form fields | `space-y-5` |
| Between form grid cells | `gap-4` |
| Between table rows | `divide-y divide-gray-200` |
| Between toolbar buttons | `gap-3` |
| Inside buttons | `gap-2` (icon + text) |
| Inside badges | `gap-1` (dot + text) |
| Modal from viewport edge | `mx-4` (max 32px from edge on mobile) |

---

## 6.8 Typography Standards

| Context | Rule |
|---|---|
| Page title | `text-xl font-bold text-gray-900 dark:text-white` |
| Page subtitle | `text-sm text-gray-500 dark:text-gray-400 mt-1` |
| Card title | `text-lg font-semibold` |
| Section title | `text-base font-semibold mb-4` |
| Table header | `text-xs font-semibold uppercase tracking-wider text-gray-500` |
| Table cell | `text-sm` |
| Form label | `text-sm font-medium text-gray-700 dark:text-gray-300` |
| Form input | `text-sm` |
| Button | `text-sm font-medium` |
| Badge | `text-xs font-medium` |
| Helper | `text-xs text-gray-500` |
| Error | `text-sm text-red-600` |
| Empty state title | `text-lg font-semibold text-gray-900` |
| Empty state message | `text-sm text-gray-500` |
| Stat value | `text-2xl font-bold` |
| Stat label | `text-sm text-gray-500` |
| Timeline event | `text-sm` |
| Timeline time | `text-xs text-gray-400` |
| Breadcrumb | `text-sm text-gray-500` |

---

## 6.9 Icon Standards

| Rule | Standard |
|---|---|
| Icon library | Use SVG inline icons (current pattern). No icon font library. |
| Icon size | Checkbox/badge/alert: `w-4 h-4`. Button: `w-3.5 h-3.5`. Stat: `w-6 h-6`. |
| Icon color | Inherits from text color by default. |
| Icon in buttons | Left of text. `gap-2` spacing. |
| Icon standalone | Include `aria-label` for accessibility. |
| No icon-only buttons | Always include text label or `title` attribute. |

---

## 6.10 Dark Mode Standards

| Rule | Standard |
|---|---|
| Implementation | `dark:` variant on every interactive element |
| Card background | `dark:bg-black` (not `gray-900`) |
| Page background | `dark:bg-black` (body, or via `.dark body` in app.css) |
| Text primary | `dark:text-white` |
| Text secondary | `dark:text-gray-400` |
| Text muted | `dark:text-gray-500` |
| Border | `dark:border-gray-700` or `dark:border-gray-700/50` |
| Form input | `dark:bg-black dark:text-white dark:border-gray-600` |
| Table header | `dark:bg-black/50` |
| Table row hover | `dark:hover:bg-gray-700/50` |
| Badge (dark) | Use `dark:bg-{color}-900/30 dark:text-{color}-300` pattern |
| Glass effect | `.dark .glass { background: rgba(0,0,0,0.85) }` |
| Toggle | System preference detection + manual override |
| Persistence | `localStorage.getItem('darkMode')` |
| FOUC prevention | Inline script in `<head>` before CSS loads |

---

## 6.11 Responsive Standards

| Breakpoint | Behavior |
|---|---|
| `< 640px` (mobile) | Single column, stacked cards, hamburger sidebar |
| `640-1023px` (tablet) | 2 columns for grids, sidebar collapsed |
| `1024px+` (desktop) | 3-4 columns for grids, sidebar expanded |
| `> 1280px` (wide) | Max width container, full layout |
| Tables | Horizontal scroll on mobile. Responsive column hiding. |
| Forms | Single column on mobile, 2 columns on desktop. |
| Cards | Stack vertically on mobile, grid on desktop. |
| Modals | Full-width on mobile (`mx-4`), centered on desktop. |
| Dashboard | 1 column mobile, 2 columns tablet, 4 columns desktop. |
| Actions on mobile | Stack vertically, full-width buttons. |

---

## 6.12 Accessibility Standards

| Rule | Standard |
|---|---|
| Forms | Every input MUST have a `<label>` with `for` attribute |
| Images/icons | Decorative: `aria-hidden="true"`. Informational: `alt` text. |
| Buttons | Include text or `aria-label` for icon-only buttons |
| Color | Never use color alone to convey information (include text or icon) |
| Focus | Visible focus ring on ALL interactive elements |
| Keyboard | All interactions must work with Tab/Enter/Escape |
| Skip link | "Skip to main content" link at top of page |
| Screen readers | Use `sr-only` classes for screen-reader-only content |
| Modals | Focus trap, `role="dialog"`, `aria-modal="true"` |
| Errors | Error messages linked to inputs via `aria-describedby` |
| Status updates | Use `role="status"` or `aria-live` for dynamic content |

---

## 6.13 Loading & Empty State Standards

| Rule | Standard |
|---|---|
| Table loading | Show `<x-skeleton type="table" :rows="5">` while data loads |
| Card loading | Show `<x-skeleton type="card">` |
| Text loading | Show `<x-skeleton type="text" :rows="3">` |
| Dashboard loading | Show `<x-skeleton>` per widget |
| Empty tables | Show `<x-empty-state>` with icon + title + message + optional action |
| Empty search | Show `<x-empty-state>` with search icon + "No results found" |
| Empty filtered | Show `<x-empty-state>` with filter icon + "Try different filters" |
| Empty dashboard widget | Show compact `<x-empty-state>` or skip rendering |

---

## 6.14 Error & Confirmation Standards

| Rule | Standard |
|---|---|
| Form validation | Show error inline (below field) AND at top of form |
| API error | Show `<x-alert type="error">` with message |
| Delete | ALWAYS require `<x-confirm-dialog variant="danger">` |
| Force delete | Require confirmation + type "DELETE" to confirm |
| Restore | Optional confirmation. Show success toast after. |
| Reveal password | No confirmation needed (permission check on backend). |
| Permission change | Show diff panel + sensitive confirmation before save. |
| Unload warning | Show `beforeunload` prompt if unsaved changes in form. |

---

## 6.15 Renewal Linked Field Standards

| Rule | Standard |
|---|---|
| Linked tracker display | Show `<x-renewal-badge>` + `<x-renewal-source-card>` |
| Synced fields | Render as read-only: `<x-field :readonly="true">` |
| Standalone fields | Render as editable: `<x-field>` or `<x-form.input>` |
| Source service link | `<x-renewal-source-card>` with dynamic route |
| No duplicate linking | Enforce validation: unique `(trackable_id, trackable_type)` |
| Sync warning | Never allow frontend to edit synced fields |

---

## 6.16 Code Quality Standards

| Rule | Standard |
|---|---|
| No inline styles | Use Tailwind classes only. No `<style>` tags in Blade. |
| No inline JS | Use Alpine `x-` attributes or extracted JS modules. |
| No hardcoded colors | Use Tailwind semantic tokens or component props. |
| No duplicated arrays | Move `$sourceTypeLabels`, `$statusColors` to shared source. |
| No `@php` in Blade | Move logic to controller, model accessor, or component class. |
| No `@include` for widgets | Use `<x-component>` pattern with explicit props. |
| No HTML in Blade outside components | Pages should be 100% component calls. |
| Always test dark mode | Every component must render correctly in both modes. |
| Always test mobile | Every page must function at 375px viewport. |
| No empty loading | Every async block must show loading state. |

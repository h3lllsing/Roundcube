# UI/UX Audit Report

**Date:** 2026-06-27  
**Scope:** 151 Blade views, 13 Blade components, custom CSS/app.css (194 lines), JS in admin layout  
**Stack:** Tailwind CSS v4 + DaisyUI 5, Instrument Sans font, Heroicons 2  
**Auditor:** opencode

---

## 1. Component Consistency

### ✅ Consistently Reused
- **x-page-header** — all resource pages (create, edit, show, index) use it with consistent pattern
- **x-button** — `variant="primary|danger|outline|success"` + `size="sm|lg"` used everywhere
- **x-action** — view/edit/delete actions in every index table
- **x-empty-state** — all index tables, notifications page
- **x-bulk-actions** — all index tables with bulk operations
- **x-stat-card** — dashboard widgets
- **x-monitor-button / x-monitor-result** — all show pages
- **x-activity-timeline** — all show pages
- **x-form.input / x-form.select / x-form.checkbox / x-form.textarea** — all create/edit forms

### ⚠️ Inconsistent Patterns
1. **x-action label="" in service-providers/index** (line 92–98): uses `label=""` + icon-only with `title` attribute. Users index (line 96–107) uses `label="View"` with text. Expiry-trackers uses `label="View"` too. Should pick one convention.
2. **Profile page uses `glass-card`** while all other create/edit forms use `bg-white dark:bg-black rounded-xl shadow-sm border`. Two different card surfaces for the same type of form.
3. **Dashboard widgets use `glass-card rounded-2xl`** while resource content uses standard cards. Creates visual inconsistency between dashboard and rest of app.

---

## 2. Spacing & Layout

### ✅ Consistent
- Index pages: `max-w-7xl mx-auto` for full-width tables
- Show/create/edit forms: `max-w-3xl mx-auto`
- Grid for show detail fields: `grid grid-cols-1 md:grid-cols-2 gap-4`
- Form spacing: `space-y-4` inside forms, `gap-4` in grid rows
- Filter/search bar: `mb-6` spacing before table
- Table below header: `bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-x-auto w-full`

### ⚠️ Inconsistencies
1. **Pagination placement** — most index pages place `{{ $items->links() }}` inside `div.mt-4` outside the form wrapper. service-providers/index has the closing `</form>` tag at line 108, before the pagination div, which is correct but could be fragile.
2. **Chart.js canvas in dashboard** — `height="200"` attribute on canvas element (line 22 of operations widget). Canvas height attribute vs CSS height can cause rendering confusion.
3. **Notifications index** uses a unique layout with inline "Read"/"Delete" links instead of `x-action` component. Different from all other index pages.

---

## 3. Button & Link Styles

### ✅ Consistent
- Primary buttons: `x-button variant="primary" size="sm"` (gradient indigo→purple)
- Danger buttons: `variant="danger"` (red→rose)
- Outline buttons: `variant="outline"` (bordered)
- Success buttons: `variant="success"` (emerald→teal)
- All buttons use `rounded-xl` consistently

### ⚠️ Inconsistencies
1. **Quick Actions** on dashboard use inline `<a>` tags with hand-crafted gradients (`bg-gradient-to-r from-indigo-50 to-purple-50`) rather than `x-button`. No consistency with the main button component. Nine different gradient combinations used.
2. **Notifications page** (line 17-18, 27, 38-39): uses raw `<a>` and `<button>` elements with manual classes instead of `x-button` component.
3. **Users index Clone button** (line 97-100): inline `<a>` with `text-sky-700` classes — only place in app using "sky" color for an action. Should use `x-action` with `color="sky"`.
4. **Login page "Sign in" button** (line 101): uses `x-button variant="primary" size="lg" class="w-full"` — size lg is unique to this page.

---

## 4. Table Consistency

### ✅ Consistently Applied
- Checkbox column: `px-4 py-3 w-10` with `bulk-select-all` / `bulk-item` classes
- Data cells: `px-6 py-3` spacing
- Row hover: `hover:bg-gray-50 dark:hover:bg-gray-700/50`
- Status badges: `inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium` with semantic colors
- Actions column: `whitespace-nowrap`
- Header row: `bg-gray-50 dark:bg-black/50` with `font-medium text-gray-500 dark:text-gray-400`

### ⚠️ Inconsistencies
1. **Users index** (line 67): suspended users get `bg-red-50 dark:bg-red-900/10` row background — unique to this table. Good UX, but no other table uses row-level status highlighting.
2. **Notifications index** doesn't use a `<table>` at all — uses `<div>` elements with `border-b` separators. A completely different presentation pattern.
3. **Expiry-trackers index** `cancelled` status uses `bg-gray-100 dark:bg-black/30` — unique badge color combination not used elsewhere.

---

## 5. Empty / Loading / Error States

### ✅ Present
- **Empty state**: `x-empty-state` component used in all index tables with contextual icon, title, message
- **Dashboard widgets**: custom empty states with inline SVGs and CTA links
- **Notifications**: empty state inside `div.py-12.text-center`
- **Quick actions**: `$hasAny` check with empty message when no permissions

### ⚠️ Missing / Inconsistent
1. **No loading states** anywhere — no skeleton loaders, no `loading` attribute on submit buttons during form submission. The layout has `onclick="startLoading(this)"` on some buttons but it's not universally applied.
2. **No error states** on individual components — `x-empty-state` only covers "no data". Pages that fail to load have no fallback UI.
3. **Monitor result component** may fail silently — no visible error state in UI for failed monitor checks (console errors only).
4. **No optimistic UI** — no loading indicators during bulk actions, AJAX calls, or filter submissions.

---

## 6. Forms & Inputs

### ✅ Consistent
- `x-form.input` component: consistent `rounded-xl border bg-white dark:bg-black` with `input-focus` - used in all create/edit forms
- `x-form.select`: consistent with input styling, `placeholder` attribute for first option
- `x-form.checkbox`: consistent `rounded border-gray-300 dark:border-gray-600 text-indigo-600`
- Error messages: consistent pattern with SVG icon + red text

### ⚠️ Inconsistencies
1. **Filter forms in index pages** don't use `x-form.*` components. They use raw `<input>` and `<select>` elements with manual class duplication. 6+ pages have near-identical raw filter markup.
2. **report-filter-bar component** (line 12-66) exists to abstract filter patterns but is NOT used by any index page. All filtering is done inline with raw HTML.
3. **Notifications search** (line 23-24): uses `x-button` for Search but a raw `<a>` tag for Clear — should use `x-button variant="outline"`.
4. **Profile page** uses `glass-card` surface while domain/hosting create/edit use `bg-white dark:bg-black rounded-xl shadow-sm border`. Inconsistent card container.
5. **No form validation summary** — errors are shown inline per-field (good) but no top-of-form error summary (acceptable, but a nice-to-have).

---

## 7. Color & Visual Design

### ✅ Consistent Palette
- Primary: indigo-500 → indigo-700 (light) / indigo-300 → indigo-400 (dark)
- Success: emerald → green tones
- Danger: red → rose tones
- Warning: amber → yellow tones
- Backgrounds: gray-50 (light) / black (dark)
- Cards: white (light) / black (dark)
- Borders: gray-200 (light) / gray-700 (dark)

### ⚠️ Issues
1. **Dark mode tables** — the `thead th` CSS rule uses a `linear-gradient` on light mode but `background: #000000` on dark mode. The gradient disappears, making dark mode headers look flat.
2. **Status badge colors vary across resources**: "suspended" uses `bg-yellow` on service-providers but `bg-orange` on users. "expired" uses `bg-red` everywhere (consistent). "cancelled" in expiry-trackers uses `bg-gray` — unique.
3. **Glass card vs standard card** — dashboard uses `glass-card rounded-2xl` (translucent, blur-backed), resource pages use `bg-white dark:bg-black rounded-xl shadow-sm border`. Two visual systems coexist.
4. **Selection color** (CSS line 25): `#6366f1` — indigo. Good.

---

## 8. Typography

### ✅ Consistent
- Font: Instrument Sans (loaded via fonts.bunny.net in all standalone pages + admin layout)
- Table headers: `text-[11px] font-semibold uppercase tracking-widest` or `text-xs font-semibold uppercase tracking-wider`
- Labels: `text-sm font-medium text-gray-700 dark:text-gray-300`
- Body text: `text-sm text-gray-500` (detail labels) / `font-medium` (values)
- Empty state titles: via x-empty-state component

### ⚠️ Issues
1. **Filter labels** in inline filter forms use `text-[11px] font-semibold uppercase tracking-widest text-gray-500` (same as report-filter-bar component). Index page inline filters DON'T use labels at all — they rely on placeholder text.
2. **Page header sub+titles** come from x-page-header component via `subtitle` prop — used in create, edit, profile; not used in index, show, or some pages.

---

## 9. Icons

### ✅ Consistent
- Heroicons 2 (outline style, `stroke="currentColor"`, `stroke-width="2"`)
- Icon sizes: `w-3.5 h-3.5` for action icons, `w-4 h-4` for toolbar actions, `w-7 h-7` for dashboard widget headers

### ⚠️ Issues
1. **Inline SVGs duplicated** — export/download icons are copy-pasted in every index page. Could be extracted to a component.
2. **Quick actions** use inline SVGs that aren't Heroicons standard — different stroke-widths and viewBox patterns.
3. **Custom icons in x-action component** — only 5 icons (view, edit, delete, download, plus). Adding more requires editing the component.

---

## 10. Responsiveness

### ✅ Good
- Tables: `overflow-x-auto w-full` with `min-width: max-content` CSS
- Detail grids: `grid-cols-1 md:grid-cols-2` for show pages
- Dashboard: `grid-cols-1 md:grid-cols-2 xl:grid-cols-4`
- Login page: `w-full max-w-sm` with `px-4`
- Form grids: `grid-cols-1 md:grid-cols-2 gap-4`

### ⚠️ Issues
1. **Sidebar** (in admin layout): sidebar toggle and mobile behavior needs testing. The layout has sidebar + navbar structure but interaction JS should be verified.
2. **Dashboard operations widget** (line 10): `grid-cols-2 sm:grid-cols-4` for stat cards at top, then `grid-cols-1 sm:grid-cols-2` for upcoming expiries. Works but complex.
3. **Quick actions** (line 21): `flex flex-wrap gap-2` — simple and responsive.
4. **No responsive font size adjustments** — font sizes are fixed (`text-sm`, `text-xs`, `text-[11px]`) and don't scale on mobile.

---

## 11. Dark Mode

### ✅ Excellent Coverage
- Every `.blade.php` view has `dark:` counterparts on all color properties
- Login page: script detects `localStorage.getItem('darkMode')` + `prefers-color-scheme` before rendering
- Admin layout: theme toggle persists to localStorage, updates `document.documentElement.classList`
- Error pages (403, 404, 419, 500): all have dark mode classes
- CSS: 15+ `dark:` selectors for scrollbars, glass effects, selection, inputs, tables, backgrounds

### ⚠️ Issues
1. **Welcome page** (`/`) is the default Laravel welcome page — has no dark mode and uses different styling (rounded-sm instead of rounded-xl, Helvetica-like font). Only page in the app without dark mode support.
2. **Table headers** in dark mode: CSS line 158 sets `background: #000000` — pure black. The gray-50 light mode gradient is not replicated in dark mode.

---

## 12. Accessibility

### ❌ Issues Found
1. **Icon-only x-action with empty label** — service-providers index uses `label=""` + `title="View"`. Screen readers read `title` but keyboard navigation users get no visible text. Users index uses `label="View"` which is better.
2. **No `aria-label` on some buttons**: Copy password buttons in service-providers and hostings, filter action buttons, table sort controls (nonexistent).
3. **Color-only status indicators**: Status badges rely solely on color (green=active, red=expired). No icon or text prefix for colorblind users.
4. **Checkbox labels missing**: Bulk select checkboxes have no explicit `<label>` association. The "Select All" label in `x-bulk-actions` wraps the input correctly, but per-row checkboxes lack labels.
5. **Empty `alt` attributes on decorative SVGs**: SVG icons used as button content should have `aria-hidden="true"` — currently missing.
6. **Focus indicators**: `input-focus` class uses `focus:ring-2 focus:ring-indigo-500/40` on buttons and inputs — present but inconsistent. Some inline `<a>` tags lack focus styles.
7. **No skip-to-content link**: Admin layout doesn't have a skip navigation link for keyboard users.
8. **Reduced motion**: CSS line 192-194 respects `prefers-reduced-motion` — good.

---

## 13. Animations & Transitions

### ✅ Present
- `fade-in-up` animation on dashboard pages
- `card-hover` with translateY + shadow on dashboard cards
- `stat-card` with pseudo-element glow on hover
- `toast-in / toast-out` for notifications
- `scale-in` for modals/cmd palette
- `nav-link` with animated left border indicator
- `input-focus` transition on all form inputs

### ⚠️ Issues
1. **Dashboard uses `fade-in-up`** class but resource pages do not — different page entry experiences.
2. **No loading/progress indicator** for page transitions or AJAX calls. The admin layout has a loading bar placeholder in JS but it may not be triggered.
3. **Animation durations vary**: `0.35s` for cards, `0.4s` for fade-in, `0.5s` for fade-in-up, `0.6s` for stat cards — no design system rationale documented.

---

## 14. Dashboard Widgets

### ✅ Good
- All widgets use `card-hover glass-card rounded-2xl p-5`
- Consistent header pattern: icon in gradient circle + title
- `border-t` dividers between sections
- All handle empty state

### ⚠️ Issues
1. **Widget empty states differ**: operations widget shows inline SVG + CTA link; quick actions shows text-only message; other widgets have no empty state at all.
2. **Chart.js canvas** in operations widget uses `height="200"` attribute — should use CSS-based responsive sizing.
3. **Quick actions dropdown**: repetitive gradient classes (9 variations) could be a single reusable class.

---

## 15. Summary

| Category | Score | Key Improvements |
|---|---|---|
| Component Consistency | 7/10 | Standardize x-action labels, unify card types |
| Spacing & Layout | 8/10 | Minor pagination/template gaps |
| Button/Link Styles | 7/10 | Quick actions + notifications use raw HTML |
| Table Consistency | 9/10 | Notifications is outlier (uses divs) |
| Empty/Loading/Error States | 5/10 | No loading states, no error fallbacks |
| Forms & Inputs | 7/10 | Filter forms don't use component |
| Color & Visual Design | 8/10 | Glass vs solid card duality |
| Typography | 9/10 | Consistent across views |
| Icons | 8/10 | Duplicated SVGs could be components |
| Responsiveness | 8/10 | Mobile sidebar unclear |
| Dark Mode | 9/10 | Welcome page is the only unthemed page |
| Accessibility | 4/10 | Icon-only labels, missing aria attributes |
| Animations | 7/10 | No loading indicators |
| Dashboard Widgets | 7/10 | Inconsistent empty states |
| **Overall** | **7.2/10** | **14 accessibility fixes + 6 component consolidations recommended** |

### Top Recommendations for v1.1
1. **Accessibility**: Add `aria-label` to all icon-only buttons, ensure `x-action` always has visible text or proper `aria-label`
2. **Filter form component**: Migrate all inline index filter forms to use `x-report-filter-bar` or a standardized filter partial
3. **Unify card surfaces**: Decide between `glass-card` and `solid card` — apply universally
4. **Loading states**: Add skeleton loaders or loading spinners for AJAX operations and page navigation
5. **Notify empty/error states**: Add fallback UI to all components that fetch data asynchronously
6. **Audit colorblind accessibility**: Add text-based status indicators alongside color badges
7. **Responsive sidebar**: Test and verify mobile navigation behavior
8. **Welcome page**: Theme to match app branding or redirect to login

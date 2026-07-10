# 3. Component Library Specification

> Complete Blade component spec for the Enterprise Design System.

---

## 3.1 Layout Components

### `<x-app-shell>`

| Field | Value |
|---|---|
| **Purpose** | Main application wrapper. Replaces the current inline layout in `admin.blade.php`. Provides sidebar, header, content area, and all global UI (toasts, modals, cmd palette). |
| **Props** | `title: string`, `sidebarCollapsed: bool (default: false)` |
| **Slots** | `sidebar`, `header`, `content`, `footer` (optional) |
| **States** | Loading (skeleton sidebar), Error (layout crash fallback) |
| **Accessibility** | Skip-to-content link, aria-current for active nav, keyboard nav for sidebar |
| **Dark mode** | Inherits from `.dark` class on `<html>`, applies glass/surface tokens |
| **Responsive** | Mobile: sidebar off-canvas with hamburger. Desktop: fixed sidebar. |
| **Replaces** | `layouts/admin.blade.php` (783 lines) |
| **Priority** | Phase 5 (last — high risk, high reward) |

### `<x-page-header>`

| Field | Value |
|---|---|
| **Purpose** | Page title bar with optional subtitle, breadcrumbs, and actions |
| **Props** | `title: string`, `subtitle: string (optional)`, `breadcrumbs: array (optional)` |
| **Slots** | `actions` (right side) |
| **Variants** | Default, Dashboard (larger title, no breadcrumbs) |
| **Replaces** | Current `components/page-header.blade.php` + breadcrumbs |
| **Priority** | Phase 1 |

### `<x-section>`

| Field | Value |
|---|---|
| **Purpose** | A page section with optional title and divider. Similar to a card but without a full card wrapper. |
| **Props** | `title: string (optional)`, `description: string (optional)`, `collapsible: bool (optional)` |
| **Slots** | Default (content) |
| **Replaces** | Manual section divs with `<hr>` dividers |
| **Priority** | Phase 2 |

### `<x-card>`

| Field | Value |
|---|---|
| **Purpose** | Generic card container. Most common wrapper in the app. |
| **Props** | `padding: comfortable|compact|none (default: comfortable)`, `hover: bool (optional)`, `header: string (optional)`, `class: string (optional)` |
| **Slots** | `header` (optional), `default`, `footer` (optional) |
| **Variants** | Default (white bg + shadow), Glass (glass-card style), Bordered (border only, no shadow) |
| **States** | Normal, Hover (with card-hover effect), Disabled |
| **Dark mode** | Applies `dark:bg-black`, `dark:border-gray-700/50` |
| **Replaces** | Manual `bg-white dark:bg-black rounded-xl shadow-sm border` pattern (~48 locations) |
| **Priority** | Phase 1 |

### `<x-toolbar>`

| Field | Value |
|---|---|
| **Purpose** | Horizontal toolbar for index page actions (filter + action buttons). |
| **Props** | `search: bool (optional)`, `searchValue: string (optional)`, `searchPlaceholder: string (optional)` |
| **Slots** | `left`, `right`, `filters` |
| **Replaces** | Manual flex-wrap divs on every index page |
| **Priority** | Phase 2 |

### `<x-tabs>`

| Field | Value |
|---|---|
| **Purpose** | Tab navigation for detail pages |
| **Props** | `tabs: array (label, key, active, route)`, `active: string` |
| **Slots** | Tab content panels |
| **Replaces** | None (tabs may need to be created if detail pages need tabbed views) |
| **Priority** | Phase 4 (nice-to-have) |

### `<x-drawer>`

| Field | Value |
|---|---|
| **Purpose** | Slide-out panel for secondary content (e.g., filters on mobile, quick-create forms) |
| **Props** | `open: bool`, `title: string`, `position: left|right (default: right)`, `width: string (default: w-96)` |
| **Slots** | `default`, `footer` |
| **Replaces** | None (new component) |
| **Priority** | Phase 4 (nice-to-have) |

### `<x-modal>`

| Field | Value |
|---|---|
| **Purpose** | Modal dialog for confirmations, forms, and detail views. Generalizes the permissions modal. |
| **Props** | `open: bool`, `title: string`, `size: sm|md|lg|xl|full (default: md)`, `closeable: bool (default: true)`, `persistent: bool (default: false)` |
| **Slots** | `default`, `footer`, `trigger` (modal trigger button) |
| **States** | Open, Closed, Loading (submit in progress) |
| **Accessibility** | Focus trap, Escape to close, aria-modal, body scroll lock |
| **Replaces** | Permissions `components/permissions/modal.blade.php` + admin layout inline confirm modal |
| **Priority** | Phase 1 |

---

## 3.2 Table Components

### `<x-table>`

| Field | Value |
|---|---|
| **Purpose** | THE most important component. Replaces 16+ manual table implementations. |
| **Props** | `:columns: array`, `:rows: Collection|array`, `:actions: array (optional)`, `bulk: bool (optional)`, `bulkAction: string (optional)`, `sortable: bool (optional)`, `sortField: string (optional)`, `sortDir: string (optional)`, `dense: bool (optional)`, `:empty: array (optional)`, `checkboxName: string (optional)` |
| **Slots** | `before` (before table — search/filter), `after` (after table — pagination), `cell-{column}` (custom cell rendering) |
| **Variants** | Default, Dense, Carded (rows as cards on mobile) |
| **States** | Loading (skeleton rows), Empty (empty-state), Error (retry message), Normal |
| **Column format** | Each column: `{ key, label, sortable, format, class, hideOnMobile, width }` |
| **Accessibility** | `role="table"`, `aria-sort`, `scope="col"`, responsive scroll hint |
| **Dark mode** | Already handled via `dark:` variants |
| **Responsive** | Horizontal scroll on mobile. Column visibility via `hideOnMobile` prop. |
| **Replaces** | ~16 manual table implementations (~100 lines each = ~1,600 lines eliminated) |
| **Migration** | This is the highest-impact component in the entire system |
| **Priority** | Phase 1 |

### `<x-table-column>`

| Field | Value |
|---|---|
| **Purpose** | Inline column definition for simple tables |
| **Props** | `key: string`, `label: string`, `sortable: bool`, `format: string (optional)` |
| **Priority** | Phase 2 (alternative to array-based columns) |

### `<x-empty-state>`

| Field | Value |
|---|---|
| **Purpose** | Empty state placeholder for tables, search results, and filtered views. Extends current component. |
| **Props** | `icon: string`, `title: string`, `message: string`, `:action: array (optional — label + route)`, `compact: bool (optional)` |
| **Variants** | Default (large with icon), Compact (inline for table rows), Search (with search tip) |
| **Replaces** | Current `components/empty-state.blade.php` (extend with action support) |
| **Priority** | Phase 1 |

### `<x-pagination>`

| Field | Value |
|---|---|
| **Purpose** | Pagination bar. Extends Laravel's pagination with consistent styling. |
| **Props** | `:paginator: LengthAwarePaginator`, `dense: bool (optional)`, `showInfo: bool (default: true)` |
| **Slots** | `info` (custom info text) |
| **Replaces** | `vendor/pagination/tailwind.blade.php` (move to component) |
| **Priority** | Phase 2 |

### `<x-filter-bar>`

| Field | Value |
|---|---|
| **Purpose** | Index page filter bar combining search, status dropdowns, date range, and action buttons. |
| **Props** | `:filters: array`, `searchable: bool`, `searchValue: string`, `searchPlaceholder: string`, `:dateRange: bool (optional)`, `:statuses: array (optional)`, `showClear: bool (optional)` |
| **Slots** | `before`, `after`, custom filter slots |
| **Replaces** | Manual filter forms on every index page + `report-filter-bar.blade.php` |
| **Priority** | Phase 2 |

### `<x-search-input>`

| Field | Value |
|---|---|
| **Purpose** | Search input with icon, clear button, and optional debounce |
| **Props** | `name: string`, `value: string`, `placeholder: string`, `debounce: int (ms, default: 0)` |
| **Replaces** | Manual search inputs on index pages |
| **Priority** | Phase 2 |

---

## 3.3 Data Display Components

### `<x-field>`

| Field | Value |
|---|---|
| **Purpose** | Label-value pair for show/detail pages. THE most impactful component for reducing duplication. |
| **Props** | `label: string`, `:value: mixed`, `copyable: bool (optional)`, `mono: bool (optional)`, `fallback: string (default: '—')`, `class: string (optional)` |
| **Variants** | Default, Inline (label next to value), Stacked (label above value, current pattern) |
| **Slots** | `value` (custom value rendering) |
| **States** | Empty (shows fallback), Loading (skeleton placeholder) |
| **Replaces** | ~hundreds of manual `<div><p>Label</p><p>{{ $value ?? '—' }}</p></div>` patterns |
| **Priority** | Phase 1 |

### `<x-date>`

| Field | Value |
|---|---|
| **Purpose** | Consistent date display with formatting and null handling |
| **Props** | `:value: Carbon|string|null`, `format: string (default: 'Y-m-d')`, `relative: bool (optional)`, `empty: string (default: '—')`, `time: bool (optional)` |
| **Variants** | Default (formatted date), Relative ("2 days ago"), Date+Time, Calendar |
| **Slots** | Custom format override |
| **Replaces** | `$entity->date?->format('Y-m-d') ?? '—'` pattern (~hundreds of locations) |
| **Priority** | Phase 1 |

### `<x-money>`

| Field | Value |
|---|---|
| **Purpose** | Consistent cost/price display |
| **Props** | `:value: float|string|null`, `currency: string (default: '$')`, `empty: string (default: '—')`, `decimals: int (default: 2)` |
| **Replaces** | `$record->cost ? '$' . number_format($record->cost, 2) : '—'` pattern |
| **Priority** | Phase 2 |

### `<x-badge>`

| Field | Value |
|---|---|
| **Purpose** | Generic colored badge/pill. The workhorse component for statuses. |
| **Props** | `:value: string`, `:color: string (optional — auto-detect from value)`, `size: sm|md (default: sm)`, `icon: string (optional)`, `dot: bool (optional)`, `class: string (optional)` |
| **Auto-color mapping** | Detects value patterns: `active`→green, `expired`→red, `suspended`→yellow, `cancelled`→gray, `enabled`→blue, `disabled`→gray, `pending`→amber, `completed`→emerald |
| **Variants** | Default (filled bg), Subtle (light bg), Outline, Dot (colored dot only) |
| **Replaces** | ~30 manual `<span @class([...])>{{ $status }}</span>` patterns |
| **Priority** | Phase 1 |

### `<x-status-badge>`

| Field | Value |
|---|---|
| **Purpose** | Shortcut for `<x-badge>` with domain-specific status values |
| **Props** | `:status: string` |
| **Replaces** | All `$domain->status`, `$tracker->status`, `$hosting->status`, etc. |
| **Priority** | Phase 1 |

### `<x-renewal-badge>`

| Field | Value |
|---|---|
| **Purpose** | Renewal-specific badge showing linked/standalone sync status |
| **Props** | `:tracker: ExpiryTracker`, `size: sm|md` |
| **Behavior** | Shows "Auto-synced" (lime) if `trackable_type` is set, "Standalone" (gray) otherwise |
| **Replaces** | Manual `@if($tracker->trackable_type)` badge in expiry-trackers views |
| **Priority** | Phase 3 |

### `<x-permission-badge>`

| Field | Value |
|---|---|
| **Purpose** | Permission level badge (Manage, View Only, No Access, Custom) |
| **Props** | `level: string|int`, `size: sm` |
| **Replaces** | Current permission badge CSS classes (`.p-mg`, `.p-vo`, `.p-na`, `.p-ce`) |
| **Priority** | Phase 3 |

### `<x-user-badge>`

| Field | Value |
|---|---|
| **Purpose** | Displays a user's name with optional avatar (initials) |
| **Props** | `:user: User|null`, `showAvatar: bool (default: true)`, `size: sm|md` |
| **Replaces** | `{{ $record->user->name ?? '—' }}` in many views |
| **Priority** | Phase 2 |

---

## 3.4 Form Components

### `<x-form.input>`

| Field | Value |
|---|---|
| **Purpose** | Text input with label, validation error, and help text. Extends current component. |
| **Props** | `name: string`, `label: string`, `type: text|email|url|tel|number (default: text)`, `:value: mixed`, `placeholder: string`, `required: bool`, `disabled: bool`, `help: string (optional)`, `icon: string (optional)`, `prepend: string (optional — text before input)`, `append: string (optional)` |
| **Slots** | `label` (custom label), `error` (custom error) |
| **States** | Default, Focused, Error (red border + message), Disabled (grayed), Read-only |
| **Dark mode** | Already handled |
| **Replaces** | Current `components/form/input.blade.php` (extend) |
| **Priority** | Phase 1 |

### `<x-form.select>`

| Field | Value |
|---|---|
| **Purpose** | Select dropdown. Same API pattern as input. |
| **Props** | `name: string`, `label: string`, `:options: array|Collection`, `:value: mixed`, `placeholder: string`, `required: bool`, `disabled: bool`, `help: string` |
| **Replaces** | Current `components/form/select.blade.php` |
| **Priority** | Phase 1 |

### `<x-form.textarea>`

| Field | Value |
|---|---|
| **Purpose** | Multi-line text input. Same API pattern. |
| **Props** | `name: string`, `label: string`, `:value: mixed`, `rows: int (default: 3)`, `required: bool`, `help: string` |
| **Replaces** | Current `components/form/textarea.blade.php` |
| **Priority** | Phase 1 |

### `<x-form.checkbox>`

| Field | Value |
|---|---|
| **Purpose** | Checkbox with label. |
| **Props** | `name: string`, `label: string`, `:checked: bool`, `help: string`, `switch: bool (optional — renders as toggle switch)` |
| **Replaces** | Current `components/form/checkbox.blade.php` |
| **Priority** | Phase 1 |

### `<x-form.toggle>`

| Field | Value |
|---|---|
| **Purpose** | Toggle switch (Alpine-powered). For boolean fields like is_active, is_monitoring_active. |
| **Props** | `name: string`, `label: string`, `:checked: bool`, `help: string`, `color: string (default: primary)` |
| **Accessibility** | Keyboard toggle, aria-checked, role="switch" |
| **Replaces** | Inline toggle implementations (currently none exist, would be new for consistency) |
| **Priority** | Phase 2 |

### `<x-form.password>`

| Field | Value |
|---|---|
| **Purpose** | Password field with reveal toggle, strength indicator, and reveal-logging awareness. |
| **Props** | `name: string`, `label: string`, `:value: mixed`, `required: bool`, `revealable: bool (default: true)`, `copyable: bool (optional)`, `placeholder: string` |
| **Behavior** | Masked by default. Reveal button toggles visibility. Reveal event dispatch for activity logging. Copy button copies to clipboard. Auto-hide after 3 seconds on reveal (optional). |
| **Replaces** | Ad-hoc password fields with inline JS toggle across all modules |
| **Priority** | Phase 1 |

### `<x-form.date>`

| Field | Value |
|---|---|
| **Purpose** | Date input with consistent UX. Wraps native `<input type="date">` with styling. |
| **Props** | `name: string`, `label: string`, `:value: Carbon|string|null`, `required: bool`, `min: string (optional)`, `max: string (optional)`, `help: string` |
| **Replaces** | Manual date inputs across all create/edit forms |
| **Priority** | Phase 2 |

### `<x-form.file>`

| Field | Value |
|---|---|
| **Purpose** | File upload input with drag-and-drop zone. |
| **Props** | `name: string`, `label: string`, `accept: string`, `maxSize: string`, `multiple: bool`, `help: string` |
| **States** | Default, Dragging (highlight zone), Uploading (progress bar), Success (file name shown), Error (size/type) |
| **Replaces** | Current file upload in attachment forms |
| **Priority** | Phase 4 |

### `<x-form.error>`

| Field | Value |
|---|---|
| **Purpose** | Validation error display. |
| **Props** | `name: string`, `:bag: string (optional)` |
| **Replaces** | `@error($name)` directives scattered in forms |
| **Priority** | Phase 2 |

### `<x-form.help>`

| Field | Value |
|---|---|
| **Purpose** | Helper text below form fields. |
| **Props** | `text: string`, `icon: string (optional)` |
| **Priority** | Phase 2 |

---

## 3.5 Feedback Components

### `<x-alert>`

| Field | Value |
|---|---|
| **Purpose** | Alert banner for page-level feedback messages. |
| **Props** | `type: success|error|warning|info (default: info)`, `message: string`, `dismissible: bool (default: true)`, `icon: bool (default: true)` |
| **Slots** | `message`, `actions` |
| **States** | Visible, Hiding (dismiss animation), Hidden |
| **Replaces** | Session flash messages rendered inline in admin layout + auth pages |
| **Priority** | Phase 1 |

### `<x-toast>`

| Field | Value |
|---|---|
| **Purpose** | Toast notification. Should be managed by a Toast component/container in app-shell. |
| **Props** | `type: success|error|warning|info`, `message: string`, `duration: int (ms, default: 5000)`, `position: top-right|top-left|bottom-right|bottom-left (default: top-right)` |
| **States** | Entering, Visible, Exiting (auto-dismiss) |
| **Replaces** | Inline toast system in admin.blade.php |
| **Priority** | Phase 1 |

### `<x-loading>`

| Field | Value |
|---|---|
| **Purpose** | Page-level loading overlay or inline loading indicator. |
| **Props** | `:loading: bool`, `message: string (default: 'Loading...')`, `fullscreen: bool (optional)`, `inline: bool (optional)` |
| **Replaces** | Loading overlay in admin.blade.php |
| **Priority** | Phase 2 |

### `<x-skeleton>`

| Field | Value |
|---|---|
| **Purpose** | Skeleton loading placeholder for content that hasn't loaded yet. |
| **Props** | `type: text|card|table|circle|badge`, `:rows: int (for table, default: 3)`, `:columns: int (for table, default: 4)`, `class: string` |
| **States** | Animated (pulsing), Static (no animation — for print) |
| **Replaces** | None — new component (currently no loading states exist) |
| **Priority** | Phase 2 |

### `<x-confirm-dialog>`

| Field | Value |
|---|---|
| **Purpose** | Confirmation dialog for destructive actions. Extends modal with confirmation pattern. |
| **Props** | `open: bool`, `title: string`, `message: string`, `confirmText: string (default: 'Confirm')`, `cancelText: string (default: 'Cancel')`, `variant: danger|warning|info (default: danger)`, `:loading: bool (optional)`, `@confirm: event`, `@cancel: event` |
| **Slots** | `message` (custom message), `actions` |
| **Replaces** | Inline confirmation dialog in admin.blade.php |
| **Priority** | Phase 1 |

### `<x-progress>`

| Field | Value |
|---|---|
| **Purpose** | Progress bar for long operations (exports, imports). |
| **Props** | `:value: int (percentage)`, `size: sm|md (default: sm)`, `color: string (default: primary)`, `showLabel: bool (optional)` |
| **Replaces** | None — new component for import/export UX |
| **Priority** | Phase 4 |

---

## 3.6 Dashboard Components

### `<x-stat-card>`

| Field | Value |
|---|---|
| **Purpose** | Dashboard statistic card with animated counter. Extends current component. |
| **Props** | `label: string`, `:value: mixed`, `icon: string`, `color: indigo|emerald|amber|rose|sky|violet (default: indigo)`, `:trend: float (optional — percentage change)`, `trendLabel: string (optional)`, `href: string (optional)` |
| **Slots** | `value` (custom value rendering) |
| **Behavior** | Animated number count via `IntersectionObserver` + `requestAnimationFrame` (preserve from admin layout) |
| **Replaces** | Current `components/stat-card.blade.php` |
| **Priority** | Phase 1 |

### `<x-widget-card>`

| Field | Value |
|---|---|
| **Purpose** | Dashboard widget card with header, content, and optional footer. |
| **Props** | `title: string`, `subtitle: string (optional)`, `:badge: string (optional)`, `:loading: bool (optional)` |
| **Slots** | `default`, `header` (right header area), `footer` |
| **Replaces** | Dashboard `@include('dashboard.widgets.*')` pattern. Provides a proper component interface. |
| **Priority** | Phase 1 |

### `<x-chart-card>`

| Field | Value |
|---|---|
| **Purpose** | Dashboard card specifically for Chart.js charts. |
| **Props** | `title: string`, `chartId: string`, `:labels: array`, `:values: array`, `type: doughnut|bar (default: doughnut)`, `height: string (default: 64)` |
| **Replaces** | Manual chart `<canvas>` + JS initialization in dashboard |
| **Priority** | Phase 2 |

### `<x-activity-widget>`

| Field | Value |
|---|---|
| **Purpose** | Recent activity list for dashboard. |
| **Props** | `:activities: Collection`, `max: int (default: 5)` |
| **Replaces** | Dashboard widget includes for activity |
| **Priority** | Phase 2 |

### `<x-renewal-widget>`

| Field | Value |
|---|---|
| **Purpose** | Upcoming renewals list for dashboard. |
| **Props** | `:renewals: Collection`, `max: int (default: 5)`, `:showViewAll: bool (default: true)` |
| **Replaces** | Dashboard widget includes for renewals |
| **Priority** | Phase 2 |

---

## 3.7 Special Business Components

### `<x-activity-timeline>`

| Field | Value |
|---|---|
| **Purpose** | Activity log timeline for entity show pages. Refines the current component. |
| **Props** | `:activities: Collection`, `:subject: Model (optional — auto-fetches activities)`, `max: int (optional)`, `filter: string (optional — event name to filter by)`, `dense: bool (optional)` |
| **Slots** | `item` (custom timeline item rendering) |
| **Replaces** | Current `components/activity-timeline.blade.php` |
| **Priority** | Phase 2 |

### `<x-renewal-source-card>`

| Field | Value |
|---|---|
| **Purpose** | Card showing linked renewal source info. Displays sync status and "View Source Service" link. |
| **Props** | `:tracker: ExpiryTracker` |
| **Behavior** | Shows link banner if `trackable_type` is set. Dynamically builds edit route based on trackable type. |
| **Replaces** | Manual `@if($isLinked)` block in `expiry-trackers/show.blade.php` (lines 37-53) |
| **Priority** | Phase 3 |

### `<x-password-reveal>`

| Field | Value |
|---|---|
| **Purpose** | Inline password display with reveal button. Used in show pages for entities with passwords. |
| **Props** | `:value: string (encrypted)`, `revealUrl: string (route)`, `:entity: Model (for activity logging)`, `copyable: bool (default: true)`, `timeout: int (seconds to auto-hide, default: 3)` |
| **Behavior** | Shows masked by default. Reveal triggers AJAX call. Logs reveal in activity log. Auto-hides after timeout. Copy button copies to clipboard. |
| **Replaces** | Ad-hoc password reveal implementations across all modules |
| **Priority** | Phase 1 |

### `<x-permission-matrix>`

| Field | Value |
|---|---|
| **Purpose** | The full permission management UI (table of modules with access levels). Wraps the existing Alpine.js components in a consistent container. |
| **Props** | `:modules: array`, `:categories: array`, `:saveUrl: string`, `:backUrl: string`, `:userName: string`, `:userRole: string`, `:userId: int`, `:sensitivePerms: array` |
| **Slots** | Summary, filters, module rows, inline editor |
| **Replaces** | Main permissions page view + all 11 permissions sub-components (gradually, keep Alpine JS) |
| **Priority** | Phase 3 |

### `<x-notification-dropdown>`

| Field | Value |
|---|---|
| **Purpose** | Notification dropdown in the global header. |
| **Props** | `:notifications: Collection`, `:unreadCount: int`, `pollUrl: string (optional)`, `pollInterval: int (seconds, default: 30)` |
| **Replaces** | Notification bell in admin layout |
| **Priority** | Phase 2 |

### `<x-command-palette>`

| Field | Value |
|---|---|
| **Purpose** | Ctrl+K / Cmd+K command palette. Wraps existing implementation. |
| **Props** | `searchUrl: string (route)`, `:pages: array` |
| **Replaces** | Command palette in admin layout |
| **Priority** | Phase 2 |

### `<x-attachment-list>`

| Field | Value |
|---|---|
| **Purpose** | Polymorphic attachment list for entity show pages. |
| **Props** | `:attachments: Collection`, `:entity: Model (optional)`, `uploadable: bool (default: true)`, `uploadUrl: string (optional)`, `deletable: bool (default: true)` |
| **Replaces** | Attachment sections in entity show pages |
| **Priority** | Phase 4 |

### `<x-note-list>`

| Field | Value |
|---|---|
| **Purpose** | Polymorphic note list for entity show pages. |
| **Props** | `:notes: Collection`, `:entity: Model (optional)`, `creatable: bool (default: true)`, `deletable: bool (default: true)` |
| **Replaces** | Note sections in entity show pages |
| **Priority** | Phase 4 |

---

## 3.8 Component Priority Map

| Phase | Components | Rationale |
|---|---|---|
| Phase 1 | x-card, x-badge, x-status-badge, x-field, x-date, x-table, x-empty-state, x-modal, x-confirm-dialog, x-form.input/select/textarea/checkbox, x-form.password, x-alert, x-toast, x-stat-card, x-password-reveal, x-page-header | Highest ROI. Eliminates 80% of UI duplication. |
| Phase 2 | x-filter-bar, x-search-input, x-pagination, x-toolbar, x-section, x-money, x-skeleton, x-loading, x-user-badge, x-permission-badge, x-form.toggle, x-form.date, x-form.error, x-form.help, x-activity-timeline, x-notification-dropdown, x-command-palette, x-widget-card, x-chart-card, x-activity-widget, x-renewal-widget | Medium ROI. Fills gaps in existing patterns. |
| Phase 3 | x-renewal-badge, x-renewal-source-card, x-permission-matrix, x-permission-badge | Module-specific. Only needed for Renewal Center and Permissions pages. |
| Phase 4 | x-drawer, x-tabs, x-form.file, x-progress, x-attachment-list, x-note-list | Nice-to-have. Fills remaining gaps. |
| Phase 5 | x-app-shell | High risk, high reward. Replaces the entire layout. |

**Total components: ~50 (27 existing + 23 new specifications above)**

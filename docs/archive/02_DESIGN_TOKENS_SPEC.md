# 2. Design Tokens Specification

> Define the complete design token system for the Enterprise Design System.

---

## 2.1 Color System

### Brand Colors

| Token | Tailwind Class | Light Value | Dark Value | Usage |
|---|---|---|---|---|
| `--color-primary` | `indigo-500` | `#6366f1` | `#818cf8` | Buttons, links, active states |
| `--color-primary-hover` | `indigo-600` | `#4f46e5` | `#6366f1` | Button hover, link hover |
| `--color-primary-subtle` | `indigo-50` | `#eef2ff` | `rgba(99,102,241,0.1)` | Badge bg, selected row bg |
| `--color-secondary` | `purple-500` | `#a855f7` | `#c084fc` | Accent, gradient partner |
| `--color-secondary-hover` | `purple-600` | `#9333ea` | `#a855f7` | Gradient hover |

### Status Colors

| Token | Tailwind Class | Light Value | Dark Value | Usage |
|---|---|---|---|---|
| `--color-success` | `emerald-500` | `#10b981` | `#34d399` | Active status, success toasts |
| `--color-success-subtle` | `emerald-50` | `#ecfdf5` | `rgba(16,185,129,0.1)` | Status badge bg |
| `--color-success-text` | `emerald-700` | `#047857` | `#6ee7b7` | Status badge text |
| `--color-warning` | `amber-500` | `#f59e0b` | `#fbbf24` | Suspended status, warning toasts |
| `--color-warning-subtle` | `amber-50` | `#fffbeb` | `rgba(245,158,11,0.1)` | Status badge bg |
| `--color-warning-text` | `amber-700` | `#b45309` | `#fde68a` | Status badge text |
| `--color-danger` | `red-500` | `#ef4444` | `#f87171` | Expired status, error toasts, delete |
| `--color-danger-subtle` | `red-50` | `#fef2f2` | `rgba(239,68,68,0.1)` | Status badge bg |
| `--color-danger-text` | `red-700` | `#b91c1c` | `#fca5a5` | Status badge text |
| `--color-info` | `blue-500` | `#3b82f6` | `#60a5fa` | Info toasts, help text |
| `--color-info-subtle` | `blue-50` | `#eff6ff` | `rgba(59,130,246,0.1)` | Info badge bg |

### Neutral / Surface

| Token | Tailwind Class | Light Value | Dark Value | Usage |
|---|---|---|---|---|
| `--color-surface` | `white` | `#ffffff` | `#000000` | Card bg, page bg |
| `--color-surface-secondary` | `gray-50` | `#f9fafb` | `#0a0a0a` | Table header, sidebar |
| `--color-surface-elevated` | `white` | `#ffffff` | `#111111` | Dropdown bg, modal bg |
| `--color-border` | `gray-200` | `#e5e7eb` | `rgba(255,255,255,0.08)` | Card borders, dividers |
| `--color-border-hover` | `gray-300` | `#d1d5db` | `rgba(255,255,255,0.12)` | Input hover border |
| `--color-text-primary` | `gray-900` | `#111827` | `#f3f4f6` | Primary text |
| `--color-text-secondary` | `gray-500` | `#6b7280` | `#9ca3af` | Secondary/muted text |
| `--color-text-tertiary` | `gray-400` | `#9ca3af` | `#6b7280` | Placeholder text |
| `--color-text-link` | `indigo-600` | `#4f46e5` | `#818cf8` | Links |
| `--color-text-link-hover` | `indigo-800` | `#3730a3` | `#a5b4fc` | Link hover |

### Special-Purpose Tokens

| Token | Light Value | Dark Value | Usage |
|---|---|---|---|
| `--color-glass-bg` | `rgba(255,255,255,0.7)` | `rgba(0,0,0,0.85)` | Glass card backgrounds |
| `--color-glass-border` | `rgba(255,255,255,0.8)` | `rgba(99,102,241,0.08)` | Glass card borders |
| `--color-glass-shadow` | `rgba(0,0,0,0.04)` | `rgba(0,0,0,0.3)` | Glass card shadows |
| `--color-overlay` | `rgba(15,23,42,0.5)` | `rgba(0,0,0,0.7)` | Modal backdrops |
| `--color-selection` | `#6366f1` | `#6366f1` | Text selection highlight |

### Renewal Status Colors

| Status | Badge Token | Light Text | Light Bg | Dark Text | Dark Bg |
|---|---|---|---|---|---|
| Active | `--renewal-status-active` | `emerald-700` | `emerald-50` | `emerald-300` | `rgba(16,185,129,0.15)` |
| Pending | `--renewal-status-pending` | `amber-700` | `amber-50` | `amber-300` | `rgba(245,158,11,0.15)` |
| Expired | `--renewal-status-expired` | `red-700` | `red-50` | `red-300` | `rgba(239,68,68,0.15)` |
| Cancelled | `--renewal-status-cancelled` | `gray-600` | `gray-100` | `gray-400` | `rgba(156,163,175,0.15)` |
| Auto-synced | `--renewal-status-linked` | `lime-700` | `lime-50` | `lime-300` | `rgba(132,204,22,0.15)` |

### Permission UI Colors

| Token | Light Value | Dark Value | Usage |
|---|---|---|---|
| `--perm-inherited` | `#94a3b8` | `#64748b` | Inherited permission text |
| `--perm-override` | `#b45309` | `#fbbf24` | Modified permission indicator |
| `--perm-sensitive` | `#dc2626` | `#f87171` | Sensitive permission warning |
| `--perm-modified-bg` | `#fffbeb` | `rgba(245,158,11,0.08)` | Modified module row bg |
| `--perm-manage` | `#1e40af` | `#93c5fd` | "Manage" badge |
| `--perm-no-access` | `#64748b` | `#94a3b8` | "No Access" badge |
| `--perm-view-only` | `#1e40af` | `#93c5fd` | "View Only" badge |
| `--perm-custom` | `#6b21a8` | `#d8b4fe` | "Custom" badge |

### Entity Type Colors (for timeline, badges, source type)

| Entity | Token | Light | Dark |
|---|---|---|---|
| Domain | `--entity-domain` | `blue-600` | `#60a5fa` |
| Hosting | `--entity-hosting` | `emerald-600` | `#34d399` |
| VPS | `--entity-vps` | `violet-600` | `#a78bfa` |
| VoIP | `--entity-voip` | `cyan-600` | `#22d3ee` |
| Domain Email | `--entity-email` | `pink-600` | `#f472b6` |
| Other Service | `--entity-service` | `orange-600` | `#fb923c` |
| Service Provider | `--entity-provider` | `slate-600` | `#94a3b8` |
| Vault | `--entity-vault` | `amber-600` | `#fbbf24` |
| Asset | `--entity-asset` | `teal-600` | `#2dd4bf` |
| Task | `--entity-task` | `rose-600` | `#fb7185` |

---

## 2.2 Typography

### Font Family

| Token | Value |
|---|---|
| `--font-sans` | `'Instrument Sans', ui-sans-serif, system-ui, sans-serif` |
| `--font-mono` | `'JetBrains Mono', 'Fira Code', ui-monospace, monospace` |

*Note: Instrument Sans is already loaded from bunny.net. Mono font may be added for code/technical fields.*

### Font Size Scale

| Token | Size | Line Height | Usage |
|---|---|---|---|
| `--text-xs` | 0.75rem (12px) | 1rem | Badge text, helper text, table cell |
| `--text-sm` | 0.875rem (14px) | 1.25rem | Body text, input text, button text |
| `--text-base` | 1rem (16px) | 1.5rem | Large body text |
| `--text-lg` | 1.125rem (18px) | 1.75rem | Section titles |
| `--text-xl` | 1.25rem (20px) | 1.75rem | Page titles |
| `--text-2xl` | 1.5rem (24px) | 2rem | Dashboard stat values |
| `--text-3xl` | 1.875rem (30px) | 2.25rem | Hero stats |

### Font Weight

| Token | Value | Usage |
|---|---|---|
| `--weight-normal` | 400 | Body text |
| `--weight-medium` | 500 | Labels, table headers |
| `--weight-semibold` | 600 | Section titles, button text |
| `--weight-bold` | 700 | Page titles, stat values |

### Specific Text Styles

| Element | Token | Size | Weight | Color |
|---|---|---|---|---|
| Page title | `--text-page-title` | xl | bold | `--color-text-primary` |
| Page subtitle | `--text-page-subtitle` | sm | normal | `--color-text-secondary` |
| Section title | `--text-section-title` | lg | semibold | `--color-text-primary` |
| Card title | `--text-card-title` | sm | semibold | `--color-text-primary` |
| Table header | `--text-table-header` | xs | semibold | `--color-text-secondary` |
| Table cell | `--text-table-cell` | sm | normal | `--color-text-primary` |
| Label (form) | `--text-form-label` | sm | medium | `--color-text-primary` |
| Helper text | `--text-form-helper` | xs | normal | `--color-text-tertiary` |
| Input text | `--text-form-input` | sm | normal | `--color-text-primary` |
| Button text | `--text-button` | sm | medium | varies |
| Badge text | `--text-badge` | xs | medium | varies |
| Stat value | `--text-stat-value` | 2xl | bold | `--color-text-primary` |
| Stat label | `--text-stat-label` | sm | normal | `--color-text-secondary` |
| Breadcrumb | `--text-breadcrumb` | xs | normal | `--color-text-tertiary` |
| Modal title | `--text-modal-title` | lg | semibold | `--color-text-primary` |
| Timeline event | `--text-timeline-event` | sm | normal | `--color-text-primary` |
| Timeline time | `--text-timeline-time` | xs | normal | `--color-text-tertiary` |

---

## 2.3 Spacing

| Token | Value | Usage |
|---|---|---|
| `--space-page` | 1.5rem (24px) | Page padding (x-axis) |
| `--space-page-y` | 1.5rem (24px) | Page padding (y-axis) |
| `--space-card` | 1.5rem (24px) | Card body padding |
| `--space-card-header` | 1rem (16px) | Card header padding |
| `--space-form-group` | 1rem (16px) | Between form fields |
| `--space-form-section` | 1.5rem (24px) | Between form sections |
| `--space-table-cell` | 0.75rem (12px) | Table cell padding (y) |
| `--space-table-cell-x` | 1rem (16px) | Table cell padding (x) |
| `--space-section` | 2rem (32px) | Between page sections |
| `--space-dashboard-gap` | 1.5rem (24px) | Dashboard grid gap |
| `--space-modal` | 1.5rem (24px) | Modal body padding |
| `--space-modal-header` | 1.25rem (20px) | Modal header padding |
| `--space-stack` | 0.75rem (12px) | Stacked elements gap |
| `--space-inline` | 0.5rem (8px) | Inline elements gap |
| `--space-badge` | 0.125rem 0.5rem (2px 8px) | Badge inner padding |

---

## 2.4 Border Radius

| Token | Value | Usage |
|---|---|---|
| `--radius-none` | 0px | — |
| `--radius-sm` | 0.375rem (6px) | Small elements, tags |
| `--radius-md` | 0.5rem (8px) | Inputs, selects, buttons |
| `--radius-lg` | 0.75rem (12px) | Cards, modals |
| `--radius-xl` | 1rem (16px) | Large cards, dialogs |
| `--radius-full` | 9999px | Badges, pills |

*Note: The current codebase uses `rounded-xl` for cards (which in Tailwind v4 is 0.75rem). The spec above standardizes at `--radius-lg = 0.75rem` for cards.*

---

## 2.5 Shadow / Elevation

| Token | Light Value | Dark Value | Usage |
|---|---|---|---|
| `--shadow-card` | `0 1px 3px rgba(0,0,0,0.08)` | `0 1px 3px rgba(0,0,0,0.3)` | Default card |
| `--shadow-card-hover` | `0 20px 40px -12px rgba(99,102,241,0.12)` | `0 20px 40px -12px rgba(0,0,0,0.5)` | Card hover |
| `--shadow-dropdown` | `0 10px 30px rgba(0,0,0,0.12)` | `0 10px 30px rgba(0,0,0,0.4)` | Dropdown menu |
| `--shadow-modal` | `0 20px 60px rgba(0,0,0,0.2)` | `0 20px 60px rgba(0,0,0,0.5)` | Modal dialog |
| `--shadow-toast` | `0 10px 30px rgba(0,0,0,0.15)` | `0 10px 30px rgba(0,0,0,0.4)` | Toast notification |

### Focus Ring

| Token | Value | Usage |
|---|---|---|
| `--focus-ring` | `0 0 0 3px rgba(99,102,241,0.15)` | All interactive elements |
| `--focus-ring-dark` | `0 0 0 3px rgba(129,140,248,0.2)` | Dark mode focus |

---

## 2.6 Animation

| Token | Duration | Easing | Usage |
|---|---|---|---|
| `--duration-fast` | 150ms | ease | Hover states, color transitions |
| `--duration-normal` | 200ms | ease | Show/hide, toggle |
| `--duration-slow` | 300ms | cubic-bezier(0.16,1,0.3,1) | Modal enter, sidebar |
| `--duration-toast` | 350ms | cubic-bezier(0.16,1,0.3,1) | Toast enter/exit |
| `--duration-page` | 400ms | ease-out | Page content fade-in |

### Keyframe Animations (to preserve from current app.css)

| Name | Duration | Usage |
|---|---|---|
| `toast-in` | 350ms | Toast slide in |
| `toast-out` | 250ms | Toast slide out |
| `fade-in` | 400ms | General fade in |
| `fade-in-up` | 500ms | Page content |
| `scale-in` | 250ms | Modal, dropdown |
| `count-up` | 600ms | Stat counter |
| `pulse` | 1.5s | Unsaved dot |
| `skeleton-pulse` | 1.5s | NEW — skeleton loading |

---

## 2.7 Density

The system supports two density modes:

### Comfortable (Default)

| Element | Spacing |
|---|---|
| Table cell padding | `py-3 px-6` |
| Form spacing | `space-y-5` |
| Card padding | `p-6` |
| Section spacing | `mb-6` |

### Compact (Table Dense Mode)

| Element | Spacing |
|---|---|
| Table cell padding | `py-1.5 px-4` |
| Form spacing | `space-y-3` |
| Badge size | `text-2xs px-1.5 py-0.5` |

---

## 2.8 Implementing Tokens

In Tailwind v4, tokens are defined in `app.css` via `@theme` directive:

```css
@theme {
    /* Colors */
    --color-status-active: #10b981;
    --color-status-expired: #ef4444;
    --color-status-suspended: #f59e0b;
    --color-status-cancelled: #6b7280;

    /* Typography */
    --font-mono: 'JetBrains Mono', ui-monospace, monospace;

    /* Custom spacing if needed beyond default scale */
}

/* Custom CSS custom properties for JS access */
:root {
    --duration-toast-in: 350ms;
    --duration-toast-out: 250ms;
}

/* Dark mode variant tokens */
.dark {
    --color-surface: #000000;
}
```

**Goal: Eliminate all hardcoded `text-*`, `bg-*`, `border-*` color classes in Blade files. Replace with semantic tokens like `bg-status-active` or `text-status-expired`.**

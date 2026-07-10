# OpsPilot — Portal Design System Audit

> Completed: 2026-06-27
> Source: Full frontend audit of Blade components, layouts, CSS, and templates.

---

## 1. COLOR SYSTEM

### 1.1 Primary Palette

| Role | Tailwind Class | HEX | RGB |
|------|---------------|-----|-----|
| **Primary** | `indigo-500` | `#6366f1` | `99, 102, 241` |
| **Primary Dark** | `indigo-600` | `#4f46e5` | `79, 70, 229` |
| **Primary Light** | `indigo-50` | `#eef2ff` | `238, 242, 255` |
| **Secondary** | `purple-500` | `#a855f7` | `168, 85, 247` |
| **Secondary Dark** | `purple-600` | `#9333ea` | `147, 51, 234` |
| **Secondary Light** | `purple-50` | `#faf5ff` | `250, 245, 255` |

### 1.2 Semantic Palette

| Role | Tailwind Class | HEX | RGB | Usage |
|------|---------------|-----|-----|-------|
| **Success** | `emerald-500` | `#10b981` | `16, 185, 129` | Task badge, active status, success toasts |
| **Success Dark** | `emerald-600` | `#059669` | `5, 150, 105` | Button gradient (→ emerald-600 → teal-600) |
| **Warning** | `amber-500` | `#f59e0b` | `245, 158, 11` | Updated changes warning, expiry badges, warning toasts |
| **Danger** | `red-500` | `#ef4444` | `239, 68, 68` | Delete confirmations, error states |
| **Danger Dark** | `red-600` | `#dc2626` | `220, 38, 38` | Button gradient (→ red-600 → rose-600) |
| **Rose** | `rose-500/600` | `#f43f5e`/`#e11d48` | `244, 63, 94` | Danger button gradient end, SMTP widget |
| **Info** | `sky-500` | `#0ea5e9` | `14, 165, 233` | Info toasts, asset widget heading |
| **Teal** | `teal-500/600` | `#14b8a6`/`#0d9488` | `20, 184, 166` | Success button gradient end |
| **Violet** | `violet-500` | `#8b5cf6` | `139, 92, 246` | Operations widget heading, stat card variant |

### 1.3 Neutral Palette

| Role | Light (Tailwind) | Light (HEX) | Dark (Tailwind) | Dark (HEX) |
|------|-----------------|-------------|-----------------|-----------|
| **Page bg** | `gray-50` | `#f8fafc` | `#000000` (pure black) | `#000000` |
| **Surface** | `white` | `#ffffff` | `black` | `#000000` |
| **Card bg** | `white` | `#ffffff` | `black` | `#000000` |
| **Sub-bg** | `gray-50`/`gray-100` | `#f9fafb`/`#f3f4f6` | `black/50` | `rgba(0,0,0,0.5)` |
| **Primary text** | `gray-900` | `#111827` | `gray-100` | `#f3f4f6` |
| **Secondary text** | `gray-500` | `#6b7280` | `gray-400` | `#9ca3af` |
| **Muted text** | `gray-400` | `#9ca3af` | `gray-500` | `#6b7280` |
| **Border** | `gray-200`/`gray-300` | `#e5e7eb`/`#d1d5db` | `gray-600`/`gray-700` | `#4b5563`/`#374151` |
| **Sidebar border** | `gray-200/50` | — | `gray-800/50` | — |
| **Table border** | `gray-200` | `#e5e7eb` | `gray-700` | `#374151` |

### 1.4 CSS Variables
Tailwind v4 with no custom CSS variables defined in `@theme`. The only `@theme` directive:
```css
@theme {
    --font-sans: 'Instrument Sans', ui-sans-serif, system-ui, sans-serif;
}
```
All colors use Tailwind default utility classes directly.

---

## 2. GRADIENTS

### 2.1 Signature Brand Gradient — `indigo → purple`
```
from-indigo-600 to-purple-600  (#4f46e5 → #9333ea)
```
**Used in:** Sidebar header, primary buttons, dashboard icon boxes (activity, quick actions, vault, page headers), avatar initials box, login logo box, confirm modal, toast icon boxes

**Hover variant:** `hover:from-indigo-700 hover:to-purple-700` (`#4338ca → #7e22ce`)

### 2.2 Status Gradients

| Gradient | Light | Dark | Usage |
|----------|-------|------|-------|
| `from-red-600 to-rose-600` | `#dc2626 → #e11d48` | — | Danger buttons, notification badge, delete confirm |
| `from-emerald-600 to-teal-600` | `#059669 → #0d9488` | — | Success buttons |
| `from-violet-500 to-purple-600` | `#8b5cf6 → #9333ea` | — | Operations dashboard widget |
| `from-emerald-500 to-teal-600` | `#10b981 → #0d9488` | — | Tasks dashboard widget |
| `from-sky-500 to-blue-600` | `#0ea5e9 → #2563eb` | — | Assets dashboard widget |
| `from-amber-500 to-orange-600` | `#f59e0b → #ea580c` | — | Renewals dashboard widget |
| `from-gray-500 to-slate-600` | `#6b7280 → #475569` | — | Server Health dashboard widget |
| `from-rose-500 to-pink-600` | `#f43f5e → #db2777` | — | SMTP dashboard widget |
| `from-red-500 to-rose-600` | `#ef4444 → #e11d48` | — | Unread notification badge |

### 2.3 Background / Hover Gradients

| Gradient | Usage |
|----------|-------|
| `from-indigo-50 to-purple-50/50` | Active nav link background (light) |
| `from-indigo-900/25 to-purple-900/10` | Active nav link background (dark) |
| `from-indigo-50 to-purple-50` | User profile footer, sidebar bottom (light) |
| `from-indigo-900/20 to-purple-900/20` | User profile footer, sidebar bottom (dark) |
| `from-gray-50 to-indigo-50/50` | Bulk actions bar (light) |
| `from-gray-800/50 to-indigo-900/10` | Bulk actions bar (dark) |
| `linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%)` | Table header background (light) |
| `from-indigo-500/10 to-purple-500/5` | Stat card (indigo variant, light) |
| `from-indigo-500/15 to-purple-500/5` | Stat card (indigo variant, dark) |
| `linear-gradient(rgba(99,102,241,0.03) 1px, transparent 1px)` | Page `.bg-grid` pattern (light) |
| `linear-gradient(rgba(255,255,255,0.012) 1px, transparent 1px)` | Page `.bg-grid` pattern (dark) |

### 2.4 Nav Link Active Indicator
```
background: linear-gradient(to bottom, #6366f1, #4f46e5);
```
3px left-border accent bar revealed on active nav links via `nav-link-active`.

### 2.5 Quick Action Buttons (per action type)
| Action | Gradient |
|--------|----------|
| Feature/Module | `from-indigo-50 to-purple-50` |
| User | `from-sky-50 to-blue-50` |
| Task | `from-emerald-50 to-teal-50` |
| Domain/Asset | `from-sky-50 to-blue-50` |
| Hosting/VPS/VoIP | `from-violet-50 to-purple-50` |
| Vault | `from-amber-50 to-orange-50` |

---

## 3. TYPOGRAPHY

### 3.1 Font Family
```css
'Instrument Sans', ui-sans-serif, system-ui, sans-serif,
'Apple Color Emoji', 'Segoe UI Emoji', 'Segoe UI Symbol', 'Noto Color Emoji'
```
**Source:** `https://fonts.bunny.net/css?family=instrument-sans:400,500,600`
**Weights loaded:** 400 (regular), 500 (medium), 600 (semibold)
**Loading strategy:** `media="print" onload="this.media='all'"` with `<noscript>` fallback

### 3.2 Type Scale

| Token | Size | Weight | Usage |
|-------|------|--------|-------|
| `text-[10px]` | 10px | `font-semibold` | Section headers, badge counts, kbd labels |
| `text-[11px]` | 11px | `font-semibold` uppercase `tracking-widest` | Form labels, stat labels, section dividers |
| `text-xs` | 12px | `font-medium` / `font-semibold` | Badges, secondary info, sub-labels |
| `text-sm` | 14px | `font-medium` / `font-semibold` | Body text, card titles, buttons, nav links |
| `text-base` | 16px | `font-semibold` | Modal headings |
| `text-lg` | 18px | `font-semibold` `tracking-tight` | Sidebar app title |
| `text-xl` | 20px | `font-bold` | Login page heading |
| `text-2xl` | 24px | `font-bold` `tracking-tight` | Page `<h1>` titles, stat values |
| `text-8xl` | 96px | `font-bold` | Error page status codes (403, 404, 419, 500) |

### 3.3 Heading Hierarchy

| Level | Class | Line Height | Color |
|-------|-------|------------|-------|
| Page `<h1>` | `text-2xl font-bold` | Tailwind default | `text-gray-900 dark:text-white` |
| Card title `<h2>` | `text-sm font-semibold` | Tailwind default | `text-gray-700 dark:text-gray-300` |
| Modal title `<h3>` | `text-base font-semibold` | Tailwind default | `text-gray-900 dark:text-white` |
| Section label | `text-[11px] font-semibold uppercase tracking-widest` | Tailwind default | `text-gray-500 dark:text-gray-400` |
| Stat label | `text-[11px] font-semibold uppercase tracking-widest` | Tailwind default | `text-gray-500 dark:text-gray-400` |
| Stat value | `text-2xl font-bold tracking-tight` | Tailwind default | `text-gray-900 dark:text-white` |
| Sidebar section | `text-[10px] font-semibold uppercase tracking-widest` | Tailwind default | `text-gray-500 dark:text-gray-400` |
| Login heading | `text-xl font-bold` | Tailwind default | `text-gray-900 dark:text-white` |

### 3.4 Special Typography
- **Breadcrumbs:** `text-sm text-gray-500`, last item `text-gray-900 font-medium`
- **Activity description:** `text-sm font-semibold text-gray-900`
- **Field diffs:** `text-xs`, old value `text-gray-400 line-through`, new value `font-medium text-gray-700`
- **Error messages:** `text-xs text-red-500`
- **Empty state:** `text-sm font-semibold text-gray-500` (title), `text-xs text-gray-400` (message)
- **Command palette:** `text-[10px] font-semibold tracking-widest` for section, `text-sm` for items

---

## 4. BUTTON DESIGN

### 4.1 Base
```css
.inline-flex items-center justify-center gap-1.5 font-medium
transition-all duration-200 whitespace-nowrap cursor-pointer
```

### 4.2 Variants

| Variant | Classes |
|---------|---------|
| **Primary** | `bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white shadow-sm shadow-indigo-500/20 hover:shadow-indigo-500/30` |
| **Danger** | `bg-gradient-to-r from-red-600 to-rose-600 hover:from-red-700 hover:to-rose-700 text-white shadow-sm shadow-red-500/20 hover:shadow-red-500/30` |
| **Success** | `bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white shadow-sm shadow-emerald-500/20 hover:shadow-emerald-500/30` |
| **Outline** | `border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-800 hover:border-gray-400 dark:hover:border-gray-500` |
| **Ghost** | `text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 hover:bg-gray-100 dark:hover:bg-gray-800` |

### 4.3 Sizes

| Size | Classes |
|------|---------|
| **sm** | `px-3 py-1.5 text-xs rounded-xl` |
| **md** | `px-4 py-2 text-sm rounded-xl` |
| **lg** | `px-6 py-2.5 text-sm rounded-xl font-semibold` |

### 4.4 States

| State | Effect |
|-------|--------|
| **Hover** | Darker gradient / lighter bg / border shift |
| **Active/Press** | `active:scale-[0.98]` (scale down 2%) |
| **Focus** | Browser default outline (managed via `focus:outline-none` + `focus:ring-2` on specific instances) |
| **Disabled** | `opacity-50 cursor-not-allowed` |
| **Loading** | `opacity-70 pointer-events-none` + prepended spinner `<span class="inline-block w-3.5 h-3.5 border-2 border-white/30 border-t-white rounded-full animate-spin">` |

### 4.5 Action Tags (`<x-action>`)
- **Radius:** `rounded-xl`
- **Size:** `px-2 py-1 text-xs font-medium`
- **Colors:** indigo, amber, red, emerald, sky
- **Style:** Soft bg with matching text, hover intensifies
- **Pattern:** `bg-{color}-50 dark:bg-{color}-900/20 text-{color}-700 dark:text-{color}-300 hover:bg-{color}-100`

---

## 5. INPUT DESIGN

### 5.1 Base Input
```css
w-full rounded-xl border bg-white dark:bg-black text-gray-900 dark:text-gray-100
px-3 py-2.5 text-sm input-focus outline-none
placeholder:text-gray-400 dark:placeholder:text-gray-500
```

### 5.2 Border Colors

| State | Light | Dark |
|-------|-------|------|
| **Default** | `border-gray-300` (#d1d5db) | `border-gray-600` (#4b5563) |
| **Error** | `border-red-400` (#f87168) | `border-red-500` (#ef4444) |
| **Disabled** | — | `opacity-60 cursor-not-allowed` |

### 5.3 Focus Ring (`.input-focus` custom class)
```css
/* Light */
.input-focus:focus {
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
/* Dark */
.dark .input-focus:focus {
    border-color: #818cf8;
    box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.15);
}
```

### 5.4 Checkbox
```css
rounded border-gray-300 dark:border-gray-600
text-indigo-600 focus:ring-indigo-500/40 cursor-pointer
```

### 5.5 Select
Same as base input with additional `appearance-none` behavior from Tailwind.

### 5.6 Textarea
Same as base input with `resize` controlled via `rows` prop (default 4).

### 5.7 Labels
```css
block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1.5
```

### 5.8 Login Page Inputs (special)
Extra left padding `pl-10` to accommodate leading icon SVGs within a `.relative` wrapper. Password field has `pr-10` for the show/hide toggle button.

---

## 6. CARD DESIGN

### 6.1 Card Variants

| Type | Class | Light | Dark |
|------|-------|-------|------|
| **Standard (show pages)** | `bg-white dark:bg-black rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6` | White bg, 200-border | Black bg, 700-border |
| **Glass Card (dashboard)** | `.glass-card` | `rgba(255,255,255,0.75)` bg + `blur(20px)` + `rgba(255,255,255,0.9)` border + `0 8px 32px rgba(0,0,0,0.04)` shadow | `rgba(0,0,0,0.88)` bg + `blur(24px)` + `rgba(99,102,241,0.07)` border + `0 4px 24px rgba(0,0,0,0.3)` shadow |
| **Stat Card** | `.stat-card rounded-xl bg-gradient-to-br border p-5 card-hover` | Subtle gradient bg | Same pattern darker |
| **Login Card** | `bg-white dark:bg-black rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-gray-900/50 p-8` | Gray shadow | Dark shadow |
| **Modal** | `bg-white dark:bg-black rounded-2xl shadow-2xl max-w-sm w-full p-6` | White | Black |
| **Command Palette** | `bg-white dark:bg-black rounded-2xl shadow-2xl shadow-indigo-500/10 border border-gray-200 dark:border-gray-700` | White + indigo tint shadow | Black |

### 6.2 Glass Effect (`.glass` utility)
```css
.glass {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.8);
}
.dark .glass {
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(99, 102, 241, 0.08);
}
```
**Used in:** Sidebar, toast notifications (`.glass-card`)

### 6.3 Card Hover (`.card-hover`)
```css
.card-hover { transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1); }
.card-hover:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 40px -12px rgba(99, 102, 241, 0.12);
}
.dark .card-hover:hover {
    box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.5);
}
```

### 6.4 Stat Card (`.stat-card`)
- `overflow: hidden` with a pseudo `::after` radial decorative element (120px circle, `opacity: 0.06`, translates top-right)
- `hover::after` opacity increases to 0.12
- `hover: translateY(-2px)`

### 6.5 Empty State Card
- Centered layout with `w-16 h-16 rounded-2xl bg-indigo-50 dark:bg-indigo-900/20` icon container
- `px-6 py-16` padding

---

## 7. SIDEBAR

### 7.1 Layout
```
w-64 | glass | border-r border-gray-200/50 dark:border-gray-800/50
flex flex-col shrink-0 | transition-all duration-300
Collapsed: w-16 | max-lg: fixed, -translate-x-full
```

### 7.2 Header
```css
.sidebar-header {
    h-14, flex items-center justify-between px-4
    bg-gradient-to-r from-indigo-600 to-purple-600
    /* Dark override: */
    background: #000000 !important;
}
```
- App title: `text-lg font-semibold tracking-tight text-white`
- Toggle icons: `text-white/80 hover:text-white`

### 7.3 Navigation Links

| State | Classes |
|-------|---------|
| **Default** | `text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 hover:translate-x-0.5` |
| **Active** | `bg-gradient-to-r from-indigo-50 to-purple-50/50 dark:from-indigo-900/25 dark:to-purple-900/10 text-indigo-700 dark:text-indigo-300 font-semibold shadow-sm nav-link-active` |
| **Indicator** | 3px left gradient bar (`#6366f1 → #4f46e5`), scaleY animation on active |

**Common:**
```css
.nav-link {
    flex items-center gap-2.5 px-3 py-2 rounded-xl text-sm
    transition-all duration-200
}
```

### 7.4 Section Dividers
```css
px-3 text-[10px] font-semibold uppercase tracking-widest text-gray-500 dark:text-gray-400
```

### 7.5 User Profile Footer
```
p-3 border-t border-gray-200 dark:border-gray-800
bg-gradient-to-r from-indigo-50 to-purple-50
      dark:from-indigo-900/20 dark:to-purple-900/20
```
- **Avatar:** `w-9 h-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white text-sm font-bold`
- **Name:** `text-sm font-semibold text-gray-900 dark:text-gray-100 truncate`
- **Email:** `text-[11px] text-gray-500 dark:text-gray-400 truncate`
- **Action icons:** `text-gray-400 hover:text-indigo-600`, `hover:bg-white/50 dark:hover:bg-gray-800/50`

### 7.6 Mobile Overlay
```
fixed inset-0 bg-black/60 backdrop-blur-sm z-40
```

---

## 8. ICON SYSTEM

### 8.1 Primary: Heroicons Outline (v2)
- 100% of icons are inline SVG matching the **Heroicons v2 outline** style
- Signature attributes: `fill="none" stroke="currentColor" viewBox="0 0 24 24"`
- Standard `stroke-width="2"`, empty states use `stroke-width="1.5"`
- Sizes: w-3.5 h-3.5 (actions), w-4 h-4 (general), w-5 h-5 (medium), w-7 h-7 (dashboard icon boxes), w-12 h-12 (empty states)
- **No FontAwesome, no Lucide, no icon font library** found

### 8.2 Icon Usage Locations

| Location | Size | Common Icons |
|----------|------|-------------|
| Sidebar nav | w-4 h-4 (included via SVG path data) | chart, lock, server, mail, etc. |
| Dashboard icon boxes | w-4 h-4 in w-7 h-7 gradient box | chart-bar, checklist, server, clock, shield, mail |
| Table actions (`<x-action>`) | w-3.5 h-3.5 | eye, pencil, trash, download, plus, copy |
| Stat cards | w-5 h-5 (color-coded) | star, checklist, users, dollar, bell, server, clock, shield, document |
| Empty states | w-12 h-12 (stroke 1.5) in w-16 h-16 container | box, search, user, bell, lock, key, clipboard, globe, server, calendar, activity, clock, chart |
| Form inputs | w-4 h-4 (leading icons, login page) | mail, lock (eye for password toggle) |
| Activity timeline | w-4 h-4 in w-8 h-8 colored box | plus, pencil, trash, refresh, eye |
| Breadcrumb | w-3.5 h-3.5 | chevron-right separator |
| Status badges | w-3.5 h-3.5 | checkmark, x-mark, info, exclamation |
| Alert/warning states | w-4 h-4 | exclamation-circle (for errors/warnings) |

### 8.3 Custom Dashboard Widget Icons (SVG strings)
Each widget has a unique SVG icon inside a gradient icon box:
- **Activity, Quick Actions, Vault:** Chart line icon
- **Operations:** Server stack icon
- **Tasks:** Clipboard check icon
- **Assets:** App window grid icon
- **Renewals:** Clock icon
- **Server Health:** Monitor/server icon
- **SMTP:** Mail envelope icon

---

## 9. DARK MODE

### 9.1 Activation
```javascript
localStorage.getItem('darkMode') === '1'
  || (localStorage.getItem('darkMode') !== '0'
      && window.matchMedia('(prefers-color-scheme: dark)').matches)
```
Toggle adds/removes `.dark` class on `<html>`, stored in localStorage.

### 9.2 Backgrounds

| Element | Light | Dark |
|---------|-------|------|
| **Body/Page** | `gray-50` (#f8fafc) | `#000000` (pure black) |
| **Content** | `gray-50` | `#000000` via `body` CSS rule |
| **Cards** | `white` | `black` |
| **Sidebar** | `rgba(255,255,255,0.7)` + blur | `rgba(0,0,0,0.85)` + blur |
| **Inputs** | `white` | `black` |
| **Modals** | `white` | `black` |
| **Command palette** | `white` | `black` |
| **Login card** | `white` | `black` |
| **Table header** | `linear-gradient(135deg, #f8fafc, #f1f5f9)` | `#000000` |
| **Table row hover** | `rgba(99,102,241,0.04)` | `rgba(99,102,241,0.08)` |
| **Toast bg** | `emerald-100` / `red-100` / etc. | `emerald-900/40` / `red-900/40` / etc. |

### 9.3 Text

| Level | Light | Dark |
|-------|-------|------|
| **Primary** | `text-gray-900` (#111827) | `text-gray-100` (#f3f4f6) |
| **Secondary** | `text-gray-500` (#6b7280) | `text-gray-400` (#9ca3af) |
| **Muted** | `text-gray-400` (#9ca3af) | `text-gray-500` (#6b7280) |
| **Link** | `text-indigo-600` (#4f46e5) | `text-indigo-400` (#818cf8) |

### 9.4 Borders

| Element | Light | Dark |
|---------|-------|------|
| **Card** | `border-gray-200` | `border-gray-700` |
| **Sidebar** | `border-gray-200/50` | `border-gray-800/50` |
| **Input** | `border-gray-300` | `border-gray-600` |
| **Divider** | `border-gray-100` or `border-gray-200` | `border-gray-700/50` or `border-gray-700` |
| **Glass** | `rgba(255,255,255,0.8)` — `rgba(255,255,255,0.9)` | `rgba(99,102,241,0.07)` — `rgba(99,102,241,0.08)` |

### 9.5 Shadows

| Type | Light | Dark |
|------|-------|------|
| **Card shadow** | `shadow-sm` (subtle) | None (pure black card + border) |
| **Glass card shadow** | `0 8px 32px rgba(0,0,0,0.04)` | `0 4px 24px rgba(0,0,0,0.3)` |
| **Card hover shadow** | `0 20px 40px -12px rgba(99,102,241,0.12)` | `0 20px 40px -12px rgba(0,0,0,0.5)` |
| **Button shadow** | `shadow-sm shadow-{color}-500/20` | Same (light on dark bg) |
| **Login shadow** | `shadow-xl shadow-gray-200/50` | `shadow-xl shadow-gray-900/50` |
| **Command palette** | `shadow-2xl shadow-indigo-500/10` | None |

### 9.6 Glass Effects (dark mode)
```css
.dark .glass {
    background: rgba(0, 0, 0, 0.85);
    backdrop-filter: blur(20px);
    border: 1px solid rgba(99, 102, 241, 0.08);
}
.dark .glass-card {
    background: rgba(0, 0, 0, 0.88);
    backdrop-filter: blur(24px);
    border: 1px solid rgba(99, 102, 241, 0.07);
    box-shadow: 0 4px 24px rgba(0, 0, 0, 0.3);
}
```

---

## 10. LIGHT MODE

### 10.1 Background Stack
```
body:        bg-gray-50 (#f8fafc)
page:        bg-grid (pattern overlay on bg-gray-50)
cards:       bg-white
sub-surface: bg-gray-50 / bg-gray-100
```

### 10.2 Color Values
```
Primary text:   #111827 (gray-900)
Secondary text: #6b7280 (gray-500)
Muted text:     #9ca3af (gray-400)
Link text:      #4f46e5 (indigo-600)
Border:         #e5e7eb (gray-200)
Input border:   #d1d5db (gray-300)
```

### 10.3 Shadows (light mode specific)
```css
/* Login card */
shadow-xl shadow-gray-200/50

/* Standard card */
shadow-sm

/* Glass card */
box-shadow: 0 8px 32px rgba(0, 0, 0, 0.04)

/* Card hover */
box-shadow: 0 20px 40px -12px rgba(99, 102, 241, 0.12)

/* Buttons */
shadow-sm shadow-{color}-500/20

/* Command palette */
shadow-2xl shadow-indigo-500/10
```

---

## 11. DESIGN LANGUAGE

OpsPilot follows a **Modern Enterprise Glassmorphism** design language:

### 11.1 Key Characteristics

| Attribute | Description |
|-----------|-------------|
| **Style** | Modern Enterprise — clean lines, generous whitespace, professional |
| **Glass effect** | Frosted glass panels with backdrop blur and subtle borders |
| **Gradient identity** | Indigo → Purple as signature brand gradient |
| **Corners** | Predominantly `rounded-xl` (12px), premium surfaces at `rounded-2xl` (16px) |
| **Depth** | Bootstraped through shadows (`shadow-sm` to `shadow-2xl`) and hover lifts |
| **Animation** | Subtle micro-interactions: scale press, fade-in, count-up, card hover lift |
| **Mobility** | Fully responsive sidebar with collapse and mobile overlay |
| **Dark mode** | True dark mode with pure black backgrounds — not just inverted colors |
| **Minimalism** | No heavy borders or decorations; content-focused with ample padding |
| **Consistency** | Single `input-focus` class, single button component with 5 variants, unified card patterns |
| **Accessibility** | `aria-label` on icons, `sr-only` skip link, `focus:ring-2` on interactive elements, `prefers-reduced-motion` support |

### 11.2 Micro-Interactions
```
Buttons:          active:scale-[0.98] (2% press), loading spinner
Card hover:       translateY(-3px), enhanced shadow, smooth 0.3s cubic-bezier
Nav link hover:   translateX(0.5px), bg tint
Nav link active:  3px left bar scaleY(0→1) reveal
Stat card:        hover:translateY(-2px), decorative ::after circle opacity increase
Row hover:        subtle bg tint (rgba indigo 4-8%)
Table cells:      No interaction (read-only data)
Toast:            slide-in-right (0.35s), slide-out-right (0.25s)
Modal:            scale-in (0.25s), overlay fade-in (0.15s)
Count-up:         IntersectionObserver triggered, cubic-bezier easing, 400-1200ms duration
Page content:     fade-in (0.4s), fade-in-up (0.5s)
```

### 11.3 Inspiration & Vibe
- **Vibe:** Professional operations dashboard, IT service management
- **Comparable to:** Laravel Nova, Vercel dashboard, Linear (for the glass effect)
- **Not:** Cyber, neobrutalist, playful, skeuomorphic
- **Notable absence:** No emoji usage, no illustrations, no photography — pure UI + data

---

## 12. BRANDING STYLE — Login Page Illustration

### 12.1 Current Login Page Analysis
```
Background:     bg-gray-50 (light) → black (dark)
Decoration:     2 ambient gradient orbs (blur-3xl):
                - top-right: indigo-500/10 (#6366f1, 10% opacity, 320x320px)
                - bottom-left: purple-500/10 (#a855f7, 10% opacity, 320x320px)
Logo box:       w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600
                shadow-lg shadow-indigo-500/20
Login card:     bg-white dark:bg-black rounded-2xl shadow-xl p-8
Text:           "Sign in to your account" — instrument-sans
CTAs:           <x-button variant="primary" size="lg" class="w-full">
Animation:      .fade-in-up on the login container
```

### 12.2 Recommended Illustration Style

| Aspect | Recommendation | Rationale |
|--------|---------------|-----------|
| **Style** | Minimalist abstract geometric illustration | Matches `.bg-grid` pattern and clean enterprise aesthetic |
| **Color** | Indigo + Purple monochromatic | Signature brand colors, no competing hues |
| **Format** | Inline SVG (inline in Blade or external SVG file) | No additional HTTP requests, theme-aware |
| **Opacity** | 5-15% opacity, subtle | Should not compete with the login form |
| **Theme support** | Light: indigo-500/10 tints, Dark: indigo-500/15 or white/10 | Consistent with existing `<svg>` color strategy |
| **Position** | Behind the login card, or as a hero section to the left of the form | Left-side hero layout elevates premium feel |
| **Shape language** | Rounded (`rounded-2xl` matching), soft geometric elements | No sharp angles — consistent with `rounded-xl` design system |
| **Metaphor** | Abstract dashboard/chart/security forms | OpsPilot = operations management: charts, servers, security, checkmarks |
| **Dark mode** | White stroke with low opacity, or dark indigo | Follows pattern of `text-gray-400 dark:text-gray-500` icon strategy |

### 12.3 Recommended Compositions (in priority order)

1. **Security Shield Silhouette + Data Flow Lines** — Abstract shield shape composed of grid points connected by lines, rotating/dynamic feel; represents operations security
2. **Abstract Server Rack / Stack** — Minimal isometric server blocks with subtle gradient fills; represents infrastructure management
3. **Orbit / Network Graph** — Connected nodes with flowing lines; represents monitoring and connectivity
4. **Dashboard Chart Abstraction** — Minimal bar/line chart forms; represents the operations dashboard

### 12.4 Technical Constraints
```css
/* Light mode colors for illustration */
--illustration-primary: rgba(99, 102, 241, 0.08);   /* indigo-500/8 */
--illustration-secondary: rgba(168, 85, 247, 0.06);  /* purple-500/6 */
--illustration-stroke: rgba(99, 102, 241, 0.3);       /* indigo-500/30 */

/* Dark mode colors for illustration */
--illustration-primary: rgba(99, 102, 241, 0.15);     /* indigo-500/15 */
--illustration-secondary: rgba(168, 85, 247, 0.10);   /* purple-500/10 */
--illustration-stroke: rgba(129, 140, 248, 0.3);       /* indigo-400/30 */
```
- Must not use third-party icon libraries
- Must be responsive (stack/form layout)
- Must respect `prefers-reduced-motion`
- Should use `aria-hidden="true"` (decorative only)
- Should use `pointer-events-none` (non-interactive)

---

## 13. KEY DESIGN TOKENS SUMMARY

| Token | Value |
|-------|-------|
| **Border radius (standard)** | `rounded-xl` = 12px |
| **Border radius (premium)** | `rounded-2xl` = 16px |
| **Border radius (small)** | `rounded-lg` = 8px |
| **Card padding (standard)** | `p-6` = 24px |
| **Card padding (glass)** | `p-5` = 20px |
| **Card padding (login)** | `p-8` = 32px |
| **Sidebar width** | 256px (w-64) / 64px (w-16 collapsed) |
| **Input padding** | `px-3 py-2.5` |
| **Button padding (md)** | `px-4 py-2` |
| **Table cell padding** | `px-6 py-3` |
| **Focus ring color** | `#6366f1` / `rgba(99, 102, 241, 0.1)` |
| **Signature gradient** | `from-indigo-600 to-purple-600` |
| **Page max-width** | `max-w-7xl` (80rem) or `max-w-3xl` (48rem for show pages) |
| **Animation default** | `cubic-bezier(0.16, 1, 0.3, 1)` — custom ease-out overshoot |
| **Z-index stack** | Toast 100, Cmd palette 200, Confirm modal 50, Loading overlay 300 |

---

*End of audit. All data extracted from live Blade templates, CSS, and components — no modifications made.*

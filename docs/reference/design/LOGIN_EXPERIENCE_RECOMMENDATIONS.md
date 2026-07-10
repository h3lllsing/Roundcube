# OpsPilot — Login Experience Recommendations

> **Phase 10 — Enterprise Branding Polish**
> **Date:** 2026-06-27
> **Based on:** PORTAL_DESIGN_SYSTEM_AUDIT.md · LOGIN_ARTWORK_SPEC.md · LOGIN_BACKGROUND_AI_PROMPT.txt
> **Status:** Design recommendations only — no code changes, no image generation.

---

## 1. DESKTOP LOGIN LAYOUT (1920×1080)

### Layout Concept: Split Screen — Illustration + Branded Login Card

```
┌──────────────────────────────────────────────────────────────────────┐
│     ILLUSTRATION ZONE (60%)         │     BRANDED LOGIN ZONE (40%)    │
│                                      │                                 │
│   ┌─────────────────────────────┐    │   ┌─────────────────────────┐  │
│   │                             │    │   │                         │  │
│   │    Operations Core          │    │   │   [indigo→purple box]   │  │
│   │    + Server Rack            │    │   │   w-14 h-14 rounded-2xl │  │
│   │    + All platform objects   │    │   │                         │  │
│   │                             │    │   │   OPSPILOT              │  │
│   │    (artwork from spec)      │    │   │   text-xl font-bold     │  │
│   │                             │    │   │   text-gray-900         │  │
│   │                             │    │   │                         │  │
│   │                             │    │   │   Enterprise IT         │  │
│   │                             │    │   │   Operations Platform   │  │
│   │                             │    │   │   text-sm text-gray-500 │  │
│   │                             │    │   │                         │  │
│   │                             │    │   │   ─── 16px spacer ───  │  │
│   │                             │    │   │                         │  │
│   │                             │    │   │   Description line      │  │
│   │                             │    │   │   text-xs text-gray-400 │  │
│   │                             │    │   │   max-w-xs mx-auto      │  │
│   │                             │    │   │                         │  │
│   │                             │    │   │   ─── 24px spacer ───  │  │
│   │                             │    │   │                         │  │
│   │                             │    │   │   ┌─────────────────┐  │  │
│   │                             │    │   │   │ Email input     │  │  │
│   │                             │    │   │   └─────────────────┘  │  │
│   │                             │    │   │   ┌─────────────────┐  │  │
│   │                             │    │   │   │ Password input  │  │  │
│   │                             │    │   │   └─────────────────┘  │  │  │
│   │                             │    │   │                         │  │  │
│   │                             │    │   │   Remember me  [link]  │  │  │
│   │                             │    │   │                         │  │  │
│   │                             │    │   │   ┌─────────────────┐  │  │  │
│   │                             │    │   │   │  Sign In btn    │  │  │  │
│   │                             │    │   │   └─────────────────┘  │  │  │
│   │                             │    │   │                         │  │  │
│   └─────────────────────────────┘    │   └─────────────────────────┘  │
│                                      │                                 │
└──────────────────────────────────────────────────────────────────────┘
```

### 1.1 Layout Specifications

| Element | Value |
|---------|-------|
| **Page background** | `bg-gray-50` (light) or `bg-black` (dark) |
| **Page body** | `min-h-screen flex items-center justify-center` |
| **Container** | `flex w-full max-w-[1440px] mx-auto h-screen` |
| **Left panel** | `w-[60%] relative overflow-hidden` — artwork fills this area |
| **Right panel** | `w-[40%] flex items-center justify-center p-12` |
| **Login card** | `w-full max-w-[420px]` — centered vertically |
| **Card background** | `bg-white dark:bg-black rounded-2xl shadow-xl shadow-gray-200/50 dark:shadow-gray-900/50 p-8` |

### 1.2 Branding Placement (Inside Login Card)

```
┌────────────────────────────────────┐
│                                    │
│     ╔══════════════════════╗       │
│     ║  Gradient icon box   ║       │  ← w-14 h-14 rounded-2xl
│     ║  (indigo→purple)     ║       │     bg-gradient-to-br from-indigo-500
│     ║  [chart icon SVG]    ║       │     to-purple-600 shadow-lg
│     ╚══════════════════════╝       │     mx-auto mb-4
│                                    │
│     OPSPILOT                       │  ← text-xl font-bold tracking-tight
│     text-gray-900 dark:text-white  │     text-center
│                                    │
│     Enterprise IT Operations       │  ← text-sm text-gray-500
│     Platform                       │     dark:text-gray-400 text-center mb-1
│                                    │
│     ────────── 16px ──────────     │
│                                    │
│     "Centralize infrastructure,    │  ← text-xs text-gray-400
│      domains, hosting, VPS,        │     dark:text-gray-500 text-center
│      assets, credentials,          │     max-w-xs mx-auto
│      renewals, monitoring,         │
│      and security from a single    │
│      enterprise workspace."        │
│                                    │
│     ────────── 24px ──────────     │
│                                    │
│     [Email input]                  │
│     [Password input]               │
│     [Remember me] [Forgot link]    │
│     [Sign in button — full width]  │
│                                    │
└────────────────────────────────────┘
```

### 1.3 Input & Button Dimensions

| Element | Width | Classes |
|---------|-------|---------|
| **Email input** | `w-full` | As per existing `form/input.blade.php` |
| **Password input** | `w-full` | With eye toggle, as per existing login page |
| **Sign in button** | `w-full` | `<x-button variant="primary" size="lg" class="w-full">` |
| **Remember me** | Inline | Checkbox + label, as per existing |
| **Forgot password** | Inline link | `text-sm font-medium text-indigo-600` |

### 1.4 Spacing Rhythm

```
From top of card to icon box:       24px
Icon box to title:                  20px
Title to subtitle:                  4px
Subtitle to description:            16px
Description to form:                24px
Form field to next field:           20px
Last field to remember row:         16px
Remember row to sign in button:     20px
Sign in button to card bottom:      8px
```

---

## 2. TABLET LAYOUT (834×1194 Portrait)

### Layout: Stacked — Condensed Artwork Above, Login Below

```
┌───────────────────────────────────────────┐
│                                           │
│   ╔═══════════════════════════════════╗   │
│   ║     Artwork (top 35% of height)   ║   │
│   ║     Cropped center 70% width      ║   │
│   ║     Operations Core visible        ║   │
│   ║     Low opacity, more subtle       ║   │
│   ╚═══════════════════════════════════╝   │
│                                           │
│   ┌───────────────────────────────────┐   │
│   │                                   │   │
│   │     [Gradient icon box]           │   │
│   │                                   │   │
│   │     OPSPILOT                      │   │
│   │     Enterprise IT Operations      │   │
│   │     Platform                      │   │
│   │                                   │   │
│   │     [description line]            │   │
│   │                                   │   │
│   │     ───────── 24px ─────────      │   │
│   │                                   │   │
│   │     [Email input    ]             │   │
│   │     [Password input ]             │   │
│   │     Remember me  [Forgot]         │   │
│   │     [Sign in — full width]        │   │
│   │                                   │   │
│   └───────────────────────────────────┘   │
│                                           │
└───────────────────────────────────────────┘
```

### 2.1 Tablet Specifications

| Element | Value |
|---------|-------|
| **Container** | `flex flex-col min-h-screen` |
| **Artwork area** | `h-[35vh] relative overflow-hidden opacity-60` |
| **Login area** | `flex-1 flex items-center justify-center px-6 pb-12` |
| **Login card** | `w-full max-w-[420px]` — no shadow/border, clean |
| **Card background** | Transparent on light, transparent on dark |
| **Artwork treatment** | Crop center 70%, reduce opacity to 50-60%, use as atmospheric backdrop rather than focal point |

---

## 3. MOBILE LAYOUT (390×844 Portrait)

### Layout: Full-Screen Form with Subtle Artwork Ghost

```
┌─────────────────────┐
│                     │
│  ░░░░░░░░░░░░░░░░░  │  ← Artwork as page background
│  ░░  (very low   ░░ │     opacity 15-20% (light)
│  ░░   opacity)   ░░ │     opacity 25-30% (dark)
│  ░░░░░░░░░░░░░░░░░  │     behind the full form
│                     │
│  ┌───────────────┐  │
│  │               │  │
│  │  [icon box]   │  │
│  │               │  │
│  │  OPSPILOT     │  │
│  │  Enterprise   │  │
│  │  IT Ops Plat. │  │
│  │               │  │
│  │  [description]│  │
│  │               │  │
│  │  ─── 20px ─── │  │
│  │               │  │
│  │  [Email     ] │  │
│  │  [Password  ] │  │
│  │               │  │
│  │  Remember me  │  │
│  │               │  │
│  │  [Sign In    ] │  │
│  │               │  │
│  │  Forgot pass? │  │
│  │               │  │
│  └───────────────┘  │
│                     │
└─────────────────────┘
```

### 3.1 Mobile Specifications

| Element | Value |
|---------|-------|
| **Container** | `flex flex-col min-h-screen px-5 py-12` |
| **Artwork** | Applied as `background-image` on body — `opacity: 0.15` (light) / `0.25` (dark), `background-size: cover`, `background-position: center` |
| **Card** | No card wrapper — the form IS the page |
| **Width** | Full width with `px-5` padding |
| **Top spacing** | `pt-16` to push content below status bar area |
| **Logo size** | `w-12 h-12` (smaller than desktop) |
| **Title size** | `text-lg` (smaller than desktop) |
| **Input sizing** | `py-3` (larger touch targets on mobile) |
| **Button sizing** | `py-3` (minimum 48px touch target) |

---

## 4. BRANDING

### 4.1 Brand Identity

| Element | Value |
|---------|-------|
| **Product name** | **OpsPilot** |
| **Subtitle** | Enterprise IT Operations Platform |
| **Tone** | Professional · Premium · Minimal · Enterprise |
| **Logo position** | Inside login card, centered above title |
| **Logo style** | `w-14 h-14 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600` with chart-upward SVG icon in white |

### 4.2 Premium Enterprise Descriptions

Three options, maximum 25 words each:

**Option A — Platform Scope:**
> "Centralize infrastructure, domains, hosting, VPS, assets, credentials, renewals, monitoring, and security from a single enterprise workspace."

**Option B — Operations Efficiency:**
> "Manage every layer of your IT operations — from DNS and cloud infrastructure to asset lifecycle and security — in one unified command center."

**Option C — Enterprise Control:**
> "Monitor, track, and secure your entire IT estate — servers, domains, credentials, renewals, and alerts — all from a single operations platform."

### 4.3 Visual Branding Elements (Login Page)

```
Logo icon:    SVG chart-up arrow (Heroicons outline style)
              stroke="white" stroke-width="2" fill="none"
              viewBox="0 0 24 24"
              path: M13 7h8m0 0v8m0-8l-8 8-4-4-6 6

Typography:  Instrument Sans (loaded via fonts.bunny.net)
              Weights: 400 (description), 500 (subtitle), 600 (title)

Title:        text-xl font-bold text-gray-900 dark:text-white tracking-tight
Subtitle:     text-sm text-gray-500 dark:text-gray-400
Description:  text-xs text-gray-400 dark:text-gray-500 leading-relaxed

Icon box:     w-14 h-14 rounded-2xl
              bg-gradient-to-br from-indigo-500 to-purple-600
              shadow-lg shadow-indigo-500/20
              flex items-center justify-center mx-auto
```

### 4.4 Dark Mode Branding Adjustments

| Element | Light | Dark |
|---------|-------|------|
| **Title** | `text-gray-900` | `text-white` |
| **Subtitle** | `text-gray-500` | `text-gray-400` |
| **Description** | `text-gray-400` | `text-gray-500` |
| **Icon box shadow** | `shadow-indigo-500/20` | `shadow-indigo-500/30` |
| **Card bg** | `bg-white` | `bg-black` |
| **Card shadow** | `shadow-gray-200/50` | `shadow-gray-900/50` |

---

## 5. RESPONSIVE BREAKPOINT SUMMARY

| Breakpoint | Layout | Artwork Treatment | Card Width | Notes |
|-----------|--------|-------------------|------------|-------|
| **≥ 1280px** (desktop) | Split: 60/40 | Full illustration, full opacity | 420px max | Primary experience |
| **1024–1279px** (small desktop) | Split: 55/45 | Slightly cropped, 90% opacity | 380px max | Reduce left panel |
| **768–1023px** (tablet landscape) | Split: 50/50 | Center crop, 70% opacity | 360px max | Narrower proportions |
| **480–767px** (tablet portrait) | Stacked | Top 35% height, 50% opacity | Full width (480px) | Artwork as header |
| **< 480px** (mobile) | Full form | Background ghost, 15–20% opacity | Full width + 40px padding | Artwork as texture |

---

## 6. IMPLEMENTATION NOTES

### 6.1 Artwork Integration

| Method | Approach | Notes |
|--------|----------|-------|
| **Desktop split** | `<div class="w-[60%] relative overflow-hidden">` with `<img>` filling the area | Artwork at full resolution, `object-cover`, `object-left` |
| **Tablet stacked** | `<div class="h-[35vh] relative overflow-hidden opacity-60">` | Artwork cropped center, reduced opacity |
| **Mobile ghost** | `body { background-image: url(...); background-size: cover; opacity: 0.15; }` | Artwork behind entire form, very subtle |

### 6.2 Theme Switching

```html
<!-- Light mode image (default) -->
<img src="/images/login-bg-light.webp" alt="" 
     class="dark:hidden pointer-events-none select-none" 
     aria-hidden="true" role="presentation" />

<!-- Dark mode image (shown when .dark is on <html>) -->
<img src="/images/login-bg-dark.webp" alt="" 
     class="hidden dark:block pointer-events-none select-none" 
     aria-hidden="true" role="presentation" />
```

### 6.3 Accessibility

- All images: `alt=""` (decorative — no information conveyed)
- Skip link at top of page: `sr-only focus:not-sr-only` — "Skip to login form"
- Login form: `<form>` with `aria-label="Sign in"` or `role="form"` with `aria-labelledby`
- Password toggle: `aria-label="Toggle password visibility"`, `aria-pressed`
- Error messages: `role="alert"` on validation errors
- Remember me: `<label>` wrapping checkbox + text for accessible hit area
- Forgot password: semantic `<a>` with `href`
- Sign in button: `<button type="submit">` with loading state spinner and `aria-busy`

### 6.4 Performance

- Artwork image: WebP format, ≤ 500 KB
- Preload: `<link rel="preload" as="image" href="..." />` for LCP optimization
- Font: `display=swap` on Instrument Sans
- No additional JavaScript on login page beyond existing dark mode toggle and password reveal

---

## 7. MOTION & MICRO-INTERACTIONS (Login Page)

| Element | Animation | Spec |
|---------|-----------|------|
| **Page entry** | Container fades in | `fade-in-up` 0.5s cubic-bezier(0.16, 1, 0.3, 1) |
| **Form card** | Scale in with fade | `scale-in` 0.35s cubic-bezier(0.16, 1, 0.3, 1), 0.1s delay |
| **Input focus** | Border + ring transition | 0.2s ease, as per existing `.input-focus` |
| **Button hover** | Slight darken + shadow | As per existing `<x-button>` |
| **Button press** | Scale down | `active:scale-[0.98]` as per existing |
| **Error shake** | Horizontal shake on validation | `translateX` keyframes, 0.3s |
| **Dark mode toggle** | Instant class swap | No transition — instant localStorage save |
| **Password reveal** | Icon swap | Instant, as per existing |

---

## 8. LOGIN PAGE STRUCTURE SUMMARY (Desktop)

```
<body class="min-h-screen bg-gray-50 dark:bg-black flex">
    <!-- Skip link -->
    <a href="#login-form" class="sr-only focus:not-sr-only ...">
      Skip to login form
    </a>

    <!-- Left: Artwork (60%) -->
    <div class="w-[60%] relative overflow-hidden hidden lg:block">
        <img src="login-bg-light.webp" alt=""
             class="absolute inset-0 w-full h-full object-cover object-left
                    dark:hidden pointer-events-none select-none" />
        <img src="login-bg-dark.webp" alt=""
             class="absolute inset-0 w-full h-full object-cover object-left
                    hidden dark:block pointer-events-none select-none" />
        <!-- Optional: subtle gradient overlay on right edge -->
        <div class="absolute inset-y-0 right-0 w-32 bg-gradient-to-l
                    from-gray-50/80 dark:from-black/80 to-transparent
                    pointer-events-none"></div>
    </div>

    <!-- Right: Branded Login (40%) -->
    <div class="w-full lg:w-[40%] flex items-center justify-center p-8 lg:p-12">
        <div class="w-full max-w-[420px] fade-in-up
                    bg-white dark:bg-black rounded-2xl
                    shadow-xl shadow-gray-200/50 dark:shadow-gray-900/50 p-8">

            <!-- Branding -->
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl mx-auto mb-4
                            bg-gradient-to-br from-indigo-500 to-purple-600
                            shadow-lg shadow-indigo-500/20
                            flex items-center justify-center">
                    <svg class="w-7 h-7 text-white" ...>...</svg>
                </div>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white
                           tracking-tight">
                    OpsPilot
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    Enterprise IT Operations Platform
                </p>
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-4
                           max-w-xs mx-auto leading-relaxed">
                    Centralize infrastructure, domains, hosting, VPS,
                    assets, credentials, renewals, monitoring, and security
                    from a single enterprise workspace.
                </p>
            </div>

            <!-- Form -->
            <form method="POST" action="{{ route('login') }}"
                  id="login-form" class="space-y-5">
                @csrf
                <!-- Email field -->
                <!-- Password field -->
                <!-- Remember + Forgot -->
                <!-- Sign in button -->
            </form>
        </div>
    </div>
</body>
```

---

## 9. COMPARISON: CURRENT VS. PROPOSED LOGIN PAGE

| Aspect | Current (Generic SaaS) | Proposed (Enterprise Premium) |
|--------|----------------------|------------------------------|
| **Background** | Plain gray with blur orbs | Full 4K artwork showing complete platform |
| **Layout** | Centered card only | Split screen: artwork (60%) + card (40%) |
| **Branding** | App name only | Title + subtitle + premium description |
| **Visual depth** | Flat, single plane | Three depth layers: background/mid/foreground |
| **Story** | None | Visual journey: Internet → ... → Security |
| **Anchor** | None | Operations Core + Server Rack as central hub |
| **Perceived value** | Generic SaaS login | Premium enterprise operations platform |
| **References** | — | Azure, GitHub Enterprise, Atlassian, Vercel |

---

*End of recommendations. Zero code modified — design documentation only.*

# OpsPilot — Login Background Artwork Specification

> **Generated:** 2026-06-27 (revised — Phase 10)
> **Based on:** PORTAL_DESIGN_SYSTEM_AUDIT.md (13-section full portal audit)
> **Purpose:** AI image generation prompt specification for the login page hero illustration
> **Target models:** Midjourney v6 / DALL-E 3 / Flux / Stable Diffusion XL

---

## 1. CANVAS

| Property | Value |
|----------|-------|
| **Aspect ratio** | 16:9 |
| **Resolution** | 3840 × 2160 px (4K) |
| **Orientation** | Landscape — horizontal composition |
| **Safe zone** | Right 40% reserved for login card overlay (keep clear of detail) |
| **Output format** | PNG (lossless, no alpha channel needed) |
| **Color space** | sRGB |

### Layout Guide

```
┌──────────────────────────────────────────────────────────────────┐
│                                                                   │
│  COMPLETE ILLUSTRATION                    SAFE ZONE               │
│  (Left 60%)                               (Right 40%)            │
│                                                                   │
│  ┌──────────────────────────────┐  ┌────────────────────────┐   │
│  │  Three depth layers:         │  │  No objects.            │   │
│  │  Background · Middle · F/G   │  │  Only faint ambient     │   │
│  │                              │  │  glow extending         │   │
│  │  Internet → Cloud → DNS      │  │  from left.             │   │
│  │  → Domains → Hosting → VPS   │  │                          │   │
│  │  → Operations Core → Rack    │  │  482px login card       │   │
│  │  → Monitoring → Assets       │  │  centered vertically    │   │
│  │  → Email → Notifications     │  │  padded 48px from edge  │   │
│  │  → Analytics → Security      │  │                          │   │
│  └──────────────────────────────┘  └────────────────────────┘   │
│                                                                   │
│  ← 2304 px →                       ← 1536 px →                  │
└──────────────────────────────────────────────────────────────────┘
```

---

## 2. COLOR PALETTE

Only colors extracted from the portal design system. No custom or invented colors.

### 2.1 Primary Brand Colors

| Name | HEX | RGB | Opacity Range | Usage |
|------|-----|-----|---------------|-------|
| **Indigo 500** | `#6366f1` | `99, 102, 241` | 6% – 40% | Primary shapes, strokes, gradient anchors |
| **Indigo 600** | `#4f46e5` | `79, 70, 229` | 10% – 30% | Deep shadows, gradient endpoints, deep accent |
| **Indigo 50** | `#eef2ff` | `238, 242, 255` | 40% – 80% | Light fills, ambient glow |
| **Purple 500** | `#a855f7` | `168, 85, 247` | 5% – 25% | Secondary accent, gradient partner |
| **Purple 600** | `#9333ea` | `147, 51, 234` | 5% – 20% | Deep purple shadow, gradient end, Operations Core accent |
| **Purple 50** | `#faf5ff` | `250, 245, 255` | 30% – 60% | Soft purple fill, secondary glow |

### 2.2 Neutral & Structural Colors

| Name | HEX | RGB | Usage |
|------|-----|-----|-------|
| **White** | `#ffffff` | `255, 255, 255` | Lines at 20–40% opacity, highlight accents |
| **Gray 900** | `#111827` | `17, 24, 39` | Dark structural lines (light mode fallback) |
| **Gray 500** | `#6b7280` | `107, 114, 128` | Secondary lines, grid dots (dark mode) |
| **Gray 400** | `#9ca3af` | `156, 163, 175` | Subtle grid lines |
| **Gray 50** | `#f8fafc` | `248, 250, 252` | Background base (light mode) |
| **Pure Black** | `#000000` | `0, 0, 0` | Background base (dark mode) |

### 2.3 Semantic Accents (Strict Usage)

| Name | HEX | Allowed Usage | Max Coverage |
|------|-----|---------------|-------------|
| **Emerald 500** | `#10b981` | Small uptick indicators on chart lines, success status dots | ≤ 5% of total image |
| **Amber 500** | `#f59e0b` | Warning alert nodes, pending status indicators | ≤ 3% of total image |
| **Red 500** | `#ef4444` | Critical alert indicators, error nodes ONLY | ≤ 2% of total image |

### 2.4 Gradient Pairs

```css
/* Signature gradient — primary object fills */
from-indigo-600 (#4f46e5) → to-purple-600 (#9333ea)
Opacity: 12% fill / 40% stroke

/* Operations Core radial gradient */
from-indigo-500/15 (#6366f1 at 15%) → to-purple-600/8 (#9333ea at 8%)
Radius: 400px center, radial, soft blur falloff

/* Ambient background wash */
from-indigo-50 (#eef2ff) → to-purple-50 (#faf5ff)
Opacity: 60% as radial gradient wash

/* Dark mode ambient */
from-indigo-500/10 (rgba(99,102,241,0.10)) → to-purple-500/6 (rgba(168,85,247,0.06))
```

### 2.5 Glassmorphism Values

```css
/* Operations Core glass */
background: rgba(99, 102, 241, 0.04);
backdrop-filter: blur(40px);
border: 1px solid rgba(99, 102, 241, 0.08);

/* Light mode — larger blur, softer */
background: rgba(255, 255, 255, 0.30);
box-shadow: 0 0 80px rgba(99, 102, 241, 0.06);

/* Dark mode — deeper, more luminous */
background: rgba(99, 102, 241, 0.06);
box-shadow: 0 0 120px rgba(99, 102, 241, 0.10);
```

### 2.6 Forbidden Colors

```
✗ sky-500 / cyan-500 / blue-500    — "blue gradients" strictly prohibited
✗ rose-500 / pink-500              — reserved for danger (not decorative)
✗ Any neon / fluorescent / pastel / rainbow / teal
✗ Any color NOT documented in this palette
✗ Green tones beyond tiny emerald chart accents
```

---

## 3. VISUAL LANGUAGE

### 3.1 Style Keywords

```
Enterprise · Premium · Minimal · Modern · Glassmorphism
IT Operations Platform · Infrastructure as Code · Cloud Architecture
Network Topology · Security Monitoring · Data Analytics · Operations Core
```

### 3.2 Design System Alignment

| Attribute | Portal Standard | Illustration Must Match |
|-----------|----------------|------------------------|
| **Corner treatment** | `rounded-xl` (12px), `rounded-2xl` (16px) | All shapes use rounded corners ≥ 8px |
| **Stroke width** | `stroke-width="2"` (Heroicons standard) | Lines: 1.5–2.5px |
| **Glass aesthetic** | `backdrop-filter: blur(16–24px)` | Frosted glass overlay effect on Operations Core |
| **Depth** | `box-shadow`, `lift` on hover | Three explicit depth layers via opacity/blur/scale |
| **Opacity** | Low-opacity accents (`/10`, `/20`, `/30`) | ≤ 30% opacity for most middle-ground elements |
| **Grid** | `.bg-grid` 40px squares at 3–1.2% opacity | Background grid pattern (mandatory) |

### 3.3 Reference Aesthetic

```
Microsoft Azure portal illustrations — abstract geometric infrastructure art
GitHub Enterprise — clean, minimal, dark/light duality, operations hub concept
Atlassian Cloud — connected node diagrams, team workflow visualization
Vercel — premium minimalism, frosted glass, indigo accents, depth layers
Linear — dark glass, subtle glow, elegant data visualization
Laravel Nova — rounded corners, gradient buttons, clean data presentation

NOT cyberpunk. NOT futuristic. NOT gaming. NOT sci-fi.
NOT busy. NOT cluttered. NOT overcrowded.
Clean, premium, enterprise infrastructure visualization — infrastructure as art.
```

### 3.4 Visual Narrative — Complete OpsPilot Workflow

The artwork subtly communicates the end-to-end OpsPilot platform workflow.
This is NOT a flowchart — the journey is suggested through object placement,
connection line hierarchy, and visual reading order:

```
                    ┌──────────────────┐
                    │    INTERNET      │  (wireframe globe, upper-left)
                    └────────┬─────────┘
                             │
                    ┌────────▼─────────┐
                    │     CLOUD        │  (geometric rounded-rect clusters)
                    └────────┬─────────┘
                             │
                    ┌────────▼─────────┐
                    │      DNS         │  (concentric arc rings)
                    └────────┬─────────┘
                             │
                    ┌────────▼─────────┐
                    │    DOMAINS       │  (rounded-rect grid blocks)
                    └────────┬─────────┘
                             │
                    ┌────────▼─────────┐
                    │    HOSTING       │  (stacked horizontal panels)
                    └────────┬─────────┘
                             │
                    ┌────────▼─────────┐
                    │      VPS         │  (partitioned container)
                    └────────┬─────────┘
                             │
              ┌──────────────▼──────────────┐
              │      OPERATIONS CORE        │  ← NEW — glassmorphism hub
              │  (heart of the platform)    │    behind the server rack
              └──────────────┬──────────────┘
                             │
                    ┌────────▼─────────┐
                    │   SERVER RACK    │  (primary visual anchor, center)
                    └────────┬─────────┘
                             │
             ┌───────────────┼───────────────┐
             │               │               │
     ┌───────▼──────┐ ┌──────▼─────┐ ┌──────▼──────────┐
     │  MONITORING  │ │  ASSETS    │ │     EMAIL       │
     │  (charts)    │ │  (grid)    │ │  (envelope node)│
     └───────┬──────┘ └──────┬─────┘ └──────┬──────────┘
             │               │               │
     ┌───────▼──────┐ ┌──────▼─────┐ ┌──────▼──────────┐
     │  ANALYTICS   │ │NOTIFICATION│ │    SECURITY     │
     │  (geo nodes) │ │  (flow)    │ │   (shield)      │
     └──────────────┘ └────────────┘ └─────────────────┘
```

**Reading direction:** Top-to-bottom, left-to-right. The eye enters at the
Internet globe (upper-left), follows connection lines down through cloud/DNS/
domains/hosting/VPS into the Operations Core, radiates outward to the server
rack, then spreads across monitoring, assets, email, notifications, analytics,
and terminates at the security shield on the right edge.

---

## 4. OBJECT LIBRARY

### 4.0 Operations Core — Central Visual Anchor (NEW)

This is the single most important addition. The Operations Core sits behind
the server rack and visually represents "the heart of the platform."

| Property | Value |
|----------|-------|
| **Concept** | A soft, luminous, glassmorphism hub — concentric elliptical rings with a radial glow center. Suggests a command center, a processing core, the central nervous system of OpsPilot. |
| **Style** | 3–4 soft concentric rings (ellipses or circles, slightly staggered), frosted glass fill, indigo→purple radial glow. No hard edges. No text. No logo. |
| **Dimensions** | ~500 × 500px — largest single element (at 3840×2160 scale) |
| **Position** | Dead center of the illustration zone (left 60%), directly behind the server rack |
| **Depth** | Layer 4 — between background blobs (layer 3) and middle-zone objects (layer 5+) |
| **Light mode** | Fill: `rgba(255,255,255,0.30)` with `backdrop-filter: blur(40px)`. Border: `rgba(99,102,241,0.08)`. Inner glow: radial `#6366f1/10` → `#a855f7/5`. Subtle shadow: `0 0 80px rgba(99,102,241,0.06)`. |
| **Dark mode** | Fill: `rgba(99,102,241,0.06)` with `backdrop-filter: blur(50px)`. Inner glow: radial `#6366f1/15` → `#9333ea/8`. Stronger glow: `0 0 120px rgba(99,102,241,0.10)`. |
| **Rings** | Ring 1 (outermost): `#6366f1` at 6%, 3px stroke, ~500px diameter. Ring 2: `#818cf8` at 8%, 2px stroke, ~380px diameter. Ring 3: `#a855f7` at 10%, 1.5px stroke, ~260px diameter. Ring 4 (innermost): `#6366f1` at 12%, 1px stroke, ~140px diameter. |
| **Effect** | Rings are not perfectly concentric — they orbit slightly, suggesting dynamic processing. Subtle pulsing glow at center (static in final image, implied via gradient). |
| **Connections** | All 14 objects connect TO the Operations Core. Network lines flow outward from the core to every zone object. The core is the central junction. |
| **Purpose** | Transforms the composition from "a collection of objects" into "a unified platform with a central operations hub." |

### 4.1 Master Object List — All 15 Platform Concepts

Each concept maps to a distinct OpsPilot platform domain. All objects must be
abstract geometric forms — no realistic hardware, no UI mockups, no text labels.

Objects are organized by **depth layer** (Background / Middle / Foreground).

### BACKGROUND LAYER (far depth — lowest opacity, largest scale, most blurred)

| # | Concept | Abstract Representation | Style | Position |
|---|---------|------------------------|-------|----------|
| B1 | **Background Grid** | Repeating 40×40px square grid pattern covering entire canvas | 1px lines, `#6366f1` at 3% (light) or white at 1.2% (dark) | Full canvas |
| B2 | **Ambient Glow Wash** | Large radial gradient from center-left, soft falloff to edges | `#eef2ff`/`#faf5ff` at 40–60% | Center-left, 800px radius |
| B3 | **Geometric Background Blobs** | 3–4 large rounded polygons (200–500px), deeply blurred (40–80px), very low opacity | `#6366f1` at 3–5% (light), `#a855f7` at 4% (dark) | Scattered behind composition |

### MIDDLE LAYER (medium depth — moderate opacity, standard scale, no blur)

| # | Concept | Abstract Representation | Style | Position |
|---|---------|------------------------|-------|----------|
| M1 | **Operations Core** | 4 soft concentric elliptical rings with frosted glass fill and radial indigo→purple glow. Central hub of the entire composition — all objects connect to it. | Glassmorphism: `rgba(255,255,255,0.30)` fill + `blur(40px)`. Rings: `#6366f1`/`#818cf8`/`#a855f7` at 6–12% opacity, 1–3px strokes. Center glow: radial `#6366f1/10 → #a855f7/5`. 500×500px. | Dead center of illustration zone, behind the server rack |
| M2 | **Internet** | Wireframe globe hemisphere — semi-circle with 3–4 radiating concentric arcs outward. Small glowing center node. | `#6366f1` stroke at 15%, 1.5px, no fill. Node: `#6366f1` at 40%, 8px. ~140×140px. | Far upper-left |
| M3 | **Cloud Infrastructure** | 2 clusters of 5–6 overlapping rounded rectangles (radius 8px) in cloud formation. | Fill: `#4f46e5/10 → #9333ea/6` gradient. Stroke: `#6366f1` at 20%, 1.5px. ~200×120px per cluster. | Upper-center |
| M4 | **DNS** | 3 concentric arc segments or layered circular rings with dotted connection lines descending to domain blocks. | Stroke: `#6366f1` at 12% dotted, `#a855f7` at 10% accent ring. 1px dotted lines downward. ~120×120px. | Upper-right |
| M5 | **Domains** | Loose 3×2 grid of 6 small rounded rectangles (40×30px each). | Fill: `#6366f1` at 10%. Stroke: `#6366f1` at 20%, 1.5px, rounded corners 6px. ~140×80px total. | Below DNS |
| M6 | **Hosting** | 3 wide horizontal rounded rectangles stacked vertically (180×30px each, 8px spacing). | Fill: `#4f46e5` at 8%. Stroke: `#6366f1` at 18%, 1.5px. | Left-center, beside rack |
| M7 | **VPS** | Large rounded rectangle subdivided by thin vertical lines into 4 partitions. Each partition has a tiny indicator dot. | Outer fill: `#a855f7` at 8%. Dividers: `#6366f1` at 15%, 1px. ~160×100px. | Above server rack |

### FOREGROUND LAYER (near depth — highest opacity, sharpest edges, smallest scale)

| # | Concept | Abstract Representation | Style | Position |
|---|---------|------------------------|-------|----------|
| F1 | **Server Rack** | Primary visual anchor. Isometric/flat-layered — 3 stacked horizontal modules with LED dots and ventilation slit lines. | Fill: gradient `#4f46e5/12 → #9333ea/8`. Stroke: `#6366f1` at 25%, 2px, rounded corners 12px. 3 slots, 2 LED dots per slot, 4–5 slit lines per slot. ~350×500px. | Center, directly in front of Operations Core |
| F2 | **Network Lines** | Sweeping cubic bezier curves connecting the Operations Core outward to every object in the composition. Thin, elegant, readable mesh. | `#6366f1` at 15–25%, 1.5px. Small glowing nodes (6–8px) at intersections. Main trunk lines radiate from Operations Core. | Throughout composition |
| F3 | **Asset Management** | Tidy 3×3 grid of tiny rounded squares (24×24px each). Some with colored indicator dots (emerald, amber). | Fill: `#6366f1` at 8–10%. Stroke: `#6366f1` at 15%, 1px. Dots: 4px. ~100×80px total. | Lower-left |
| F4 | **Monitoring Dashboard** | Abstract chart cluster: 1 rising curve line with emerald uptick dot, 4 vertical bars, 1 donut arc segment. No axes or labels. | Line: `#6366f1` at 30%, 2px + gradient fill. Bars: `#a855f7` at 12–18%. Donut: `#6366f1` at 20%. ~400×180px. | Lower-center-left |
| F5 | **Email** | Geometric envelope — rounded square with triangular fold detail. Connected to server rack by a short line. | Fill: `#6366f1` at 10%. Stroke: `#6366f1` at 20%, 1.5px. ~80×60px. | Right of server rack |
| F6 | **Analytics** | 3 varied geometric shapes (diamond, triangle, hexagon) connected by thin lines — data relationship diagram. | Fill: `#a855f7` at 10–15%. Stroke: `#6366f1` at 18%, 1.5px. Each ~24–36px. Lines: `#6366f1` at 12%, 1px, some dotted. | Lower-center-right |
| F7 | **Notification Flow** | Curved dotted path line from server rack toward security shield, with 5–8 streaming small circles (4–6px). | Path: `#6366f1` at 12%, 1px dashed. Dots: `#6366f1` at 40%, one amber dot at 30%. Suggests flow direction (larger at front, smaller at tail). | Mid-to-lower, rightward |
| F8 | **Security Shield** | Semi-transparent shield outline on far right edge of illustration zone, acting as transition to safe zone. | Stroke: `#6366f1` at 20–30% (light) or `#818cf8` at 25% (dark), 2.5px. No fill. Background glow: radial `#6366f1` at 8%, 60px blur. ~180×220px. | Right edge of illustration zone |

### 4.2 Semantic Status Indicators (Applied Sparingly)

| Indicator | Color | Placement |
|-----------|-------|-----------|
| **Success dot** | `#10b981` at 30% | On monitoring chart line uptick + 1–2 asset grid squares |
| **Warning dot** | `#f59e0b` at 25% | 1 node in notification flow path |
| **Critical dot** | `#ef4444` at 20% | 1 tiny node on monitoring dashboard (optional) |

These are subtle 6–8px circles. They must not dominate the composition.

### 4.3 Forbidden Objects

```
✗ People, humans, characters, silhouettes, avatars, faces, hands
✗ Anime, cartoon, comic, illustrative style characters
✗ Photorealistic objects, photographs, textures
✗ Animals, mascots, brand characters, robots
✗ Buildings, cityscapes, skylines, architecture
✗ Natural elements: fire, water, trees, plants, smoke
✗ Realistic hardware: actual server photos, cable photos, rack photos
✗ UI elements: buttons, forms, input fields, menus, tabs, cards
✗ Text: any letters, words, numbers, labels, tags
✗ Logos, watermarks, signatures, copyright marks
✗ Charts with gridlines, axes labels, or data values
✗ Sharp 90-degree corners — all objects must have rounded corners
✗ Flowchart arrows — use bezier curves, not arrowheads
✗ Overlapping objects that create visual clutter
```

---

## 5. COMPOSITION & DEPTH LAYERS

### 5.1 Three Explicit Depth Layers

The illustration uses three depth layers to create spatial depth WITHOUT
realistic 3D rendering. Depth is achieved through opacity, blur, scale,
and shadow — not perspective.

```
                      ┌────────────────────┐
                      │   FOREGROUND       │ ← sharpest, highest opacity
                      │   Layer 8-15       │    smallest scale
                      │                    │
                      │   Server Rack      │
                      │   Network Lines    │
                      │   Asset Grid       │
                      │   Monitoring       │
                      │   Email            │
                      │   Analytics        │
                      │   Notifications    │
                      │   Security Shield  │
                      └─────────┬──────────┘
                                │
                      ┌─────────▼──────────┐
                      │   MIDDLE           │ ← moderate opacity
                      │   Layer 4-7        │    standard scale
                      │                    │    no blur
                      │   Operations Core  │
                      │   Internet         │
                      │   Cloud            │
                      │   DNS              │
                      │   Domains          │
                      │   Hosting          │
                      │   VPS              │
                      └─────────┬──────────┘
                                │
                      ┌─────────▼──────────┐
                      │   BACKGROUND        │ ← lowest opacity
                      │   Layer 0-3        │    largest scale
                      │                    │    Gaussian blur
                      │   Grid             │
                      │   Ambient Glow     │
                      │   Geometric Blobs  │
                      └────────────────────┘
```

### 5.2 Layer Stack (Back to Front — Full 19 Layers)

| Layer | Content | Depth Technique | Light Opacity | Dark Opacity |
|-------|---------|----------------|---------------|--------------|
| 0 | Background solid | — | `#f8fafc` 100% | `#000000` 100% |
| 1 | Grid pattern (40px squares) | Flat, no blur | `#6366f1` at 3% | White at 1.2% |
| 2 | Ambient gradient wash | Radial, 800px, no blur | `#eef2ff` at 60% → 0% | `#6366f1` at 10% → 0% |
| 3 | Geometric blobs (3–4) | Gaussian blur 60px | `#6366f1` at 4% | `#a855f7` at 5% |
| 4 | **Operations Core rings** | Glassmorphism blur 40px | `rgba(255,255,255,0.30)` + glow | `rgba(99,102,241,0.06)` + glow |
| 5 | Internet globe | No blur, 15% stroke | `#6366f1` at 15% | `#818cf8` at 18% |
| 6 | Cloud nodes | No blur, 20% stroke | Fill at 10% | Fill at 14% |
| 7 | DNS rings + connections | No blur, dotted | Stroke at 12% | Stroke at 15% |
| 8 | Domain blocks | No blur, 20% stroke | Fill at 10% | Fill at 14% |
| 9 | Hosting panels | No blur, 18% stroke | Fill at 8% | Fill at 12% |
| 10 | VPS container | No blur, 15% stroke | Fill at 8% | Fill at 12% |
| 11 | **Server rack** (anchor) | Sharp, 25% stroke, shadow | Fill at 12% | Fill at 18% |
| 12 | Asset grid | Sharp, 15% stroke | Fill at 10% | Fill at 14% |
| 13 | Monitoring charts | Sharp, 30% line | Line at 30% | Line at 35% |
| 14 | Email node | Sharp, 20% stroke | Fill at 10% | Fill at 14% |
| 15 | Analytics nodes | Sharp, 18% stroke | Fill at 12% | Fill at 16% |
| 16 | Notification flow path + dots | Sharp, dashed | Path at 12%, dots at 40% | Path at 15%, dots at 45% |
| 17 | **Security shield** | Sharp, 25% stroke, glow | Stroke at 25% | Stroke at 30% |
| 18 | Network lines (all connections) | Sharp, bezier | Stroke at 15–25% | Stroke at 18–28% |
| 19 | Subtle glass vignette (right fade) | Gradient, no blur | `rgba(255,255,255,0.03)` | `rgba(0,0,0,0.05)` |

### 5.3 Depth Rules

| Rule | Background | Middle | Foreground |
|------|-----------|--------|------------|
| **Gaussian blur** | 40–80px | 0px | 0px |
| **Opacity range** | 2–5% | 8–15% | 12–40% |
| **Stroke thickness** | 1px | 1.5px | 1.5–2.5px |
| **Shadow** | None | Subtle (4px blur) | Noticeable (8px blur) |
| **Scale** | 200–500px (oversized) | 100–500px (standard) | 60–500px (mixed) |
| **Detail level** | None (pure shape) | Low (2–3 elements) | High (4–6 elements) |

### 5.4 Visual Weight Distribution

| Element | Weight | Layer |
|---------|--------|-------|
| Operations Core (hub) | 18% | Middle |
| Server rack (anchor) | 16% | Foreground |
| Network lines (connective) | 12% | Foreground |
| Cloud nodes (upper) | 8% | Middle |
| Monitoring charts (lower) | 8% | Foreground |
| Security shield (right edge) | 7% | Foreground |
| DNS + Domains (upper-right) | 7% | Middle |
| Internet globe (upper-left) | 6% | Middle |
| Hosting + VPS (mid-left) | 6% | Middle |
| Notification flow | 5% | Foreground |
| Analytics nodes | 4% | Foreground |
| Email node | 3% | Foreground |
| Asset grid | 3% | Foreground |
| Background elements | 3% | Background |

---

## 6. LIGHTING & ATMOSPHERE

### 6.1 Light Mode

| Property | Value |
|----------|-------|
| **Background** | `#f8fafc` (gray-50) |
| **Primary glow** | Subtle radial gradient from center-left: `rgba(99,102,241,0.06)` → transparent, 800px |
| **Operations Core glow** | Radial `#6366f1` at 8% → `#a855f7` at 4%, 400px radius, center of composition |
| **Shadow (mid-ground)** | Objects cast subtle shadow bottom-right: `rgba(0,0,0,0.03)`, 4px blur |
| **Shadow (foreground)** | Objects cast clearer shadow: `rgba(0,0,0,0.05)`, 8px blur |
| **Highlight** | White strokes at 30% opacity on top edges of server rack and Operations Core |
| **Overall** | Clean, bright, airy — matches `.glass-card` light aesthetic |

### 6.2 Dark Mode

| Property | Value |
|----------|-------|
| **Background** | `#000000` (pure black — matches portal body) |
| **Primary glow** | Center-left radial: `rgba(99,102,241,0.10)` → transparent, 600px radius |
| **Secondary glow** | Lower-left radial: `rgba(168,85,247,0.06)` → transparent, 400px radius |
| **Operations Core glow** | Radial `#6366f1` at 15% → `#9333ea` at 8%, 500px radius — luminous hub |
| **Shadow** | Objects merge into black (no cast shadow needed in dark mode) |
| **Highlight** | Indigo-400 (`#818cf8`) strokes at 25% on object edges |
| **Overall** | Dark, premium, glassy, glow-driven — matches `.dark .glass-card` aesthetic |

### 6.3 Shared Lighting Rules

- All objects use flat/soft diffuse lighting — no hard shadows
- Glow effects use `blur()` equivalent: 40–80px for ambient, 8–16px for accent
- No specular highlights, no reflections, no caustics
- Consistent light source from upper-left (matches portal illumination convention)
- Light mode: brighter, higher contrast, crisp edges, subtle casting shadows
- Dark mode: glow-driven, softer edges, more atmospheric, objects emerge from dark

---

## 7. SPECIFIC OBJECT STYLING

### 7.0 Operations Core

```
Style:      Glassmorphism hub — 4 elliptical concentric rings with
            frosted glass center. No hard edges. No text. No logo.
Dimensions: ~500 × 500px — the largest single element
Position:   Dead center of illustration zone, behind the server rack
Depth:      Middle layer (layer 4)

Glass fill (light):
  background: rgba(255, 255, 255, 0.30)
  backdrop-filter: blur(40px)
  border: 1px solid rgba(99, 102, 241, 0.08)
  box-shadow: 0 0 80px rgba(99, 102, 241, 0.06)

Glass fill (dark):
  background: rgba(99, 102, 241, 0.06)
  backdrop-filter: blur(50px)
  border: 1px solid rgba(99, 102, 241, 0.10)
  box-shadow: 0 0 120px rgba(99, 102, 241, 0.10)

Rings (outside-in):
  Ring 1: #6366f1 at 6%, 3px stroke, ~500px, slightly elliptical
  Ring 2: #818cf8 at 8%, 2px stroke, ~380px, rotated 2 degrees
  Ring 3: #a855f7 at 10%, 1.5px stroke, ~260px
  Ring 4: #6366f1 at 12%, 1px stroke, ~140px, subtle glow fill

Ring behavior: NOT perfectly concentric — slight orbital offset
(2–5px) to suggest dynamic processing activity.

Center glow: radial gradient from #6366f1/15 → #a855f7/5
  Soft, no hard edges, approximately 60px radius
```

### 7.1 Internet Globe

```
Style:      Wireframe hemisphere — semi-circle with radiating arcs
Dimensions: ~140 × 140px
Lines:      3–4 concentric thin arcs radiating outward from bottom
            of hemisphere. 1 vertical meridian line through center.
Stroke:     #6366f1 at 15% opacity, 1.5px
Center:     1 glowing node, 8px circle, #6366f1 at 40%
```

### 7.2 Cloud Nodes

```
Style:      Geometric — 5–6 overlapping rounded rectangles (radius 8px)
            arranged to suggest a cloud silhouette
Dimensions: ~200 × 120px per cluster, 2 clusters
Fill:       #4f46e5 at 10% → #9333ea at 6% gradient
Stroke:     #6366f1 at 20% opacity, 1.5px
```

### 7.3 DNS

```
Style:      3 concentric arc segments or layered circular rings
            with dotted connection lines descending to domain blocks
Dimensions: ~120 × 120px
Stroke:     #6366f1 at 12% (arcs, dotted), #a855f7 at 10% (accent ring)
Lines:      1px dotted, connecting downward to domains
```

### 7.4 Domains

```
Style:      Loose grid of small rounded rectangles — 6 blocks in 3×2
Dimensions: Each block ~40 × 30px, total ~140 × 80px
Fill:       #6366f1 at 10%, some with subtle #a855f7 at 6% variation
Stroke:     #6366f1 at 20%, 1.5px, rounded corners 6px
```

### 7.5 Hosting Panels

```
Style:      3 wide horizontal rounded rectangles stacked vertically
Dimensions: ~180 × 30px each, spaced 8px apart
Fill:       #4f46e5 at 8%
Stroke:     #6366f1 at 18%, 1.5px
```

### 7.6 VPS

```
Style:      Large rounded rectangle subdivided by thin vertical lines
            into 4 partitions, suggesting virtualized servers
Dimensions: ~160 × 100px
Outer fill: #a855f7 at 8%
Divider:    #6366f1 at 15%, 1px vertical lines
Each partition: small indicator dot at top-right corner
```

### 7.7 Server Rack

```
Style:      Isometric-angled or flat layered — 3 stacked horizontal
            modules. This is the largest foreground object.
Dimensions: ~350 × 500px (at 3840×2160 scale)
Fill:       Linear gradient: #4f46e5/12 → #9333ea/8
Stroke:     #6366f1 at 25% opacity, 2px, rounded corners 12px
Front:      3 server slots, each with:
            - 2 small LED dots (#6366f1 at 50% and #10b981 at 40% for success)
            - 4–5 horizontal ventilation slit lines (#6366f1 at 15%)
Depth:      Optional side face at 35% width with darker fill
            (#4f46e5 at 15%)
```

### 7.8 Email Node

```
Style:      Abstract envelope — rounded square with a triangular
            top-fold detail OR a simple message bubble shape
Dimensions: ~80 × 60px
Fill:       #6366f1 at 10%
Stroke:     #6366f1 at 20%, 1.5px
Connection: Small line extending to server rack / Operations Core
```

### 7.9 Network Lines (Connective Tissue)

```
Style:      Sweeping cubic bezier curves radiating outward from the
            Operations Core to every object in the composition
Stroke:     #6366f1 at 15–25% opacity, 1.5px
            White at 20% highlight variant on some curves
Nodes:      Small circles (6–8px) at key intersections
            Fill: #6366f1 at 30%, glow: 12px at 15%
Flow:       Main trunk lines radiate from Operations Core like spokes.
            Secondary branches connect to individual objects.
            NOT a tangled web — clean, spaced, readable mesh.
            Lines should be thicker (2px) near the Core, tapering to
            thinner (1px) at endpoints to suggest energy disbursement.
```

### 7.10 Asset Grid

```
Style:      3×3 or 4×3 grid of tiny rounded squares
Dimensions: Each ~24 × 24px, total ~100 × 80px
Fill:       #6366f1 at 8–10%, some with #a855f7 at 6%
Stroke:     #6366f1 at 15%, 1px
Indicators: 2–3 squares have small colored dots (emerald or amber)
            in top-right corner (4px circles)
```

### 7.11 Monitoring Dashboard

```
Style:      Minimal chart cluster — no axes, no labels, no gridlines
Position:   Lower-center-left, ~400 × 180px
Elements:
  Line:     1 smooth rising curve from left to right
            Stroke: #6366f1 at 30%, 2px
            Area below: linear-gradient(#6366f1/8 → transparent)
            Uptick dot: #10b981 at 40%, 6px circle (success indicator)
  Bars:     4 vertical bars of varying height (30%, 60%, 45%, 80%)
            Fill: #a855f7 at 12–18%, rounded top corners 4px
            Widest bar optionally filled with gradient
  Donut:    1 arc segment (quarter-circle) in lower-right of chart area
            Stroke: #6366f1 at 20%, 3px
Background: Subtle card shape behind charts, rounded-xl, #6366f1/5 fill
```

### 7.12 Security Shield

```
Style:      Minimal outline — no fill, semi-transparent
Position:   Right edge of illustration zone, fading toward safe zone
Dimensions: ~180 × 220px
Stroke:     #6366f1 at 20–30% (light) or #818cf8 at 25% (dark), 2.5px
Shape:      Simplified shield: 2 curved top edges, straight sides,
            pointed or flat bottom. No internal icons or patterns.
Effect:     Subtle glow behind shield: radial #6366f1 at 8%, 60px blur
```

### 7.13 Notification Flow

```
Style:      Streaming dots along a curved path
Path:       Gentle arc from server rack (mid-center) toward
            the security shield (right edge)
Line:       #6366f1 at 12% opacity, 1px, dashed or dotted
Dots:       5–8 circles (4–6px) distributed along the path
            Fill: #6366f1 at 40%, one amber dot #f59e0b at 30%
Motion:     Dots should suggest flow direction — slightly larger
            at the front of the flow, smaller at the tail
```

### 7.14 Analytics Nodes

```
Style:      Geometric data relationship diagram
Elements:   3–4 varied shapes:
            - Diamond (pointed top/bottom, rounded corners)
            - Triangle (equilateral, rounded corners)
            - Hexagon (6-sided, rounded corners)
Dimensions: ~24–36px each
Fill:       #a855f7 at 10–15%
Stroke:     #6366f1 at 18%, 1.5px
Lines:      3–4 thin lines connecting nodes to monitoring chart
            #6366f1 at 12%, 1px, some dotted
```

### 7.15 Background Grid

```
Style:      Matches portal .bg-grid exactly
Pattern:    Repeating 40×40px squares
Lines:      1px, #6366f1 at 3% opacity (light)
            White at 1.2% opacity (dark)
```

---

## 8. RESPONSIVENESS & CUTOUTS

### 8.1 Layout Variants

The 3840×2160 master should be designed so it can be cropped to:

| Viewport | Crop | Notes |
|----------|------|-------|
| **1920×1080** | Center crop, full height | Most common desktop |
| **2560×1440** | Center crop, full height | Retina/QHD |
| **1536×864** | Center crop, full height | Tablet landscape |
| **834×1194 (iPad)** | Crop center 70% width, stack login below | Tablet portrait |
| **390×844 (mobile)** | Crop center 60%, very subtle edges | Mobile |

### 8.2 Safe Zone Detail

The right 40% of the 3840×2160 canvas must contain:
- Only: Very faint gradient extension of the ambient glow
- Only: 1–2 network line endpoints that fade to 0% opacity before the edge
- Only: The very right edge of the security shield (≤ 20% of shield width)
- No: Full objects, object centers, text, nodes, or anything that would clash with the login card overlay (482px max-width card)

---

## 9. AI GENERATION PROMPTS

### 9.1 Primary Prompt (Midjourney / DALL-E / Flux / SDXL)

```
A premium enterprise login page background illustration for OpsPilot,
an Enterprise IT Operations Platform. 16:9, 4K resolution.

The artwork uses THREE EXPLICIT DEPTH LAYERS to create spatial depth
through opacity, blur, scale, and shadow — NOT through 3D rendering.

BACKGROUND LAYER (lowest opacity, largest scale, Gaussian blur 40-80px):
- A faint 40px square engineering grid pattern
- A soft radial ambient glow in indigo from center-left
- 3-4 large rounded geometric blobs in deep indigo at 4% opacity

MIDDLE LAYER (moderate opacity, standard scale, no blur):
- THE OPERATIONS CORE — a large frosted glass hub at the exact center
  of the composition. 4 soft elliptical concentric rings in indigo
  and purple at 6-12% opacity with a radial glow center. The rings
  are slightly off-center from each other to suggest dynamic processing.
  Glassmorphism effect: semi-transparent fill with 40px backdrop blur.
  This is the heart of the platform — all objects connect to it.
- A wireframe globe hemisphere with radiating arcs (Internet)
- Two clusters of overlapping rounded rectangles (Cloud)
- Three concentric arc rings with dotted descending lines (DNS)
- A 3x2 grid of small rounded rectangle blocks (Domains)
- Three wide horizontal stacked panels (Hosting)
- A subdivided partitioned container with vertical dividers (VPS)

FOREGROUND LAYER (highest opacity, sharpest edges, noticeable shadows):
- A 3-slot isometric server rack with LED dots and ventilation lines,
  positioned directly IN FRONT of the Operations Core. This is the
  primary visual anchor.
- A tidy 3x3 grid of tiny rounded squares with colored dots (Assets)
- Abstract charts: one rising curve with a tiny emerald uptick dot,
  four vertical bars, one donut arc (Monitoring)
- A geometric envelope shape with triangular fold (Email)
- A diamond, triangle, and hexagon connected by thin lines (Analytics)
- A curved dashed path with 5-8 streaming small circles (Notifications)
- A semi-transparent shield outline on the far right edge (Security)
- Sweeping bezier network lines radiating FROM the Operations Core
  outward to every object. Lines taper from thicker (2px) near the
  Core to thinner (1px) at endpoints.

VISUAL FLOW (suggested through placement, not arrows):
Internet → Cloud → DNS → Domains → Hosting → VPS → Operations Core
→ Server Rack → Monitoring → Assets → Email → Notifications →
Analytics → Security. Read top-to-bottom, left-to-right.

COLORS (STRICT): pure indigo (#6366f1), deep indigo (#4f46e5),
light purple (#a855f7), purple (#9333ea), and their transparent
tints on white-gray (#f8fafc). Emerald (#10b981) ONLY on chart
uptick — max 2 dots. Amber (#f59e0b) ONLY on notification dot —
max 1 dot. Red ONLY if critical indicator — max 1 dot.
No blue. No cyan. No neon. No rainbow. No green beyond accent.

CRITICAL: Right 40% deliberately EMPTY — only grid and faint glow.
Login form overlay sits here.

RULES: No people. No humans. No characters. No anime. No cartoon.
No text. No letters. No words. No labels. No logos. No watermarks.
No UI elements. No buttons. No forms. No realistic hardware photos.
No buildings. No cityscapes. No nature. No fire. No neon.
No cyberpunk. No gaming aesthetics. No flowchart arrows.

STYLE: Enterprise premium illustration. Clean, minimal, glassmorphism.
Soft diffuse lighting, upper-left source. Frosted glass on Operations
Core. Depth through opacity/blur/scale — NOT 3D.
Comparable to: Microsoft Azure portal abstract art, GitHub Enterprise,
Atlassian Cloud diagrams, Vercel, Linear, Laravel Nova.
```

### 9.2 Dark Mode Variant Prompt

```
Identical to primary prompt EXCEPT:

Background: pure black (#000000) instead of white-gray.
Operations Core glass: background rgba(99,102,241,0.06) with 50px
  backdrop blur, stronger glow: 0 0 120px rgba(99,102,241,0.10).
Operations Core inner glow: radial #6366f1 at 15% → #9333ea at 8%.
Primary glow: indigo at 10% from center-left + purple at 6% from lower-left.
Object strokes shift to indigo-400 (#818cf8) at 20-25% opacity.
Grid lines: white at 1.2% opacity instead of indigo.
No drop shadows — objects emerge from the dark background.
Overall: darker, glow-driven, more atmospheric, luminous core.
```

### 9.3 Negative Prompt

```
people, human, face, character, anime, cartoon, mascot, animal,
text, typography, letters, words, numbers, logo, watermark,
signature, UI, button, form, input, menu, icon, dashboard UI,
cityscape, building, skyline, nature, tree, plant, fire, smoke,
explosion, neon, rainbow, cyan, blue, green (except emerald accent),
gaming, cyberpunk, glitch, cluttered, busy, noisy, chaotic,
sharp corners, right angles, photorealism, realistic photo,
3D render, ray tracing, reflection, caustics, lens flare,
film grain, arrowhead, flowchart arrow, directional arrow
```

---

## 10. THEME VARIANTS

### 10.1 Light Mode Theme

```yaml
background: "#f8fafc"
grid_lines: "rgba(99, 102, 241, 0.03)"
ambient_glow:
  - color: "rgba(99, 102, 241, 0.06)"
    radius: 800px
    position: "center-left"
core_glass: "rgba(255, 255, 255, 0.30)"
core_blur: "40px"
core_glow: "rgba(99, 102, 241, 0.06)"
core_shadow: "0 0 80px rgba(99, 102, 241, 0.06)"
core_rings:
  - color: "rgba(99, 102, 241, 0.06)"  stroke_width: 3px  diameter: 500px
  - color: "rgba(129, 140, 248, 0.08)" stroke_width: 2px  diameter: 380px
  - color: "rgba(168, 85, 247, 0.10)"  stroke_width: 1.5px diameter: 260px
  - color: "rgba(99, 102, 241, 0.12)"  stroke_width: 1px   diameter: 140px
object_fill: "linear-gradient(135deg, rgba(79,70,229,0.12), rgba(147,51,234,0.08))"
object_stroke: "rgba(99, 102, 241, 0.25)"
network_lines: "rgba(99, 102, 241, 0.20)"
chart_line: "rgba(99, 102, 241, 0.30)"
chart_area_fill: "linear-gradient(to top, rgba(99,102,241,0.08), transparent)"
shadow_mid: "rgba(0, 0, 0, 0.03)"
shadow_fore: "rgba(0, 0, 0, 0.05)"
accent_emerald: "rgba(16, 185, 129, 0.30)"
accent_amber: "rgba(245, 158, 11, 0.25)"
accent_red: "rgba(239, 68, 68, 0.20)"
```

### 10.2 Dark Mode Theme

```yaml
background: "#000000"
grid_lines: "rgba(255, 255, 255, 0.012)"
ambient_glow:
  - color: "rgba(99, 102, 241, 0.10)"
    radius: 800px
    position: "center-left"
  - color: "rgba(168, 85, 247, 0.06)"
    radius: 600px
    position: "bottom-left"
core_glass: "rgba(99, 102, 241, 0.06)"
core_blur: "50px"
core_glow: "rgba(99, 102, 241, 0.10)"
core_shadow: "0 0 120px rgba(99, 102, 241, 0.10)"
core_rings:
  - color: "rgba(99, 102, 241, 0.08)"  stroke_width: 3px  diameter: 500px
  - color: "rgba(129, 140, 248, 0.10)" stroke_width: 2px  diameter: 380px
  - color: "rgba(168, 85, 247, 0.12)"  stroke_width: 1.5px diameter: 260px
  - color: "rgba(99, 102, 241, 0.15)"  stroke_width: 1px   diameter: 140px
object_fill: "linear-gradient(135deg, rgba(79,70,229,0.18), rgba(147,51,234,0.12))"
object_stroke: "rgba(129, 140, 248, 0.25)"
network_lines: "rgba(129, 140, 248, 0.20)"
chart_line: "rgba(129, 140, 248, 0.30)"
chart_area_fill: "linear-gradient(to top, rgba(129,140,248,0.10), transparent)"
shadow_mid: "none"
shadow_fore: "none"
accent_emerald: "rgba(16, 185, 129, 0.35)"
accent_amber: "rgba(245, 158, 11, 0.30)"
accent_red: "rgba(239, 68, 68, 0.25)"
```

---

## 11. TECHNICAL CONSTRAINTS

### 11.1 File Requirements

| Property | Value |
|----------|-------|
| **Format** | PNG-24 (lossless) or WebP (lossless) |
| **Max file size** | 500 KB (WebP) / 1 MB (PNG) after optimization |
| **Color depth** | 24-bit sRGB |
| **Alpha** | Not required (solid background) |
| **Metadata** | Strip EXIF — copyright: OpsPilot / alphatach.com |

### 11.2 Integration Requirements

- Image is decorative: `<img>` tag with `aria-hidden="true"` and `role="presentation"`
- Applied as: `background-image` on the login page body or as an absolutely positioned `<img>` behind the login card
- CSS class: `pointer-events-none select-none`
- No `<picture>` or responsive image switching needed — 4K source scales down gracefully
- Must be compatible with both light/dark themes — either:
  - **(Option A — recommended):** Two separate images, toggled via `.dark` class on `<html>`
  - **(Option B):** Single image with medium opacity that works on both backgrounds (less ideal)

### 11.3 Accessibility

- Image is purely decorative: `alt=""` (empty alt text)
- Must not convey information required for login
- Must respect `prefers-reduced-motion`: static only (no animation in final asset)

---

## 12. QUALITY CHECKLIST

Before finalizing the artwork, verify:

### Platform Coverage
- [ ] Operations Core — frosted glass hub with 4 concentric rings at center
- [ ] Internet — wireframe globe/hemisphere (upper-left)
- [ ] Cloud Infrastructure — geometric rounded-rect clusters (upper-center)
- [ ] DNS — concentric arc rings (upper-right)
- [ ] Domains — small rounded-rect grid blocks (below DNS)
- [ ] Hosting — stacked horizontal panels (left-center)
- [ ] VPS — subdivided partitioned container (above rack)
- [ ] Server Rack — isometric 3-slot unit (center, in front of Core)
- [ ] Network — sweeping bezier curves radiating FROM Core to all objects
- [ ] Email — geometric envelope or message node (right of rack)
- [ ] Asset Management — tiny organized square grid (lower-left)
- [ ] Monitoring Dashboard — abstract line + bars + donut (lower-center)
- [ ] Analytics — diamond, triangle, hexagon nodes with lines (lower-right)
- [ ] Notification Flow — streaming dots on curved path (mid-right)
- [ ] Security Shield — semi-transparent outline (right edge)

### Depth & Composition
- [ ] Three explicit depth layers (Background / Middle / Foreground)
- [ ] Background: grid + glow + blurred blobs at 2–5% opacity
- [ ] Middle: Operations Core + upper-zone objects at 8–15% opacity
- [ ] Foreground: server rack + lower-zone objects at 12–40% opacity
- [ ] Depth achieved through opacity/blur/scale/shadow — NOT 3D
- [ ] Server rack sits directly in front of Operations Core
- [ ] Network lines taper from 2px (near Core) to 1px (at endpoints)
- [ ] Right 40% deliberately empty for login card

### Design Compliance
- [ ] ALL colors from portal palette only (indigo, purple, gray, white, black)
- [ ] Emerald ONLY on chart uptick (≤ 5% of image)
- [ ] Amber ONLY on notification node (≤ 3% of image)
- [ ] Red ONLY on critical indicator (≤ 2% of image)
- [ ] No blue gradients, no cyan, no neon, no rainbow
- [ ] All objects use rounded corners (≥ 8px)
- [ ] No people, characters, faces, anime
- [ ] No text, letters, words, logos, watermarks
- [ ] No UI elements (buttons, forms, menus)
- [ ] No realistic hardware, no photos, no buildings
- [ ] No flowchart arrows — bezier curves only
- [ ] Grid pattern matches portal `.bg-grid` (40px spacing)
- [ ] Light mode: gray-50 bg, indigo at 3–8% opacity
- [ ] Dark mode: pure black bg, indigo at 6–18% opacity
- [ ] File size ≤ 500 KB (WebP) or 1 MB (PNG)
- [ ] Works at 3840×2160 and scales to 1920×1080 cleanly

---

## 13. REFERENCE AESTHETIC (Descriptive)

```
"Imagine a login page background for OpsPilot — a premium Enterprise
IT Operations Platform.

At the center of the composition, a luminous frosted glass hub pulses
with soft indigo and purple concentric rings — the Operations Core.
It is the heart of the platform, the central nervous system from which
everything connects and radiates outward.

Above the Core, the journey begins: a wireframe globe (Internet) sends
connection lines down through translucent cloud clusters, through DNS
rings, and into a grid of domain blocks. Hosting panels and a partitioned
VPS container flank the path downward into the Core.

From the Core, the data flows forward into a three-slot server rack —
the primary visual anchor, rendered in crisp indigo gradient with small
glowing LED dots. The rack sits directly in front of the Core, creating
a powerful depth effect.

Below and around the rack, the platform comes alive: a tidy asset grid,
a minimal monitoring chart with a single rising emerald line, a geometric
email node, connected analytics shapes, and a streaming notification path
sending dots toward a semi-transparent security shield on the right edge.

Elegant bezier network lines radiate from the Operations Core like spokes,
connecting every object. Lines are thicker near the Core and taper as they
reach outward, suggesting energy and data flowing through the platform.

The entire scene sits on three depth layers: a softly blurred background
with grid and glow, a mid-ground with the Core and cloud-layer objects,
and a sharp foreground with the rack and data-layer objects. The right
40% opens into clean empty space — just the grid and a faint indigo glow —
ready for the login form.

The feeling: Microsoft Azure portal illustration meets GitHub Enterprise
meets Linear — clean, minimal, premium, infrastructure as art.
Professional. Expensive. No clutter. No humans. No text.
Just pure abstract enterprise technology with a beating heart."
```

---

*End of specification. Zero code modified — this is a read-only design artifact for AI image generation.*

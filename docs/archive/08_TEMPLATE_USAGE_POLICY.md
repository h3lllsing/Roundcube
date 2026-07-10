# 8. Premium Template Usage Policy

> Rules for evaluating and using external design resources.

---

## Policy Summary

**Do NOT use premium admin templates for this project.**

The current custom layout is more feature-rich than any commercial
template (see `PREMIUM_TEMPLATE_COMPARISON.txt` in previous analysis).
Replacing it with a template would cost features and introduce
incompatibilities.

However, external components can be used as INSPIRATION if they follow
the rules below.

---

## Hard Rules

### NEVER

| Rule | Why |
|---|---|
| Do NOT replace the existing layout with any template's layout | Current layout has command palette, toast system, permission UI, sidebar search, scroll persistence, animated counters — none exist in any template |
| Do NOT install any Bootstrap-based template | This project uses Tailwind CSS v4. Bootstrap classes will conflict and create a CSS mess. |
| Do NOT install any React/Vue/Inertia-based template | This project uses Blade. Mixing frameworks adds unacceptable complexity. |
| Do NOT copy a template's entire CSS file | Will create the same problem as the existing prototype CSS (lines 196-382 of app.css) |
| Do NOT purchase a template that requires its own build tool | Must work with existing Vite setup |

### MAYBE (with approval)

| Action | Condition |
|---|---|
| Reference a template's component design for inspiration | Follow the component spec in this document, not the template's API |
| Use a single component from a template library | Must be MIT-licensed or compatible. Must be adapted to use Tailwind v4, not directly copied. |
| Use Tailwind component libraries (Preline, Flowbite, etc.) for specific component ideas | Only for UX patterns. Never wholesale copy CSS classes. |

### YES (always)

| Action | Why |
|---|---|
| Look at template dashboards for layout ideas | Dashboard layout patterns are universal |
| Look at template table designs for inspiration | Table density, column layout, action placement |
| Look at template form designs for validation UX | Error placement, inline validation, section layout |
| Use open-source component libraries like Preline (MIT) for specific components | Preline is MIT, Tailwind-native, and can be used piecemeal |

---

## Template Evaluation Criteria

If a template is proposed as a reference source, evaluate against:

| Criteria | Must Pass |
|---|---|
| Framework | Tailwind CSS ONLY (v3 or v4) |
| JavaScript | Vanilla JS or Alpine.js ONLY |
| License | MIT, CC, or purchased with unlimited use |
| Build | Must work with Vite (no Webpack-only) |
| Dark mode | Must support dark mode natively |
| Responsive | Must be mobile-responsive |
| Components needed | Any component used must have documented API |
| Overlap with existing | Must not duplicate existing functionality |

---

## What the Current Layout Has That No Template Does

When tempted to "just use a template," remember:

| Feature | Current | Any Template |
|---|---|---|
| Command palette (Ctrl+K) | ✅ With live API search | ❌ None |
| Permission management UI | ✅ 11 interactive Alpine components | ❌ None |
| Activity timeline with diff | ✅ Event icons + before/after | ❌ None |
| Entity-type-specific badges | ✅ Domain, hosting, VPS, VoIP colors | ❌ None |
| Password reveal with logging | ✅ AJAX reveal + activity log | ❌ None |
| Linked/standalone renewal UI | ✅ Polymorphic source card | ❌ None |
| Bulk actions toolbar | ✅ Select-all + action dropdown | ❌ None |
| Dark mode with FOUC prevention | ✅ localStorage + system pref | ✅ Only if premium |
| Glass morphism | ✅ Custom `.glass` + `.glass-card` | ❌ Rare |
| Tailwind v4 | ✅ Latest version | ❌ Most are v3 or Bootstrap |

---

## Recommended Sources for Inspiration (Not Copying)

| Source | License | Use For |
|---|---|---|
| Preline (preline.cc) | MIT | Specific component patterns (forms, dropdowns, modals) |
| Tailwind UI (tailwindui.com) | Paid | Layout patterns (if they ever release v4 components) |
| Dribbble search: "admin dashboard tailwind" | N/A | Visual inspiration only |
| SaaS landing pages | N/A | Layout density, spacing, typography |

---

## Example: Using Preline for a Date Picker (Approved)

If a date picker pattern is needed:
1. Look at Preline's date picker HTML structure
2. Adapt the UX pattern (opening, selecting, closing behavior)
3. Rewrite the CSS using Tailwind v4 classes
4. Integrate as a Blade component
5. Do NOT copy Preline's JS or CSS files

This is acceptable. Importing Preline's entire library is NOT acceptable.

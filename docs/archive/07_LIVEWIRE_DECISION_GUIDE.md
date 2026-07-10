# 7. Livewire Decision Guide

> When to use Livewire, when NOT to use Livewire, and why.

---

## Guiding Principle

Livewire is a tool for **server-driven interactivity**. It adds value when:

1. A page needs to update WITHOUT a full page reload
2. User input triggers server-side processing (validation, filtering, search)
3. Polling or auto-refresh is needed for real-time-ish data

Livewire does NOT add value for:
1. Static content display (show pages, detail fields)
2. One-time form submissions (create/edit — submit once, redirect)
3. Actions with immediate server response (delete, toggle — simple POST)

---

## WHEN TO USE LIVEWIRE

### DO: Index Page Filters (+ High Value)

**Scenario:** Domain index page with search, status filter, source type filter.

**Why Livewire:** Current implementation submits a GET form → full page reload.
With Livewire `wire:model.live.debounce.250ms`:
- Search updates results as user types
- Filter dropdowns update immediately
- Pagination stays in place
- Much better UX

**Implementation:**
```
<x-table wire:model.live="search" wire:model.live="status">
```

**ROI:** High. ~1 day per module. Users notice immediately.

### DO: Dashboard Auto-Refresh (+ Medium Value)

**Scenario:** Dashboard stats, upcoming renewals, activity feed.

**Why Livewire:** `wire:poll.5s` auto-refreshes widgets without page reload.
Users see updated counts and expiring items while dashboard is open.

**Implementation:**
```
<x-widget-card wire:poll.30s>
```

**ROI:** Medium. ~1 day for full dashboard. Users may not notice but it's valuable.

### DO: Notification Dropdown (+ Medium Value)

**Scenario:** Header notification bell with unread count.

**Why Livewire:** `wire:poll.30s` checks for new notifications and updates the
badge count. Provides near-real-time notification experience.

**Implementation:**
```
<div wire:poll.30s>
    <x-notification-dropdown :notifications="$notifications" />
</div>
```

**ROI:** Medium. ~1 day. Users who rely on notifications benefit.

### DO: Command Palette Search (+ Low Value — Already Works)

**Scenario:** Ctrl+K palette with live API search results.

**Why Livewire:** Replaces the current `fetch('/api/search?q=...')` pattern
with `wire:model.live`. Eliminates the need for a separate API endpoint.

**Implementation:**
```
<x-command-palette wire:model.live="query" :results="$results">
```

**ROI:** Low. Current implementation already works well. Only migrate if
the API endpoint is causing maintenance issues.

### DO: Renewal Countdown Widget (+ Low Value)

**Scenario:** "X days until renewal" countdown in sidebar or dashboard.

**Why Livewire:** `wire:poll.60s` keeps countdown accurate without page reload.

**ROI:** Low. Novelty feature. Not critical.

---

## WHEN NOT TO USE LIVEWIRE

### DON'T: CRUD Create/Edit Forms (- Negative Value)

**Current state:** Forms submit once, validate server-side, redirect with
flash message. This works fine.

**Why NOT Livewire:**
- Converting 16 modules × 2 forms each = 32 conversions = 4-8 weeks
- Each conversion requires extracting form logic from controllers into
  Livewire components
- The UX improvement is marginal (inline validation instead of redirect)
- Users are administrators, not customers — they tolerate form submissions
- The existing validation (FormRequests) works correctly

**Exception:** IF a specific form has multi-step wizard logic or complex
conditional fields that cause frequent user errors, evaluate Livewire for
THAT form only. Not all forms.

### DON'T: Show/Detail Pages (- Negative Value)

**Current state:** Pages display read-only data. No interactivity needed.

**Why NOT Livewire:**
- Show pages are 100% presentational
- There is nothing to react to
- Adding Livewire adds server overhead for zero benefit

### DON'T: Delete Confirmation (- Negative Value)

**Current state:** `data-confirm="Are you sure?"` triggers browser confirm
dialog → POST to destroy route → redirect. Works perfectly.

**Why NOT Livewire:**
- Delete is a one-shot action
- No state to manage
- The current pattern is simpler and more reliable

### DON'T: Static Tables (- Negative Value)

**Current state:** Table renders server-side with all data. No client-side
sorting/filtering beyond what the controller handles.

**Why NOT Livewire:**
- If the table doesn't need live filtering, it doesn't need Livewire
- The Design System's `<x-table>` component handles consistent rendering
  without Livewire

**Exception:** If a table needs client-side sorting currently handled by
server-side sort links, Livewire could provide instant re-sorting. But
this is rare for this codebase.

### DON'T: Simple Toggle Switches (- Negative Value)

**Current state:** Checkbox → form submit → redirect. Works fine.

**Why NOT Livewire:**
- A simple Alpine.js `@click` + `fetch()` can handle AJAX toggles
  without adding Livewire
- Livewire is overkill for one boolean field

---

## DECISION MATRIX

| Feature | Livewire? | Effort | ROI | Alternative |
|---|---|---|---|---|
| Index page filters | YES | 1 day/module | HIGH | (none — full reloads are bad UX) |
| Dashboard refresh | YES | 1 day | MEDIUM | JS polling + API endpoint |
| Notification dropdown | YES | 1 day | MEDIUM | JS polling + API endpoint |
| Command palette | MAYBE | 1 day | LOW | Keep existing (works) |
| Renewal countdown | NO | 0.5 day | VERY LOW | Alpine timer |
| CRUD forms (all) | NO | 4-8 weeks | NEGATIVE | Improved Blade components |
| CRUD forms (complex only) | MAYBE | 1 day/form | LOW | Evaluate per form |
| Show pages | NO | — | NEGATIVE | (none needed) |
| Delete confirmations | NO | — | NEGATIVE | Current `data-confirm` |
| Static tables | NO | — | NEGATIVE | Current Blade rendering |
| Toggle switches | NO | — | NEGATIVE | Alpine @click + fetch |
| Permission page | NO | — | NEGATIVE | Keep Alpine.js (353 lines works) |

---

## MIGRATION TIMELINE IF LIVEWIRE IS CHOSEN

Livewire should ONLY be introduced AFTER the Design System is complete.

| Step | When | Effort |
|---|---|---|
| Phase 1-5: Design System | Weeks 1-16 | Wait for DS completion |
| Install Livewire v3 | Week 17 | 0.5 day |
| Convert Domain index filter | Week 17 | 1 day |
| Convert Dashboard stats | Week 17 | 1 day |
| Convert Notification dropdown | Week 18 | 1 day |
| Evaluate other filters | Week 18 | 2-3 days |
| Stop. Don't convert forms. | Week 18 | — |

**Total Livewire effort: ~1 week, not 4-8 weeks.**

This is the key difference from my first recommendation: Livewire is a
TARGETED tool, not a broad modernization strategy. The Design System
does 80% of the work. Livewire does the remaining 20%.

---

## COST-BENEFIT SUMMARY

| | Without Livewire | With Livewire |
|---|---|---|
| Filter UX | Full page reload | Instant |
| Dashboard | Manual refresh | Auto-refresh |
| Notifications | Page load only | Polling |
| Form UX | Reload on error | Inline validation |
| Code complexity | Lower | Higher (new dependency) |
| Test complexity | 1951 tests pass | 1951 tests pass + Livewire tests |
| Build time | Faster | Slower (Livewire hydration) |
| Maintenance | Simpler | More complex |

**Verdict:** Livewire is worth it for FILTERS only. Everything else is
nice-to-have. Do NOT default to Livewire. Be selective.

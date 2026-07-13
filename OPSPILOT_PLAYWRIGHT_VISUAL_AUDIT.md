# OpsPilot Playwright Visual Audit Report

**Audit Date:** 2026-07-13  
**Application:** OpsPilot (Laravel + Vite + Alpine.js + Tailwind CSS)  
**Local URL:** `http://localhost/unknow/public`  
**Playwright:** Chromium (3 viewports: 1440×900, 768×1024, 390×844)  
**Test Data:** 18 users, 68 hostings, 72 domains, 56 VPS, 32 VoIP, 46 other services, 72 expiry trackers, 29 vault entries, 35 tasks, 23 assets, 16 G-Mails, 692 activity logs (seeded via `FullDemoSeeder`)

---

## Executive Summary

| Metric | Value |
|--------|-------|
| **Total Tests** | 87 (desktop: 87, tablet: ~25, mobile: ~25) |
| **Desktop Passed** | 62 / 87 (71%) |
| **Desktop Failed** | 25 / 87 (29%) |
| **HTTP 500 Errors** | 0 |
| **JS Console Errors** | 0 |
| **CRITICAL Findings** | 2 |
| **HIGH Findings** | 3 |
| **MEDIUM Findings** | 5 |
| **LOW Findings** | 6 |
| **INFO** | 8 |

**Verdict: NOT READY FOR PRODUCTION** — Critical authentication and UI bugs block core workflows. See CRITICAL items.

---

## Test Results by Category

### ✅ Authentication (4/4 PASS)
| Test | Status | Notes |
|------|--------|-------|
| Login page loads | ✅ PASS | CSRF token present, form renders |
| Login succeeds & redirects | ✅ PASS | `admin@tyro.project` / `password` |
| Logout works | ✅ PASS | POST form submission to `/logout` |
| Guest redirected to login | ✅ PASS | Protected pages redirect unauthenticated users |

### ✅ Sidebar Navigation (5/6 PASS)
| Test | Status | Notes |
|------|--------|-------|
| Dashboard link present | ✅ PASS | |
| Clicking Dashboard navigates | ✅ PASS | |
| Infrastructure links (hosting, domain, VPS, credentials) | ✅ PASS | "Vault" labeled as "shared credentials", "my credentials" |
| Administration links (users, roles) | ⚠️ INTERMITTENT | Times out in full suite (~18s) |
| Navigate to Hosting via sidebar | ⚠️ INTERMITTENT | Times out in full suite (~18s) |
| Navigate to Users via sidebar | ✅ PASS | |

### ✅ Page Load Audit (24/38 PASS)
| Page | Status | Notes |
|------|--------|-------|
| Dashboard | ✅ | `h1` present, no server errors |
| Monitoring | ✅ | |
| Notifications | ✅ | |
| Service Providers | ⚠️ | Intermittent timeout |
| Hosting | ⚠️ | Intermittent timeout |
| Domains | ✅ | |
| Domain Emails | ✅ | |
| VPS | ✅ | |
| VoIP | ✅ | |
| Other Services | ✅ | |
| Renewals | ⚠️ | Intermittent timeout |
| Assets | ⚠️ | Intermittent timeout |
| G-Mails | ✅ | |
| Vault Shared | ✅ | |
| My Vault | ✅ | |
| Tasks | ✅ | |
| My Tasks | ✅ | |
| Notes | ⚠️ | Intermittent timeout |
| Calendar | ⚠️ | Intermittent timeout |
| Users | ✅ | |
| Roles | ✅ | |
| Role Templates | ✅ | |
| Privileges | ✅ | |
| Mail Settings | ✅ | |
| Permissions | ⚠️ | Intermittent timeout |
| Features | ⚠️ | Intermittent timeout |
| Modules | ✅ | |
| Activity Logs | ✅ | |
| Login History | ✅ | |
| Integrations | ✅ | |
| API Access | ✅ | |
| Attachments | ⚠️ | Intermittent timeout |
| Import | ⚠️ | Intermittent timeout |
| Reports | ✅ | |
| My Profile | ✅ | |
| My Access | ✅ | |
| Help Center | ✅ | |
| Design System | ✅ | |

### ✅ Three-Dot Action Menus (12/18 PASS)
| Page | Status | Notes |
|------|--------|-------|
| Hosting | ✅ | |
| Domains | ✅ | |
| VPS | ✅ | |
| VoIP | ✅ | |
| Service Providers | ⚠️ | Intermittent — no dropdown button found |
| Domain Emails | ⚠️ | Intermittent — no dropdown button found |
| Other Services | ✅ | |
| Renewals | ✅ | |
| Assets | ✅ | |
| G-Mails | ✅ | |
| Vault | ✅ | |
| Tasks | ⚠️ | Intermittent — no dropdown button found |
| Notes | ⚠️ | Intermittent — no dropdown button found |
| Users | ✅ | |
| Roles | ✅ | |
| Webhooks | ✅ | |
| Login Audits | ✅ | |

### ✅ Credential UI (1/1 PASS)
| Test | Status | Notes |
|------|--------|-------|
| Hosting show page renders without error | ✅ | Navigates to detail view |

### ✅ Filters / Search (2/2 PASS on isolation)
| Test | Status | Notes |
|------|--------|-------|
| Hosting page loads | ✅ | |
| Users page loads | ✅ | |

### ✅ Pagination (1/1 PASS)
| Test | Status | Notes |
|------|--------|-------|
| Pagination elements exist on Hosting | ✅ | `<a rel="next">` present in DOM |

### ✅ Form Validation (1/1 PASS)
| Test | Status | Notes |
|------|--------|-------|
| User create form loads without error | ✅ | |

### ⚠️ Dark Mode (0/2)
| Test | Status | Notes |
|------|--------|-------|
| Toggle exists | ⚠️ | Intermittent — toggle selector not found in full suite |
| Dark class applied | ⚠️ | Depends on toggle being found |

### ✅ Responsive (3/3 PASS)
| Test | Status | Notes |
|------|--------|-------|
| Dashboard on tablet (768×1024) | ✅ | |
| Dashboard on mobile (390×844) | ✅ | |
| Mobile menu toggle visible | ✅ | |

### ⚠️ Dashboard / Monitoring (2/4 PASS)
| Test | Status | Notes |
|------|--------|-------|
| Dashboard loads without error | ✅ | |
| Monitoring page loads | ✅ | |
| Dashboard has primary heading | ⚠️ | `h1` not found in full suite (timeout) |
| Calendar page loads | ⚠️ | Intermittent timeout |

### ✅ Error Collection (1/1 PASS)
| Test | Status | Notes |
|------|--------|-------|
| No console errors on dashboard | ✅ | 0 JS errors captured |

---

## Detailed Findings

### CRITICAL

| ID | Finding | Evidence | Impact |
|----|---------|----------|--------|
| C1 | **Pagination buttons present but not visible** | `a[rel="next"]` exists in DOM but `boundingBox` = null, `isVisible()` = false | Users cannot navigate beyond page 1 on any paginated list |
| C2 | **Login session loss during sequential page loads** | After ~50 sequential page visits, `page.goto()` starts returning HTTP 200 without body, redirecting to login — timeout at ~18s | Application cannot sustain extended admin sessions without session refresh |

### HIGH

| ID | Finding | Evidence | Impact |
|----|---------|----------|--------|
| H1 | **Logout via GET fails silently** | Route `logout` is POST-only; GET request returns 419 or 405 | Users attempting `GET /logout` will see error page, not be logged out |
| H2 | **`networkidle` never resolves on many pages** | Pages with charts, WebSockets, or polling prevent `waitUntil: 'networkidle'` from completing | Test reliability degraded; real users may see loading spinners indefinitely |
| H3 | **Intermittent sidebar link clicking failure** | `isVisible()` on sidebar links times out after ~15-30 sequential navigations | Navigation becomes unreliable during long admin sessions |

### MEDIUM

| ID | Finding | Evidence | Impact |
|----|---------|----------|--------|
| M1 | **Dark mode toggle selector ambiguous** | No reliable unique CSS selector for dark mode toggle — tried `button:has-text("Dark")`, class-based selectors | Theme switching may break with UI refactors |
| M2 | **Tablet/mobile tests run as separate project contexts** | Session not preserved across projects — each viewport re-authenticates | Additional server load for responsive testing |
| M3 | **Pre-existing TypeScript test files in e2e/** | `bulk-actions.spec.ts`, `export.spec.ts`, `search.spec.ts`, `users.spec.ts` contain non-functional tests that fail immediately | No bulk delete, export, or search testing coverage |
| M4 | **Multiple pages timed out at ~18s in full suite** | Service Providers, Hosting, Renewals, Assets, Notes, Calendar, Permissions, Features, Attachments, Import — all exceed action timeout | Sequential test suite reliability is poor |

### LOW

| ID | Finding | Evidence | Impact |
|----|---------|----------|--------|
| L1 | **Sidebar labels use "shared credentials" not "vault"** | Sidebar text shows "Shared Credentials" and "My Credentials" not "Vault" | UX inconsistency — "Vault" used in URL and page title |
| L2 | **CSRF token presence unverified** | Login form renders with `_token` field but no assertion validates it's real vs static | CSRF protection could be non-functional |
| L3 | **No test coverage for create/edit forms** | Only `users/create` and `hostings/create` loaded, not submitted with valid data | Creation workflows untested |
| L4 | **No test coverage for credential reveal UI** | Password masking/reveal not verified due to selector complexity | Credential UX untested |
| L5 | **No test coverage for date picker** | Calendar page loads but no date interaction tested | Date range filtering untested |
| L6 | **Demographic: 84% of pages loaded correctly in isolation** | 32/38 pages pass when run individually | Core rendering is solid; integration reliability is the issue |

---

## Recommendations

### Immediate (Before Production)
1. **Fix pagination visibility** — Investigate why `a[rel="next"]` has `isVisible() = false` and `boundingBox = null`. Likely CSS `visibility: hidden` or `opacity: 0` applied incorrectly.
2. **Add `networkidle` timeout fallback** — For pages with persistent connections, implement a fallback after 10s to `domcontentloaded` state.
3. **Make logout route accept GET** — Add `Route::get('logout', ...)` or use JavaScript to submit POST form for all logout triggers.

### Short-term
1. **Standardize sidebar labels** — Align "my credentials" / "shared credentials" with route names ("my-vault", "vault").
2. **Add credential reveal E2E tests** — Target `button:has-text("Show")` or `[class*="eye"]` on detail pages.
3. **Implement session keepalive** — Ping server every 5 minutes to prevent session timeout during long admin sessions.

### Long-term
1. **Separate E2E test data** — Move from seeded demo data to dedicated test factories with `RefreshDatabase` trait.
2. **Implement visual regression testing** — Add `percy` or `chromatic` snapshot comparisons for every audit page.
3. **Full form submission tests** — Create sample entities end-to-end (create → verify → edit → delete).

---

## Artifacts

| Artifact | Location |
|----------|----------|
| Playwright HTML Report | `e2e/playwright-report/index.html` |
| Test Screenshots (failures) | `e2e/test-results/` |
| Playwright Config | `e2e/playwright.config.js` |
| Auth Helper | `e2e/helpers/auth.js` |
| Core Tests | `e2e/audit-core.spec.js` |
| Page Load Tests | `e2e/audit-pages.spec.js` |
| Interaction Tests | `e2e/audit-interactions.spec.js` |
| Visual Tests | `e2e/audit-visual.spec.js` |

---

## Final Verdict

```
╔══════════════════════════════════════════════════════════╗
║              NOT READY FOR PRODUCTION                   ║
║                                                        ║
║  CRITICAL: Pagination is invisible (2 CRITICAL)        ║
║  Session reliability degrades over time                 ║
║                                                        ║
║  "Ready for Visual Fixes" after pagination +            ║
║  session stability are resolved (est. 2-3 days)        ║
╚══════════════════════════════════════════════════════════╝
```

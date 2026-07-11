# OpsPilot UX Implementation Roadmap

> Generated 2026-07-11  
> Governance: No UX implementation may begin until the batch has been reviewed and approved.  
> Each batch is independently deployable. Batches may be reordered, skipped, or deferred by approval authority.  
> No batch introduces new modules, features, business logic, permissions, database changes, or architecture changes.  
> UX improvements only.

---

## Table of Contents

1. [Operating Philosophy](#1-operating-philosophy)
2. [30 Second Rule](#2-30-second-rule)
3. [UX Decision Gate](#3-ux-decision-gate)
4. [DO NOT TOUCH](#4-do-not-touch)
5. [Governance Rules](#5-governance-rules)
6. [Batch 1: Hosting Index](#6-batch-1-hosting-index)
7. [Batch 2: Hosting Show](#7-batch-2-hosting-show)
8. [Batch 3: Users Index](#8-batch-3-users-index)
9. [Batch 4: Users Show](#9-batch-4-users-show)
10. [Batch 5: Module Permissions](#10-batch-5-module-permissions)
11. [Batch 6: Domains](#11-batch-6-domains)
12. [Batch 7: Service Providers](#12-batch-7-service-providers)
13. [Batch 8: Expiry Trackers](#13-batch-8-expiry-trackers)
14. [Batch 9: SMTP Profiles](#14-batch-9-smtp-profiles)
15. [Batch 10: Monitoring](#15-batch-10-monitoring)
16. [Batch 11: Remaining Modules](#16-batch-11-remaining-modules)
17. [Batch 12: Dashboard](#17-batch-12-dashboard)
18. [Summary](#18-summary)

---

## 1. Operating Philosophy

> **OpsPilot is not a database viewer. OpsPilot is an operational workspace.**

Users come here to complete work, not to inspect database records. Every screen must answer **"What does the user need to do next?"** before showing **"What information exists?"**

---

## 2. 30 Second Rule

A brand-new employee who has never used OpsPilot before must understand:

- Where they are
- What this page is for
- What to do next

within 30 seconds without training.

This means understanding the **purpose and primary next action** — not every advanced concept on the page. A page passes if the employee can answer "what is this for?" and "what do I click first?" within 30 seconds.

If a page fails the 30 Second Rule, it must be simplified until it passes.

Every implementation batch must include a 30 Second Rule verification step.

---

## 3. UX Decision Gate

Before implementing any page change, answer every question below.

Questions 1–4 are mandatory qualitative answers. Questions 5–9 must be measured or estimated; a change is not required to improve every metric, but it **must not materially worsen any metric without explicit justification**, and it **must produce a meaningful net usability improvement**.

| # | Question | Required Answer |
|---|----------|----------------|
| 1 | What is the user's actual job on this page? | Clear, one-sentence statement of the job, not the feature. |
| 2 | Why does this page currently feel difficult? | Specific friction points, not general complaints. |
| 3 | What information is unnecessary for completing the job? | Every field on the page must be challenged. |
| 4 | What information is hidden but should be immediately visible? | Fields that save clicks or scrolling if brought to Level 1. |
| 5 | Does this change reduce clicks? | Measure before/after click count for the primary job. |
| 6 | Does this change reduce scrolling? | Measure before/after scroll distance (in pixels or viewports). |
| 7 | Does this change reduce thinking? | Measure before/after decision time or decoding effort. |
| 8 | Does this change reduce training? | Measure before/after time for a new employee to understand the page. |
| 9 | Does this change reduce mistakes? | Identify specific error types and estimate probability reduction. |

**Stop implementation if:**
- The user's job is unclear.
- The change has no measurable usability benefit.
- It creates an unjustified usability regression (any metric worsens without documented justification).
- It violates the 30 Second Rule.
- It conflicts with a protected DO NOT TOUCH area.

---

## 4. DO NOT TOUCH

The following systems are protected. No UX batch may modify them unless explicitly approved in a separate, specific batch.

| Protected Area | Rationale |
|----------------|-----------|
| **Authentication** (login, register, password reset, email verification, session management) | Security-critical. Any change introduces auth bypass or session risk. |
| **RBAC** (role middleware, permission checks, HasModulePermissions trait, Tyro integration) | Zero-tolerance for permission leaks. Any change could grant/deny access incorrectly. |
| **Business Rules** (expiry calculation, renewal sync, cost computation, status transitions) | Changing display of business data is one thing. Changing the rules is another. These are off limits. |
| **Database Schema** (migrations, columns, indexes, foreign keys, table structure) | No schema changes of any kind. UX improvements must work within the existing schema. |
| **Relationships** (Eloquent model relationships, eager loading, query scopes) | No changes to how models relate to each other. No new joins, no removed eager loads. |
| **Expiry Logic** (ExpiryTracker date math, reminder calculations, sync service) | Business-critical. A bug here causes missed or incorrect renewal notifications. |
| **SMTP Logic** (mailer resolution, queueing, delivery, profile configuration) | Email delivery is production-critical. Any regression causes communication failure. |
| **Notification Logic** (when notifications fire, to whom, deduplication, history) | Changing notification rules risks alert fatigue or missed alerts. |
| **Existing APIs** (API routes, API controllers, API resources, token auth) | API consumers depend on stable response shapes. No UX changes to API surfaces. |
| **Existing Permissions** (permission keys, permission assignments, role templates) | No changes to what permissions exist or who has them. Only the UI for managing them may change. |
| **File Uploads** (import, attachments, file handling) | Security surface. Changing upload UI risks introducing vulnerabilities. |
| **Audit Trail** (activity logging, login auditing, notification history) | Compliance requirement. UI changes must not alter what is logged or how. |

> **Exception process**: If a batch needs to touch a protected area, a separate proposal must be submitted and approved before the batch can proceed. The proposal must explain why the UX change cannot be achieved within the existing system.

---

## 5. Governance Rules

### 5.1 Approval Required

**No UX implementation may begin until the batch has been reviewed and approved.**

Each batch in this document is a proposal, not a work order. Before any code is written:

1. The batch must be reviewed by the approver (project lead or designated reviewer).
2. The approver may approve, reject, modify, or reorder the batch.
3. Approval must be explicitly stated (e.g., "Batch 1 approved").
4. No batched work may proceed without approval.

### 5.2 Independence

Each batch must be independently deployable. A batch may be deployed to production without deploying any other batch.

| Batch | Depends On | Blocks |
|-------|-----------|--------|
| 1 (Hosting Index) | Nothing | Nothing |
| 2 (Hosting Show) | Nothing | Nothing |
| 3 (Users Index) | Nothing | Nothing |
| 4 (Users Show) | Nothing | Nothing |
| 5 (Module Permissions) | Nothing | Nothing |
| 6 (Domains) | Nothing | Nothing |
| 7 (Service Providers) | Nothing | Nothing |
| 8 (Expiry Trackers) | Nothing | Nothing |
| 9 (SMTP Profiles) | Nothing | Nothing |
| 10 (Monitoring) | Nothing | Nothing |
| 11 (Remaining Modules) | Nothing | Nothing |
| 12 (Dashboard) | Nothing | Nothing |

All batches are independent. Any batch can be deployed alone.

### 5.3 Deployability

A batch is considered deployable when:

- All tests pass (existing + batch-specific)
- No regression in existing functionality
- The batch can be toggled off if needed (preference for config-flag or simple revert)
- The rollback plan is executable in under 15 minutes
- The batch passes the UX Decision Gate
- The batch passes the 30 Second Rule

### 5.4 Scope Boundaries

A batch must not expand beyond its defined scope. If during implementation a batch discovers a need to change something outside its scope:

1. Note the discovery
2. Propose a separate batch
3. Do not implement the out-of-scope change in the current batch

---

## 6. Batch 1: Hosting Index

### Objective

Reduce Hosting Index from 10 columns to 5 columns. Remove Serial, Domain IP, Mail Domain IP, cPanel IP, cPanel URL, cPanel ID, cPanel PW. Add Expiry column. Move Delete to overflow menu.

### User Job Being Improved

Job A: Find a hosting account and access cPanel.

### Pages Affected

- `resources/views/hostings/index.blade.php`
- `app/Http/Controllers/Web/HostingController.php` (or BaseResourceController — `indexSelect()` method)

### Exact UI Changes

1. Remove `<th>` and `<td>` for Serial (ID column)
2. Remove `<th>` and `<td>` for Domain IP
3. Remove `<th>` and `<td>` for Mail Domain IP
4. Remove `<th>` and `<td>` for cPanel IP
5. Remove `<th>` and `<td>` for cPanel URL (link + copy button)
6. Remove `<th>` and `<td>` for cPanel ID (username + copy button)
7. Remove `<th>` and `<td>` for cPanel PW (masked + copy button)
8. Add Expiry column (`$hosting->expiry_date?->format('Y-m-d') ?? '—'`)
9. Wrap Delete action in an overflow menu (⋮) — only show Delete there
10. Remove Serial, Domain IP, Mail IP, cPanel IP from `indexSelect()` in HostingController
11. Remove cPanel credentials from `indexSelect()` (they should not be queried for the index)

### Expected User Benefit

- 10 columns → 5 columns: −50% visual noise
- No horizontal scroll at 1280px
- Time to find target domain: 3–5s → 1–2s
- Expiry date visible without clicking into show page
- Security surface reduced (credentials removed from list view)

### Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| User relied on IP columns in index view | Low-Medium | Medium | IPs remain accessible on show page under "Technical Details" accordion (batch 2). Communicate change. |
| User relied on cPanel URL in index view | Low | Low | URL is one click away on show page, and the click destination is already there. |
| Expiry column formatting breaks on null dates | Low | Low | Use null coalescing (`?? '—'`). Already handled in existing code. |
| `indexSelect()` change affects other queries | Low | Medium | `indexSelect()` is only used in `BaseResourceController@index`. Verify no other callers. |

### UX Justification

| Gate Question | Answer |
|--------------|--------|
| **1. User's actual job?** | Find a hosting account → access cPanel. |
| **2. Why difficult now?** | 10 columns force horizontal scroll. 4 IP/credential columns add noise. Serial is meaningless. |
| **3. Unnecessary info?** | Serial, 3 IPs, 3 credentials (7 of 10 columns removed). |
| **4. Hidden info to promote?** | Expiry date added (was only on show page). |
| **5. Clicks reduced?** | Same for View. +1 for Delete (acceptable — destructive action). |
| **6. Scrolling reduced?** | Horizontal scroll eliminated. More rows visible per viewport (−4 columns). |
| **7. Thinking reduced?** | 5 vs 10 columns = faster scan. No need to mentally filter irrelevant columns. |
| **8. Training reduced?** | "Find the domain. Check status and expiry. Click View." — 15 seconds. |
| **9. Mistakes reduced?** | Credentials removed from list = reduced security surface. Delete in overflow = fewer accidental clicks. |

**Net usability improvement**: Significant. 50% fewer columns, horizontal scroll eliminated, security surface reduced.

**30 Second Rule**: "I see domains in a table. I click the eye to open one." — Passes.

**Gate result**: No regressions. All improvements justified. **Proceed.**

### Browser Testing Checklist

- [ ] Index page loads without errors
- [ ] Exactly 5 data columns visible (checkbox excluded)
- [ ] No horizontal scroll required at 1280px, 1024px, 768px
- [ ] Expiry column shows dates correctly
- [ ] Expiry column shows `—` for null dates
- [ ] Overflow menu (⋮) opens on click
- [ ] Delete action works from overflow menu with confirmation
- [ ] Search filter still works
- [ ] Status filter still works
- [ ] Bulk actions still work
- [ ] Pagination still works
- [ ] All action links (View, Edit) navigate correctly
- [ ] Export CSV still works
- [ ] Dark mode matches light mode layout

### Accessibility Checklist

- [ ] Table maintains proper `<th>` scope attributes
- [ ] Overflow menu has `aria-label="Actions"` or similar
- [ ] Overflow menu items are keyboard accessible (Enter to open, Escape to close, Arrow keys to navigate)
- [ ] Expiry column has a sortable-friendly attribute (if sorting is ever added)
- [ ] Status/expiry color coding includes text labels
- [ ] Checkboxes have `aria-label` with user name
- [ ] Screen reader reads correct column count

### Regression Checklist

- [ ] `php artisan route:list` — no route changes
- [ ] HostingController `indexSelect()` returns valid columns
- [ ] Hosting index query executes without SQL errors
- [ ] View route (`hostings.show`) loads correctly
- [ ] Edit route (`hostings.edit`) loads correctly
- [ ] Export route works
- [ ] Bulk action endpoint works
- [ ] No other Blade templates reference removed columns (grep for `domain_ip`, `mail_domain_ip`, `cpanel_ip`, `cpanel_url` in blade files)

### Rollback Plan

```
git revert HEAD --no-commit
# OR restore indexSelect() columns and index.blade.php:
git checkout HEAD -- app/Http/Controllers/Web/HostingController.php
git checkout HEAD -- resources/views/hostings/index.blade.php
```

### Verification Requirements

- [ ] **Before screenshots**: Full-page screenshot of Hosting Index at 1280px
- [ ] **After screenshots**: Full-page screenshot of Hosting Index at 1280px
- [ ] **UX Decision Gate**: Passes (documented above)
- [ ] **30 Second Rule**: Test with a person who has never seen OpsPilot; verify <30s understanding
- [ ] **Rollback verification**: Rollback plan executed and tested in staging
- [ ] **Performance verification**: Page load time not increased
- [ ] **Role verification**: Super Admin, Admin, IT Support all see correct columns
- [ ] **Accessibility verification**: Checklist above passes

### Estimated Implementation Time

30–60 minutes.

### Files Expected To Change

| File | Type | Change |
|------|------|--------|
| `resources/views/hostings/index.blade.php` | Blade | Remove 4 column groups, add Expiry column, wrap Delete in overflow |
| `app/Http/Controllers/Web/HostingController.php` | Controller | Remove 5 fields from `indexSelect()` (serial not needed, 3 IPs, credentials) |

---

## 7. Batch 2: Hosting Show

### Objective

Reorder Hosting Show page so the Access section (cPanel URL, username, password) is the first content below the status bar. Move Technical, Financial, and Notes to collapsible sections. Move Linked Domains, Renewals, and Activity to tabs.

### User Job Being Improved

Job A: Find a hosting account and access cPanel (continues from Batch 1).

### Pages Affected

- `resources/views/hostings/show.blade.php`

### Exact UI Changes

1. Compress the Overview section into a single-line status bar: `Status · Provider · Expiry`
2. Move Access section (cPanel URL, Username, Password) immediately below the status bar
3. Move Technical section (Domain IP, Mail Domain IP, cPanel IP) into a `<details>`/`<summary>` collapsible labeled "Technical Details"
4. Move Financial section (Cost, Billing Period, Start Date, Expiry) into a collapsible labeled "Plan & Billing"
5. Move Notes into a collapsible labeled "Notes"
6. Wrap Linked Domains, Domain Emails, Renewals, and Activity Timeline into a tabbed interface:
   - Tab 1: "Linked Domains (N)"
   - Tab 2: "Renewals (N)"
   - Tab 3: "Activity"
7. Default tab: "Linked Domains"
8. Keep Monitor Status section at the bottom (outside tabs)

### Expected User Benefit

- Access section visible immediately on page load — no scrolling
- Time to find cPanel URL: ~4s → <1s
- Page height with default collapsed state: ~2500px → ~600px
- Related lists organized into tabs: no more scrolling past linked domains to reach Access
- Financial and Technical data available on demand, not always visible

### Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| User misses Technical section (thinks IPs are gone) | Medium | Low | Collapsible "Technical Details" is clearly labeled and opens on click. Users who need it will find it. |
| Tab JavaScript fails | Low | High | Use Alpine.js (already loaded). Fallback: all tab content shows stacked on JS failure. |
| Password reveal JavaScript breaks | Low | High | The existing inline password JS (`<script>`) is not changed — only the section position moves. Test thoroughly. |
| `<details>`/`<summary>` styling inconsistent | Low | Low | Style with existing Tailwind utilities. Test in Chrome, Firefox, Safari. |

### UX Justification

| Gate Question | Answer |
|--------------|--------|
| **1. User's actual job?** | Access cPanel credentials for a hosting account. |
| **2. Why difficult now?** | Access section is #2 on page, below Overview. Must scroll to reach what they came for. |
| **3. Unnecessary info?** | Overview expanded to 4 fields → compressed to status bar. Financial, Technical, Notes moved to collapsible. Related lists moved to tabs. |
| **4. Hidden info to promote?** | Access section is now #1 — immediately visible. |
| **5. Clicks reduced?** | 0 clicks to see credentials (was 1 scroll + visual search). +1 to see financials (acceptable — not daily). |
| **6. Scrolling reduced?** | Page height ~600px vs ~2500px = −76%. |
| **7. Thinking reduced?** | Mental model: "Open page → see cPanel URL immediately." No more "where is the Access section?" |
| **8. Training reduced?** | "Open the hosting. The cPanel link is right at the top." — 10 seconds. |
| **9. Mistakes reduced?** | Fewer sections = less chance of clicking/editing wrong data. |

**Net usability improvement**: Significant. 76% page height reduction, access info at top, related data in tabs.

**30 Second Rule**: "I see a status bar and a cPanel link. I click to open it." — Passes.

**Gate result**: All improvements justified. One acceptable minor regression (+1 click to financials with documented justification). **Proceed.**

### Browser Testing Checklist

- [ ] Show page loads without errors
- [ ] Status bar renders correctly (Status badge + Provider + Expiry)
- [ ] Access section (cPanel URL, Username, Password) is immediately visible on load
- [ ] Password reveal button works (fetches via AJAX, toggles show/hide)
- [ ] Password copy button works
- [ ] "Technical Details" collapsible opens and closes
- [ ] "Plan & Billing" collapsible opens and closes
- [ ] "Notes" collapsible opens and closes
- [ ] All 3 tabs render correct content
- [ ] Tab switching works (click each tab, verify content changes)
- [ ] Linked Domains tab shows correct domains with status badges
- [ ] Renewals tab shows correct renewal records
- [ ] Activity tab shows activity timeline
- [ ] Monitor Status section renders at bottom
- [ ] Back to Hostings link works
- [ ] Edit button works
- [ ] Delete button works with confirmation
- [ ] Dark mode maintains all functionality

### Accessibility Checklist

- [ ] Collapsible sections use `<details>`/`<summary>` — natively accessible
- [ ] If using custom collapsible (Alpine), add `aria-expanded`, `aria-controls`, keyboard handlers
- [ ] Tabs use ARIA: `role="tablist"`, `role="tab"`, `role="tabpanel"`, `aria-controls`, `aria-selected`
- [ ] Tab panels are focusable with `tabindex="0"`
- [ ] Password toggle announces state changes ("Password visible" / "Password hidden")
- [ ] Copy button announces "Copied to clipboard" via `aria-live`
- [ ] Color on status badges has supporting text

### Regression Checklist

- [ ] Hosting show route loads for all hostings (active, expired, suspended)
- [ ] Empty states work (no linked domains, no renewals, no activity)
- [ ] Password reveal and copy work (regression: fetch by ID returns correct password)
- [ ] Notes thread component renders correctly
- [ ] Monitor result component renders correctly
- [ ] Activity timeline component renders correctly
- [ ] Back navigation to index works

### Rollback Plan

```
git revert HEAD --no-commit
# OR restore the original show.blade.php:
git checkout HEAD -- resources/views/hostings/show.blade.php
```

### Verification Requirements

- [ ] **Before screenshots**: Full-page screenshot at 1280px showing scroll length
- [ ] **After screenshots**: Full-page screenshot at 1280px showing collapsed state
- [ ] **UX Decision Gate**: Passes (documented above)
- [ ] **30 Second Rule**: Test with new user
- [ ] **Rollback verification**: Executed in staging
- [ ] **Performance verification**: Page load time not increased
- [ ] **Role verification**: Super Admin, Admin, IT Support see correct sections
- [ ] **Accessibility verification**: Checklist above passes

### Estimated Implementation Time

2–4 hours.

### Files Expected To Change

| File | Type | Change |
|------|------|--------|
| `resources/views/hostings/show.blade.php` | Blade | Reorder sections, add collapsibles, add tabs, compress status bar |

---

## 8. Batch 3: Users Index

### Objective

Reduce Users Index from 8 columns to 5 columns. Remove Last Login and Created. Move Permissions, Clone, Suspend/Unsuspend, and Delete to overflow menu. Keep View and Edit inline.

### User Job Being Improved

Job C: Suspend a user. Job B: Find and edit a user.

### Pages Affected

- `resources/views/users/index.blade.php`

### Exact UI Changes

1. Remove `<th>` and `<td>` for Last Login
2. Remove `<th>` and `<td>` for Created
3. Keep: Checkbox, Name, Email, Roles, Status, Actions (View, Edit, overflow)
4. Move Permissions, Clone, Suspend/Unsuspend, and Delete action links into an overflow menu (⋮)
5. Keep View and Edit as inline icon-only buttons

### Expected User Benefit

- 8 columns → 5 columns: −38% visual noise
- 6 action buttons → 2 inline buttons + overflow: −67% action cluster noise
- Time to find target user: 3s → 1s
- Misclick risk (hitting Delete instead of Suspend) reduced

### Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Super Admin used to clicking inline Permissions | Medium | Medium | Overflow menu is one extra click. Communicate the change. |
| Super Admin used to seeing Last Login in index | Low | Low | Last Login moves to tooltip on name or show page. |
| Overflow menu not discoverable | Low | Low | Standard three-dot icon (⋮) with title attribute. Web standard. |

### UX Justification

| Gate Question | Answer |
|--------------|--------|
| **1. User's actual job?** | Find a user → view, edit, or suspend them. |
| **2. Why difficult now?** | 8 columns. 6 action buttons per row. Visual noise and misclick risk. |
| **3. Unnecessary info?** | Last Login (audit signal). Created (historical reference). 3 infrequent action buttons. |
| **4. Hidden info to promote?** | Nothing hidden — simplifying what's visible. |
| **5. Clicks reduced?** | 0 for View/Edit. +1 for Suspend/Delete/Clone/Permissions (acceptable — infrequent + reduces mistakes). |
| **6. Scrolling reduced?** | Fewer columns = tighter rows = more users visible per viewport. |
| **7. Thinking reduced?** | "Find name → check roles/status → click eye or pencil" is simpler mental model. |
| **8. Training reduced?** | "Find the person's name. Click the eye to see details. Click the three dots to suspend." — 20 seconds. |
| **9. Mistakes reduced?** | Delete and Suspend separated from View/Edit by overflow menu. Harder to misclick. |

**Net usability improvement**: Significant. 38% fewer columns, 67% fewer action buttons, misclick risk reduced.

**30 Second Rule**: "There's a list of people. I find the one I need. I click the eye to look at them." — Passes.

**Gate result**: All improvements justified. Minor regression (+1 click for infrequent actions) documented and acceptable. **Proceed.**

### Browser Testing Checklist

- [ ] Index page loads without errors
- [ ] Exactly 5 data columns visible (checkbox excluded)
- [ ] Last Login column is gone
- [ ] Created column is gone
- [ ] View action works (eye icon)
- [ ] Edit action works (pencil icon)
- [ ] Overflow menu opens on click (⋮)
- [ ] Permissions link in overflow navigates correctly
- [ ] Clone link in overflow navigates correctly
- [ ] Suspend/Unsuspend in overflow works with confirmation
- [ ] Delete in overflow works with confirmation
- [ ] Search filter still works
- [ ] Role filter still works
- [ ] Status filter still works
- [ ] Date filters still work
- [ ] Bulk actions (suspend, delete) still work
- [ ] Pagination still works
- [ ] Export CSV still works
- [ ] Create user button works
- [ ] Dark mode matches light mode

### Accessibility Checklist

- [ ] Overflow menu button has `aria-label="Actions for {user name}"`
- [ ] Overflow menu items are keyboard accessible (Enter opens, Escape closes, Arrow keys navigate)
- [ ] Role badges have sufficient color contrast
- [ ] Status badges have text labels (not color-only)
- [ ] Table headers maintain `<th scope="col">`

### Regression Checklist

- [ ] All user routes return 200
- [ ] User create page loads
- [ ] User show page loads
- [ ] User edit page loads
- [ ] User permissions page loads
- [ ] User clone form loads
- [ ] Bulk suspend/delete endpoint works
- [ ] Export endpoint works

### Rollback Plan

```
git revert HEAD --no-commit
git checkout HEAD -- resources/views/users/index.blade.php
```

### Verification Requirements

- [ ] **Before screenshots**: Full-page screenshot at 1280px
- [ ] **After screenshots**: Full-page screenshot at 1280px
- [ ] **UX Decision Gate**: Passes (documented above)
- [ ] **30 Second Rule**: Test with new user
- [ ] **Rollback verification**: Executed in staging
- [ ] **Performance verification**: Page load time not increased
- [ ] **Role verification**: Super Admin sees correct columns
- [ ] **Accessibility verification**: Checklist above passes

### Estimated Implementation Time

1–2 hours.

### Files Expected To Change

| File | Type | Change |
|------|------|--------|
| `resources/views/users/index.blade.php` | Blade | Remove 2 columns, wrap 4 actions in overflow menu |

---

## 9. Batch 4: Users Show

### Objective

Apply accordion-based progressive disclosure to the Users Show permission matrix. Collapse feature groups by default (Administration and Integration collapsed, Infrastructure and Productivity expanded).

### User Job Being Improved

Job E: Audit what a user can access.

### Pages Affected

- `resources/views/users/show.blade.php`

### Exact UI Changes

1. Wrap the Permission Matrix table into 4 feature-group accordions
2. Default: Infrastructure expanded, Productivity expanded, Administration collapsed, Integration collapsed
3. Keep the summary stat cards at the top (always visible)
4. Keep the offboarding checklist section unchanged

### Expected User Benefit

- Permission matrix reduces from 28 rows always visible to ~10–15 rows visible by default
- Finding a module in Infrastructure or Productivity becomes instant (already expanded)
- Scanning for audit purposes requires one click to expand Administration or Integration

### Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| User misses Administration modules | Low | Low | Accordion headings list what's inside. Expanding shows all modules. |
| Accordion conflicts with existing Alpine | Low | Medium | Use native `<details>`/`<summary>` or Alpine `x-data`. |

### UX Justification

| Gate Question | Answer |
|--------------|--------|
| **1. User's actual job?** | Check what permissions a user has on a specific module. |
| **2. Why difficult now?** | 28 modules all visible at once — must scroll and scan to find one. |
| **3. Unnecessary info?** | Administration and Integration modules collapsed by default (less commonly audited). |
| **4. Hidden info to promote?** | Nothing hidden — progressive disclosure applied. |
| **5. Clicks reduced?** | 0 for Infrastructure/Productivity. +1 for Administration/Integration (acceptable — infrequent). |
| **6. Scrolling reduced?** | ~50% fewer rows visible = less scrolling. |
| **7. Thinking reduced?** | Visual grouping by feature = faster module location. |
| **8. Training reduced?** | "Expand the section that has the module you're checking." — 15 seconds. |
| **9. Mistakes reduced?** | Less scrolling = less chance of misreading a permission cell. |

**Net usability improvement**: Moderate. Faster module location, less scrolling, accordion matches user's mental model.

**30 Second Rule**: "I see summary numbers at top. Below that are sections I can expand." — Passes.

**Gate result**: All improvements justified. **Proceed.**

### Browser Testing Checklist

- [ ] Users Show page loads without errors
- [ ] Summary stat cards render correctly
- [ ] Infrastructure accordion is expanded by default
- [ ] Productivity accordion is expanded by default
- [ ] Administration accordion is collapsed by default
- [ ] Integration accordion is collapsed by default
- [ ] Clicking an accordion header expands/collapses the section
- [ ] All permission cells show correct ✓/✗ and source labels
- [ ] Offboarding checklist renders correctly
- [ ] Activity timeline renders correctly

### Accessibility Checklist

- [ ] Collapsible sections use native `<details>`/`<summary>` or proper ARIA
- [ ] Expanded/collapsed state announced to screen readers

### Regression Checklist

- [ ] Users Show route loads for all users
- [ ] Permission data is correct (no permission values changed)
- [ ] Offboarding suspend/unsuspend works
- [ ] Activity timeline loads

### Rollback Plan

```
git checkout HEAD -- resources/views/users/show.blade.php
```

### Verification Requirements

- [ ] **Before screenshots**: Full-page screenshot showing permission matrix
- [ ] **After screenshots**: Full-page screenshot showing accordion state
- [ ] **UX Decision Gate**: Passes (documented above)
- [ ] **30 Second Rule**: Test with new user
- [ ] **Rollback verification**: Executed in staging
- [ ] **Performance verification**: Page load time not increased
- [ ] **Role verification**: Super Admin sees correct data
- [ ] **Accessibility verification**: Checklist above passes

### Estimated Implementation Time

1–2 hours.

### Files Expected To Change

| File | Type | Change |
|------|------|--------|
| `resources/views/users/show.blade.php` | Blade | Wrap permission matrix in feature-group accordions |

---

## 10. Batch 5: Module Permissions

### Objective

Replace the flat permission matrix with a role-first design. Introduce role selector dropdown, feature-group accordions, labeled toggle switches (replacing CRUDAREI codes), and inline row editor (replacing modal).

### User Job Being Improved

Job D: Change module permissions for a role.

### Pages Affected

- `resources/views/module-permissions/index.blade.php`
- `public/js/module-permissions.js` (if separate) or inline `<script>`
- `app/Http/Controllers/Web/ModulePermissionController.php`

### Exact UI Changes

1. Add a role selector dropdown above the permission grid — only one role shown at a time
2. Group modules by feature in accordions
3. Default: Infrastructure expanded, Productivity expanded, Administration collapsed, Integration collapsed
4. Replace permission letter "CRUDAREI Rev" display with labeled toggle switches
5. Replace the modal editor with an inline editor that slides open within each module row
6. Add per-module Save button (instead of one form for all modules)
7. Keep "Manage Roles" header button

### Expected User Benefit

- Horizontal scroll eliminated (N role columns → 1 role at a time)
- Time to change one permission: 12–18s → 5–7s
- Cognitive decoding of letter codes eliminated
- Context-switching eliminated (modal → inline editor)
- Batch permission changes easier (toggle multiple, save once per module)

### Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Role selector causes page reload | Medium | Low | Use Alpine.js AJAX or query parameter reload (simpler). |
| Inline editor breaks on small screens | Low | Medium | Test at 768px, 1024px, 1280px. Editor slides below row. |
| Permission save endpoint changes | High | High | Must send exact same payload. No backend changes allowed. |
| Toggle styling inconsistent | Low | Low | Use existing Tailwind + `role="switch"`. |

### UX Justification

| Gate Question | Answer |
|--------------|--------|
| **1. User's actual job?** | Change one permission for one role on one module. |
| **2. Why difficult now?** | Must decode letter codes, scan horizontally across N role columns, use a context-hiding modal. |
| **3. Unnecessary info?** | All N role columns (replace with selector). Letter codes (replace with toggles). Repeated feature names (accordion). |
| **4. Hidden info to promote?** | Permission states now visible as labeled toggles — no decoding. |
| **5. Clicks reduced?** | 5 → 2 clicks for a single permission change (modal eliminated). |
| **6. Scrolling reduced?** | No horizontal scroll. Feature accordions reduce vertical scanning. |
| **7. Thinking reduced?** | CRUDAREI decoding (~2s per cell) eliminated. "Which column is Admin?" eliminated. |
| **8. Training reduced?** | "Pick a role. Find the module. Flip the switches. Click Save." — 30 seconds. |
| **9. Mistakes reduced?** | Letter code misreading eliminated. Editing wrong role's permissions eliminated. |

**Net usability improvement**: Significant. 60% fewer clicks, no horizontal scroll, no letter decoding, no context-switching modal.

**30 Second Rule**: "I pick a role from the dropdown. I see modules with switches. I flip what I need." — Passes.

**Gate result**: All improvements justified. No regressions. **Proceed.**

### Browser Testing Checklist

- [ ] Module Permissions page loads without errors
- [ ] Role selector dropdown shows correct roles
- [ ] Selecting a role updates the permission grid
- [ ] Infrastructure accordion expanded by default
- [ ] Productivity accordion expanded by default
- [ ] Administration accordion collapsed by default
- [ ] Integration accordion collapsed by default
- [ ] Each module shows 8 labeled toggle switches
- [ ] Toggle switches reflect current permission state
- [ ] Clicking a toggle changes its visual state
- [ ] Save button saves only that module's permissions
- [ ] "Reset to Role Default" button works
- [ ] "Remove all permissions" works with confirmation
- [ ] Manage Roles link works
- [ ] Dark mode maintains all functionality

### Accessibility Checklist

- [ ] Role selector is a `<select>` with `<option>` elements
- [ ] Accordion groups use proper ARIA
- [ ] Toggle switches use `role="switch"` with `aria-checked`
- [ ] Save button has accessible label
- [ ] Permission changes announce success/failure via `aria-live`
- [ ] Keyboard navigation: Tab through toggles, Enter/Space to toggle

### Regression Checklist

- [ ] `ModulePermissionController@update` receives correct payload format
- [ ] `ModulePermissionController@destroy` receives correct payload format
- [ ] Permission values are persisted correctly
- [ ] No permission changes affect modules not explicitly saved
- [ ] `updated_at` conflict detection still works
- [ ] Super Admin permissions remain unaffected

### Rollback Plan

```
git revert HEAD --no-commit
git checkout HEAD -- resources/views/module-permissions/index.blade.php
git checkout HEAD -- app/Http/Controllers/Web/ModulePermissionController.php
```

### Verification Requirements

- [ ] **Before screenshots**: Showing horizontal scroll and CRUDAREI codes
- [ ] **After screenshots**: Showing role selector and toggle switches
- [ ] **UX Decision Gate**: Passes (documented above)
- [ ] **30 Second Rule**: Test with new user
- [ ] **Rollback verification**: Executed in staging
- [ ] **Performance verification**: Page load time not increased
- [ ] **Role verification**: Super Admin sees correct roles
- [ ] **Accessibility verification**: Checklist above passes

### Estimated Implementation Time

4–8 hours.

### Files Expected To Change

| File | Type | Change |
|------|------|--------|
| `resources/views/module-permissions/index.blade.php` | Blade | Replace entire table with role selector + accordion + toggles + inline editor |
| `resources/views/components/permissions/` | Components | May need new toggle-switch component |
| `app/Http/Controllers/Web/ModulePermissionController.php` | Controller | May need `?role_id` query param support |

---

## 11. Batch 6: Domains

### Objective

Apply the same Hosting Index simplification pattern to the Domains Index.

### User Job Being Improved

"Find a domain and view its details." — Admin, IT Support.

### Pages Affected

- `resources/views/domains/index.blade.php`
- `app/Http/Controllers/Web/DomainController.php` (or `indexSelect()`)

### Exact UI Changes

- Audit current Domains Index columns against Constitution Level 1–4 hierarchy
- Remove columns that do not help find a domain
- Add Expiry column if missing
- Move infrequent actions to overflow menu

### Verification Requirements

- [ ] Before/after screenshots
- [ ] UX Decision Gate — all pass
- [ ] 30 Second Rule — pass
- [ ] Rollback verification
- [ ] Performance verification
- [ ] Role verification
- [ ] Accessibility verification

### Estimated Implementation Time

1–2 hours.

---

## 12. Batch 7: Service Providers

### Objective

Apply the same simplification pattern to the Service Providers Index.

### User Job Being Improved

"Find a provider and view their details." — Admin.

### Pages Affected

- `resources/views/service-providers/index.blade.php`
- `app/Http/Controllers/Web/ServiceProviderController.php` (or `indexSelect()`)

### Exact UI Changes

- Audit current columns
- Remove non-essential columns
- Apply overflow menu for infrequent actions

### Verification Requirements

- [ ] Before/after screenshots
- [ ] UX Decision Gate — all pass
- [ ] 30 Second Rule — pass
- [ ] Rollback verification
- [ ] Performance verification
- [ ] Role verification
- [ ] Accessibility verification

### Estimated Implementation Time

30–60 minutes.

---

## 13. Batch 8: Expiry Trackers

### Objective

Apply the same simplification pattern to the Expiry Trackers Index.

### User Job Being Improved

"View upcoming renewals and take action." — Admin, Office Management.

### Pages Affected

- `resources/views/expiry-trackers/index.blade.php`
- `app/Http/Controllers/Web/ExpiryTrackerController.php` (or `indexSelect()`)

### Exact UI Changes

- Audit current columns
- Keep: Name, Type, Expiry Date, Days Left, Status, Assigned To, Actions
- Move less essential columns to show page or tooltip

### Verification Requirements

- [ ] Before/after screenshots
- [ ] UX Decision Gate — all pass
- [ ] 30 Second Rule — pass
- [ ] Rollback verification
- [ ] Performance verification
- [ ] Role verification
- [ ] Accessibility verification

### Estimated Implementation Time

1–2 hours.

---

## 14. Batch 9: SMTP Profiles

### Objective

Apply the same simplification pattern to the SMTP Profiles Index.

### User Job Being Improved

"Find and test an SMTP profile." — Super Admin.

### Pages Affected

- `resources/views/smtp-profiles/index.blade.php`

### Exact UI Changes

- Audit current columns
- Keep: Name, Sender Email, Status (verified/unverified), Provider, Actions
- Move technical fields (host, port, protocols) to show page

### Verification Requirements

- [ ] Before/after screenshots
- [ ] UX Decision Gate — all pass
- [ ] 30 Second Rule — pass
- [ ] Rollback verification
- [ ] Performance verification
- [ ] Role verification
- [ ] Accessibility verification

### Estimated Implementation Time

30–60 minutes.

---

## 15. Batch 10: Monitoring

### Objective

Apply the same simplification pattern to the Monitor Index.

### User Job Being Improved

"Check the health of monitored resources." — Admin, IT Support.

### Pages Affected

- `resources/views/monitor/index.blade.php` (or equivalent)

### Exact UI Changes

- Audit current columns
- Keep: Resource, Type, Status, Last Check, Response Time, Actions
- Move historical data to show page

### Verification Requirements

- [ ] Before/after screenshots
- [ ] UX Decision Gate — all pass
- [ ] 30 Second Rule — pass
- [ ] Rollback verification
- [ ] Performance verification
- [ ] Role verification
- [ ] Accessibility verification

### Estimated Implementation Time

1–2 hours.

---

## 16. Batch 11: Remaining Modules

### Objective

Apply the simplification pattern to all remaining modules that have index/show pages.

### User Job Being Improved

General: consistent UX across all modules.

### Pages Potentially Affected

- VPS Index
- VoIP Index
- Domain Emails Index
- Other Services Index
- Assets Index
- G-Mails Index
- Roles Index
- Activity Logs Index
- Login Audits Index
- Notes Index
- Tasks Index
- Vault Index
- Calendar Index
- Webhooks Index
- Tokens Index
- Import Index
- Reports Index

### Exact UI Changes

For each module:
1. Audit current columns against Constitution Level 1–4 hierarchy
2. Apply column reduction: keep only Level 1 fields
3. Move Level 3 fields to collapsible sections on show page
4. Move Level 4 fields to tooltips or detail-only pages
5. Apply overflow menu for infrequent actions

### Processing Order

| Priority | Module | Reason |
|----------|--------|--------|
| 1 | VPS | Infrastructure — high traffic |
| 2 | VoIP | Infrastructure — moderate traffic |
| 3 | Tasks | Productivity — high traffic |
| 4 | Vault | Productivity — high traffic |
| 5 | Notes | Productivity — moderate traffic |
| 6 | Remaining modules | Lower traffic |

### Verification Requirements

- [ ] Before/after screenshots (per module)
- [ ] UX Decision Gate — all pass
- [ ] 30 Second Rule — pass (per module)
- [ ] Rollback verification
- [ ] Performance verification
- [ ] Role verification
- [ ] Accessibility verification

### Estimated Implementation Time

4–8 hours total (15–30 minutes per module).

---

## 17. Batch 12: Dashboard

> **Why Dashboard is last**: Dashboards summarize workflows. Workflows must be simplified first (Batches 1–11). Only after the individual pages are clean can the Dashboard accurately represent what needs attention. Implementing Dashboard first would mean redesigning it again after the pages change.

### Objective

Reduce visual noise on the Dashboard. Show only information that helps the user decide what to do next.

### User Job Being Improved

"Where should I start my day?" — All roles.

### Pages Affected

- `resources/views/dashboard.blade.php` (or equivalent)
- Any widgets/components loaded on the dashboard

### Exact UI Changes

- Determine current dashboard widgets/panels
- Remove any widget that does not drive a next-action decision
- Remaining widgets sorted by urgency (expiries first, alerts second, summary third)
- Add "Last updated X min ago" timestamp to each widget
- Widgets requiring scrolling to see collapsed into "View All" link

### Expected User Benefit

The user sees at a glance what needs attention today without scrolling past stale or irrelevant widgets.

### Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Removing a widget users depend on | Medium | Medium | Audit usage first. Keep any widget with active users. |
| Layout breakage | Low | Low | Tailwind grid adapts. Test at 1280px, 1920px. |
| Changed information hierarchy | Medium | Low | Communicate changes. Temporary. |

### UX Justification

| Gate Question | Answer |
|--------------|--------|
| **1. User's actual job?** | Decide where to start work today. |
| **2. Why difficult now?** | Too many widgets — hard to find urgent items. |
| **3. Unnecessary info?** | Widgets that don't drive a next-action decision. |
| **4. Hidden info to promote?** | Urgency-sorted order puts expiring items first. |
| **5. Clicks reduced?** | Fewer widgets = less scanning. |
| **6. Scrolling reduced?** | Collapse low-priority widgets behind "View All". |
| **7. Thinking reduced?** | First widget = most urgent. No decision needed. |
| **8. Training reduced?** | "Look at the top widget. That's what needs attention first." — 10 seconds. |
| **9. Mistakes reduced?** | Less visual noise = less chance of overlooking urgent renewals. |

**Net usability improvement**: Moderate. Urgency-sorted, less visual noise, actionable at a glance.

**30 Second Rule**: "I see what needs attention at the top. I click to act on it." — Passes.

**Gate result**: All improvements justified. No regressions. **Proceed.**

### Browser Testing Checklist

- [ ] Dashboard loads without errors
- [ ] All remaining widgets render data correctly
- [ ] Widgets stack properly at 1280px, 1024px, 768px
- [ ] No horizontal scroll introduced
- [ ] "View All" links navigate to correct pages
- [ ] Dark mode matches light mode

### Accessibility Checklist

- [ ] Widget headings use `<h2>` or `<h3>` in correct document outline
- [ ] Widgets have accessible names via `aria-label`
- [ ] Color-coded urgency signals include text labels
- [ ] Widget refresh timestamps announced via `aria-live="polite"`
- [ ] Keyboard navigation flows left-to-right, top-to-bottom

### Regression Checklist

- [ ] All existing dashboard routes return 200
- [ ] Widget data queries return correct counts
- [ ] "View All" links point to correct index routes
- [ ] Page load time not increased

### Rollback Plan

```
git revert HEAD --no-commit
git checkout HEAD -- resources/views/dashboard.blade.php
```

### Verification Requirements

- [ ] **Before screenshots**: Full-page screenshot at 1280px
- [ ] **After screenshots**: Full-page screenshot at 1280px
- [ ] **UX Decision Gate**: Passes (documented above)
- [ ] **30 Second Rule**: Test with new user
- [ ] **Rollback verification**: Executed in staging
- [ ] **Performance verification**: Page load time not increased
- [ ] **Role verification**: All roles see correct widgets
- [ ] **Accessibility verification**: Checklist above passes

### Estimated Implementation Time

2–4 hours.

### Files Expected To Change

| File | Type | Change |
|------|------|--------|
| `resources/views/dashboard.blade.php` | Blade | Remove widgets, reorder, add timestamps |
| Any widget component blades | Blade | May add timestamps or collapse |

---

## 18. Summary

### Implementation Order

| Batch | Page | Est. Time | Complexity | Why This Order |
|-------|------|-----------|------------|----------------|
| 1 | Hosting Index | 30–60 min | Very Low | Highest-frequency job, lowest effort, highest impact |
| 2 | Hosting Show | 2–4 hrs | Medium | Completes Job A optimization |
| 3 | Users Index | 1–2 hrs | Low | Safety improvement (misclick reduction) |
| 4 | Users Show | 1–2 hrs | Low | Progressive disclosure for permission audit |
| 5 | Module Permissions | 4–8 hrs | High | Cognitive load reduction on complex page |
| 6 | Domains | 1–2 hrs | Low | Follows Hosting Index pattern |
| 7 | Service Providers | 30–60 min | Low | Simple index simplification |
| 8 | Expiry Trackers | 1–2 hrs | Low | Office management workflow |
| 9 | SMTP Profiles | 30–60 min | Low | Simple index simplification |
| 10 | Monitoring | 1–2 hrs | Low | Health check workflow |
| 11 | Remaining Modules | 4–8 hrs | Low-Med | Consistent UX across all modules |
| 12 | Dashboard | 2–4 hrs | Low | **Last** — workflows must be simplified first |

**Total estimated implementation time**: 19–36 hours.

### Success Metrics

Every batch is measured against these goals:

| Metric | Target |
|--------|--------|
| Less thinking | Decision time reduced by ≥50% per page |
| Less searching | Scan time reduced by ≥50% per page |
| Less scrolling | Page height reduced by ≥50% or horizontal scroll eliminated |
| Less clicking | Primary job click count not increased (infrequent actions may add +1) |
| Less training | New employee understands page in ≤30 seconds |
| Fewer mistakes | Specific error types identified and probability reduced |

---

> **Governance reminder**: No batch may be implemented without explicit approval. This document is a planning artifact, not a work order. Each batch must pass the UX Decision Gate and the 30 Second Rule before code is written.

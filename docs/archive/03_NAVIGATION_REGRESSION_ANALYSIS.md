# 03 — NAVIGATION REGRESSION ANALYSIS

> Every change that could make navigation WORSE.
> Not theory — specific regressions that WILL occur.

---

## Regression 1: Spatial Memory Destruction

### What Breaks

Every user who has been using the system for more than 2 weeks has SPATIAL MEMORY of the current nav. They know "Domain Emails is the 5th item in the Infrastructure group, 4th from the bottom."

### Affected Users

| User Type | Training | Current Speed | Breakage |
|-----------|----------|---------------|----------|
| IT Operator (daily user) | 6+ months | 0.5s to click any known item | 4-6 weeks of slower navigation while rebuilding spatial map |
| Super Admin (daily admin) | 6+ months | 0.3s to click Users, Roles, Permissions | Same |
| Weekly user (Manager) | 3+ months | 1-2s scanning | Less affected (less muscle memory) |

### Quantified Cost

| Factor | Value | Source |
|--------|-------|--------|
| Current click time (known item) | ~0.5s | Estimate |
| New click time (first 2 weeks) | ~2.0s | UI transition research |
| Extra time per navigation | 1.5s | |
| Navigations per day (IT Ops) | ~50 | Estimate |
| Extra time per day | ~75s (1.25 min) | |
| Extra time x 10 IT Ops x 20 days | ~4,167 min = ~69 hours | |
| **Total productivity loss (transition period)** | **~69 person-hours** | |

**This cost is paid ONCE per user.** After transition, new navigation should be faster. But the transition cost is real and should be budgeted.

### Mitigation

| Mitigation | Cost | Effectiveness |
|-----------|------|---------------|
| Gradual rollout (show old nav for 2 weeks before switching) | High dev cost | High user benefit |
| "What changed" overlay on first load | Low dev cost | Medium |
| Side-by-side comparison guide | Low dev cost | Low (users won't read) |
| Do NOT change for 3 months post-v1.0 | Zero cost | Highest — let users stabilize on current nav, THEN change |

**Recommendation: DELAY navigation changes until 3 months post-v1.0 launch.** Let users stabilize on the current nav. Ship merges as a single, well-communicated release.

---

## Regression 2: Support Call Increase

### Current State

```
Support: "Click Administration → Login Audits."
User: "Got it."
Call time: 30 seconds.
```

### Post-Change

```
Support: "What role do you have?"
User: "Admin."
Support: "You won't see Login Audits. Let me find another way."
User: "Where should I look?"
Support: "Do you have an 'Oversight' section?"
User: "No. I have My Workspace, Team Workspace, System."
Support: "Let me transfer you to a super admin to check."
Call time: 5+ minutes.
```

### Estimated Support Impact

| Factor | Current | Post-Change | Delta |
|--------|---------|-------------|-------|
| Support calls about navigation | 5/week | 25/week (est) | +400% |
| Average call duration | 2 min | 5 min | +150% |
| Escalations to super admin | 0/week | 5/week | INF |
| First-contact resolution rate | 95% | 60% (est) | -35% |

### Why This Matters

The recommendation assumed "persona-filtered nav reduces support calls because users find things faster." This is TRUE for IN-ROLE items. It's FALSE for CROSS-ROLE support.

**The system doesn't exist in isolation.** Users:
- Ask colleagues for help (different roles → different nav)
- Follow documentation written for a specific role
- Receive instructions from vendors, trainers, or consultants
- Switch roles (promotion, temporary assignment)

Every cross-role interaction becomes a translation problem.

---

## Regression 3: Documentation Fragmentation

### Current State

One set of documentation works for all users:
```
"To reset a mailbox password, click Domain Emails."
```

### Post-Change

Documentation must specify persona:
```
"To reset a mailbox password:
- IT Ops, Service Desk: Click Team Workspace → Services → Domains → Select domain → Mailboxes tab
- (If you don't see Services, you may need a different role. Contact your administrator.)
- Super Admin: Click System → Configuration → ... (actually, Super Admin should use the same path)
```

### Documentation Cost

| Factor | Current | Post-Change |
|--------|---------|-------------|
| Instructions per page | 1 path | 3-4 paths |
| Documentation maintenance | Low | Very high |
| Screenshot updates | 1 per flow | 3-4 per flow |
| User confusion probability | 5% | 30%+ |

---

## Regression 4: Calendar Visibility

### What Breaks

The Calendar was used weekly by IT Managers for planning. Moving it to a "view toggle" on Tasks and Renewals:

| Regression | Impact |
|------------|--------|
| Manager can no longer see ALL events in one place | Planning quality decreases |
| Calendar is not discoverable (hidden in view toggle) | Usage drops 50-80% |
| Tasks calendar shows only tasks. Renewals calendar shows only renewals. No unified view. | Cross-modality planning lost |

### Severity: HIGH for IT Manager persona.

---

## Regression 5: Attachment Discovery

### What Breaks

The global Attachments list was the ONLY way to find a file without knowing which record it belongs to. Making attachments "contextual only":

| Regression | Impact |
|------------|--------|
| "Find the contract PDF for Provider XYZ" impossible without knowing the provider exists | Search becomes the only path |
| If attachment was uploaded without proper parent record association, it's permanently lost | Data loss |
| No way to audit ALL attachments across the system | Compliance gap |

### Severity: MEDIUM for auditing. LOW for daily use.

---

## Regression 6: Feature Flag Visibility

### What Breaks

Removing "Features" from navigation:

| Regression | Impact |
|------------|--------|
| Super Admin configuring a new module doesn't know feature flags exist | Configuration incomplete |
| Documentation reference to "Features page" returns 404 | Support calls |
| Audit trail: "Was feature X enabled or disabled?" requires navigating to module detail page | Extra steps |

### Severity: LOW (affects only super admin, rarely). But if Features are critically important for module configuration, the regression is real.

---

## Regression 7: Privilege Discovery

### What Breaks

Merging Privileges into Roles & Permissions (or removing them):

| Regression | Impact |
|------------|--------|
| New super admin trying to understand "what actions exist" has no reference | Learning impeded |
| Developer documentation references privilege names that aren't visible | Confusion |
| Permission audit requires cross-referencing between Roles and an invisible Privilege list | Incomplete audit |

### Severity: LOW (affects only super admin, rarely). Privileges are a developer concept exposed in UI — removing them may actually BE the correct fix.

---

## Regression 8: Multi-Role Blind Spots

### What Breaks

Consider Sarah: she's an IT Operator who also handles procurement. Under persona-filtered nav:

| If assigned to | She sees | She misses |
|---------------|----------|------------|
| IT Operator | Services, Vendors, Assets, Tasks, Vault | Reports, Renewals forecast |
| Procurement | Vendors, Renewals, Assets, Reports | Hosting, Domains, VPS, VoIP |

Either assignment creates blind spots. Sarah must ask for role switching (if implemented) or log in as a different user.

### Severity

| Factor | Value |
|--------|-------|
| % multi-role users | Unknown (estimate 20-40%) |
| Blind spots per user | 2-5 critical items |
| Workaround | Role switching, separate login, "browse all" |
| Productivity impact | 5-10 min/day switching contexts |

---

## Regression 9: The "Where Did X Go?" Problem

Every user will experience this:

| Old Location | New Location | User's Mental Query |
|-------------|--------------|---------------------|
| Infrastructure → Domain Emails | Services → Domains → Mailboxes tab | "Where did email go?" |
| Administration → Login Audits | Oversight → Audit Trail → Logins tab | "Where did login history go?" |
| Administration → Modules | System → Module Setup | "Where did module config go?" |
| Credentials → My Credentials | My Workspace → Vault (my filter) | "Where did my passwords go?" |
| Operations → Calendar | (view toggle) | "Where did the calendar go?" |

### Quantified

| Item | Old Location | New Location | Clicks to Find (old) | Clicks to Find (new) |
|------|-------------|--------------|---------------------|---------------------|
| Domain Emails | Infrastructure (3rd item) | Services → Domains → Mailboxes tab | 1 | 3 |
| Login Audits | Administration (9th item) | Oversight → Audit Trail → Logins tab | 1 | 3 |
| Permissions | Administration (4th item) | System → Roles & Permissions | 1 | 2 |
| Calendar | Operations (3rd item) | View toggle on Tasks/Renewals | 1 | 2-3 |
| Attachments | Administration (12th item) | Contextual only (no global list) | 1 | Search or browse |

**For Domain Emails and Login Audits, the click cost INCREASES by 2-3x.** This is a regression for the users who access these items daily.

---

## Regression 10: Internationalization Complexity

### What Breaks

Persona-filtered nav requires persona-specific labels. For a system that supports multiple languages:

| Current | Post-Change |
|---------|-------------|
| Translate 34 item labels = 34 strings | Translate 34 item labels × 8 persona profiles × N workspace tiers = 272+ strings |
| One translation per language | Persona-aware translations per language |
| Simple i18n | Persona × i18n matrix |

**Maintenance cost for internationalization increases 8x.**

---

## REGRESSION SUMMARY

| # | Regression | Severity | Affected Users | Mitigation Cost |
|---|-----------|----------|---------------|-----------------|
| 1 | Spatial memory breakage | MEDIUM | ALL | Delay 3 months post-launch |
| 2 | Support call increase | HIGH | ALL (via support) | "Browse all" toggle, role-aware instructions |
| 3 | Documentation fragmentation | MEDIUM | Documentation consumers | Persona-aware documentation generator |
| 4 | Calendar visibility loss | MEDIUM | IT Managers | Keep Calendar standalone |
| 5 | Attachment discovery loss | LOW | File searchers | Global search covers this |
| 6 | Feature flag invisibility | LOW | Super Admin | Inline on Module detail |
| 7 | Privilege reference loss | LOW | Super Admin | Remove — correct fix |
| 8 | Multi-role blind spots | HIGH | 20-40% of users | Role switching, "browse all" |
| 9 | Item relocation friction | HIGH | ALL | Communication, migration guide |
| 10 | i18n complexity | MEDIUM | International deployments | Accept 8x cost or reduce persona variants |

**Total regression severity: 4 HIGH, 3 MEDIUM, 3 LOW.**

**Every recommendation must include a regression mitigation plan, not just a migration plan.**

# V1.1 ROADMAP — Post-Release Product Experience Plan

> Based on: Persona Model (8 personas), Navigation Architecture Review, Information Architecture Audit, Menu Semantic Analysis, Business Workflow Mapping, Role-Based Navigation Strategy, Architecture Board Findings, Known Limitations, and Business Rules.
>
> **Status:** Pre-release planning document. Do not implement — prioritize against user feedback.

---

## Tier 1: Must Do Before Real Users Onboard

These items are production safety and data integrity guarantees. Users should never encounter these conditions.

| # | Item | Source | Effort | Risk If Skipped |
|---|------|--------|--------|-----------------|
| M1 | **firstOrFail in all 10 Web controllers' store()** — `Module::where('slug', ...)` must use `->firstOrFail()` instead of `->first()` with silent null `if ($module)` check. Silent null module_id = invisible records. | BR-11, BR-12, Architecture Board P0 | 1 hour | Records created with null module_id → invisible to all queries, exports, dashboards |
| M2 | **NOT NULL + FK constraint on `module_id`** across all business tables. Requires data cleanup first (find and fix records with null module_id), then migration. | BR-11, Architecture Board P1 | 1 day | Database allows orphan records with no module association |
| M3 | **Fix pre-existing test flakes** — ExceptionHandlerTest invalid cloudflare status. Not blocking release but produces false negatives. | Final closeout report | 2 hours | Unreliable CI pipeline |
| M4 | **Seed production-safe initial data** — If production requires initial data (e.g., default roles, default modules), create a `ProductionSeeder` that explicitly excludes demo data. | BR-06 | 4 hours | Manual setup steps required for every fresh install |
| M5 | **Add read-only role for Reporting** — IT Director, Security Officer, and Procurement need Reports access without being super-admin. Create a `report-viewer` or `oversight` role that grants Report access only. | User Journey mapping, BR-13, BR-14 | 1 day | IT Director must be super-admin to see Reports → least-privilege violation |

---

## Tier 2: Can Do After 1 Week of User Feedback

These items require real-user validation to prioritize correctly. Do not pre-commit.

| # | Item | Feedback Needed | Effort |
|---|------|-----------------|--------|
| F1 | **Rename "Other Services" → "SaaS Subscriptions"** | Do users refer to these as "SaaS"? Or "Software"? Or "Third-party apps"? | 30 min |
| F2 | **Rename "Domain Emails" → "Mailboxes"** and nest under Domains in sidebar | Is "Mailboxes" more intuitive than "Domain Emails"? | 1 hour |
| F3 | **Merge My Tasks + Task Management → single "Tasks" with filter toggle** | Do users benefit from the split? Or is it confusing? | 2 hours |
| F4 | **Merge My Credentials + Shared Credentials → single "Vault" with filter toggle** | Do users understand "Vault"? Or prefer "Credentials" with filter? | 2 hours |
| F5 | **Add "Quick Actions" dropdown to top bar** (New Hosting, New Domain, New Task, Store Credential) | Which shortcuts do users actually use most? Should be data-driven. | 4 hours |
| F6 | **Reorder sidebar groups** — put Account/Profile at bottom, collapse unused groups by default | Does group ordering match mental model? Test with 3-5 users. | 1 hour |
| F7 | **Rename technical labels to business language** — SMTP→Mail Settings, Webhooks→Integrations, Activity Logs→Audit Trail, Login Audits→Login History, Import→Data Import | Are these renames helpful? Or do existing names cause confusion? | 2 hours |
| F8 | **Dashboard personalization** — show persona-relevant KPIs first, hide irrelevant widgets | Which KPIs does each persona actually monitor? | 1 day |
| F9 | **Calendar context** — clarify whether calendar shows renewal dates, task deadlines, or both. Rename to "Timeline" or add descriptive subtitle. | What do users expect Calendar to show? | 2 hours |

---

## Tier 3: v1.1 Architecture Debt

These are engineering improvements with no user-facing change. Prioritize by risk, not by user request.

### P0 — Must Fix (Production Safety)

| # | Item | Effort | Detail |
|---|------|--------|--------|
| A1 | **ModuleSlug enum/singleton** — consolidate 18+ hardcoded slug arrays into a single registry. Controllers reference `ModuleSlug::Domains->value` instead of `'domains'`. | 3 days | BR-07, 18+ locations. Slug changes currently cause silent breakage across 10 controllers, sidebar, export, search, calendar, monitor, import, bulk ops, renewal sync |
| A2 | **ModulePolicy: prevent slug changes and deletion** — add authorization to Module CRUD that blocks slug modifications and hard-deletes. Allow only display name changes. | 4 hours | BR-07. Module deletion is a catastrophic action with no safeguards |
| A3 | **API show/update/destroy align to module-scoped** — change from `where('user_id', auth()->id())` to `getAccessibleModuleIds('read')` pattern, matching API index() and all Web controllers. | 1 day | BR-04, BR-08. Zero frontend consumers today, but contract correctness matters |
| A4 | **Remove `user_id` from `$fillable`** — currently restored in Phase 4. Move `user_id` setting to `Auth::id()` in controller only, never from request. | 1 day | BR-08. Prevents mass-assignment override |

### P1 — Should Fix (Maintainability)

| # | Item | Effort | Detail |
|---|------|--------|--------|
| A5 | **`super-admin` hardcoded literal → config constant** — 40+ locations use `'super-admin'` string. Define `config('auth.super_admin_role')` or enum. | 1 day | BR-15, Architectural Assumption 1.2 (RISKY) |
| A6 | **`created_by` migration** — rename `user_id` → `created_by` on 13+ business tables. Requires coordinated migration + updating all references. | 2 days | BR-08, Phase 4 semantic audit. Clarifies creator vs. owner semantics |
| A7 | **Duplicate hardcoded slug arrays consolidated** — `ExpiryNotificationService`, `CalendarController`, `SearchController`, `ExportController`, `SidebarComposer`, `DashboardController` all have independent module slug lists. Merge into registry. | 2 days | 18+ arrays, each a drift risk |

### P2 — Nice to Fix

| # | Item | Effort | Detail |
|---|------|--------|--------|
| A8 | **AppServiceProvider cache invalidation granularity** — `Model::saved()` increments version for EVERY model. Scope to only models that affect dashboard. | 2 hours | Cache stampede risk under write load |
| A9 | **Search performance** — `LIKE '%term%'` does not scale. Add full-text index or MeiliSearch integration. | 2-4 days | Acceptable <10k records |
| A10 | **RenewalSyncService table-name assumption** — `slug = getTable()` is brittle. Use explicit mapping from registry. | 2 hours | BR-12, Architectural Assumption 3.2 |

---

## Tier 4: v1.1 Navigation/UX Improvements

These are Blade-template-only changes (same routes, same permissions, same controllers).

### Phase A: Label Fixes (2 hours total)

| # | Current Label | New Label | Change Type |
|---|---------------|-----------|-------------|
| N1 | Other Services | SaaS Subscriptions | String change |
| N2 | Domain Emails | Mailboxes | String change |
| N3 | SMTP Profiles | Mail Settings | String change, move to System group |
| N4 | Webhooks | Integrations | String change, move to System group |
| N5 | API Access | Developer Tokens | String change, move to System group |
| N6 | Activity Logs | Audit Trail | String change, merge with Login History |
| N7 | Login Audits | Login History | String change, merge with Audit Trail |
| N8 | Import | Data Import | String change, move to Operations |
| N9 | Modules | Applications | String change |
| N10 | Features | (merge into Applications) | Remove separate entry |
| N11 | Privileges | (merge into Roles & Permissions) | Remove separate entry |
| N12 | Role Templates | (merge into Roles & Permissions) | Remove separate entry |
| N13 | Permissions | (merge into Roles & Permissions) | Remove separate entry |

### Phase B: Group Restructuring (4 hours)

**Current (7 groups, 34 items):**
```
Dashboard | Notifications
Infrastructure (9) | Credentials (2) | Operations (3) | Administration (14) | Reports (1) | Account (3)
```

**Proposed (8 groups, 24 items):**
```
Dashboard | Quick Actions | Notifications
SERVICES (5): Domains > Mailboxes | Web Hosting | Servers | Phone Systems | SaaS Subscriptions
VENDORS (2): Providers | Contracts & Renewals
ASSETS (1): Hardware & Software
CREDENTIALS (1): Vault
OPERATIONS (3): Tasks | Calendar | Data Import
ACCESS CONTROL (3): Users | Roles & Permissions
SYSTEM (3): Module Setup | Mail Settings | Integrations & API | Attachments
AUDIT (2): Audit Trail | Login History
REPORTS (1): Reports
ACCOUNT (3): My Profile | My Access | Help Center
```

### Phase C: Role-Based Visibility (1 day)

Using existing role labels (no new roles needed):

| Existing Role | Profile | Visible Items |
|---|---|---|
| `super-admin` | Full nav | All 24 items |
| `admin` | IT Operator | ~18 items (Services + Vendors + Assets + Credentials + Operations + Account) |
| `editor` | Service Desk | ~11 items (Services + Credentials + Operations + Account) |
| `user`/`customer` | End User | ~5 items (Vault (My) + Tasks (My) + Account) |

**New roles to create (if architecture allows):**
- `security` → Security Officer profile (~9 items: Access Control + Audit + Reports + Account)
- `procurement` → Procurement profile (~8 items: Vendors + Assets + Reports + Account)
- `director` → IT Director profile (~7 items: Vendors + Operations + Reports + Audit + Account)

**Net effect:** Average irrelevant items drops from 64% to 13%.

### Phase D: Quick Actions Dropdown (4 hours)

Add top-bar dropdown with persona-relevant shortcuts:
- IT Operator: New Hosting, New Domain, New VPS, New Credential, New Task
- Service Desk: New Task, Search Credentials
- End User: Store Credential
- Super Admin: New User, New Module
- (hidden per role via existing Blade `@hasrole` directives)

---

## Tier 5: Wontfix

These are acknowledged limitations with no planned remediation.

| # | Item | Reason |
|---|------|--------|
| W1 | **Full workflow entry points** ("New Site Launch", "Employee Offboarding") | Requires new controller + view + permission → new feature, not improvement |
| W2 | **API consumer documentation** (Swagger domain fix) | No consumer exists. Fix when first external integration connects |
| W3 | **Multi-tenancy** | Scope change. Single-tenant is explicit v1.0 design |
| W4 | **Push notifications / Slack integration** | New feature, not nav/UX improvement. v2.0 candidate |
| W5 | **Drag-and-drop Kanban** | Requires frontend framework upgrade. v2.0 candidate |
| W6 | **OAuth / SSO** | New feature. v2.0 candidate |
| W7 | **Repository layer** (AD-7) | Over-engineering for current scale. Introduce only if controllers grow beyond current size |
| W8 | **Full-text search** | Not needed until >10k records per table. Monitor and implement when needed |
| W9 | **Mobile app** | Scope change. The app is responsive web; mobile app is a separate product decision |
| W10 | **Rename `user_id`→`created_by` without also fixing API parity** | Must do both together. API parity (A3) must precede schema rename (A6) |

---

## Summary Timeline

| Tier | When | Items | Effort | Priority |
|------|------|-------|--------|----------|
| Tier 1 | Before onboarding | M1-M5 | ~2-3 days | Critical — data integrity |
| Tier 2 | After 1 week feedback | F1-F9 | ~2 days | High — user experience validation |
| Tier 3 | Sprint 1-3 | A1-A10 | ~8-10 days | Architecture — continuous |
| Tier 4 | Sprint 2-4 | N1-N13 + Phases A-D | ~2 days | Navigation — visible improvement |
| Tier 5 | Never (v2.0+) | W1-W10 | — | Explicit wontfix |

**Recommended sprint order:**
1. **Sprint 1:** M1-M5 (must-do before onboard) + A1-A3 (architecture P0)
2. **Sprint 2:** A4-A6 (architecture P1) + Phase A label fixes + Phase B group restructure
3. **Sprint 3:** Phase C role-based visibility + Phase D quick actions + A7-A8 (architecture P2)
4. **Sprint 4:** F1-F9 (feedback-driven) + A9-A10
5. **After Sprint 4:** Re-prioritize remaining items based on real user data

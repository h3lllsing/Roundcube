# NAVIGATION IMPROVEMENT PLAN — v1.1

> Blade-template-only changes. Same routes, same permissions, same controllers, same database.

---

## Current State

```
TOP:       Dashboard | Notifications
────────────────────────────────────────────
Infrastructure (9):
  Service Providers | Hosting | Domains | Domain Emails | VPS Accounts | VoIP | Other Services | Renewals | Assets

Credentials (2):
  My Credentials | Shared Credentials

Operations (3):
  My Tasks | Task Management | Calendar

Administration (14):
  Users | Roles | Role Templates | Privileges | Modules | Permissions | Features
  | SMTP Profiles | Activity Logs | Login Audits | Import | Attachments | Webhooks | API Access

Reports (1):
  Reports

Account (3):
  My Profile | My Access | Help Center
```

**34 items. 7 groups. 6 IA violations. 53% of labels need changing. 64% of items irrelevant to the average user.**

---

## Proposed State

```
TOP:       Dashboard | [Quick Actions] | Notifications
────────────────────────────────────────────
SERVICES (5):
  Domains > Mailboxes | Web Hosting | Servers | Phone Systems | SaaS Subscriptions

VENDORS (2):
  Providers | Contracts & Renewals

ASSETS (1):
  Hardware & Software

CREDENTIALS (1):
  Vault  [My | Shared filter toggle]

OPERATIONS (3):
  Tasks  [My | All filter toggle] | Calendar | Data Import

ACCESS CONTROL (3):
  Users | Roles & Permissions

SYSTEM (4):
  Module Setup | Mail Settings | Integrations | Attachments

AUDIT (2):
  Audit Trail | Login History

REPORTS (1):
  Reports

ACCOUNT (3):
  My Profile | My Access | Help Center
```

**24 items. 10 groups. 0 IA violations. Business labels. Role-filterable.**

---

## Detailed Changes

### 1. Group Restructuring

| Current Group | Current Items | New Groups | Rationale |
|---------------|---------------|------------|-----------|
| Infrastructure (9) | Service Providers, Hosting, Domains, Domain Emails, VPS, VoIP, Other Services, Renewals, Assets | **SERVICES** (Domains > Mailboxes, Web Hosting, Servers, Phone Systems, SaaS Subscriptions) + **VENDORS** (Providers, Contracts & Renewals) + **ASSETS** (Hardware & Software) | Infrastructure was a grab-bag of vendors, services, time-based processes, and hardware. Split into 3 cohesive groups. |
| Credentials (2) | My Credentials, Shared Credentials | **CREDENTIALS** (Vault with My/Shared filter) | Merge duplicates. One entry, filter toggle. |
| Operations (3) | My Tasks, Task Management, Calendar | **OPERATIONS** (Tasks with My/All filter, Calendar, Data Import) | Merge duplicate tasks. Import moves from Administration. |
| Administration (14) | All 14 admin items | **ACCESS CONTROL** (Users, Roles & Permissions) + **SYSTEM** (Module Setup, Mail Settings, Integrations, Attachments) + **AUDIT** (Audit Trail, Login History) | Split 14-item monolith into 3 cohesive groups. |
| Reports (1) | Reports | **REPORTS** (unchanged, but role-gated to non-super-admin) | Reports access granted to Manager/Director/Security roles. |
| Account (3) | My Profile, My Access, Help Center | **ACCOUNT** (unchanged) | Keep. |

### 2. Label Renames

| Current Label | New Label | Why |
|---------------|-----------|-----|
| Other Services | SaaS Subscriptions | "Other" is an IA violation. SaaS/Subscriptions describes actual content. |
| Domain Emails | Mailboxes | Business language. "Domain Emails" is technical jargon. |
| VPS Accounts | Servers | Shorter, clearer. "VPS" is an acronym. |
| VoIP | Phone Systems | Business language for non-technical users. |
| Service Providers | Providers | Shorter. Context makes it clear. |
| Renewals | Contracts & Renewals | Clarifies that renewals lives here alongside contracts. |
| Assets | Hardware & Software | Describes what's actually tracked. |
| My Credentials / Shared Credentials | Vault | Single term for credential storage. Filter replaces separate entries. |
| My Tasks / Task Management | Tasks | Single term. Filter replaces separate entries. |
| Users | (keep) | Clear. |
| Roles | (merged into Roles & Permissions) | Use single entry with sub-tabs. |
| Role Templates | (merged into Roles & Permissions) | Too granular for top-level nav. |
| Privileges | (merged into Roles & Permissions) | Conceptually identical to Permissions. |
| Modules | Applications | Business language. "Modules" is internal architecture. |
| Permissions | (merged into Roles & Permissions) | Use single entry. |
| Features | (merged into Applications) | Features are part of module config, not standalone nav. |
| SMTP Profiles | Mail Settings | Protocol acronym → plain language. |
| Activity Logs | Audit Trail | Business language for change tracking. |
| Login Audits | Login History | Clearer. "Audit" can sound alarming. |
| Import | Data Import | Clarifies what's being imported. |
| Webhooks | Integrations | Developer term → business feature name. |
| API Access | Developer Tokens | Describes what the page actually manages. |

### 3. Quick Actions Dropdown

**Location:** Top bar, between Dashboard and Notifications.

**When collapsed:** Shows "+" icon.

**When expanded (per role):**

| Role | Quick Actions Shown |
|------|---------------------|
| IT Operator (admin) | New Domain, New Hosting, New VPS, New Credential, New Task |
| Service Desk (editor) | New Task, Find Credential |
| IT Manager | New Task, View Renewals |
| Procurement | New Provider, View Renewals |
| Security Officer | Audit Log, User Search |
| IT Director | Reports Dashboard |
| Super Admin | New User, New Module, System Settings |
| End User (user/customer) | Store Credential, New Task |

**Implementation:** Inline Blade switch on existing roles (`@hasrole` directives). 10 lines of template code.

### 4. Reports Access (Role Gating)

**Current:** Reports visible to `super-admin` only.

**Proposed:** Reports visible to:
- `super-admin` (full access)
- `admin` (read-only)
- New role `director` or via `manager` label (read-only)
- New role `security` (read-only)
- New role `procurement` (read-only)

**Change needed:** Add `@hasanyrole('admin|director|security|procurement')` check to Reports sidebar item visibility. The controller already supports non-super-admin access if permission rows exist.

### 5. Role-Based Visibility Matrix

See `ROLE_BASED_NAVIGATION_STRATEGY.md` for full 8-persona mapping.

**Summary of visible item counts:**

| Persona | Current (34 total) | Proposed | Irrelevant (proposed) | Improvement |
|---------|-------------------|----------|----------------------|-------------|
| IT Operator | 34 | 18 | 6 (33%) | -47% |
| IT Manager | 34 | 16 | 5 (31%) | -53% |
| Service Desk | 34 | 11 | 1 (9%) | -68% |
| Security Officer | 34 | 9 | 0 (0%) | -74% |
| Procurement | 34 | 8 | 1 (13%) | -76% |
| IT Director | 34 | 7 | 0 (0%) | -79% |
| Super Admin | 34 | 24 | 0 (0%) | -29% |
| End User | 34 | 5 | 0 (0%) | -85% |

**Average: 64% irrelevant → 13% irrelevant.**

### 6. Implementation Order

| Phase | Changes | Files | Effort | Depends On |
|-------|---------|-------|--------|------------|
| **Phase A** (Sprint 2) | Label renames only. No group moves. | `sidebar-nav-groups.blade.php` (label strings) | 30 min | Nothing |
| **Phase B** (Sprint 2) | Group restructuring. Reorder items. | `sidebar-nav-groups.blade.php` (template restructure) | 2 hours | Phase A |
| **Phase C** (Sprint 3) | Role-based visibility. Add `@hasrole` conditionals. | `sidebar-nav-groups.blade.php` (visibility conditionals) | 4-8 hours | Phase B |
| **Phase D** (Sprint 3) | Quick Actions dropdown. | `layouts/admin.blade.php` (top bar) | 2-4 hours | Nothing |
| **Phase E** (Sprint 3) | Reports role gating. | `sidebar-nav-groups.blade.php` + `ReportController` guard | 1 hour | Phase C |

### 7. Risks and Mitigations

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Users can't find moved items | Medium | Medium | Keep old labels as search aliases in command palette. Add "What's new" banner for 1 week. |
| Role-based nav hides items user needs | Low | High | Add "Show all" toggle at bottom of sidebar (temporary override). |
| Quick Actions mislead users | Low | Low | Make dropdown context-aware. Show only actions the user has permission to take. |
| Phase B + C in same sprint breaks nav | Medium | High | Ship Phase A first (labels only). Let users adjust. Phase B + C in separate deploys with QA. |

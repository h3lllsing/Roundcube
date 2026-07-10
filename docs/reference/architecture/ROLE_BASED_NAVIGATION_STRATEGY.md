# ROLE-BASED NAVIGATION STRATEGY

## Navigation Redesign Around Personas

**Constraint:** No backend changes, no permission changes, no route changes, no database changes.
**Scope:** Blade template reorganization only. Same routes, same controllers, same authorization logic.

---

## Principle

The current navigation is organized by **database entity** (one table = one menu item). The proposed navigation is organized by **job function** (one persona's workflow = one group).

Navigation must answer the user's question: **"Where do I go to do my job?"** not "Which table does this data live in?"

---

## Navigation Group Redesign

### Current: 7 groups, 34 items

```
Top links:     Dashboard | Notifications
Groups (7):    Infrastructure (9) | Credentials (2) | Operations (3) | Administration (14) | Reports (1) | Account (3)
```

### Proposed: 8 groups, 30 items (net -4 after merges)

```
Top links:     Dashboard | Quick Actions | Notifications
Groups (8):    Services (5) | Vendors (2) | Assets (1) | Credentials (1) | Operations (3) | Access Control (3) | System (3) | Account (3)
Plus:          Audit (2) | Reports (1) — visible by role, not crammed into one group
```

**Net reduction:** 34 → 30 items (4 removed by merging duplicates)

---

## Proposed Navigation Tree

### Top Bar (always visible)

| Item | Route | Role Access |
|------|-------|-------------|
| **Dashboard** | `/dashboard` | Everyone |
| **Quick Actions** | (dropdown: New Hosting, New Domain, New Task, Store Credential) | Everyone |
| **Notifications** | `/notifications` | Everyone |

### Group 1: SERVICES (what we operate)
*Replaces: Infrastructure (partial)*

| Item | Route | Visible To |
|------|-------|------------|
| Domains | `/domains` | IT Ops, IT Mgr, Service Desk, Procurement* |
| ¬ Mailboxes | `/domain-emails` | Same as Domains (shown as sub-item) |
| Web Hosting | `/hostings` | IT Ops, IT Mgr, Service Desk |
| Servers | `/vps` | IT Ops, IT Mgr, Service Desk |
| Phone Systems | `/voip` | IT Ops, IT Mgr, Service Desk |
| SaaS Subscriptions | `/other-services` | IT Ops, IT Mgr, Procurement* |

*Procurement sees read-only*

### Group 2: VENDORS (who we buy from)
*Replaces: Infrastructure → Service Providers + Renewals (partial)*

| Item | Route | Visible To |
|------|-------|------------|
| Providers | `/service-providers` | IT Ops, IT Mgr, Procurement, IT Director |
| Contracts & Renewals | `/expiry-trackers` | IT Ops, IT Mgr, Procurement, IT Director |

### Group 3: ASSETS (what we own)
*Replaces: Infrastructure → Assets*

| Item | Route | Visible To |
|------|-------|------------|
| Hardware & Software | `/assets` | IT Ops, IT Mgr, Service Desk, Procurement, IT Director |

### Group 4: CREDENTIALS (what we secure)
*Replaces: Credentials (merged)*

| Item | Route | Visible To |
|------|-------|------------|
| Vault | `/vault` | Everyone (personal filter default) OR `/my-vault` |
| ¬ My | (filter toggle) | Everyone |
| ¬ Shared | (filter toggle) | Everyone with access |

### Group 5: OPERATIONS (what we do daily)
*Replaces: Operations (expanded)*

| Item | Route | Visible To |
|------|-------|------------|
| Tasks | `/tasks` | Everyone (personal filter default) |
| Calendar | `/calendar` | Everyone |
| Data Import | `/import` | IT Ops, Super Admin |

### Group 6: ACCESS CONTROL (who has access)
*Replaces: Administration → Users, Roles, Privileges, Role Templates, Permissions*

| Item | Route | Visible To |
|------|-------|------------|
| Users | `/users` | Security Officer, Super Admin |
| Roles & Permissions | `/admin/roles`, `/module-permissions` | Security Officer, Super Admin |
| Role Templates | `/admin/role-templates` | Super Admin only |

### Group 7: SYSTEM (how OpsPilot works)
*Replaces: Administration → SMTP Profiles, Webhooks, API Access, Modules, Features + Import*

| Item | Route | Visible To |
|------|-------|------------|
| Module Setup | `/modules`, `/features` | Super Admin |
| Mail Settings | `/admin/smtp-profiles` | Super Admin |
| Integrations & API | `/webhooks`, `/tokens` | Super Admin |
| Attachments | `/attachments` | Super Admin |

### Group 8: ACCOUNT (personal)

| Item | Route | Visible To |
|------|-------|------------|
| My Profile | `/profile` | Everyone |
| My Permissions | `/my-permissions` | Everyone |
| Help Center | `/guide` | Everyone |

### Separate: AUDIT (cross-cutting)
*Replaces: Administration → Activity Logs, Login Audits*

| Item | Route | Visible To |
|------|-------|------------|
| Change Log | `/activity-logs` | IT Mgr, Security Officer, Super Admin |
| Login History | `/login-audits` | Security Officer, Super Admin |

### Separate: REPORTS (cross-cutting)
*Replaces: Reports (currently super-admin only)*

| Item | Route | Visible To |
|------|-------|------------|
| Reports | `/reports` | IT Mgr, Security Officer, Procurement, IT Director, Super Admin |

---

## Persona → Navigation Mapping

### IT Operator sees:
```
Dashboard | Quick Actions | Notifications
────────────────────────────────────────────
SERVICES
  Domains > Mailboxes
  Web Hosting
  Servers
  Phone Systems
  SaaS Subscriptions

VENDORS
  Providers
  Contracts & Renewals

ASSETS
  Hardware & Software

CREDENTIALS
  Vault > My | Shared

OPERATIONS
  Tasks
  Calendar

ACCOUNT
  My Profile | My Permissions | Help Center
```

**Items hidden:** Access Control (all), System (all), Audit, Reports.
**Total visible:** ~18 items across 5 groups. **47% reduction from current 34.**

---

### IT Manager sees:
```
Dashboard | Quick Actions | Notifications
────────────────────────────────────────────
SERVICES
  Domains > Mailboxes
  Web Hosting
  Servers
  Phone Systems

VENDORS
  Providers
  Contracts & Renewals

ASSETS
  Hardware & Software

OPERATIONS
  Tasks (all view)
  Calendar

AUDIT
  Change Log

REPORTS
  Reports

ACCOUNT
  My Profile | My Permissions | Help Center
```

**Items hidden:** Credentials (can view but navigates via search), Access Control (all), System (all).
**Total visible:** ~16 items across 6 groups.

---

### Service Desk sees:
```
Dashboard | Notifications
────────────────────────────
SERVICES
  Domains > Mailboxes
  Web Hosting
  Servers
  Phone Systems

CREDENTIALS
  Vault > My | Shared

OPERATIONS
  Tasks
  Calendar

ACCOUNT
  My Profile | My Permissions | Help Center
```

**Items hidden:** Vendors (all), Assets, Access Control (all), System (all), Audit, Reports, Quick Actions.
**Total visible:** ~11 items across 4 groups. **68% reduction from current 34.**

---

### Security Officer sees:
```
Dashboard | Notifications
────────────────────────────
ACCESS CONTROL
  Users
  Roles & Permissions

AUDIT
  Change Log
  Login History

CREDENTIALS
  Vault > Shared (read-only audit)

REPORTS
  Reports

ACCOUNT
  My Profile | My Permissions | Help Center
```

**Items hidden:** Services (all), Vendors (all), Assets, Operations (all), System (all).
**Total visible:** ~9 items across 4 groups. **74% reduction from current 34.**

---

### Procurement sees:
```
Dashboard | Notifications
────────────────────────────
VENDORS
  Providers
  Contracts & Renewals

SERVICES
  SaaS Subscriptions

ASSETS
  Hardware & Software

REPORTS
  Reports

ACCOUNT
  My Profile | My Permissions | Help Center
```

**Items hidden:** Most Services, Credentials, Operations (except Calendar), Access Control (all), System (all), Audit.
**Total visible:** ~8 items across 4 groups.

---

### IT Director sees:
```
Dashboard | Notifications
────────────────────────────
VENDORS
  Contracts & Renewals

OPERATIONS
  Tasks (all view)
  Calendar

AUDIT
  Change Log

REPORTS
  Reports

ACCOUNT
  My Profile | Help Center
```

**Items hidden:** Services detail, Credentials, Access Control, System, Assets.
**Total visible:** ~7 items across 4 groups. **79% reduction from current 34.**

---

### Super Admin sees:
```
Dashboard | Quick Actions | Notifications
────────────────────────────────────────────
WORKSPACE (expanded by default)
  Domains > Mailboxes
  Web Hosting
  Servers
  Phone Systems
  Providers
  Vault

ACCESS CONTROL
  Users
  Roles & Permissions
  Role Templates

SYSTEM
  Module Setup
  Mail Settings
  Integrations & API
  Attachments

OPERATIONS
  Tasks
  Calendar
  Data Import

AUDIT
  Change Log
  Login History

REPORTS
  Reports

ACCOUNT
  My Profile | My Permissions | Help Center
```

**Items:** 24 across 6 groups (down from 34 across 7 groups). Merges and group restructuring reduce the wall of 14 administration items.

---

### End User sees:
```
Dashboard | Notifications
────────────────────────────
CREDENTIALS
  Vault > My

OPERATIONS
  Tasks (my view)

ACCOUNT
  My Profile | Help Center
```

**Items hidden:** Everything except Credentials (My), Tasks (My), Account.
**Total visible:** 5 items across 3 groups. **85% reduction from current 34.**

---

## Implementation Strategy

This redesign requires ONLY Blade template changes:

### Files to modify

| File | Change |
|------|--------|
| `resources/views/components/sidebar-nav-groups.blade.php` | Restructure groups, rename labels, reorder items, add role-based visibility |
| `resources/views/layouts/admin.blade.php` | Add Quick Actions dropdown (same routes, just a shortcut) |

### Blade-level role detection (no backend changes)

The existing `@hasrole('super-admin')` directive already gates the Administration group. The same pattern extends to all personas:

```
@if(!Auth::user()->hasRole('super-admin') && !Auth::user()->hasRole('admin'))
    {{-- End User: show only vault + tasks --}}
@endif
```

The SidebarComposer already computes `$showVault`, `$showHostings`, etc. These flags determine entity-level visibility. The new navigation adds ROLE-GROUP visibility on top of entity permissions:

| Role | Navigation Profile |
|------|-------------------|
| `super-admin` | Full nav (24 items) |
| `admin` | IT Operator profile (18 items) + Vendors + Reports |
| `manager` (new label) | IT Manager profile (16 items) |
| `support` (new label) | Service Desk profile (11 items) |
| `security` (new label) | Security Officer profile (9 items) |
| `procurement` (new label) | Procurement profile (8 items) |
| `director` (new label) | IT Director profile (7 items) |
| `user` / `customer` / `editor` | End User profile (5 items) |

**Note:** New role labels (manager, support, security, procurement, director) would need to be created as actual roles in the Tyro system. This is a permission change. If this is out of scope, the alternative is to use the EXISTING roles (super-admin, admin, editor, user, customer) and map them to profiles:

| Existing Role | Navigation Profile |
|---------------|-------------------|
| `super-admin` | Full nav |
| `admin` | IT Operator (18 items) |
| `editor` | Service Desk (11 items) |
| `customer` | End User (5 items) |
| `user` | End User (5 items) |

This requires ZERO backend changes — just Blade template conditionals on existing roles.

---

## Navigation Profile → Existing Role Mapping

| Nav Profile | Super Admin | Admin | Editor | User | Customer |
|-------------|:-----------:|:-----:|:------:|:----:|:--------:|
| Services (full) | ✅ | ✅ | ✅ | ❌ | ❌ |
| Vendors | ✅ | ✅ | ❌ | ❌ | ❌ |
| Assets | ✅ | ✅ | ✅ | ❌ | ❌ |
| Credentials | ✅ | ✅ | ✅ | ✅ | ✅ |
| Operations (Tasks) | ✅ | ✅ | ✅ | ✅ | ✅ |
| Access Control | ✅ | ❌ | ❌ | ❌ | ❌ |
| System | ✅ | ❌ | ❌ | ❌ | ❌ |
| Audit | ✅ | ❌ | ❌ | ❌ | ❌ |
| Reports | ✅ | ❌ | ❌ | ❌ | ❌ |
| Account | ✅ | ✅ | ✅ | ✅ | ✅ |

**Current role structure does NOT support Security Officer, Procurement, or IT Director personas without granting them super-admin (too much access) or admin (too many irrelevant items).** A future improvement would add specialized roles, but for v1.0, the existing roles provide reasonable coverage:

- **Admin → IT Operator** — 90% match
- **Editor → Service Desk** — 80% match
- **User/Customer → End User** — 100% match
- **Super Admin** — 100% match (but overloaded with 34 items; nav redesign helps)
- **Missing: Security Officer, Procurement, IT Director** — no current role maps well

---

## Summary: Changes by Persona

| Persona | Current Irrelevant Items | Proposed Irrelevant Items | Improvement |
|---------|-------------------------|--------------------------|-------------|
| IT Operator | 16 of 34 (47%) | 6 of 18 (33%) | -14 items (−29%) |
| IT Manager | 20 of 34 (59%) | 5 of 16 (31%) | -15 items (−28%) |
| Service Desk | 23 of 34 (68%) | 1 of 11 (9%) | -22 items (−59%) |
| Security Officer | 28 of 34 (82%) | 0 of 9 (0%) | -28 items (−82%) |
| Procurement | 28 of 34 (82%) | 1 of 8 (13%) | -27 items (−69%) |
| IT Director | 28 of 34 (82%) | 0 of 7 (0%) | -28 items (−82%) |
| Super Admin | 0 of 34 (0%) | 0 of 24 (0%) | -10 items (−29%) |
| End User | 31 of 34 (91%) | 0 of 5 (0%) | -31 items (−91%) |

**Average irrelevant items per user:** 21.8 of 34 (64%) → 1.6 of 12.2 (13%)

**The current navigation wastes 64% of every user's screen space on items irrelevant to their job.** The proposed navigation reduces this to 13%.

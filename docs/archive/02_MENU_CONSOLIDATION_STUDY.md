# 02 — MENU CONSOLIDATION STUDY

> Every pair of related items challenged. Can they merge? Split? Nest? Disappear?

---

## 1. My Tasks vs Task Management

| Question | Answer |
|----------|--------|
| Same entity? | **YES** — both are `tasks` table records. Only the query filter differs (assignee = me vs. all). |
| Can they merge? | **YES** — single "Tasks" entry. Default filter depends on role (My Tasks for end users, All Tasks for managers). |
| Can they become tabs? | **YES** — "My" / "All" / "By Team" tabs within the Tasks page. |
| Can they become filters? | **YES** — a dropdown or toggle filter on the unified task list. |
| Can one become a child? | **YES** — "My Tasks" is a pre-filtered view of Task Management. |
| Can one disappear? | **YES** — removing one and keeping the other works, but WHY force a choice? The user's intent ("show me my tasks") is a filter, not a destination. |
| Contextual instead of global? | The task view could be contextual to the current user. Show "My Tasks" on Dashboard. Show "All Tasks" in Operations. But both are needed somewhere. |

**Verdict:** MERGE. Single "Tasks" entry point. Filter toggle or tabs for scope.

**What would break:** Any URL or link pointing specifically to `/my-tasks` or `/task-management`. If the merge changes URLs, redirects needed.

---

## 2. My Credentials vs Shared Credentials

| Question | Answer |
|----------|--------|
| Same entity? | **YES** — both are `vault_entries` table records. Filter differs by ownership/sharing scope. |
| Can they merge? | **YES** — single "Vault" or "Credentials" entry. |
| Can they become tabs? | **YES** — "My" / "Shared" / "All Access" tabs. |
| Can they become filters? | **YES** — scope filter. |
| Can one become a child? | **YES** — "My" is a subset of "Shared" conceptually (you share what you own). |
| Can one disappear? | **YES** — but the scope toggle is critical. |
| Contextual? | Credentials could be contextual (show credentials related to the current service on its detail page). The global vault list is still needed for search. |

**Verdict:** MERGE. Single "Vault" or "Credentials" entry. Filter toggle.

---

## 3. Help Center vs Guide

| Question | Answer |
|----------|--------|
| Same entity? | **APPEARS TO BE** — both serve documentation/user guides. If they are separate content sets, they should still share a single entry point with a topic filter. |
| Can they merge? | **YES** — single "Help Center" or "Documentation" entry. |
| Can they become tabs? | "User Guide" and "Admin Guide" tabs within Help Center. |
| Can one disappear? | **YES** — eliminate "Guide" if it's a duplicate label. |

**Verdict:** MERGE. Single "Help Center." Tabs for content type if needed.

---

## 4. Roles vs Privileges

| Question | Answer |
|----------|--------|
| Same entity? | **NO** — Roles are named permission groupings. Privileges are individual permission atoms. Different granularity. |
| Different enough? | **BARELY.** Privileges are an implementation detail of the Roles & Permissions system. They should not be independently navigable. |
| Can they merge? | **YES** — into "Roles & Permissions." Privileges become a read-only reference tab within that page. |
| Can one become a tab? | Privileges as a read-only tab under "Roles & Permissions." |
| Can one become a child? | Privileges are a child concept of Roles. |
| Can one disappear? | Privileges from navigation: **YES.** The Permissions matrix is the primary interface. Privileges are a developer reference. |

**Verdict:** ELIMINATE Privileges as navigation. Merge into "Roles & Permissions" as a reference tab or remove entirely.

---

## 5. Privileges vs Permissions

| Question | Answer |
|----------|--------|
| Same entity? | **Almost.** Privileges = permission atoms (can_read, can_create). Permissions = the matrix assigning those atoms to roles per module. Different, but inextricably linked. |
| Different enough for separate items? | **NO.** They are two views of the same access control system: what actions exist (Privileges) and who can do them per module (Permissions). |
| Can they merge? | **YES** — single "Permissions" page that shows the matrix, with a reference panel for available privileges. |
| Can one become a tab? | Privileges as a reference tab within Permissions. |
| Can one disappear? | Both should disappear into "Roles & Permissions." |

**Verdict:** MERGE both into "Roles & Permissions."

---

## 6. Roles vs Permissions

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** Roles hold permissions. Permissions define what roles can do. |
| Separate enough? | **YES** — they are different tiers: list roles first, then assign permissions to a selected role. |
| Can they merge? | **YES** — single "Roles & Permissions" page with a role selector on the left and the permission matrix on the right. |
| Can one become a tab? | Permissions could be a tab for a selected role. |
| Can one become a child? | Permissions are children of Roles conceptually. |
| One disappear? | Neither disappears entirely, but they share one navigation slot. |

**Verdict:** MERGE into single "Roles & Permissions" entry. The current split into 4 items (Roles, Role Templates, Privileges, Permissions) is excessive for any user.

---

## 7. Role Templates vs Roles

| Question | Answer |
|----------|--------|
| Same entity? | **Partially.** Templates are pre-saved role configurations used to create new roles quickly. |
| Different enough? | **NO.** A template is just a role that hasn't been applied yet. |
| Can they merge? | **YES** — "Create from template" button on the Roles page. No separate navigation needed. |
| Can one become a tab? | "Templates" tab under Roles. |
| One disappear? | From navigation: **YES.** Make it a button/action within Roles. |

**Verdict:** MERGE into Roles. Button: "Create Role from Template."

---

## 8. Modules vs Features

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** Modules are the top-level application sections. Features are sub-groupings within modules. |
| Different enough? | **YES — but.** They are both configuration concepts. An admin configuring the system needs both. A daily user needs neither. |
| Can they merge? | **YES** — Features become an inline configuration section within the Module detail/edit page. |
| Can one become a tab? | Features tab under Module detail. |
| One disappear from nav? | Features: **YES.** Modules: **YES** (move to System Configuration). |

**Verdict:** MERGE Features into Modules. Move Modules into System Configuration as "Module Setup" or "Applications."

---

## 9. Activity Logs vs Login Audits

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** Activity Logs track CRUD operations. Login Audits track authentication events. Different tables. |
| Conceptually similar? | **YES** — both are append-only audit trails. One is "what users did," the other is "when users logged in." |
| Different enough? | **BARELY.** The user cares about "what happened in the system" (audit). Type is a filter: Changes vs Logins. |
| Can they merge? | **YES** — single "Audit Trail" page. Filter: Changes / Logins / All. |
| Can one become a tab? | Tabs: "Changes" | "Logins" under "Audit Trail." |
| Can they become filters? | Type filter on unified audit view. |
| One disappear? | From navigation: **YES.** Merge into one. |

**Verdict:** MERGE. Single "Audit Trail." Filter by type.

---

## 10. SMTP Profiles vs Webhooks

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** SMTP = outbound mail. Webhooks = outbound HTTP. |
| Conceptually similar? | **YES** — both are outbound communication configuration. Both set up once and rarely touched. |
| Different enough? | **YES** — mail settings and webhook integrations serve different purposes and have different configuration UIs. |
| Can they merge into one page? | **NO** — they're too different in purpose and UI. |
| Can they share a group? | **YES** — both belong in "System Configuration" as separate items within that group. But neither needs a standalone group. |
| Can one become a child? | No — they're siblings, not parent-child. |
| One disappear? | Neither from functionality. Both from standalone navigation: **YES** — move both into System Configuration. |

**Verdict:** KEEP SEPARATE but move both into "System Configuration" group. Don't merge the pages. But don't give them their own sidebar entries either — group them under a single "System Settings" or "Configuration" entry with sub-nav.

---

## 11. Import vs Attachments

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** Import = bulk data creation. Attachments = file storage associated with records. |
| Conceptually similar? | **NO.** One is a write operation. One is a read operation on metadata. |
| Different enough? | **YES** — they serve completely different purposes. |
| Can they merge? | **NO** — no conceptual overlap. |
| Can they share a group? | "Data Tools" or "Tools" group, but they're an odd pair. |
| One become a child? | No. |
| One disappear? | Attachments from nav: **YES** (make contextual). Import from nav: **YES** (move to Operations). Both disappear from the admin group specifically. |

**Verdict:** NO MERGE. Move Import to Operations. Eliminate Attachments as standalone nav (contextual only).

---

## 12. Webhooks vs API Access

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** Webhooks = outgoing event notifications. API Access = incoming authentication tokens. |
| Conceptually similar? | **YES** — both are integration/developer tools. Both are rarely touched. |
| Different enough? | **BARELY.** Both serve developers integrating external systems. One sends data out, one lets data in. |
| Can they merge? | **PARTIALLY** — they could be tabs in a single "Integrations & Developer Settings" page. The UIs are different (list of webhooks vs. list of tokens) but the audience is the same. |
| Can one become a tab? | **YES** — "Webhook Endpoints" tab, "API Tokens" tab under "Integrations." |
| One disappear? | From navigation: **YES** — merge into a single "Integrations" entry. |

**Verdict:** MERGE into "Integrations" or "Developer Settings." Tabs for Webhooks and API Access.

---

## 13. Reports vs Dashboard

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** Dashboard = real-time operational state (today's numbers). Reports = historical analysis (trends over time). |
| Conceptually similar? | **YES** — both show aggregated data. Both provide situational awareness. |
| Different enough? | **YES** — Dashboard answers "what's happening NOW?" Reports answer "what happened over the LAST MONTH?" Different decision contexts. |
| Can they merge? | **NO** — merging would create a single page that tries to be both operational and analytical, failing at both. |
| Can one become a tab? | Reports could have a "Dashboard" summary tab. But the Dashboard is the landing page — it shouldn't be buried in Reports. |
| One disappear? | Neither. Both are essential. |

**Verdict:** KEEP SEPARATE. Dashboard is the operational landing page. Reports is the analytical layer.

---

## 14. Renewals vs Calendar

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** Renewals = list of items needing renewal action. Calendar = date visualization of events. |
| Conceptually similar? | **YES** — both deal with time-based events. Calendar often shows renewal dates. |
| Can they merge? | **YES** — Calendar is a VIEW of renewal and task data. It could be a tab or toggle within Renewals (and Tasks). |
| Can Calendar become a view mode? | **YES** — "Calendar view" toggle on the Renewals page. List view → Calendar view. |
| One disappear? | Calendar from standalone navigation: **YES** — make it a display mode toggle on Renewals and Tasks. |

**Verdict:** CALENDAR MERGES INTO RENEWALS AND TASKS. Calendar view toggle. Renewals stays as standalone (for cross-cutting view) or merges into each service type.

---

## 15. Users vs My Profile

| Question | Answer |
|----------|--------|
| Same entity? | **YES** — both reference the `users` table. |
| Different enough? | **YES** — Users is admin management of all accounts. My Profile is self-service on your own account. |
| Can they merge? | **NO** — conceptually wrong. A user should never see the global user list from their profile page. |
| One disappear? | Neither. But My Profile moves to top-right user menu. Users stays in Access Control. |

**Verdict:** KEEP SEPARATE. Different personas, different access levels.

---

## 16. My Profile vs My Access

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** Profile = account settings. Access = permission visibility. |
| Conceptually similar? | **YES** — both are personal account-related read/write information. |
| Can they merge? | **YES** — My Access as a tab within My Profile. |
| One disappear? | From sidebar: **YES** — merge into Profile. |

**Verdict:** MERGE. My Access becomes a tab within Profile.

---

## 17. Modules vs Permissions

| Question | Answer |
|----------|--------|
| Same entity? | **NO.** Modules = application sections. Permissions = access rules. |
| Connected? | **YES** — permissions are assigned PER MODULE. Changing a module affects its permission structure. |
| Can they merge? | **NO** — they are cause and effect, but managing sections is different from managing access rules. |
| One become a child? | Permissions reference modules. But the permission matrix page is complex enough to stand alone. |

**Verdict:** KEEP SEPARATE within System Configuration / Access Control respectively.

---

## CONSOLIDATION SUMMARY

| Merge Group | Items Affected | Net Removal | New Item |
|-------------|---------------|-------------|----------|
| Tasks | My Tasks + Task Management | -2 | Tasks |
| Vault | My Credentials + Shared Credentials | -2 | Vault |
| Roles & Permissions | Roles + Role Templates + Privileges + Permissions | -4 | Roles & Permissions |
| Audit Trail | Activity Logs + Login Audits | -2 | Audit Trail |
| Integrations | Webhooks + API Access | -2 | Integrations |
| Module Setup | Modules + Features | -2 | Module Setup |
| Help Center | Help Center + Guide | -1 | Help Center |
| System Configuration | SMTP Profiles | -1 (moved) | Mail Settings |
| Profile + Access | My Profile + My Access | -1 (moved to header) | Profile |
| Services | Domain Emails → Domains tab | -1 | — |
| Calendar | → view toggle | -1 | — |
| Attachments | → contextual | -1 | — |
| **Total** | **34 → 18 items** | **-16** | |

**Before:** 34 items
**After primary consolidation:** 18 items
**After moving secondary items to contextual only:** ~14-16 permanently visible items

The question is not IF these should merge, but whether the merged item deserves top-level navigation or should live within a group.

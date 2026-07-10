# 06 — TOP-LEVEL MENU JUSTIFICATION

> For every item that could be top-level, justify or reject.
> Goal: define the MINIMUM set of items that MUST be immediately accessible.

---

## The 5-Second Rule

If a user cannot navigate to a top-level item within 5 seconds of opening the sidebar, it fails daily usability.

**Question for every item:** Does this item need to be accessible within 5 seconds of every session start?

---

## Universal Items (visible to ALL personas)

### 1. Dashboard

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | It's the landing page. Every session starts here. Users need to navigate back to it constantly. |
| Frequency | Every session (multiple times per session). |
| Persona coverage | 8/8 personas. |
| Consequences of hiding | Users lose their home anchor. Session disorientation. |
| Alternative | Not top-level: dashboard as a tab on another page. This forces users to be on another page to access their home. Circular dependency. |
| **Verdict** | **YES — top-level, pinned, first position.** Universal anchor. |

### 2. Notifications

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Event-driven. Users need to know they have unread notifications without navigating to a page. |
| Frequency | Every session (multiple times). |
| Persona coverage | 8/8 personas. |
| Consequences of hiding | Users miss task assignments, renewal reminders, credential shares. |
| Alternative | Not sidebar — header bell icon (current). This is the correct pattern. |
| **Verdict** | **YES — header icon (not sidebar).** Header is always visible. Full notification list page accessible from icon. |

### 3. Account (Profile)

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | It's NOT used daily. It's used rarely (password change, profile update). |
| Frequency | Rarely (monthly or less). |
| Persona coverage | 8/8 personas. |
| Consequences of hiding | Users can't update their own profile. Workaround: add to header user dropdown. |
| Alternative | Not sidebar — top-right user dropdown. Standard pattern across all enterprise apps. |
| **Verdict** | **YES — header user dropdown (not sidebar).** Profile + My Access as tabs within. |

### 4. Help Center

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | It's NOT used daily. It's used when stuck. Users need it findable but not prominent. |
| Frequency | Rarely (when stuck). |
| Persona coverage | 8/8 personas. |
| Consequences of hiding | Users can't find help → frustration → support tickets. |
| Alternative | Not sidebar — "?" icon in header (constant but unobtrusive). |
| **Verdict** | **YES — header "?" icon (not sidebar).** Full help page accessible from icon. Merge Guide into Help Center. |

---

## Services Items (visible to IT Ops, Service Desk, IT Manager)

### 5. Domains (with Mailboxes tab)

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | IT Ops access domains daily. Creating/checking/renewing domains is a core workflow. |
| Frequency | Daily (IT Ops), Weekly (Service Desk). |
| Persona coverage | 3/8 personas (IT Ops, Service Desk, IT Manager). |
| Consequences of hiding | IT Ops loses primary workflow. Must use search to find domains. |
| Alternative | 2nd-level under "Services." Adds one click per access. For daily use: unacceptable friction. |
| **Verdict** | **YES — top-level under "Services" group.** Not individual top-level (no "Domain" as independent sidebar slot). |

### 6. Hosting → Web Hosting

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Same as Domains. Core service type. Daily use for IT Ops. |
| Frequency | Daily (IT Ops), Weekly (Service Desk). |
| Persona coverage | 3/8 personas. |
| Consequences of hiding | IT Ops loses hosting management workflow. |
| **Verdict** | **YES — top-level under "Services" group.** |

### 7. VPS → Servers

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Same pattern. Server management is daily for IT Ops. |
| Frequency | Daily (IT Ops). |
| **Verdict** | **YES — top-level under "Services" group.** |

### 8. VoIP → Phone Systems

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Weekly use. Less frequent than Hosting/Domains but still a primary service type. |
| Frequency | Weekly (IT Ops). |
| **Verdict** | **YES — top-level under "Services" group.** Consider contextual hide if organization has no VoIP. |

### 9. Other Services → SaaS Subscriptions

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Increasingly important as organizations adopt more SaaS. Only "Other" label is the problem. |
| Frequency | Weekly (IT Ops), Monthly (Procurement). |
| Consequences of NOT renaming | "Other" communicates "we don't know what this is." Undermines user confidence. |
| **Verdict** | **YES — top-level under "Services" group. BUT MUST RENAME.** |

### Summary: Services Group

**Decision:** All 5 service types are top-level items under a "Services" group header. They are used too frequently to bury under a second-level flyout. The group header provides categorization without adding click friction.

---

## Vendors Items (visible to IT Ops, Procurement, IT Manager)

### 10. Service Providers → Vendors

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Vendors are a primary business entity. Procurement manages them weekly. IT Ops references them daily. |
| Frequency | Weekly (Procurement), Daily (IT Ops referencing). |
| Persona coverage | 3/8 personas (IT Ops, Procurement, IT Manager). |
| Consequences of hiding | Vendors would need to be accessed through each service. No vendor-centric view. |
| **Verdict** | **YES — top-level under "Vendors" group.** |

### 11. Renewals → Contracts & Renewals

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Renewals is a cross-cutting workflow. Procurement needs a single view of all upcoming renewals regardless of service type. |
| Frequency | Daily (IT Ops checking), Weekly (Procurement processing). |
| Persona coverage | 4/8 personas (IT Ops, Procurement, IT Manager, IT Director). |
| Alternative | Inline on each service. Procurement loses consolidation. |
| **Verdict** | **DEBATABLE.** Two valid models documented elsewhere. Tentative: YES under "Vendors" for Procurement. Inline renewal date on each service for IT Ops. |

---

## Assets Items (visible to IT Ops, IT Manager, Procurement, Service Desk)

### 12. Assets → Hardware & Software

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Asset lifecycle is distinct from services. Assets are owned, assigned, returned, retired — fundamentally different workflow. |
| Frequency | Weekly (assigning to new hires), Monthly (audit). |
| Persona coverage | 4/8 personas. |
| Consequences of hiding | Assets must be managed through a sub-menu. Asset assignment becomes two clicks instead of one. |
| **Verdict** | **YES — standalone group.** NOT under Services. NOT under Vendors. Own group. |

---

## Vault (visible to ALL)

### 13. Credentials → Vault

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | #1 reason End Users log in. #2 reason Service Desk uses the system (after Tasks). Universal. |
| Frequency | Daily (everyone). Multiple times daily for Service Desk. |
| Persona coverage | 8/8 personas. |
| Consequences of hiding | End Users cannot access their passwords. System loses its primary value proposition. |
| Alternative | Under "Security" or "Access" group. Adds categorization but ALL users need credentials. Extra categorization is overhead. |
| **Verdict** | **YES — top-level. Own group.** Vault is too universal to bury in a group with less-frequent items. |

---

## Tasks (visible to ALL)

### 14. Tasks

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Tasks are the universal work item. Every persona creates, completes, or tracks tasks. |
| Frequency | Daily (everyone). |
| Persona coverage | 8/8 personas. |
| Consequences of hiding | Primary workflow mechanism becomes buried. Users lose their daily action list. |
| **Verdict** | **YES — top-level. Own group.** Same logic as Vault. Universal + daily = top-level. |

---

## Access Control Items (visible to Super Admin, Security Officer)

### 15. Users

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Primary identity management. Super Admin manages users weekly. |
| Frequency | Weekly (Super Admin). |
| Persona coverage | 2/8 personas (Super Admin, Security Officer). |
| Consequences of hiding | Identity management becomes two clicks away. User onboarding/offboarding friction. |
| **Verdict** | **YES — top-level under "Access Control" group.** Visible only to Super Admin and Security Officer. |

### 16. Roles & Permissions (merged from 4 items)

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Access control configuration. Weekly for Super Admin managing new roles or permission changes. |
| Frequency | Weekly-Monthly (Super Admin). |
| Persona coverage | 1/8 personas (Super Admin). Security Officer reads only. |
| Consequences of hiding | Permission management becomes two clicks away. Defensible — permissions are not daily. |
| Alternative | Under "System Configuration." Acceptable. |
| **Verdict** | **YES — top-level under "Access Control" group.** Kept at same level as Users because they share an access control workflow. |

---

## System Configuration Items (visible to Super Admin only)

### 17. Module Setup (merged from Modules + Features)

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Used once during setup, almost never again. |
| Frequency | Rarely (once at setup). |
| Consequences of hiding | Negligible. Super Admin configures modules once. |
| **Verdict** | **NO — under "System Configuration" group.** Not top-level. Bury it. |

### 18. Mail Settings (moved from SMTP Profiles)

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Configured once during deployment. Changed only if mail provider changes (years apart). |
| Frequency | Rarely (once at deploy). |
| Consequences of hiding | None. Hidden until needed. |
| **Verdict** | **NO — under "System Configuration" group.** Not top-level. |

### 19. Integrations (merged from Webhooks + API Access)

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Set up once per integration. Changed rarely. |
| Frequency | Rarely (once per integration). |
| Consequences of hiding | None. Hidden until needed. |
| **Verdict** | **NO — under "System Configuration" or separate "Integrations" group.** Not top-level. |

### 20. Import → Data Import

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Used during migration. Then occasional bulk updates. |
| Frequency | Rarely (one-time migration + quarterly updates). |
| Consequences of hiding | Low. Import is a tool, not a data domain. |
| **Verdict** | **NO — under "Operations" group.** Not top-level. |

---

## Audit Items (visible to Security Officer, Super Admin, IT Manager)

### 21. Audit Trail (merged from Activity Logs + Login Audits)

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Security Officer checks daily for anomalies. Change history is a core oversight function. |
| Frequency | Daily (Security Officer), Weekly (IT Manager). |
| Persona coverage | 3/8 personas (Security Officer, Super Admin, IT Manager). |
| Consequences of hiding | Security oversight becomes two clicks. Daily Security workflow suffers. |
| Alternative | Under "Access Control." Security Officer now clicks Access Control → Audit instead of directly. Adds one click to daily task. |
| **Verdict** | **DEBATABLE.** Daily enough for Security Officer to justify top-level. But only 3/8 personas need it. If persona-filtering is active: **YES — top-level for Security Officer, hidden for others.** If single nav for all: **NO — under Access Control or own group.** |

---

## Reports (visible to Manager, Director, Security, Procurement)

### 22. Reports

| Criterion | Justification |
|-----------|---------------|
| Why top-level? | Reports are the primary analytical tool for decision-makers. Used weekly for managerial review. |
| Frequency | Weekly (managers), Monthly (directors). |
| Persona coverage | 4/8 personas (Manager, Director, Security Officer, Procurement). |
| Consequences of hiding | Report access requires scrolling past Services, Vendors, Assets, Vault, Tasks. Friction for decision-makers. |
| **Verdict** | **YES — separate group.** Reports are conceptually distinct from operations and administration. |

---

## Items REJECTED as Top-Level

| Item | Reason |
|------|--------|
| Domain Emails | Child of Domains. Not a primary navigation target. |
| Role Templates | Child of Roles & Permissions. Configured once. |
| Privileges | Internal architecture. Not user-facing. |
| Modules | System configuration. Configured once. |
| Features | Sub-child of Modules. Not user-facing. |
| Permissions | Child of Roles & Permissions. |
| My Access | Child of Profile. Read-only. Accessed rarely. |
| Guide | Duplicate of Help Center. |
| Calendar | View mode, not data domain. |
| Attachments | Contextual only. No standalone navigation. |

---

## FINAL TOP-LEVEL INVENTORY

After merge consolidation and top-level justification:

| # | Item | Group | Visible To | Frequency |
|---|------|-------|-----------|-----------|
| 1 | Dashboard | — (top) | ALL | Daily |
| 2 | Notifications | Header icon | ALL | Daily |
| 3 | My Profile | Header menu | ALL | Rarely |
| 4 | Help Center | Header "?" | ALL | Rarely |
| 5 | Vault | — | ALL | Daily |
| 6 | Tasks | — | ALL | Daily |
| 7 | Services (header) | Services | IT Ops, Service Desk, IT Manager | — |
| 7a | Domains | Services | IT Ops, Service Desk, IT Manager | Daily |
| 7b | Web Hosting | Services | IT Ops, Service Desk | Daily |
| 7c | Servers | Services | IT Ops | Daily |
| 7d | Phone Systems | Services | IT Ops | Weekly |
| 7e | SaaS Subscriptions | Services | IT Ops, Procurement | Weekly |
| 8 | Vendors (header) | Vendors | IT Ops, Procurement, IT Manager | — |
| 8a | Providers | Vendors | IT Ops, Procurement | Weekly |
| 8b | Renewals | Vendors | IT Ops, Procurement, IT Manager | Weekly |
| 9 | Hardware & Software | Assets | IT Ops, IT Manager, Procurement | Weekly |
| 10 | Users | Access Control | Super Admin, Security Officer | Weekly |
| 11 | Roles & Permissions | Access Control | Super Admin | Monthly |
| 12 | Module Setup | System Config | Super Admin | Rarely |
| 13 | Mail Settings | System Config | Super Admin | Rarely |
| 14 | Integrations | Integrations | Super Admin | Rarely |
| 15 | Audit Trail | Audit | Security Officer, IT Manager, Super Admin | Daily-Wkly |
| 16 | Data Import | Operations | Super Admin | Rarely |
| 17 | Reports | Reports | Manager, Director, Security, Procurement | Weekly |

**Total: 17 visible items + 3 header icons = 20 navigation targets.**
**Down from 34. Reduction: 41%.**

**Before persona filtering:** 20 items (vs. 34 current).
**After persona filtering:** 5-18 items per user (vs. 3-34 current).

**The question is not what the top-level items are. The question is which ITEMS are visible to which PERSONA.** A single sidebar of 17 items still forces 75% irrelevance on most users. The power is in persona-filtering (Model E).

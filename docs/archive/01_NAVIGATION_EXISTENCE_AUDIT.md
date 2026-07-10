# 01 — NAVIGATION EXISTENCE AUDIT

> Every menu item challenged against 16 criteria.
> Think: if this item were proposed today for a greenfield product, would it survive?

---

## DASHBOARD

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Landing page. Aggregate health of all monitored services, tasks, renewals in one view. |
| 2 | Who actually uses it? | EVERY persona — first thing every user sees on every session. |
| 3 | How often? | **Daily** — every login, every session start. |
| 4 | Can it live inside another module? | No — it IS the aggregation of all modules. Putting it inside one module subordinates all others. |
| 5 | Can it become a tab? | No — it's the landing page, not a subordinate view. |
| 6 | Can it become a filter? | No — it's an aggregate view, not a dataset refinement. |
| 7 | Can it become a modal? | No — it's the primary navigation anchor. |
| 8 | Can it become a settings page? | No — it's not configuration. |
| 9 | Can it disappear completely? | NO — removing the dashboard forces users to guess where to start every session. |
| 10 | Does it deserve top-level? | **YES** — universal, session-anchoring, primary landing page. Non-negotiable. |
| 11 | Is it a DB table? | No — it queries 8+ tables. |
| 12 | Business capability? | **Yes** — "Operational Monitoring & Situational Awareness." |
| 13 | Workflow? | **Yes** — "Daily check-in & health assessment" workflow. |
| 14 | Configuration? | No. |
| 15 | Administration? | No. |
| 16 | Should end users know? | **Yes** — it's their landing page too. |

**Verdict:** KEEP. Top-level. Universal. Non-negotiable.

---

## NOTIFICATIONS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Alert users to events requiring attention: task assignments, renewals, credential shares, monitor failures. |
| 2 | Who actually uses it? | EVERYONE — triggered by system events, consumed by all personas. |
| 3 | How often? | **Daily** — multiple times per session for active users. |
| 4 | Inside another module? | No — notifications span all modules. Could be in a global top bar. |
| 5 | Become a tab? | Could be a tab in a "My Activity" concept, but that's an anti-pattern (forcing users to navigate away to check). |
| 6 | Become a filter? | The notification LIST is already filtered (unread/read/all). The context is the notification bell. |
| 7 | Become a modal? | **Already is** — the bell icon opens a dropdown. The full list page is secondary. |
| 8 | Become settings? | Notification preferences are settings (which events trigger which channels). The feed itself is not. |
| 9 | Disappear? | **NO** — removing notifications removes the only push-like awareness mechanism. |
| 10 | Top-level? | **YES** — as a bell icon in the global header, not as a sidebar item. |
| 11 | DB table? | Partially — `notifications` table, but the concept is cross-cutting. |
| 12 | Business capability? | **Yes** — "Event-Driven Awareness & Alerting." |
| 13 | Workflow? | **Yes** — "Respond to alert" workflow. |
| 14 | Configuration? | Notification preferences page (separate). |
| 15 | Administration? | Notification channel configuration (separate). |
| 16 | End users? | **Yes** — critically important for all users. |

**Verdict:** KEEP. Header bell icon (not sidebar). Full list as a secondary page accessible from the bell. Merge notification preferences into Account settings.

---

## SERVICE PROVIDERS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Track companies that provide IT services (vendors, suppliers, contractors). |
| 2 | Who uses it? | IT Ops (weekly), Procurement (weekly), IT Manager (monthly). |
| 3 | How often? | **Weekly** (onboarding new provider) to **Monthly** (provider review). |
| 4 | Inside another module? | Could be a reference field on each service (Hosting.provider_id). But the provider LIST needs its own management page. |
| 5 | Become a tab? | Tab under a "Vendors & Suppliers" group. |
| 6 | Become a filter? | Yes — "show me all services from Provider X" is a filter on each service index. |
| 7 | Become a modal? | Create/Edit as modal. List needs a full page. |
| 8 | Become settings? | No. |
| 9 | Disappear? | **NO** — vendors are a tracked entity. But the LABEL "Service Providers" could become "Vendors" or "Suppliers." |
| 10 | Top-level? | **DEBATABLE.** Important enough for Procurement. Overhead for IT Ops who see it as a secondary reference. |
| 11 | DB table? | **Yes** — `service_providers` table directly mapped. |
| 12 | Business capability? | **Yes** — "Vendor Relationship Management." |
| 13 | Workflow? | **Yes** — "Onboard Vendor" + "Vendor Review" + "Contract Renewal." |
| 14 | Configuration? | No. |
| 15 | Administration? | No — operational vendor management. |
| 16 | End users? | **No** — Procurement, IT Ops, IT Manager only. |

**Verdict:** KEEP as a managed entity. Move out of "Infrastructure" into a dedicated "Vendors" group. Consider renaming to "Vendors."

---

## HOSTING

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Track web hosting accounts: provider, plan, cost, status, credentials. |
| 2 | Who uses it? | IT Ops (daily), Service Desk (weekly). |
| 3 | How often? | **Daily** for IT Ops managing hosting. **Weekly** for Service Desk troubleshooting. |
| 4 | Inside another module? | No — it's a primary service type. |
| 5 | Become a tab? | Tab under a "Services" group. Or could be implicit (user selects "Services" then chooses type). |
| 6 | Become a filter? | No — hosting is an entity type, not a filter dimension. |
| 7 | Become a modal? | Create/Edit as modal from a unified "New Service" entry point. |
| 8 | Become settings? | No. |
| 9 | Disappear? | **NO** — core service type for any IT operations platform. |
| 10 | Top-level? | **DEBATABLE.** It's a service type. The question is whether services are grouped or exposed individually. |
| 11 | DB table? | **Yes** — `hostings` table directly mapped. |
| 12 | Business capability? | **Yes** — "Web Hosting Management." |
| 13 | Workflow? | **Yes** — "Provision Hosting" + "Manage Hosting" + "Troubleshoot Hosting" + "Renew Hosting." |
| 14 | Configuration? | No. |
| 15 | Administration? | No. |
| 16 | End users? | **No** — IT staff only. |

**Verdict:** KEEP as a service type. Group under "Services" with other service types. Individual top-level only if the organization has ONLY hosting and nothing else.

---

## DOMAINS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Track registered domain names: registrar, expiry, DNS, cost. |
| 2 | Who uses it? | IT Ops (daily), Service Desk (weekly). |
| 3 | How often? | **Daily** for IT Ops. **Weekly** for Service Desk (finding domain info for tickets). |
| 4 | Inside another module? | No — it's a primary service type. |
| 5 | Become a tab? | Tab under "Services" group. |
| 6 | Become a filter? | No. |
| 7 | Create as modal? | Yes — from a unified "New Service" process. |
| 8 | Become settings? | No. |
| 9 | Disappear? | **NO** — domain management is a core IT function. |
| 10 | Top-level? | Same answer as Hosting — service type, group or individual. |
| 11 | DB table? | **Yes** — `domains` table. |
| 12 | Business capability? | **Yes** — "Domain Name Management." |
| 13 | Workflow? | **Yes** — "Register Domain" + "Renew Domain" + "Configure DNS" + "Transfer Domain." |
| 14 | Configuration? | No. |
| 15 | Administration? | No. |
| 16 | End users? | **No**. |

**Verdict:** KEEP as a service type. Group with other services.

---

## DOMAIN EMAILS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Track email mailboxes associated with domains. |
| 2 | Who uses it? | IT Ops (weekly), Service Desk (weekly for password resets). |
| 3 | How often? | **Weekly** — mailbox creation, password reset, quota changes. |
| 4 | Inside another module? | **YES** — Domain Emails are a sub-entity of Domains. You never have a domain email without a domain. |
| 5 | Become a tab? | **YES** — tab on the Domain detail page ("Mailboxes" tab). |
| 6 | Become a filter? | Could be a relationship list on Domain detail. |
| 7 | Become a modal? | Create/Edit as modal FROM the Domain detail page. |
| 8 | Become settings? | No. |
| 9 | Disappear as standalone? | **YES** — absolutely no reason for this to be a top-level navigation item. It's a child relationship. |
| 10 | Top-level? | **NO.** Strong no. |
| 11 | DB table? | **Yes** — `domain_emails` table, but that's implementation detail, not UX justification. |
| 12 | Business capability? | **No** — it's a sub-capability of Domain Management. |
| 13 | Workflow? | **No** — it's a step in the "Register Domain" workflow ("now create mailboxes"). |
| 14 | Configuration? | No. |
| 15 | Administration? | No. |
| 16 | End users? | **No**. |

**Verdict:** ELIMINATE AS TOP-LEVEL. Move to a contextual tab on Domain detail pages. Rename to "Mailboxes."

---

## VPS ACCOUNTS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Track virtual private server instances. |
| 2 | Who uses it? | IT Ops (daily). |
| 3 | How often? | **Daily** — provisioning, monitoring, troubleshooting. |
| 4 | Inside another module? | No — primary service type. |
| 5 | Become a tab? | Tab under "Services" group. |
| 6 | Become a filter? | No. |
| 7 | Create as modal? | Yes. |
| 8 | Become settings? | No. |
| 9 | Disappear? | **NO** — core service type. |
| 10 | Top-level? | Same as Hosting/Domains — group or individual. |
| 11 | DB table? | **Yes**. |
| 12 | Business capability? | **Yes** — "Server Management." |
| 13 | Workflow? | **Yes** — "Provision Server" + "Monitor Server" + "Troubleshoot Server." |
| 14 | Configuration? | No. |
| 15 | Administration? | No. |
| 16 | End users? | **No**. |

**Verdict:** KEEP as service type. Rename to "Servers" for clarity. Group under Services.

---

## VOIP

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Track Voice-over-IP phone system accounts and extensions. |
| 2 | Who uses it? | IT Ops (weekly). |
| 3 | How often? | **Weekly** — extension changes, troubleshooting call quality. |
| 4 | Inside another module? | No — primary service type. |
| 5 | Become a tab? | Tab under "Services" group. |
| 6 | Become a filter? | No. |
| 7 | Create as modal? | Yes. |
| 8 | Become settings? | No. |
| 9 | Disappear? | **NO** — but only if the organization HAS VoIP. A small org may not. |
| 10 | Top-level? | Same debate. |
| 11 | DB table? | **Yes**. |
| 12 | Business capability? | **Yes** — "Telephony Management." |
| 13 | Workflow? | **Yes** — "Provision Phone System" + "Manage Extensions." |
| 14 | Configuration? | No. |
| 15 | Administration? | No. |
| 16 | End users? | **No**. |

**Verdict:** KEEP as service type. Rename to "Phone Systems." Group under Services. Conditionally hide if no VoIP data exists.

---

## OTHER SERVICES

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Catch-all for services that don't fit Hosting/Domains/VPS/VoIP. The label admits taxonomic failure. |
| 2 | Who uses it? | IT Ops (weekly), Procurement (monthly). |
| 3 | How often? | **Weekly** — adding SaaS subscriptions, tracking costs. |
| 4 | Inside another module? | It IS the "other" module. The question is whether the taxonomy needs fixing. |
| 5 | Become a tab? | Tab under "Services" — but still needs a proper name. |
| 6 | Become a filter? | No. |
| 7 | Create as modal? | Yes. |
| 8 | Become settings? | No. |
| 9 | Disappear? | As a LABEL? **YES** — rename to "SaaS Subscriptions" or "Software Services." As a concept? **NO** — the records must go somewhere. |
| 10 | Top-level? | **NO** — especially not with a label that means "I don't know what this is." |
| 11 | DB table? | **Yes** — `other_services`. The table name itself signals the problem. |
| 12 | Business capability? | **No** — "Other" is never a business capability. The capability is "SaaS Subscription Management" or similar. |
| 13 | Workflow? | **No** — the label reveals nothing about the workflow. |
| 14 | Configuration? | No. |
| 15 | Administration? | No. |
| 16 | End users? | **No**. |

**Verdict:** RENAME to "SaaS Subscriptions" or "Software Services." Group under Services. The label MUST describe what goes there.

---

## RENEWALS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Track expiry and renewal dates across ALL service types. Cross-cutting concern. |
| 2 | Who uses it? | IT Ops (daily for expiry checks), Procurement (weekly), IT Manager (weekly). |
| 3 | How often? | **Daily** — IT Ops checks what's expiring today/tomorrow. **Weekly** — Procurement processes renewals. |
| 4 | Inside another module? | **YES** — renewals could be inline on each service type. But the cross-cutting view is valuable. |
| 5 | Become a tab? | Tab on each service type detail. But also deserves a cross-cutting timeline view. |
| 6 | Become a filter? | "Show expiring in 30 days" is a filter on each service index. The consolidated view is more than a filter. |
| 7 | Become a modal? | No — needs a list view with sorting, filtering, batch actions. |
| 8 | Become settings? | Reminder timing preferences are settings. The renewal list is not. |
| 9 | Disappear? | As standalone? **DEBATABLE.** If every service type shows "Next renewal date" inline and has a "due for renewal" filter, the standalone renewals page could disappear into a Dashboard widget. |
| 10 | Top-level? | **FOR PROCUREMENT? YES.** For IT Ops? Could be inline. |
| 11 | DB table? | **Yes** — `expiry_trackers` table. But note: it's a cross-cutting table, not a service type. |
| 12 | Business capability? | **Yes** — "Renewal & Expiry Management." |
| 13 | Workflow? | **Yes** — "Review Expiring Services" + "Process Renewal" + "Forecast Renewal Costs." |
| 14 | Configuration? | No — but reminder settings are. |
| 15 | Administration? | No. |
| 16 | End users? | **No**. |

**Verdict:** NEEDS DISCUSSION. Two valid models:
1. **Cross-cutting Renewals page** (visible to Procurement, IT Manager) with renewals inline on each service for IT Ops
2. **No standalone Renewals** — every service shows its own renewals inline, dashboard shows aggregate expiry timeline

Depends on whether the organization has a dedicated Procurement role or IT Ops handles renewals.

---

## ASSETS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Track IT hardware (laptops, monitors, phones) and software licenses. |
| 2 | Who uses it? | IT Ops (weekly for assignment), Procurement (monthly for audit), IT Manager (quarterly). |
| 3 | How often? | **Weekly** — assigning equipment to new hires, tracking returns. |
| 4 | Inside another module? | No — assets have a fundamentally different lifecycle from services. Services are consumed; assets are owned, assigned, returned, retired. |
| 5 | Become a tab? | Tab under a dedicated group. |
| 6 | Become a filter? | No — it's an entity type. |
| 7 | Create as modal? | Yes. |
| 8 | Become settings? | No. |
| 9 | Disappear? | **NO** — asset management is a distinct IT function. |
| 10 | Top-level? | **DEBATABLE.** Services and Assets are conceptually different. Mixing them creates confusion. A separate group adds clarity. |
| 11 | DB table? | **Yes** — but this IS a legitimate business entity, not just a table. |
| 12 | Business capability? | **Yes** — "IT Asset Management." |
| 13 | Workflow? | **Yes** — "Acquire Asset" + "Assign to User" + "Track Maintenance" + "Retire Asset." |
| 14 | Configuration? | No. |
| 15 | Administration? | No — operational asset management. |
| 16 | End users? | **Partially** — they should see THEIR assigned assets (on their profile). They don't need the full list. |

**Verdict:** KEEP as a standalone entity. Do NOT group with services. Assets and services have different lifecycles, different users, different workflows. Group under "Assets & Inventory."

---

## MY CREDENTIALS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Personal password vault — credentials created by or assigned to the current user. |
| 2 | Who uses it? | EVERYONE — end users see their own passwords; IT staff see their personal credentials + shared ones. |
| 3 | How often? | **Daily** — for end users accessing work passwords. **Multiple times daily** for Service Desk. |
| 4 | Inside another module? | No — credentials are cross-cutting. |
| 5 | Become a tab? | Tab under a unified "Vault" or "Credentials" entry. |
| 6 | Become a filter? | **YES** — "My credentials" is a filter on the full credential list. |
| 7 | Become a modal? | Reveal password as modal. List needs a page. |
| 8 | Become settings? | Password policy settings are separate. |
| 9 | Disappear as standalone? | **YES** — merge with Shared Credentials into a single entry with a My/Shared filter. |
| 10 | Top-level? | **YES** — credentials are the #1 reason end users log in. |
| 11 | DB table? | **Yes** — `vault_entries` table, filtered by `created_by` or `assigned_to`. |
| 12 | Business capability? | **Yes** — "Credential Management" or "Password Vault." |
| 13 | Workflow? | **Yes** — "Store Credential" + "Retrieve Credential" + "Rotate Credential" + "Share Credential." |
| 14 | Configuration? | Password policy (separate). |
| 15 | Administration? | No. |
| 16 | End users? | **YES** — this is the primary reason they use the system. |

**Verdict:** MERGE with Shared Credentials. Single "Vault" entry. My/Shared filter toggle. Top-level.

---

## SHARED CREDENTIALS

**Identical analysis to My Credentials. The only difference is a query filter (`created_by` vs. `shared_with`).**

**Verdict:** MERGE INTO VAULT. Remove standalone entry. Filter toggle.

---

## MY TASKS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Personal task list — tasks assigned to the current user. |
| 2 | Who uses it? | EVERYONE — tasks are universal. |
| 3 | How often? | **Daily** — multiple times per day for task-based roles. |
| 4 | Inside another module? | No — tasks are cross-cutting. |
| 5 | Become a tab? | Tab under a unified "Tasks" entry. |
| 6 | Become a filter? | **YES** — "My tasks" is a filter on the full task list. |
| 7 | Become a modal? | Create task as modal from anywhere. |
| 8 | Become settings? | No. |
| 9 | Disappear as standalone? | **YES** — merge with Task Management. |
| 10 | Top-level? | **YES** — tasks are universal. |
| 11 | DB table? | **Yes** — `tasks` table filtered by assignee. |
| 12 | Business capability? | **Yes** — "Task Management & Workflow." |
| 13 | Workflow? | **Yes** — "Create Task" + "Assign Task" + "Complete Task" + "Review Tasks." |
| 14 | Configuration? | Task status workflows (separate). |
| 15 | Administration? | No. |
| 16 | End users? | **YES**. |

**Verdict:** MERGE with Task Management. Single "Tasks" entry. My/All/By Team filter. Top-level.

---

## TASK MANAGEMENT

**Identical analysis to My Tasks. The only difference is the scope of the query (all tasks vs. my tasks).**

**Verdict:** MERGE INTO TASKS. Remove standalone entry. Filter toggle.

---

## CALENDAR

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Date-based view of renewals and task deadlines. |
| 2 | Who uses it? | IT Manager (weekly planning), IT Ops (checking deadlines). |
| 3 | How often? | **Weekly** — planning sessions. **Daily** — quick check. |
| 4 | Inside another module? | **YES** — Calendar is a VIEW. It aggregates data from Tasks and Renewals. It's not an entity itself. |
| 5 | Become a tab? | Tab on Renewals or Tasks page ("Calendar view"). |
| 6 | Become a filter? | "View as calendar" is a display mode toggle on any list page. |
| 7 | Become a modal? | No — it needs screen real estate. |
| 8 | Become settings? | Display preferences (what to show, default view) are settings. |
| 9 | Disappear as standalone? | **YES** — if every list page has a "Calendar" toggle, the standalone page adds no value. |
| 10 | Top-level? | **NO** — it's a display mode, not a data domain. |
| 11 | DB table? | No — it queries `tasks` and `expiry_trackers` tables. |
| 12 | Business capability? | **No** — it's a visualization of other capabilities. |
| 13 | Workflow? | **Yes** — "Plan Renewals" + "Schedule Tasks" — but these workflows live in their respective modules. |
| 14 | Configuration? | Display preferences. |
| 15 | Administration? | No. |
| 16 | End users? | Only if they use visual planning. For most end users, "Tasks" is sufficient. |

**Verdict:** ELIMINATE AS STANDALONE. Replace with a "Calendar view" toggle on Tasks and Renewals pages. Dashboard widget shows upcoming events. This is the strongest candidate for elimination.

---

## USERS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Create, suspend, delete system user accounts. |
| 2 | Who uses it? | Super Admin (weekly), Security Officer (weekly). |
| 3 | How often? | **Weekly** — onboarding/offboarding. |
| 4 | Inside another module? | No — it's the identity management root. |
| 5 | Become a tab? | Tab under "Access Control." |
| 6 | Become a filter? | No. |
| 7 | Create as modal? | Yes — user creation as modal or dedicated page. |
| 8 | Become settings? | No. |
| 9 | Disappear? | **NO** — identity management is fundamental. |
| 10 | Top-level? | **FOR SECURITY OFFICER/SUPER ADMIN? YES.** For everyone else? **NO — hidden.** |
| 11 | DB table? | **Yes** — but this is a legitimate administrative entity. |
| 12 | Business capability? | **Yes** — "Identity & Account Management." |
| 13 | Workflow? | **Yes** — "Onboard User" + "Offboard User" + "Suspend User." |
| 14 | Configuration? | No — it's operational identity management. |
| 15 | Administration? | **YES**. |
| 16 | End users? | **No** — they manage their own profile. |

**Verdict:** KEEP. Group under "Access Control." Visible only to Super Admin and Security Officer.

---

## ROLES

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Define named sets of permissions (e.g., "Admin", "Editor", "Support"). |
| 2 | Who uses it? | Super Admin only. |
| 3 | How often? | **Monthly** — creating new roles. **Rarely** — modifying existing roles. |
| 4 | Inside another module? | **YES** — merge with Permissions, Privileges, Role Templates into "Roles & Permissions." |
| 5 | Become a tab? | Tab under "Roles & Permissions." |
| 6 | Become a filter? | No. |
| 7 | Create as modal? | Yes. |
| 8 | Become settings? | **YES** — this IS configuration of access control. |
| 9 | Disappear as standalone? | **YES** — this should never be a standalone navigation item. It's a tab within access control configuration. |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | **Yes** — `roles` table. |
| 12 | Business capability? | **Yes** — "Role-Based Access Control Configuration." |
| 13 | Workflow? | **Yes** — "Create Role" + "Assign Permissions to Role." |
| 14 | Configuration? | **YES**. |
| 15 | Administration? | **YES**. |
| 16 | End users? | **No — never.** |

**Verdict:** MERGE into "Roles & Permissions" as a tab. Not a standalone item.

---

## ROLE TEMPLATES

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Predefined role configurations for quick role creation. |
| 2 | Who uses it? | Super Admin. |
| 3 | How often? | **Rarely** — used once during initial setup, then almost never. |
| 4 | Inside another module? | **YES** — merge into "Roles & Permissions." |
| 5 | Become a tab? | Tab under "Roles & Permissions." |
| 6-9 | Same as Roles. |
| 10 | Top-level? | **NO — absolutely not.** |
| 11 | DB table? | **Yes** — `role_templates`. |
| 12-16 | Same as Roles. |

**Verdict:** MERGE into "Roles & Permissions." Sub-tab or inline within the Roles configuration.

---

## PRIVILEGES

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Tyro package exposes individual permission actions as a standalone concept. |
| 2 | Who uses it? | Super Admin only. Maybe never — permissions are managed through the Permissions matrix. |
| 3 | How often? | **Almost never.** Permission atoms are defined by developers, not configured by admins. |
| 4 | Inside another module? | **YES** — this is an implementation detail of the Roles & Permissions system. |
| 5 | Become a tab? | Tab under "Roles & Permissions" — but it's really a backend concept. |
| 6 | Become a filter? | No. |
| 7 | Become a modal? | No. |
| 8 | Become settings? | **YES** — it's system configuration. |
| 9 | Disappear as standalone? | **YES** — this should not exist as navigation. It's a developer-oriented view of the permission system. |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | **Yes** — `privileges` table. This IS a database table pretending to be navigation. |
| 12 | Business capability? | **No** — it's an internal architecture concept (Tyro package). |
| 13 | Workflow? | **No**. |
| 14 | Configuration? | **YES**. |
| 15 | Administration? | **YES**. |
| 16 | End users? | **No — never.** |

**Verdict:** ELIMINATE AS NAVIGATION. Merge into "Roles & Permissions" as a read-only reference tab, or remove entirely if the Permissions matrix covers the same ground. This is a Tyro package artifact exposed as UI.

---

## MODULES

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Configure application modules: enable/disable, rename, set display order. |
| 2 | Who uses it? | Super Admin only. Used once at setup, then rarely. |
| 3 | How often? | **Rarely** — initial setup, then maybe once a year. |
| 4 | Inside another module? | **YES** — "System Configuration." |
| 5 | Become a tab? | Tab under "System Configuration." |
| 6 | Become a filter? | No. |
| 7 | Become a modal? | No. |
| 8 | Become settings? | **YES** — this IS system configuration. |
| 9 | Disappear as standalone? | **YES** — it's a settings page. |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | **Yes** — `modules` table. |
| 12 | Business capability? | **No** — it's application configuration, not a business function. |
| 13 | Workflow? | **No** — except "Set up module" which happens once. |
| 14 | Configuration? | **YES**. |
| 15 | Administration? | **YES**. |
| 16 | End users? | **No — never.** They interact with module FEATURES, not modules themselves. |

**Verdict:** ELIMINATE AS TOP-LEVEL. Move to "System Configuration." Rename to "Applications" or "Module Setup." This is configuration, not operations.

---

## PERMISSIONS (ModuleRolePermission)

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Assign which roles can read/create/update/delete which modules. |
| 2 | Who uses it? | Super Admin only. |
| 3 | How often? | **Monthly** — when a new role is created or module added. |
| 4 | Inside another module? | **YES** — merge with Roles. |
| 5 | Become a tab? | Tab under "Roles & Permissions." |
| 6 | Become a filter? | By role, by module. |
| 7 | Become a modal? | No — it's a matrix view. |
| 8 | Become settings? | **YES** — access control configuration. |
| 9 | Disappear as standalone? | **YES** — merge into "Roles & Permissions." |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | **Yes** — `module_role_permissions` table. |
| 12 | Business capability? | **Yes** — "Permission Configuration." |
| 13 | Workflow? | **Yes** — "Assign permissions to role." |
| 14 | Configuration? | **YES**. |
| 15 | Administration? | **YES**. |
| 16 | End users? | **No** — they use "My Access" to see their own permissions. |

**Verdict:** MERGE into "Roles & Permissions." Central tab within access control configuration.

---

## FEATURES

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Feature groupings within modules — another layer of configuration. |
| 2 | Who uses it? | Super Admin only. Almost never after initial setup. |
| 3 | How often? | **Rarely** — feature flags are set once. |
| 4 | Inside another module? | **YES** — merge with Modules. |
| 5 | Become a tab? | Tab under "Module Setup." |
| 6 | Become a filter? | No. |
| 7 | Become a modal? | Yes — inline on Module configuration. |
| 8 | Become settings? | **YES**. |
| 9 | Disappear as standalone? | **YES** — this is over-engineering exposed as navigation. |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | **Yes** — `features` table. |
| 12 | Business capability? | **No** — it's an internal feature-flag system. |
| 13 | Workflow? | **No**. |
| 14 | Configuration? | **YES**. |
| 15 | Administration? | **YES**. |
| 16 | End users? | **No — never.** |

**Verdict:** ELIMINATE AS NAVIGATION. Merge into Module Setup as an inline configuration tab. No user should ever navigate to "Features" directly.

---

## SMTP PROFILES

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Configure outbound mail server settings for system emails. |
| 2 | Who uses it? | Super Admin only. Configured once during deployment. |
| 3 | How often? | **Rarely** — set once, changed only if mail provider changes. |
| 4 | Inside another module? | **YES** — "System Configuration" or "Mail Settings." |
| 5 | Become a tab? | Tab under "System Configuration." |
| 6 | Become a filter? | No. |
| 7 | Become a modal? | Yes — configuration form as modal or settings page. |
| 8 | Become settings? | **YES** — this IS settings. |
| 9 | Disappear as standalone? | **YES** — it's a system setting, not a daily operation. |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | **Yes** — `smtp_profiles` table. |
| 12 | Business capability? | **No** — it's infrastructure configuration. |
| 13 | Workflow? | **No**. |
| 14 | Configuration? | **YES**. |
| 15 | Administration? | **YES**. |
| 16 | End users? | **No — never.** |

**Verdict:** ELIMINATE AS TOP-LEVEL. Move to "System Configuration" as "Mail Settings."

---

## ACTIVITY LOGS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Record of all system changes: who did what, when, to which record. |
| 2 | Who uses it? | Security Officer (daily), Super Admin (weekly), IT Manager (weekly for team oversight). |
| 3 | How often? | **Daily** for Security Officer (reviewing changes). **Weekly** for management. |
| 4 | Inside another module? | **YES** — merge with Login Audits into an "Audit Trail." |
| 5 | Become a tab? | Tab under "Audit." |
| 6 | Become a filter? | By user, by action, by date, by module. |
| 7 | Become a modal? | Detail view as modal. |
| 8 | Become settings? | Retention policy as settings. The log itself is data. |
| 9 | Disappear as standalone? | **YES** — merge with Login Audits. |
| 10 | Top-level? | **FOR SECURITY OFFICER? YES.** For everyone else? **NO.** |
| 11 | DB table? | **Yes** — `activity_log` table. But this is a legitimate audit function. |
| 12 | Business capability? | **Yes** — "Audit Trail & Compliance Monitoring." |
| 13 | Workflow? | **Yes** — "Investigate Change" + "Compliance Review." |
| 14 | Configuration? | Retention policy (separate). |
| 15 | Administration? | **Semi** — it's oversight, not system config. |
| 16 | End users? | **No** — their changes are logged, but they don't navigate here. |

**Verdict:** MERGE with Login Audits into "Audit Trail." Rename to "Audit Trail." Group separately from Access Control and System Configuration.

---

## LOGIN AUDITS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Record of login attempts: success/failure, IP, timestamp, user agent. |
| 2 | Who uses it? | Security Officer (daily), Super Admin (as needed). |
| 3 | How often? | **Daily** — Security Officer checks for anomalies. |
| 4 | Inside another module? | **YES** — merge with Activity Logs. |
| 5 | Become a tab? | Tab under "Audit Trail" with type filter (Changes / Logins). |
| 6 | Become a filter? | **YES** — it's already a type filter within the audit concept. |
| 7 | Become a modal? | Detail view as modal. |
| 8 | Become settings? | Retention policy. |
| 9 | Disappear as standalone? | **YES** — merge with Activity Logs. |
| 10 | Top-level? | **NO** — not as a separate item. |
| 11 | DB table? | **Yes** — `login_audits` table. |
| 12 | Business capability? | **Yes** — part of "Access Monitoring." |
| 13 | Workflow? | **Yes** — "Review Login Attempts" + "Investigate Anomaly." |
| 14 | Configuration? | Alert thresholds (separate). |
| 15 | Administration? | Semi. |
| 16 | End users? | **No**. |

**Verdict:** MERGE with Activity Logs into "Audit Trail." Type filter (Changes / Logins). Remove standalone entry.

---

## IMPORT

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Bulk import records from CSV files. |
| 2 | Who uses it? | Super Admin (rarely), IT Ops (during migration). |
| 3 | How often? | **Rarely** — one-time data migration, then occasional bulk updates. |
| 4 | Inside another module? | **YES** — "Operations" or "Data Tools." |
| 5 | Become a tab? | Tab under "Operations." |
| 6 | Become a filter? | No. |
| 7 | Become a modal? | Import wizard is inherently a multi-step page. |
| 8 | Become settings? | No. |
| 9 | Disappear as standalone? | **YES** — it's a tool, not a data domain. Move to Operations. |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | No — it's a tool that writes to multiple tables. |
| 12 | Business capability? | **Yes** — "Data Import & Migration." |
| 13 | Workflow? | **Yes** — "Bulk Import Data." |
| 14 | Configuration? | Import mapping templates (separate). |
| 15 | Administration? | Semi — it's an administrative tool. |
| 16 | End users? | **No — never.** |

**Verdict:** MOVE to "Operations" or "Data Tools." Not a top-level item. Not a standalone group.

---

## ATTACHMENTS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Global view of all files attached to all records across all modules. |
| 2 | Who uses it? | Almost no one directly. Users see attachments IN CONTEXT on the parent record's detail page. |
| 3 | How often? | **Rarely** — only when searching for a file without knowing which record it belongs to. |
| 4 | Inside another module? | **YES** — attachments are always contextual to their parent record. |
| 5 | Become a tab? | Tab on the parent record's detail page. |
| 6 | Become a filter? | By module, by date, by file type — but the default view should be contextual. |
| 7 | Create as modal? | Upload as modal from the parent record's detail page. |
| 8 | Become settings? | File size limits, allowed types (separate). |
| 9 | Disappear as standalone? | **YES** — the global attachments list is an edge case that doesn't deserve a top-level slot. |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | **Yes** — `attachments` table. |
| 12 | Business capability? | **No** — it's a cross-cutting feature within other capabilities. |
| 13 | Workflow? | **No** — it supports other workflows (e.g., "attach invoice to provider"). |
| 14 | Configuration? | File storage configuration. |
| 15 | Administration? | Semi. |
| 16 | End users? | They interact with attachments inline. Don't need a standalone page. |

**Verdict:** ELIMINATE AS TOP-LEVEL. Attachments should only be visible IN CONTEXT on the parent record. The global list could be a search filter, not a navigation item.

---

## WEBHOOKS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Configure outgoing HTTP notifications when system events occur. |
| 2 | Who uses it? | Super Admin only. Development/integration teams. |
| 3 | How often? | **Rarely** — set once per integration, then tested occasionally. |
| 4 | Inside another module? | **YES** — "Integrations" or "System Configuration." |
| 5 | Become a tab? | Tab under "System Configuration" or "Integrations." |
| 6 | Become a filter? | No. |
| 7 | Create as modal? | Yes. |
| 8 | Become settings? | **YES** — this is system integration configuration. |
| 9 | Disappear as standalone? | **YES** — merge with API Access into "Integrations & Developer Settings." |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | **Yes** — `webhooks` table. |
| 12 | Business capability? | **Yes** — "System Integration." |
| 13 | Workflow? | **Yes** — "Configure Integration." |
| 14 | Configuration? | **YES**. |
| 15 | Administration? | **YES**. |
| 16 | End users? | **No — never.** |

**Verdict:** MERGE with API Access into "Integrations" or "Developer Settings." Not a standalone item.

---

## API ACCESS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Generate and revoke Sanctum personal access tokens for API authentication. |
| 2 | Who uses it? | Super Admin, developers building integrations. |
| 3 | How often? | **Rarely** — generate once per integration, revoke on security incident. |
| 4 | Inside another module? | **YES** — merge with Webhooks into "Integrations & Developer Settings." |
| 5 | Become a tab? | Tab under "Integrations." |
| 6 | Become a filter? | No. |
| 7 | Create as modal? | Yes — token generation wizard. |
| 8 | Become settings? | **YES** — this is developer configuration. |
| 9 | Disappear as standalone? | **YES** — merge with Webhooks. |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | **Yes** — `personal_access_tokens` table. |
| 12 | Business capability? | **Yes** — "API Integration Management." |
| 13 | Workflow? | **Yes** — "Generate Token" + "Revoke Token." |
| 14 | Configuration? | **YES**. |
| 15 | Administration? | **YES**. |
| 16 | End users? | **No — never.** |

**Verdict:** MERGE with Webhooks into "Integrations & Developer Settings." Rename to "Developer Access."

---

## REPORTS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Aggregated analytics and data summaries across modules. |
| 2 | Who uses it? | IT Manager (weekly), Procurement (weekly/monthly), IT Director (monthly), Security Officer (weekly). |
| 3 | How often? | **Weekly** for managers. **Monthly** for directors. |
| 4 | Inside another module? | Reports AGGREGATE across modules. Putting reports inside one module would bias the scope. |
| 5 | Become a tab? | Reports KINDS could be tabs within Reports. |
| 6 | Become a filter? | By date range, by module. |
| 7 | Become a modal? | No — needs page real estate for tables and charts. |
| 8 | Become settings? | Report preferences (default date range, favorite reports). |
| 9 | Disappear? | **NO** — reports are essential for decision-makers. But access must be role-gated. |
| 10 | Top-level? | **YES** — but only for personas who analyze data (Manager, Director, Security, Procurement). |
| 11 | DB table? | **No** — queries aggregate data from multiple tables. |
| 12 | Business capability? | **Yes** — "Reporting & Business Intelligence." |
| 13 | Workflow? | **Yes** — "Monthly Review" + "Cost Analysis" + "Audit Report." |
| 14 | Configuration? | Report settings. |
| 15 | Administration? | **Semi** — it's oversight, not system config. |
| 16 | End users? | **No** — reports are for management, not task-doers. |

**Verdict:** KEEP as top-level. Role-gate to Manager/Director/Security/Procurement. Expand report types over time.

---

## MY PROFILE

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | User account settings: name, email, password, avatar, preferences. |
| 2 | Who uses it? | EVERYONE. |
| 3 | How often? | **Rarely** — password change, profile photo update. |
| 4 | Inside another module? | No — account is universal. |
| 5 | Become a tab? | No — it's a standalone page. |
| 6 | Become a filter? | No. |
| 7 | Become a modal? | **YES** — profile editing as modal from the user menu. |
| 8 | Become settings? | **YES** — it IS user preferences. |
| 9 | Disappear? | **NO** — users must be able to manage their account. |
| 10 | Top-level? | **NO** — account menu, top-right user dropdown. Not sidebar. |
| 11 | DB table? | Yes — `users` table, but every app has a profile. |
| 12 | Business capability? | **Yes** — "User Account Self-Service." |
| 13 | Workflow? | **Yes** — "Update Profile" + "Change Password." |
| 14 | Configuration? | Personal preferences. |
| 15 | Administration? | No. |
| 16 | End users? | **YES**. |

**Verdict:** KEEP but move from sidebar to top-right user dropdown menu. This is universal but not a daily destination.

---

## MY ACCESS

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Show current user what modules they can access and what actions they can perform. |
| 2 | Who uses it? | Everyone (occasionally — usually when troubleshooting "why can't I see X?"). |
| 3 | How often? | **Rarely** — when access issues arise. |
| 4 | Inside another module? | **YES** — could be a tab on Profile. |
| 5 | Become a tab? | Tab under "My Profile." |
| 6 | Become a filter? | No. |
| 7 | Become a modal? | Could be a "Why can't I access this?" contextual modal from any 403 page. |
| 8 | Become settings? | No — it's read-only. |
| 9 | Disappear as standalone? | **YES** — merge into Profile. |
| 10 | Top-level? | **NO**. |
| 11 | DB table? | No — it queries module_role_permissions for the current user. |
| 12 | Business capability? | **Yes** — "Permission Transparency." |
| 13 | Workflow? | **Yes** — "Check My Access." |
| 14 | Configuration? | No — it's read-only. |
| 15 | Administration? | No. |
| 16 | End users? | **YES** — but rarely. |

**Verdict:** MERGE into Profile as a tab. Remove standalone sidebar item. Alternatively, make it a contextual modal triggered from 403 pages.

---

## HELP CENTER

| # | Question | Answer |
|---|----------|--------|
| 1 | Why does this exist? | Documentation, user guides, FAQs. |
| 2 | Who uses it? | EVERYONE — but only when stuck. |
| 3 | How often? | **Rarely** — when encountering something new or confusing. |
| 4 | Inside another module? | No — help is cross-cutting. |
| 5 | Become a tab? | No. |
| 6 | Become a filter? | Search within help. |
| 7 | Become a modal? | Could be a slideout panel or modal from a "?" icon. |
| 8 | Become settings? | No. |
| 9 | Disappear? | **NO** — help is essential for user adoption. |
| 10 | Top-level? | **NO** — needs to be accessible from anywhere (header "?" icon), not as a sidebar item that eats vertical space. |
| 11 | DB table? | No — static or Markdown content. |
| 12 | Business capability? | **Yes** — "User Assistance & Documentation." |
| 13 | Workflow? | **Yes** — "Find Help" + "Learn Feature." |
| 14 | Configuration? | No. |
| 15 | Administration? | No. |
| 16 | End users? | **YES** — prominently. |

**Verdict:** Move from sidebar to top-right "?" icon (next to user menu). Keep the full page for deep browsing, but primary access is contextual.

---

## GUIDE

**Identical analysis to Help Center. These appear to be the same thing with two labels.**

**Verdict:** MERGE with Help Center. Eliminate duplicate. Keep one entry point.

---

## SUMMARY: SURVIVAL SCORE

| Item | Top-Level? | Standalone? | Group | Merge Target |
|------|-----------|-------------|-------|-------------|
| Dashboard | **YES** | **YES** | Top | — |
| Notifications | Header icon | **YES** | Top | — |
| Service Providers | No | **YES** | Vendors | Rename to "Vendors" |
| Hosting | No | **YES** | Services | — |
| Domains | No | **YES** | Services | — |
| Domain Emails | **NO** | **NO** | — | → Domains > Mailboxes tab |
| VPS Accounts | No | **YES** | Services | Rename to "Servers" |
| VoIP | No | **YES** | Services | Rename to "Phone Systems" |
| Other Services | **NO** | **YES** | Services | Rename to "SaaS Subscriptions" |
| Renewals | NEEDS DISCUSSION | NEEDS DISCUSSION | Vendors or inline | Needs decision |
| Assets | No | **YES** | Assets & Inventory | Separate group from services |
| My Credentials | **YES** | **NO** | Vault | Merge into "Vault" |
| Shared Credentials | **YES** | **NO** | Vault | Merge into "Vault" |
| My Tasks | **YES** | **NO** | Tasks | Merge into "Tasks" |
| Task Management | **YES** | **NO** | Tasks | Merge into "Tasks" |
| Calendar | **NO** | **NO** | — | → View toggle on Tasks/Renewals |
| Users | No | **YES** | Access Control | — |
| Roles | **NO** | **NO** | — | → Roles & Permissions tab |
| Role Templates | **NO** | **NO** | — | → Roles & Permissions tab |
| Privileges | **NO** | **NO** | — | → Roles & Permissions (or eliminate) |
| Modules | **NO** | **NO** | — | → System Configuration tab |
| Permissions | **NO** | **NO** | — | → Roles & Permissions tab |
| Features | **NO** | **NO** | — | → System Configuration (Modules) tab |
| SMTP Profiles | **NO** | **NO** | — | → System Configuration > Mail Settings |
| Activity Logs | No | **NO** | — | → Audit Trail (merge with Login Audits) |
| Login Audits | **NO** | **NO** | — | → Audit Trail (merge with Activity Logs) |
| Import | **NO** | No | — | → Operations or Data Tools tab |
| Attachments | **NO** | **NO** | — | → Contextual on parent records |
| Webhooks | **NO** | **NO** | — | → Integrations tab |
| API Access | **NO** | **NO** | — | → Integrations tab |
| Reports | **YES** | **YES** | Reports | Role-gated |
| My Profile | Header menu | **YES** | Account tab | My Access → Profile tab |
| My Access | Header menu | **NO** | — | → Profile tab |
| Help Center | Header "?" | **YES** | — | Merge with Guide |
| Guide | **NO** | **NO** | — | → Help Center |

**Net effect:** 34 items → approximately 18 top-level destinations after merges and eliminations.

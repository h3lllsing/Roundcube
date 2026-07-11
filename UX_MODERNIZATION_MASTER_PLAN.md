# OpsPilot UX Modernization Master Plan

> **Status:** Strategy document — not implementation  
> **Version:** 1.0  
> **Scope:** Product UX design phase only. Batch implementation follows after approval.

---

## Executive Summary

OpsPilot is feature-complete. It manages infrastructure, credentials, tasks, monitoring, renewals, and teams — all in one place. The functional breadth is its strength. The UX breadth is its weakness.

The current interface treats every module as an independent UI island. Index tables are 10 columns wide. Show pages display every database field. Forms offer no visual grouping. The sidebar is a flat list of 30+ links. The dashboard loads 10 independently-designed widgets. The permissions page requires understanding 4 permission states across 8 toggle types.

The result: users spend more time parsing the interface than acting on it.

This plan does not add features. It rethinks **the job of each screen**, **the hierarchy of information**, and **the standards that unify the product**.

**Core thesis:** A user should understand any page in OpsPilot within 3 seconds, because every page follows the same template, shows the same button order, uses the same information hierarchy, and hides the same kinds of complexity.

---

## Design Philosophy

### 1. One job per page

Every page in OpsPilot should answer exactly one question:

| Page | Question |
|------|----------|
| Dashboard | "What needs my attention right now?" |
| Index (list) | "Which item do I need?" |
| Show (detail) | "What do I know about this item?" |
| Create/Edit | "What must I provide to save this?" |
| Permissions | "Who can do what?" |
| Reports | "What does the data say?" |
| Settings | "How do I configure this?" |

If a page tries to answer two questions, it fails at both.

### 2. The 5-column limit

No index table should exceed 5 data columns. Every additional column forces the user to horizontally scan past irrelevant data. If a column matters only when viewing a single record, it belongs on the Show page, not the index.

This is not a visual preference. It is a cognitive constraint: the human brain can hold ~4-7 chunks of information at once. A 10-column table guarantees overflow.

### 3. Progressive disclosure as a rule, not a feature

| Layer | What lives here |
|-------|----------------|
| Always visible | Primary task, critical fields, search, add button |
| One click away | Filters, sort options, secondary fields |
| Two clicks away | Advanced settings, technical details, history, relationships |
| Show page only | Full record detail, activity timeline, monitoring results |

The app currently puts everything on layer 1. This is the single biggest UX problem.

### 4. Consistency before aesthetics

A consistent-but-plain interface trains users faster than a beautiful-but-inconsistent one. Every index table should look like every other index table. Every form should follow the same field order (General → Access → Technical → Billing). Every Show page should use the same section layout.

Once consistency is established, visual polish creates trust. Without consistency, polish feels like decoration.

### 5. Role-aware interfaces

Permissions already exist in the backend. The frontend should reflect what a user can actually do — not by hiding menu items (which it already does), but by simplifying what's visible. A user with only "view" permission on a module should see a simpler page than a super-admin on the same module.

This is not about gating access. It is about reducing cognitive load for users who can only perform a subset of actions.

---

## UX Principles (non-negotiable)

1. **Every page has exactly one primary action** — labeled with a verb, positioned in the same place on every page (top-right, primary button)
2. **Every index shows max 5 data columns** — anything else moves to Show or hover detail
3. **Every form has visibly grouped sections** — no flat list of 13 fields
4. **Every Show page uses the same 4-section template** — Overview, Relationships, Technical, History
5. **Every filter bar shows max 3 controls by default** — "Show advanced" reveals the rest
6. **Every table row has exactly 3 action icons max** — View, Edit, Delete (more actions go in a dropdown)
7. **Every list has a meaningful empty state** — not "No records found" but "No domains yet. Add your first domain."
8. **Every notification is actionable** — click a notification to navigate to the relevant record
9. **Every modal has exactly 2 buttons** — primary action + Cancel
10. **Every page works at 375px width** — if it doesn't, the layout is wrong

---

## Phase 0 — Design System & UX Standards

### Why this is Phase 0

Without standards, every developer-implemented page introduces a new pattern. The current codebase already shows this: some filters use `<x-filter-input>`, others use raw `<input>`. Some form buttons are inside `<x-card>`, others are outside. Some pages have breadcrumbs, others don't.

Fixing these one page at a time means fixing them forever. Establishing standards first means every future change follows the same rules automatically.

### What must be standardized

| Element | Current state | Target state |
|---------|---------------|--------------|
| Table columns | 7-10 per page | 5 max, with responsive collapse |
| Form sections | Flat field list | 3-4 grouped sections with headers |
| Show page layout | Custom per module | 4-section template |
| Button order | Inconsistent across pages | Primary right, Cancel left (same everywhere) |
| Empty states | Generic "No records found" | Actionable: "What to do next" |
| Filter bar | Inline raw inputs per page | Reusable component, 3 controls default |
| Modal buttons | 2-3 buttons, different positions | Exactly 2: primary + cancel |
| Date picker | Native `<input type="date">` | Alpine.js component with quick-select |
| Password reveal | Inline JS per page | Reusable `<x-password-reveal>` component |
| Breadcrumbs | Incomplete map, missing pages | Complete route map, every page has breadcrumbs |

### Justification

**Why this should be first:** Every subsequent phase depends on reusable components. Building a form with sections (Phase 1) requires knowing the standard section layout. Designing a Show page template (Phase 4) requires knowing the standard card component. Without standards, each phase will produce slightly different results.

**Lower-effort alternative:** Skip standards and fix pages ad-hoc. This is faster initially but guarantees inconsistency over time. Given that OpsPilot has 14+ module pages, ad-hoc fixes will cost more in maintenance than establishing standards upfront.

**Risk:** Standards can be over-engineered. Keep the first version minimal — 3 section types, 5 column limit, 2 button positions — and iterate.

### Effort: 4h

---

## Phase 1 — Product Simplification

### The problem

Every OpsPilot page treats every field as equally important. The Hosting create form shows 13 fields in a flat grid. The Hosting index shows 10 columns. The Hosting show page shows 7 sections.

The user doesn't need all of this at once. They need the **primary task** first.

### Methodology

For every module, identify:

| Layer | Example (Hosting) |
|-------|-------------------|
| Primary task | "Find the hosting account and check its status / log into cPanel" |
| Secondary task | "Check billing dates, update provider, view linked domains" |
| Rare task | "Update IP addresses, view change history, force delete" |

### Applying this to the roadmap

**Index pages** should show only what's needed for the primary task:

| Module | Primary columns (visible) | Hide behind expand |
|--------|--------------------------|-------------------|
| Hostings | Name, Domain, Status, Provider | IPs, cPanel URL, created_at |
| Domains | Domain, Status, Expiry, Provider | Registrar, DNS, created_at |
| VPS | Name, IP, Status, Provider | Root credentials, hostname |
| Vault | Service Name, Username, Module | URL, created_at |
| Tasks | Title, Status, Priority, Assignee, Due | Module, created_at |

**Create forms** should show only fields needed for the primary task first:

| Section | Visible by default | Behind "Show advanced" |
|---------|-------------------|----------------------|
| General | Name, Provider, Status | Description |
| Access | Username, Password, URL | IP addresses |
| Billing | Cost, Billing Period | Start date, Expiry date |

**Justification for this order:** A user creating a hosting account knows the name and provider. They may not know the exact start date yet — that can be set later. Showing 13 fields at once on a create form increases drop-off.

### Critical self-challenge

**Is this just "hiding fields"?** No. It's about identifying the primary cognitive load per page. The Hosting index currently requires scanning 10 columns to find a specific hosting account. Five of those columns (IPs, Serial, cPanel PW placeholder) are never the search criteria. They're noise. Removing them from the index doesn't lose data — it moves it to the Show page where it's useful.

**What if users actually need those columns?** If multiple users consistently use a secondary column for filtering, it should be promoted. But the default should be minimal. Adding columns is easier than removing them.

### Effort: 8h (across all 14 modules)

---

## Phase 2 — Information Architecture

### The problem

OpsPilot's current information architecture is **database-driven**: if a model has a `cpanel_ip` field, it appears on the index, show, and form. No page currently ranks information by importance to the user.

### Information ranking framework

| Rank | Definition | How it behaves |
|------|------------|----------------|
| Critical | Required for the primary task | Always visible, no scroll |
| Important | Frequently needed but not primary | One click away, or visible on scroll |
| Secondary | Useful context | Behind "Show more" or on Show page only |
| Technical | Infrastructure detail | Show page "Technical" section only |

### Applying to every module

**Example: Hosting Show page**

| Section | Items | Rank | Justification |
|---------|-------|------|---------------|
| Overview | Name, Provider, Plan, Domain, Status | Critical | This is what the user needs to identify the account |
| Access | cPanel URL, Username, Password | Critical | The primary action is logging in |
| Linked Domains | Domains list | Important | Related data the user frequently needs |
| Technical | Domain IP, Mail Domain IP, cPanel IP | Technical | Needed for DNS/troubleshooting only |
| Financial | Cost, Billing Period | Important | Needed for budgeting |
| Dates | Start Date, Expiry Date | Important | Renewal planning |
| History | Activity timeline | Secondary | Useful but not for daily tasks |
| Notes | Description | Secondary | Context, not action |

**Example: Tasks Index**

| Column | Rank | Justification |
|--------|------|---------------|
| Title | Critical | The primary identifier |
| Status | Critical | Determines next action |
| Priority | Important | Determines urgency |
| Assignee | Important | Determines ownership |
| Due date | Important | Determines deadline |
| Module | Secondary | Context, not action-driving |
| Created | Technical | Only needed for audit |
| Description | Show page only | Too long for a table cell |

### Why technical information should not dominate

IP addresses, serial numbers, and timestamps are "truth" data — they're correct, precise, and verifiable. But they're almost never the basis for a user decision. A user decides "I need to renew this domain" based on the domain name and expiry date. The registrar IP doesn't matter.

Information architecture is about **decision support**, not data completeness. Every piece of data on a page should justify its presence by answering: "What decision does this enable?"

### Self-challenge

**Isn't this subjective?** Yes. Different organizations value different data. The framework (Critical → Important → Secondary → Technical) is universal. Which fields fall into which bucket should be validated with actual users. The default ranking proposed here is a starting point, not a final answer.

### Effort: 6h (audit all fields across all modules)

---

## Phase 3 — Progressive Disclosure

### The principle

Don't show everything at once. Show what the user needs for their current task. Everything else is one click (or one page navigation) away.

### The three layers

| Layer | Interaction | Content |
|-------|-------------|---------|
| Surface | Always visible, no interaction needed | Primary task, search, add button, critical fields, status |
| Expanded | One click/keypress | Filters, sort, secondary fields, quick actions |
| Deep | Page navigation | Show page (full detail), Activity history, Advanced settings |

### Applying to each page type

**Index pages**

| Element | Layer | Justification |
|---------|-------|---------------|
| Search input | Surface | Finding items is the primary job |
| Add button | Surface | Creating is the second most common action |
| Status filter | Surface | Most common filter criterion |
| Table (5 cols) | Surface | Needed for scanning |
| Advanced filters | Expanded | Date ranges, sort order, module filter used less often |
| Bulk actions | Expanded | Multi-select is frequent but not every visit |
| Deleted items toggle | Deep (parameter) | Recovery, not daily use |

**Show pages**

| Element | Layer | Justification |
|---------|-------|---------------|
| Overview (4 fields) | Surface | Identity and status |
| Access (URL + credentials) | Surface | Primary action destination |
| Relationships | Surface (collapsed) | Context, scroll to see |
| Technical details | Expanded ("Show technical") | Rarely needed for operations |
| Activity timeline | Expanded ("Show history") | Audit, not daily use |
| Delete button | Expanded (in dropdown) | Destructive, should not be a primary action |

**Create/Edit forms**

| Element | Layer | Justification |
|---------|-------|---------------|
| General fields | Surface | What the user knows now |
| Access fields | Surface | Required for setup |
| Billing fields | Surface | Required for accounting |
| Technical fields | Expanded ("Show advanced") | Can be set later or left as default |
| Description | Expanded | Optional context |

### Self-challenge

**Does progressive disclosure add clicks?** Yes. It trades clicks for clarity. The question is whether the tradeoff is worth it. Showing 13 fields to save one click is a bad tradeoff. Showing 13 fields when the user only needs 5 adds 8 units of cognitive load every time they visit the page. One extra click to reveal 8 hidden fields removes that load permanently.

**Is there a lower-effort solution?** Collapsible sections (`<details><summary>`) are zero-JS and implementable in minutes. They don't require Alpine or Vue. The simplest possible version of progressive disclosure is already browser-native.

### Effort: 4h (implementation as collapsible sections)

---

## Phase 4 — Standard Page Templates

### The problem

Every OpsPilot module has its own index, show, create, and edit views. That's 56 individual Blade files (14 modules × 4 views). Each one was written independently. Each one has slightly different:
- Button placement
- Section ordering
- Filter layout
- Empty state messaging
- Breadcrumb support

This means every new developer must learn 56 pages instead of 4 templates.

### The templates

**Index template**

```
Search [_________]  Status: [All ▼]  [Filter]  [Clear]  [+ Create]
┌─────────────────────────────────────────────────────────────┐
│ ☐ │ Name │ Domain │ Status │ Provider │ Expires │ [Actions] │
│ ☐ │ ...  │ ...    │ ...    │ ...      │ ...     │ ⋮         │
│ ☐ │ ...  │ ...    │ ...    │ ...      │ ...     │ ⋮         │
└─────────────────────────────────────────────────────────────┘
[< 1 2 3 ... >]
```

5 data columns max. Actions column always last. Search + 1 quick filter visible. Advanced filters in collapsible bar.

**Show template**

```
← Back to [Module]

┌─ Overview ─────────────────────────────────────────────────┐
│ Name: ...   │  Status: ● Active  │  Provider: ...         │
│ Domain: ... │  Plan: ...         │  Cost: ...             │
└────────────────────────────────────────────────────────────┘

┌─ Access ───────────────────────────────────────────────────┐
│ URL: [link]  │  Username: ...  │  Password: [•••••] [Show]│
└────────────────────────────────────────────────────────────┘

┌─ Relationships ────────────────────────────────────────────┐
│ [Linked items with links to their show pages]               │
└────────────────────────────────────────────────────────────┘

[▸ Show technical details]  [▸ Show activity history]
```

Four sections. Three visible by default. Technical and history collapsed.

**Create/Edit template**

```
← Back to [Module]
┌─ General ──────────────────────────────────────────────────┐
│ Name * [_________]  │  Provider [Select ▼]                 │
│ Status [Active ▼]   │  Plan [_________]                    │
└────────────────────────────────────────────────────────────┘

┌─ Access ───────────────────────────────────────────────────┐
│ Username [_________]  │  Password [_________]              │
│ URL [_________]                                            │
└────────────────────────────────────────────────────────────┘

┌─ Billing ──────────────────────────────────────────────────┐
│ Cost [$____]  │  Period [Monthly ▼]                       │
└────────────────────────────────────────────────────────────┘

[▸ Show advanced]  ── reveals:
┌─ Technical ────────────────────────────────────────────────┐
│ Domain IP [_________]  │  cPanel IP [_________]            │
│ Start Date [____]      │  Expiry Date [____]               │
└────────────────────────────────────────────────────────────┘

[Save]  [Cancel]
```

Same section order on every form. Technical fields always collapsed. Three visible sections at most.

### Why templates reduce training cost

Every user action follows a predictable pattern:
1. Scan the index (5 columns, search at top, add button top-right)
2. Click a name to view details (4 sections, overview first)
3. Click Edit (form has General → Access → Billing, advanced is collapsed)

Users don't learn 14 modules. They learn 1 pattern applied 14 times. This is the difference between "I know how to find a hosting account" and "I know how to find anything in OpsPilot."

### Self-challenge

**Does this make all modules look the same?** Yes — and that's the goal. Modules *should* look the same because they *are* the same: CRUD resources with slightly different fields. The field differences are what make each module unique. The template structure should not be.

**What if a module genuinely needs a different layout?** Exceptions should be rare and deliberate. The task Kanban board is a legitimate exception (it's a visual workflow, not a list). The permissions page is a legitimate exception (it's a matrix, not a CRUD resource). These exceptions prove the rule — 12 of 14 modules fit the template. The 2 that don't should be intentionally different, not accidentally different.

### Effort: 12h (create 4 templates, refactor 14 modules × 4 views)

---

## Phase 5 — Role-Based UX

### The premise

OpsPilot already has granular permissions: view, create, update, delete, approve, export, reveal, import. The backend enforces these. The frontend currently uses `<x-permission-check>` to conditionally show/hide individual buttons.

But role-based UX goes deeper than hiding buttons. It means **changing the interface to match what each role actually does**.

### The analysis

| Role | Primary activity | Current interface | Ideal interface |
|------|------------------|-------------------|-----------------|
| Super-admin | Configure system, manage users, audit | Full sidebar, all admin sections | Show admin sections prominently, hide rarely used modules |
| Admin | Oversee infrastructure, manage team | Full sidebar minus some admin | Infrastructure focus, operations as secondary |
| Editor | Manage specific modules, create/update tasks | Full sidebar minus admin | Module-specific views, task-focused |
| User (viewer) | View assigned resources, check status | Full sidebar (read-only) | Simplified sidebar: Dashboard, My Tasks, Vault (my), Help |

### Benefits

1. **Reduced cognitive load**: A viewer doesn't need to know that "Administration → Privileges" exists. Showing them 30 nav items when they can use 5 is actively harmful.

2. **Faster onboarding**: New users see only what they can do. No confusion about "why can I see this but not click it?"

3. **Increased confidence**: Users trust an interface that shows them exactly their scope.

### Drawbacks

1. **Inconsistency across roles**: Two users looking at the same page will see different interfaces. This makes support harder ("I can't see the delete button" "What's your role?").

2. **Implementation complexity**: Role-based templates require conditional rendering at the page level, not just the button level.

3. **Edge cases**: Users with multiple roles or custom permission mixes don't fit clean buckets.

### Recommendation

Implement role-based UX only at the **navigation level** (sidebar). Keep the page content the same for all roles — just use permission checks to enable/disable actions (which already happens). The sidebar is the best place for role-based simplification because:
- It sets expectations for what the user can do
- It doesn't change how individual pages work
- It's a single file to modify

Do not create different Show pages for different roles. That's too much complexity for too little gain. A viewer seeing a dimmed "Edit" button is fine — they know it exists but can't use it.

### Effort: 3h (sidebar-only simplification per role)

---

## Phase 6 — Dashboard Information Architecture

### The wrong question

Don't ask: "Which widgets should be on the dashboard in which order?"

Ask: **"What question should the dashboard answer in the first 5 seconds?"**

### The answer

The dashboard should answer: **"What needs my attention right now?"**

Not "How many assets do I have?" Not "What's the monthly cost breakdown?" Not "What's the monitoring status of every service?"

**"What needs my attention right now?"**

### Redesigning around that answer

Current dashboard order:
1. Operations summary (stat cards)
2. Renewals summary
3. Tasks summary
4. Assets overview
5. Vault overview
6. Monitoring
7. Quick actions
8. Recent activity
9. SMTP status
10. Server health

The user's attention should flow:

```
╔══════════════════════════════════════════════════════════════╗
║  5-second scan:                                             ║
║                                                              ║
║  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐         ║
║  │ Renewals    │  │ Tasks       │  │ Monitoring  │         ║
║  │ due this    │  │ overdue     │  │ failures    │         ║
║  │ month: 3    │  │ today: 2    │  │ today: 1    │         ║
║  └─────────────┘  └─────────────┘  └─────────────┘         ║
║                                                              ║
║  [Quick actions: Add domain | Create task | View vault]     ║
║                                                              ║
║  ┌─ Recent activity ─────────────────────────────────────┐  ║
║  │ Today: John updated domain example.com status → Active │  ║
║  │ Today: Expiry reminder sent for server-01             │  ║
║  │ Yesterday: New VPS added — 192.168.1.1                │  ║
║  └────────────────────────────────────────────────────────┘  ║
║                                                              ║
║  [▸ View summary stats]  ← collapsed, not primary           ║
║  [▸ Server health]       ← collapsed, not primary           ║
╚══════════════════════════════════════════════════════════════╝
```

### Justification

1. **Attention signals first** — Renewals due, tasks overdue, and monitoring failures are the most urgent items. They should be the first thing a user sees.

2. **Quick actions second** — Once the user knows what needs attention, they need a way to act on it. Quick actions (Add domain, Create task) should be immediately available.

3. **Activity feed third** — The "what changed" view is high-value context. It tells the user what happened since their last visit.

4. **Stats and health collapsed** — "How many assets do I have?" is a query, not an alert. It should be available but not dominant.

### Self-challenge

**Doesn't this bury important monitoring data?** If monitoring failures exist, they're in the attention signal row (top of page). The full monitoring dashboard is one click away. The collapsed "Server health" section is for routine checks, not alerts.

**Doesn't this deprioritize renewals?** Renewals due this month are the top attention signal. This actually *promotes* renewals from widget #2 to the most visible position.

### Effort: 4h

---

## Phase 7 — Navigation Simplification

### Navigation hierarchy

OpsPilot has 4 distinct navigation layers that are currently mixed together:

| Layer | Definition | Current state |
|-------|------------|---------------|
| Primary | Tasks you do every day | Mixed into 6 groups |
| Secondary | Tasks you do weekly | Mixed into 6 groups |
| Context | Actions related to the current page | Inline buttons scattered across pages |
| Quick | Urgent, cross-module actions | Hidden in Cmd+K (undiscoverable) |

### Proposed navigation hierarchy

**Primary navigation** (visible in sidebar, no click to expand)

Dashboard — Monitoring — My Tasks — My Credentials

This is the same for every role. These are the pages users visit daily.

**Secondary navigation** (sidebar, one click to expand)

Infrastructure (collapsed by default):
- Vendors, Hosting, Domains, Domain Emails, VPS, VoIP, SaaS, Renewals, Assets, G-Mail

Credentials (collapsed by default):
- Shared Credentials

Operations (collapsed by default):
- Task Management, Calendar, Notes

**Tertiary navigation** (sidebar, super-admin only, collapsed by default)

Administration:
- Access Control (Users, Roles, Role Templates, Privileges)
- System (Modules, Permissions, Features)
- Configuration (Mail, Integrations, API)
- Monitoring (Audit Trail, Login History, Import, Attachments)

**Context navigation**

Inline in each page — the 3 action pattern (View, Edit, Delete) that already exists. No changes needed here.

**Quick actions**

Promote Cmd+K or add a visible quick-action toolbar (sticky, bottom of viewport) with:
- [Add Domain] [Create Task] [New Vault Entry] [Search Everything]

### Justification

The current sidebar shows 30+ links. That's overwhelming for any user. The proposed model shows:
- **Always visible**: 4 links
- **One click away**: 10 links (infrastructure) + 2 (credentials) + 4 (operations)
- **Two clicks away**: 14 links (admin, in sub-groups)

The key principle: **a link that is visible every day should never require a click to reach**. Everything else should be one or two clicks away.

Groups should be collapsed by default because the average user visits 1-2 items per group, not all of them.

### Self-challenge

**Doesn't this bury administration too deep?** Super-admins visit administration pages frequently. But they visit 1-2 specific ones (Users, Roles), not all 14. Sub-grouping within administration helps: "Access Control" is 4 items, not 14.

**Should "Account" be primary navigation?** No. Profile and Help Center are visited rarely (once per session at most). They belong in a collapsed group at the bottom.

### Effort: 3h

---

## Phase 8 — Permissions UX

### Why this needs its own phase

The permissions page is the most cognitively complex screen in OpsPilot. It has:
- 4 permission states per toggle (role default, role value, effective value, user override)
- 8 permission keys per module (view, create, edit, delete, approve, export, reveal, import)
- 5 different modals
- 8 permission-specific Blade components
- A 93-line PHP data preparation block at the top of the view
- A separate CSS file

This isn't just a "redesign" problem. The conceptual model is too complex.

### Why 4 states is too many

The current model is:
1. **Role baseline**: What the role allows
2. **Role effective**: What the role allows after inheritance
3. **User effective**: What the user actually gets (role + overrides)
4. **User override**: What the user specifically set

A super-admin managing permissions needs to understand all 4 to predict what a user will see. This is unreasonable.

### Simplification to 2 states

| New state | Meaning | Implementation |
|-----------|---------|----------------|
| Inherit | User gets whatever the role provides | No override stored |
| Override | User gets this specific value | Override stored explicitly |

The interface becomes:

```
[Module Name]                        [Inherit ▼]  [View] [Create] [Update] [Delete]
```

Where "Inherit" means "use role default". Clicking "Override" unlocks the individual toggles. This is the same conceptual model as Unix file permissions ("inherit from parent or set explicitly") — and it's well-understood.

### Why 8 permission keys is too many

The current keys are: view, create, edit, delete, approve, export, reveal, import.

Fewer than half of these keys are used on the majority of modules. "Reveal" only applies to vault modules. "Import" only applies to a few modules. "Approve" may not be used at all.

**Simplification to 4 keys:**

| Key | Covers | Rationale |
|-----|--------|-----------|
| View | Read access | The most basic permission |
| Create | Create new records | Often paired with view |
| Update | Edit existing records | The most common "write" action |
| Delete | Remove records | The most sensitive action |

"Export", "Reveal", "Import", and "Approve" should be **module-level flags**, not per-module permission keys. If a user can view a vault module, they can reveal passwords only if the module allows it. This removes 4 toggles from every single module row.

### Reduction calculation

| Metric | Before | After | Reduction |
|--------|--------|-------|-----------|
| Permission keys per module | 8 | 4 | 50% |
| States per toggle | 4 | 2 | 50% |
| Effective complexity | 32 states per module | 8 states per module | 75% |

### Additional simplification

1. **Remove 3 of 5 modals**. The "sensitive confirmation" modal can be a checkbox ("I understand this grants elevated access"). The "bulk apply" preview can be inline. The "reset editor" confirmation can be inline. Keep only the "Unsaved changes" modal and the "Role changed" modal.

2. **Merge filter chips**. The current 6 filters (All, Modified, Sensitive, Manage, Custom, Inherited) can be 3: All, Modified, Sensitive. "Manage" and "Custom" map to "Modified". "Inherited" is the default view.

3. **Remove the separate CSS file**. Use Tailwind classes consistently. The permissions.css file exists because the component markup doesn't use the existing design system. Fix the markup, not the CSS.

### Self-challenge

**Does reducing to 4 keys lose functionality?** Yes, for users who need granular "can export but not delete" control. But that level of granularity is rarely needed — and when it is, it can be implemented as a separate settings page rather than cluttering every module row.

**Isn't this redesigning the permission system, not the UX?** The backend can remain unchanged. The UX simplification maps 8 backend keys to 4 visible keys, and maps 4 backend states to 2 visible states. The mapping layer lives entirely in the Blade view and Alpine component. No database changes needed.

### Effort: 10h

---

## Recommended Execution Order

```
Phase 0 → Phase 1 → Phase 2 → Phase 4 → Phase 7 → Phase 8
                                                        ↓
                     Phase 3  →  Phase 5  →  Phase 6
```

### Why this order

1. **Phase 0 (Standards)** — Foundation. Everything else depends on it.

2. **Phase 1 (Simplification)** — The highest-impact change with the lowest effort. Removing columns from index pages is a quick win that touches every user.

3. **Phase 2 (Information Architecture)** — Needed before templates. You can't build a template until you know which information goes where.

4. **Phase 4 (Templates)** — Now that standards exist and information is ranked, build the templates. This is the bulk of the implementation.

5. **Phase 7 (Navigation)** — Sidebar changes are independent of page templates but benefit from knowing the final page structure.

6. **Phase 8 (Permissions UX)** — The most complex change. Should come after templates are stable so the permission page can use the same design system.

7. **Phase 3 (Progressive Disclosure)** — Implemented last because it's a refinement layer on top of templates. Templates define the structure; progressive disclosure defines what's visible by default.

8. **Phase 5 (Role-Based UX)** — The most speculative change. Should be implemented last and only if the other phases don't provide enough simplification on their own.

9. **Phase 6 (Dashboard)** — Dashboard changes are independent of the rest. Can be done in parallel with any other phase. Placed last because the dashboard benefits from the UX standards established in Phase 0.

### Dependency graph

```
Phase 0 ──┬── Phase 1 ── Phase 2 ── Phase 4 ── Phase 7 ── Phase 8
          │                                        ↑
          └── Phase 3 ──────────────────────────────┘
          
Phase 0 ── Phase 5 (independent, low risk)
Phase 0 ── Phase 6 (independent, high visibility)
```

---

## Risks

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Users rely on currently-visible secondary columns | Medium | Medium | Deploy template changes to staging first, gather feedback |
| Super-admins resist permission simplification | Medium | High | Keep backend permission granularity, only simplify the UI mapping |
| Templates feel too rigid for diverse modules | Low | Medium | Allow exceptions with documented justification (max 2 per module) |
| Dashboard changes surprise daily users | Medium | Medium | Announce changes before deploy, show side-by-side comparison |
| Phase 8 (permissions) breaks existing overrides | Low | Very high | Keep Alpine data structure backward-compatible, map old states to new |
| Role-based UX increases support ticket volume | Low | Medium | Add a "Show all menu items" toggle for power users |
| Progressive disclosure buries features users need | Medium | Medium | Track "Show advanced" click rates, promote frequently-used hidden fields |

---

## Expected Impact

| Metric | Current (estimated) | Target (estimated) | How measured |
|--------|---------------------|--------------------|--------------|
| Index page column count | 7-10 per page | 5 max per page | Manual audit |
| Form field visible by default | 100% | 60-70% | Template comparison |
| Dashboard attention time | 15-20s to find priorities | 3-5s | User testing |
| Permissions page state complexity | 32 states per module | 8 states per module | Calculation |
| Sidebar links visible without scroll | 30+ | 4 (+ group headers) | Count |
| Empty states with actionable guidance | ~20% | 100% | Audit of all empty states |
| Breadcrumb coverage | ~60% of pages | 100% | Route map check |
| Mobile usability at 375px | Poor (horizontal scroll) | Full responsive | Manual test on 375px viewport |

---

## Success Metrics

1. **Task completion time** — User can find a hosting account and copy its cPanel URL in under 10 seconds (from dashboard). Currently ~20-30s.

2. **Permission page completion** — Super-admin can modify a user's module permissions in under 60 seconds without errors. Currently can take 3-5 minutes.

3. **New user onboarding** — A new user can create their first task within 2 minutes of first login. Currently requires navigating sidebar, finding "Operations > Task Management > Create", then filling a form with unclear fields.

4. **Support tickets** — UX-related support questions ("Where do I find X?", "How do I Y?") decrease by 50% within 1 month of changes.

5. **Navigation path depth** — Average user can reach any page within 2 clicks. Currently some pages require 3-4 clicks (sidebar → group expand → sub-link → sub-sub-action).

---

## What NOT to Change

These are explicitly out of scope for UX modernization:

| Item | Reason |
|------|--------|
| Backend permission system | The data model is sound. Only the UI for managing it needs simplification. |
| API routes and responses | Frontend consumes API data — changing API responses breaks existing consumers. |
| Database schema | No migrations, no new columns, no new tables. |
| Authentication logic | Login, register, password reset flows are standard Laravel and work correctly. |
| Email templates | Only sent by the system, not interacted with by daily users. |
| Dark mode toggle | Already implemented and working. No changes needed. |
| Command palette (Cmd+K) | Already implemented. Only needs promotion/discoverability (Phase 7). |
| Monitoring system | The monitoring check logic is functional. Only the monitoring overview page UI may change. |
| Task Kanban board | It's a deliberately different UI from the standard index. Keep the exception. |
| Export functionality | CSV export works. The button position may change (templates) but the feature doesn't. |
| Help center content | Content updates are documentation, not UX. |
| Error pages (401, 403, 404, 419, 429, 500) | These are triggered by Laravel, not by our views in most cases. Minimal impact. |

---

## Estimated Effort Summary

| Phase | Description | Effort | Dependencies |
|-------|-------------|--------|-------------|
| 0 | Design System & UX Standards | 4h | None |
| 1 | Product Simplification | 8h | Phase 0 |
| 2 | Information Architecture | 6h | Phase 1 |
| 3 | Progressive Disclosure | 4h | Phase 2 |
| 4 | Standard Page Templates | 12h | Phase 2 |
| 5 | Role-Based UX | 3h | Phase 4 |
| 6 | Dashboard IA | 4h | Phase 0 |
| 7 | Navigation Simplification | 3h | Phase 4 |
| 8 | Permissions UX | 10h | Phase 4 |
| **Total** | | **54h** | |

---

## Appendix: How this differs from the original roadmap

| Original Batch | New Phase | What changed |
|----------------|-----------|--------------|
| 1 — Table density | → Merged into Phase 1 (Simplification) + Phase 4 (Templates) | Previously treated as a standalone fix. Now it's a consequence of the simplification framework. |
| 2 — Form simplification | → Phase 4 (Templates) | Previously a standalone effort. Now it's a template, not a per-form fix. |
| 3 — Show pages | → Phase 2 (IA) + Phase 4 (Templates) | Information ranking feeds the template design. |
| 4 — Dashboard | → Phase 6 | Still its own phase, but reframed around the question it answers. |
| 5 — Navigation | → Phase 7 | Still its own phase, but now has a clear hierarchy framework. |
| 6 — Auth/onboarding | → Dropped | After analysis, login page and registration are low-traffic screens. The 7 hours of effort don't justify the impact. Auth is a Laravel default that works. |
| 7 — Permissions | → Phase 8 | Still the last phase. The simplification proposal is more aggressive (4 states → 2 states). |
| 8 — Filters | → Merged into Phase 1 + Phase 3 | Filter simplification is now a consequence of progressive disclosure, not a standalone effort. |

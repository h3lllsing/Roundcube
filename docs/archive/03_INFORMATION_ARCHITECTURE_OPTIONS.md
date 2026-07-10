# 03 — INFORMATION ARCHITECTURE OPTIONS

> Navigation philosophies from first principles.
> Each model is a complete rethinking of the information architecture, not a tweak of the current state.

---

## Model A: Resource-Centric (Current Philosophy)

Organize by WHAT the system tracks. One menu entry per database entity.

```
Dashboard | Notifications
├── Services: Hosting | Domains | VPS | VoIP | SaaS | Providers | Renewals | Assets
├── Credentials: Vault
├── Tasks: Tasks
├── Administration: Users | Roles & Perms | Applications | Audit | Mail | Integrations | Import
├── Reports: Reports
└── Account: Profile | Help
```

**Advantages:**
- Predictable (one table = one entry)
- Engineers love it
- No ambiguity about where data lives
- Low maintenance (add table → add menu item)

**Disadvantages:**
- **UX disaster**: 34 items, 64% irrelevant per user
- **No workflow support**: "Provision a new service" requires 5+ navigation actions
- **Scales linearly with tables**: Add 10 more tables = 44 items
- **Administration monolith**: 14 items forced into one group

**Cognitive load:** VERY HIGH. 34 choices.
**Enterprise suitability:** LOW. An enterprise platform must serve diverse roles, not just developers.
**Training cost:** HIGH. Users must learn the database schema to navigate.
**Discoverability:** HIGH for simple lookups. LOW for complex workflows.
**Future growth:** FAILS. Adding entities makes navigation worse, not better.

---

## Model B: Service-Centric (ITIL-Inspired)

Organize by the SERVICE LIFECYCLE: Plan → Source → Operate → Retire.

```
Dashboard | Notifications
├── Service Portfolio: Domains | Hosting | Servers | Phone Systems | SaaS
├── Supplier Management: Vendors | Contracts & Renewals
├── Asset Management: Hardware | Software
├── Operations: Tasks | Calendar | Data Import
├── Security & Access: Users | Roles & Perms | Vault | Audit | Login History
├── Configuration: Module Setup | Mail Settings | Integrations
├── Reporting: Reports
└── My Account: Profile | Help
```

**Advantages:**
- Aligned with ITIL/ITSM standard — CIO-approved vocabulary
- Clear lifecycle stages
- Natural grouping: suppliers separate from services
- Scales well (add new service types under Service Portfolio)

**Disadvantages:**
- ITIL terminology may not match small/medium org vocabulary
- "Security & Access" mixes credentials (daily use) with audit (weekly) with RBAC (rarely)
- Users who don't know ITIL may not understand the grouping logic

**Cognitive load:** LOW (after learning ITIL grouping). **MEDIUM** (during initial learning).
**Enterprise suitability:** HIGH (for enterprises following ITIL). **MEDIUM** (for others).
**Training cost:** MEDIUM-HIGH. ITIL vocabulary requires teaching.
**Discoverability:** MEDIUM. ITIL grouping is logical but abstract.
**Future growth:** GOOD. Service Portfolio can expand.

---

## Model C: Workspace-Centric (Role-Contextual)

Organize by the USER'S SPHERE: My personal → Team shared → System admin.

```
Dashboard | Notifications
├── MY WORKSPACE: My Tasks | My Vault | My Profile
├── TEAM WORKSPACE: All Tasks | Services | Vendors | Assets | Renewals
├── OVERSIGHT: Audit Trail | Reports
└── ADMIN WORKSPACE: Users | Roles & Perms | Applications | Settings | Integrations
```

**Advantages:**
- Intuitive mental model (mine → ours → system)
- Clear privacy boundary
- Naturally role-gated (admin workspace invisible to non-admins)
- Reduces cognitive load by tier (most users never leave Workspace 1)

**Disadvantages:**
- "Team Workspace" becomes a catch-all for diverse items
- Services (daily) and Vendors (weekly) and Renewals (weekly) all in one group
- Duplication risk ("My Tasks" in Workspace 1, "All Tasks" in Workspace 2 — but that's the task split we're trying to eliminate)
- Workspace 2 could grow to 15+ items

**Cognitive load:** LOW. Three-tier model is intuitive.
**Enterprise suitability:** HIGH. Separates personal from administrative naturally.
**Training cost:** LOW. "Your stuff → team stuff → system stuff" is universal.
**Discoverability:** GOOD for personal items. MEDIUM for team items (catch-all group).
**Future growth:** MEDIUM. Team workspace becomes the growth bottleneck.

---

## Model D: Verb-Centric / Activity-Centric

Organize by what you DO, not what you have.

```
Dashboard | Notifications
├── MONITOR: Dashboard | Reports | Audit Trail | Login History
├── PROVISION: Domains | Hosting | Servers | Phone Systems | SaaS
├── SECURE: Vault | Credential Access
├── VENDOR: Providers | Renewals
├── TRACK: Tasks | Calendar | Assets
├── CONFIGURE: Users | Roles & Perms | Applications | Mail | Integrations
└── SETTINGS: Profile | Help
```

**Advantages:**
- Action-oriented: user sees verbs, not nouns
- Workflow alignment: "I want to PROVISION something" → click Provision
- Natural for new users who don't know the data model

**Disadvantages:**
- Verbs overlap: "Monitor" could include parts of "Track"
- Some items resist verb categorization: "Assets" = Track? Own? Manage?
- Verb names are hard to keep consistent: Monitor/Observe/Oversee?
- Verbs are harder to localize

**Cognitive load:** MEDIUM. Verbs are intuitive but grouping boundaries are fuzzy.
**Enterprise suitability:** MEDIUM-HIGH. Action-oriented design is user-friendly.
**Training cost:** LOW. "What do you want to do?" is natural.
**Discoverability:** GOOD for goal-oriented tasks. POOR for data lookup.
**Future growth:** MEDIUM. New entities need verb assignment, which isn't always obvious.

---

## Model E: Persona-Centric (Adaptive)

Show different navigation based on role. Same backend, different sidebar.

```
SUPER ADMIN sees:
Dashboard | Notifications
├── Operations: All Tasks | Calendar
├── Services: Domains | Hosting | Servers | SaaS
├── Vendors: Providers | Renewals
├── Assets
├── Access Control: Users | Roles & Perms
├── System: Applications | Mail | Integrations
├── Audit: Audit Trail | Login History
├── Reports
└── Account: Profile | Vault | Help

IT OPERATOR sees:
Dashboard | Notifications
├── Services: Domains | Hosting | Servers | Phone | SaaS
├── Vendors: Providers | Renewals
├── Assets
├── Tasks
├── Vault
└── Account: Profile | Help

SERVICE DESK sees:
Dashboard | Notifications
├── Services: Domains | Hosting | Servers | Phone
├── Tasks
├── Vault
└── Account: Profile | Help

END USER sees:
Dashboard | Notifications
├── My Tasks
├── My Vault
└── Account: Profile | Help
```

**Advantages:**
- **Maximum relevance**: each user sees only what they need
- **Lowest cognitive load**: 5-10 items per persona instead of 34
- **Highest adoption**: users find what they need immediately

**Disadvantages:**
- **Maintenance complexity**: 8+ sidebar configurations
- **Role-edge cases**: users in multiple roles (e.g., IT Operator + Security Officer) may need a combined view
- **Testing burden**: each sidebar variant must be tested
- **Role mapping**: current roles don't map neatly to all 8 personas (no Security Officer role, no Procurement role)

**Cognitive load:** LOWEST (5-10 items per user). BEST score.
**Enterprise suitability:** HIGHEST. Enterprise platforms must serve diverse roles.
**Training cost:** LOWEST. Users see only what matters to them.
**Discoverability:** HIGH for relevant items. LOW for cross-role discovery (a user may not know a feature exists until someone tells them).
**Future growth:** MEDIUM. Each new entity must be assigned to personas. But the sidebar doesn't grow for any single user.

---

## Model F: Minimalist / Progressive Disclosure

Show only the TOP 5 items by default. All other items accessible through a "More" menu or search.

```
Default view:
Dashboard | Notifications
├── Tasks
├── Vault
├── Services
└── [More...]

Expanded view (after clicking "More"):
├── Vendors
├── Assets
├── Reports
├── Audit
└── Administration
```

**Advantages:**
- **Lowest initial cognitive load**: 3-4 items visible on first load
- **Forces discovery through search** (command palette becomes primary nav)
- **Cleanest UI**: minimal sidebar

**Disadvantages:**
- **Extra click for everything**: users must expand "More" to find less-frequent items
- **"Services" becomes a catch-all** for 5+ service types — now you need a second-level navigation
- **Power users hate it**: IT Ops who use 5+ services daily now have an extra click every time
- **The "More" menu grows** to 15+ items — same problem shifted

**Cognitive load:** LOW (first view). HIGH (when "More" is needed).
**Enterprise suitability:** LOW. Power users need fast access. Minimalist design prioritizes first-time experience over daily efficiency.
**Training cost:** LOW. Easy for beginners.
**Discoverability:** LOW. Items hidden behind "More" are invisible.
**Future growth:** MEDIUM. New items go in "More." Works until "More" reaches 20 items.

---

## Model G: Hub-and-Spoke / Dashboard-Centric

Minimal sidebar. Every major workflow starts from the Dashboard.

```
Dashboard (universal landing page, shows widgets for everything)
├── [Go to Tasks]
├── [Go to Vault]
├── [Go to Services]
├── [Go to Renewals]
├── [Go to Reports]
├── [Go to Admin]
└── [Search everything]
```

**Advantages:**
- **Dashboard is the command center**: one page to rule them all
- **Widget-based**: each widget links to its full page
- **Natural progressive disclosure**: see summary → click for detail

**Disadvantages:**
- **Dashboard becomes a homepage**: if it lists everything, it's just a slow sidebar replacement
- **Customization required**: each persona needs different widgets. A universal dashboard serves no one well.
- **Widget overload**: 10+ widgets on one page is overwhelming
- **Maintenance**: adding a new entity requires a new dashboard widget

**Cognitive load:** HIGH (dashboard widget density) + LOW (sidebar).
**Enterprise suitability:** MEDIUM. Dashboard-first works for execs. Fails for operators who need direct entity access.
**Training cost:** LOW. "Start here, go anywhere."
**Discoverability:** MEDIUM. Depends on widget visibility.
**Future growth:** POOR. Dashboard becomes increasingly crowded.

---

## Model H: Domain-Centric (DAMA/DMBOK-Inspired)

Organize by DATA DOMAINS — the business areas the data represents.

```
Dashboard | Notifications
├── PARTY (Who): Users | Vendors | Contacts
├── PRODUCT (What): Services | SaaS | Assets | Renewals
├── ACCESS (How): Vault | Roles & Perms | Audit | Login History
├── WORK (When): Tasks | Calendar
├── INTELLIGENCE (Why): Dashboard | Reports
├── CONFIGURATION (How): Applications | Mail | Integrations | Import
└── SELF (Me): Profile | Help
```

**Advantages:**
- **Enterprise data governance alignment**: matches how enterprise architects think
- **Stable categories**: domains rarely change even as entities change
- **Clear ownership boundaries**: each domain maps to a data steward

**Disadvantages:**
- **Abstract categories**: "Party" and "Product" are meaningless to end users
- **High learning curve**: users must understand data domain theory
- **Over-engineered**: for a 500-user org, DAMA terminology is excessive

**Cognitive load:** HIGH (abstract terminology).
**Enterprise suitability:** HIGH (for regulated industries with data governance). LOW (for SMB).
**Training cost:** HIGHEST. Requires data governance training.
**Discoverability:** LOW. Abstract categories don't match user mental models.
**Future growth:** EXCELLENT. Domains are stable.

---

## Model I: Search-Centric (Unified Navigation)

Minimal sidebar. Primary navigation is search/command palette.

```
Dashboard (landing)
├── [Ctrl+K / Cmd+K to search everything]
├── [Recent items (5)]
├── [Bookmarked items (user-configurable)]
└── [Quick links: Tasks | Vault | More...]
```

**Advantages:**
- **Forces content quality**: every item must be findable by search
- **Zero navigation decisions**: type what you need
- **Minimal maintenance**: no menu structure to maintain
- **Modern UX pattern** (Superhuman, Linear, Notion)

**Disadvantages:**
- **High learning curve**: users must know WHAT they're looking for before they can find it
- **Discovery problem**: how does a new user learn what's available? They must browse the sidebar.
- **Power user friendly, beginner hostile**: experts love search, novices need structure
- **Accessibility concerns**: keyboard-dependent, screen reader challenges

**Cognitive load:** LOW (no navigation decisions) + HIGH (must form query mentally).
**Enterprise suitability:** LOW-MEDIUM. Works for developer tools. Fails for broad enterprise user bases where 90% of users are non-technical.
**Training cost:** HIGH. Users must learn system vocabulary.
**Discoverability:** LOWEST. What you don't know exists, you can't search for.
**Future growth:** BEST. Add anything, search finds it.

---

## Model J: Hybrid (Task-First + Contextual)

Primary navigation organized by WORKFLOW. Secondary navigation organized by ENTITY.

```
Dashboard | Notifications
├── [Quick Actions: New Task | Store Credential | New Domain | New Hosting]
├── TODAY'S WORK: Tasks (my) | Renewals Due | Recent Changes
├── MY THINGS: Vault (my) | Profile
├── OPERATIONS: All Tasks | Services | Vendors | Assets | Calendar
├── OVERSIGHT: Audit Trail | Reports
└── ADMINISTRATION: Users | Roles & Perms | System Config | Integrations
```

**Advantages:**
- **Best of both worlds**: workflow-first on top, entity browse on bottom
- **Natural split**: daily work at the top, occasional admin at the bottom
- **Progressive disclosure**: most users never scroll past "OVERSIGHT"

**Disadvantages:**
- **Two navigation paradigms on one page**: potentially confusing
- **"Services" still a catch-all**: 5+ service types under one entry
- **Harder to maintain**: need to decide every new item's workflow vs. entity placement

**Cognitive load:** MEDIUM. Two paradigms but each is simple.
**Enterprise suitability:** HIGH. Matches how enterprise users actually work (task-first, browse-second).
**Training cost:** LOW-MEDIUM. "What do you need to do today?" at top. "Browse all things" below.
**Discoverability:** GOOD. Workflow section shows what's actionable. Entity section shows what exists.
**Future growth:** GOOD. New workflows go in top section. New entities go in bottom section.

---

## Model K: Tiered Responsibility Stack

Organize by LEVEL OF RESPONSIBILITY: Operational → Tactical → Strategic → Systemic.

```
Dashboard | Notifications
├── OPERATIONAL (Daily): Tasks | Vault | Services | Assets
├── TACTICAL (Weekly): Vendors | Renewals | Calendar | Reports
├── STRATEGIC (Monthly): Audit | Trends | Budget
└── SYSTEMIC (Infrequent): Users | Roles | Config | Integrations
```

**Advantages:**
- **Time-horizon alignment**: matches how often things are used
- **Natural prioritization**: daily items at top, never-used at bottom
- **Intuitive for all roles**: every user operates at every level at different frequencies

**Disadvantages:**
- **Arbitrary boundaries**: is Assets daily? weekly? for IT Ops yes, for Manager no
- **Same item appears at different levels for different users**: cannot be static
- **"Operational" becomes a catch-all**: if it's used daily, it goes there

**Cognitive load:** LOW. Time-horizon is intuitive.
**Enterprise suitability:** HIGH. Matches management layers.
**Training cost:** LOW. "How often do you use this?" is a natural question.
**Discoverability:** GOOD. Frequency-based grouping helps users find by expected usage.
**Future growth:** GOOD. Add item → assign frequency tier.

---

## Model L: Navigation-by-Frequency (Adaptive)

Sidebar reorders itself based on usage frequency. Most-used items float to top.

```
Dashboard | Notifications
├── [Your Top Items: adaptively reordered]
├── [More...: alphabetical list of remaining]
```

**Advantages:**
- **Self-optimizing**: sidebar learns user behavior
- **Personal**: every user's sidebar is different
- **No design decisions**: algorithm determines placement

**Disadvantages:**
- **Unpredictable**: sidebar changes without user action
- **Hard to build**: usage tracking backend, algorithms, caching
- **Users hate unexpected changes**: "where did my menu item go?"
- **Privacy**: tracking user behavior has compliance implications
- **New user problem**: sidebar is empty until behavior is learned

**Cognitive load:** LOW (after learning). HIGH (during adaptation).
**Enterprise suitability:** LOW. Predictability is more important than optimization for enterprise users.
**Training cost:** HIGH. Users can't develop muscle memory if sidebar changes.
**Discoverability:** LOW. Items can migrate to unpredictable positions.
**Future growth:** MEDIUM. Algorithm handles growth, but unpredictability is worse.

---

## QUICK COMPARISON

| Model | Cognitive Load | Enterprise Fit | Training | Discoverability | Growth | Dev Effort |
|-------|---------------|----------------|----------|-----------------|--------|------------|
| **A** Resource-Centric | HIGH | LOW | HIGH | MED | POOR | NONE |
| **B** Service-Centric | MED | HIGH | MED-HI | MED | GOOD | LOW |
| **C** Workspace-Centric | LOW | HIGH | LOW | MED-HI | MED | MED |
| **D** Verb-Centric | MED | MED-HI | LOW | MED-HI | MED | LOW |
| **E** Persona-Centric | **LOWEST** | **HIGHEST** | **LOWEST** | HIGH* | MED | MED-HI |
| **F** Minimalist | MED-LO | LOW | LOW | POOR | MED | LOW |
| **G** Hub-and-Spoke | MED | MED | LOW | MED | POOR | MED |
| **H** Domain-Centric | HIGH | HIGH | HIGH | LOW | **BEST** | HIGH |
| **I** Search-Centric | MED | LOW | HIGH | **LOWEST** | **BEST** | HIGH |
| **J** Hybrid Task-First | MED | HIGH | LOW-MED | GOOD | GOOD | MED |
| **K** Tiered Responsibility | LOW | HIGH | LOW | GOOD | GOOD | LOW |
| **L** Adaptive Frequency | MED | LOW | HIGH | LOW | MED | **HIGHEST** |

\* Discoverability is HIGH for relevant items but LOW for cross-role discovery.

**Models requiring backend changes:** E (new roles), I (search infrastructure), L (usage tracking), H (new concepts).
**Models requiring only template changes:** B, C, D, F, G, J, K.

**Final observation:** The highest-scoring models (E, C, K, J) all share one thing: they group items by CONTEXT (whose job, which time horizon, which workflow) rather than by DATA TYPE (which table).

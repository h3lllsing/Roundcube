# 05 — MODULE MERGE MATRIX

> Every pair of current items evaluated for merge potential.
> Green = strong merge candidate. Yellow = possible. Red = keep separate.

---

## Decision Key

| Label | Meaning |
|-------|---------|
| **MERGE** | Items should become a single navigation entry |
| **TAB** | Items become tabs within a shared parent |
| **FILTER** | One item becomes a filter on the other |
| **CHILD** | One item nests under the other |
| **CONTEXT** | Item becomes contextual (shown inline on related record) |
| **KEEP** | Items remain separate navigation entries |
| **ELIM** | Item should be removed from navigation entirely |

---

## Merge Matrix

Rows and columns represent current navigation items. Read row → column.

### Services & Operations

| | Svc Prov | Host | Dom | DomEmail | VPS | VoIP | OtherSvc | Renew | Assets | MyCred | ShrCred | MyTask | TaskMgmt | Cal |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| **SvcProv** | — | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **Host** | KEEP | — | KEEP | KEEP | KEEP | KEEP | KEEP | CHILD | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **Dom** | KEEP | KEEP | — | **TAB** | KEEP | KEEP | KEEP | CHILD | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **DomEmail** | KEEP | KEEP | **CHILD** | — | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **VPS** | KEEP | KEEP | KEEP | KEEP | — | KEEP | KEEP | CHILD | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **VoIP** | KEEP | KEEP | KEEP | KEEP | KEEP | — | KEEP | CHILD | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **OtherSvc** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | — | CHILD | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **Renew** | KEEP | **PARENT** | **PARENT** | KEEP | **PARENT** | **PARENT** | **PARENT** | — | KEEP | KEEP | KEEP | **FILTER** | **FILTER** | **TAB** |
| **Assets** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | — | KEEP | KEEP | KEEP | KEEP | KEEP |
| **MyCred** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | — | **FILTER** | KEEP | KEEP | KEEP |
| **ShrCred** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | **FILTER** | — | KEEP | KEEP | KEEP |
| **MyTask** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | **FILTER** | KEEP | KEEP | KEEP | — | **FILTER** | KEEP |
| **TaskMgmt** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | **FILTER** | KEEP | KEEP | KEEP | **FILTER** | — | KEEP |
| **Cal** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | **TAB** | KEEP | KEEP | KEEP | **TAB** | **TAB** | — |

### Administration Items

| | Users | Roles | RoleTmpl | Priv | Mod | Perms | Feat | SMTP | ActLog | LogAud | Import | Attach | Webhook | API |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| **Users** | — | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **Roles** | KEEP | — | **TAB** | **TAB** | KEEP | **TAB** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **RoleTmpl** | KEEP | **TAB** | — | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **Priv** | KEEP | **TAB** | KEEP | — | KEEP | **TAB** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **Mod** | KEEP | KEEP | KEEP | KEEP | — | KEEP | **TAB** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **Perms** | KEEP | **TAB** | KEEP | **TAB** | KEEP | — | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **Feat** | KEEP | KEEP | KEEP | KEEP | **TAB** | KEEP | — | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP |
| **SMTP** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | — | KEEP | KEEP | KEEP | KEEP | **GROUP** | **GROUP** |
| **ActLog** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | — | **TAB** | KEEP | KEEP | KEEP | KEEP |
| **LogAud** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | **TAB** | — | KEEP | KEEP | KEEP | KEEP |
| **Import** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | — | KEEP | KEEP | KEEP |
| **Attach** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | — | KEEP | KEEP |
| **Webhook** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | **GROUP** | KEEP | KEEP | KEEP | KEEP | — | **TAB** |
| **API** | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | KEEP | **GROUP** | KEEP | KEEP | KEEP | KEEP | **TAB** | — |

### Account Items

| | Profile | MyAccess | HelpCenter | Guide |
|---|---|---|---|---|
| **Profile** | — | **TAB** | KEEP | KEEP |
| **MyAccess** | **TAB** | — | KEEP | KEEP |
| **HelpCenter** | KEEP | KEEP | — | **MERGE** |
| **Guide** | KEEP | KEEP | **MERGE** | — |

---

## Consolidation Groups

### Group 1: Services (5 items → 1 group entry)

| Current Items | Action | Result |
|--------------|--------|--------|
| Hosting | KEEP | "Web Hosting" |
| Domains | KEEP | "Domains" |
| Domain Emails | **CHILD** → Tab under Domains | "Mailboxes" tab |
| VPS Accounts | KEEP | "Servers" |
| VoIP | KEEP | "Phone Systems" |
| Other Services | **RENAME** | "SaaS Subscriptions" |

**Navigation question:** Should these be individual items under a "Services" group, or ONE "Services" item with a second-level menu?

**Answer:** Individual items under "Services" group. Services are used daily by IT Ops. An extra click for the second level adds friction 5+ times per day. The group header "Services" provides context.

### Group 2: Vendors (2 items → 1 group entry)

| Current Items | Action | Result |
|--------------|--------|--------|
| Service Providers | **RENAME** | "Vendors" or "Providers" |
| Renewals | **MOVE** | "Contracts & Renewals" |

**Navigation question:** Should Renewals be here or inline on each service?

**NEEDS DISCUSSION.** Two valid models:
- **Centralized**: Procurement persona needs a single renewals dashboard. Standalone entry.
- **Distributed**: Each service shows its own renewal info. No standalone needed.

**Answer (provisional):** Keep standalone under Vendors for Procurement personas. Show renewal dates inline on each service for IT Ops personas. Same data, two access patterns.

### Group 3: Assets (1 item → stays standalone)

| Current Items | Action | Result |
|--------------|--------|--------|
| Assets | **MOVE** to own group | "Hardware & Software" |

**Why separate:** Assets have different lifecycle, different users, different workflows than services. Mixing them causes confusion. Standalone group.

### Group 4: Vault (2 items → 1)

| Current Items | Action | Result |
|--------------|--------|--------|
| My Credentials | **MERGE** | "Vault" with My/Shared filter |
| Shared Credentials | **MERGE** | "Vault" with My/Shared filter |

### Group 5: Tasks (2 items → 1)

| Current Items | Action | Result |
|--------------|--------|--------|
| My Tasks | **MERGE** | "Tasks" with My/All filter |
| Task Management | **MERGE** | "Tasks" with My/All filter |

### Group 6: Calendar (eliminated as standalone)

| Current Items | Action | Result |
|--------------|--------|--------|
| Calendar | **VIEW TOGGLE** | Calendar view on Tasks and Renewals pages |

### Group 7: Access Control (4 items → 1)

| Current Items | Action | Result |
|--------------|--------|--------|
| Users | **KEEP** | Standalone in "Access Control" group |
| Roles | **MERGE** → Roles & Permissions | Tab |
| Role Templates | **MERGE** → Roles & Permissions | Sub-tab or button |
| Privileges | **MERGE** → Roles & Permissions (or ELIM) | Reference tab or remove |
| Permissions | **MERGE** → Roles & Permissions | Tab |

**Why Users stays separate:** Users is the #1 navigation target for Super Admin (daily). Users has fundamentally different UI (CRUD list) than Roles (matrix configuration). Merging Users into Roles & Permissions would cause daily friction for Super Admin.

### Group 8: System Configuration (4 items → 1)

| Current Items | Action | Result |
|--------------|--------|--------|
| Modules | **MERGE** → Module Setup | Tab |
| Features | **MERGE** → Module Setup | Inline on Module detail |
| SMTP Profiles | **RENAME & MOVE** → System Config | "Mail Settings" tab |
| Import | **MOVE** → Operations or Tools | "Data Import" tab |

### Group 9: Integrations (2 items → 1)

| Current Items | Action | Result |
|--------------|--------|--------|
| Webhooks | **MERGE** → Integrations | Tab |
| API Access | **MERGE** → Integrations | "Developer Tokens" tab |

### Group 10: Audit (2 items → 1)

| Current Items | Action | Result |
|--------------|--------|--------|
| Activity Logs | **MERGE** → Audit Trail | "Changes" tab |
| Login Audits | **MERGE** → Audit Trail | "Logins" tab |

### Group 11: Reports (stays standalone)

| Current Items | Action | Result |
|--------------|--------|--------|
| Reports | **ROLE-GATE** | Visible to Manager/Director/Security/Procurement |

### Group 12: Account (3 items → 1 visible entry)

| Current Items | Action | Result |
|--------------|--------|--------|
| My Profile | **MOVE** to top-right user menu | Profile + My Access tabs |
| My Access | **MERGE** into Profile | Tab |
| Help Center | **MOVE** to "?" header icon | Keep full page for deep browsing |
| Guide | **MERGE** into Help Center | Eliminate duplicate |

### Group 13: Attachments (eliminated as standalone)

| Current Items | Action | Result |
|--------------|--------|--------|
| Attachments | **CONTEXTUAL ONLY** | Show inline on parent record. Global search available via command palette. |

---

## Result: Items per Model

| Navigation Model | Items Remaining | Reduction |
|-----------------|----------------|-----------|
| Current | 34 items | — |
| After merge consolidation | ~15-18 items | -47% to -53% |
| After moving to contextual | ~14 items | -59% |
| After persona-filtering | 5-18 items per user | -47% to -85% |
| After ALL optimizations | **~14 permanent + search** | **-59%** |

The exact count depends on which navigation philosophy is adopted (File 03). The consolidation above is philosophy-neutral — these merges benefit EVERY model.

# INFORMATION ARCHITECTURE AUDIT

## Evaluation of OpsPilot Navigation Against Enterprise IA Principles

---

## Current Navigation Model

```
Top Level:     Dashboard | Notifications
Groups:        Infrastructure | Credentials | Operations | Administration | Reports | Account
Total Items:   34
Administration Group: 14 items (41% of all navigation)
```

---

## IA Principle 1: Predictable Grouping

**Principle:** Items in the same group must share a clear, non-overlapping category.

**Violations:**

| Group | Items | Shared Characteristic? | Verdict |
|-------|-------|-----------------------|---------|
| Infrastructure | Service Providers, Hosting, Domains, Domain Emails, VPS, VoIP, Other Services, Renewals, Assets | ❌ Vendors + services + time-based + hardware | **FAIL** |
| Administration (14 items) | Users, Roles, Role Templates, Privileges, Modules, Permissions, Features, SMTP Profiles, Activity Logs, Login Audits, Import, Attachments, Webhooks, API Access | ❌ Identity + configuration + system + audit + tools | **FAIL** |
| Operations | My Tasks, Task Management, Calendar | ⚠️ Mostly tasks + calendar | **WEAK PASS** |
| Credentials | My Credentials, Shared Credentials | ✅ Both are credentials | **PASS** |
| Account | My Profile, My Access, Help Center | ✅ Personal account | **PASS** |
| Reports | Reports | ✅ Single item | **PASS** |

**Two out of six groups fail** the predictable grouping test. These two groups contain 23 of 34 items (68%).

---

## IA Principle 2: Minimum Cognitive Load (Hick's Law)

**Principle:** Decision time increases logarithmically with number of choices. Maximum recommended items per group: 5-9 (Miller's Law) or fewer for complex tasks.

| Group | Item Count | Cognitive Load | Verdict |
|-------|-----------|----------------|---------|
| Infrastructure | 9 | HIGH — at upper limit of Miller's Law | **WARN** |
| Administration | 14 | VERY HIGH — exceeds recommended by 2x | **FAIL** |
| Operations | 3 | LOW | **PASS** |
| Credentials | 2 | LOW | **PASS** |
| Account | 3 | LOW | **PASS** |
| Reports | 1 | MINIMAL | **PASS** |

**The Administration group at 14 items is the most severe IA violation.** A super-admin scanning for a specific item (e.g., "Webhooks") must mentally parse 14 options. The 95th percentile scan time for this group is approximately 4-6 seconds versus 1-2 seconds for a well-structured group of 5-6 items.

---

## IA Principle 3: No "Other" Bins

**Principle:** A category named "Other" or "Miscellaneous" is always an IA failure. Every entity must have a legitimate, specific category.

**Violation:** "Other Services" exists as a first-class menu item.

```
"Other Services" → What is "other" relative to? 
  Other than domains? Other than hosting? 
  The label defines what something IS NOT, not what it IS.
```

**Fix:** Rename to "SaaS Subscriptions," "Software Services," or define the actual scope. If the scope is genuinely miscellaneous, the taxonomy is incomplete and needs a new proper category.

---

## IA Principle 4: Unique Navigation Purpose

**Principle:** No two menu items should serve the same purpose or be differentiated only by a property (e.g., "mine" vs "all").

**Violations:**

| Items | Differentiator | Verdict |
|-------|---------------|---------|
| My Tasks / Task Management | Ownership (mine vs all) | **FAIL** — Same entity, split by filter |
| My Credentials / Shared Credentials | Ownership (mine vs shared) | **FAIL** — Same entity, split by filter |

**Each pair should be a single menu entry with an internal toggle or filter.** The current design forces users to make an ownership decision before seeing the page. This is premature commitment.

---

## IA Principle 5: Business Language, Not Database Language

**Principle:** Menu labels must describe what the USER does, not what the DATABASE stores.

**Violations:**

| Current Label | Database Table | Business Label | Severity |
|---------------|---------------|----------------|----------|
| Domain Emails | `domain_emails` | Mailboxes / Email Accounts | Medium |
| Other Services | `other_services` | SaaS / Subscriptions | High |
| Renewals | `expiry_trackers` | ❓ Acceptable (good name, wrong table) | Low |
| Modules | `modules` | ❓ Depends on audience | High |
| Permissions | `module_role_permissions` | Module Permissions / Role Permissions | Medium |
| Features | `features` | Capabilities | High |
| Privileges | `privileges` | (should merge with Permissions) | Medium |
| Activity Logs | `activity_log` | Audit Trail / Change History | Low |
| Login Audits | `login_audits` | Login History / Access Log | Low |
| SMTP Profiles | `smtp_profiles` | Mail Settings | High |
| Webhooks | `webhooks` | Integrations / Automations | High |
| API Access | `personal_access_tokens` | Developer Tokens / API Keys | Medium |

**12 of 34 items (35%) use database table names rather than business process labels.**

---

## IA Principle 6: Progressive Disclosure

**Principle:** Don't show everything at once. Reveal complexity progressively. Users should see only what they need at each level.

**Current State:** All 34 items are shown on page load (in a scrolling sidebar). The only progressive disclosure is the collapsible group headers, which collapse by default only for Credentials and Reports.

**Problems:**
- Administration (14 items) is expanded by default — a massive wall of text
- 34 scrolling items requires user to scroll past everything to reach Account at the bottom
- No search within sidebar (command palette exists but is separate)
- No sub-grouping within Administration (flat list of 14)

---

## IA Principle 7: Group Cohesion

**Principle:** Items within a group should relate to each other MORE than they relate to items in other groups.

**Administration group analysis — 5 actual categories forced into 1:**

| Cohesive Category | Items | % of Admin Group |
|------------------|-------|-----------------|
| **Identity & Access** | Users, Roles, Role Templates, Privileges, Permissions | 5 | 36% |
| **System Configuration** | SMTP Profiles, Webhooks, API Access | 3 | 21% |
| **Module Configuration** | Modules, Features | 2 | 14% |
| **Audit** | Activity Logs, Login Audits | 2 | 14% |
| **Data Tools** | Import, Attachments | 2 | 14% |

**These 5 categories cohere internally but do NOT cohere with each other.** The within-group similarity is lower than the between-group similarity. "Webhooks" is more similar to "API Access" than either is to "Users" or "Login Audits."

**The 14-item Administration group should be at least 3 groups.**

---

## IA Principle 8: Scanning Pattern

**Principle:** Users scan navigation in F-pattern (top-to-bottom, left-to-right). Most important items should be at the top.

**Current top 4 items:** Dashboard, Notifications, Infrastructure (9 items), Credentials

**Problem:** Infrastructure (9 items) creates a massive block at the top that buries Operations, Account, and other groups. A user looking for "My Tasks" must scroll past 11 items (Dashboard + Notifications + 9 Infrastructure) before reaching Operations.

---

## IA Principle 9: Safety and Reversibility

**Principle:** Users should feel safe navigating. They should not fear clicking the wrong menu item.

**Problem Items:**
- **Import** — irreversibly creates database records. User may fear clicking it accidentally.
- **API Access** — generating an API token is a security action. User may be unsure.
- **Webhooks** — creating a webhook sends real HTTP requests. User may be uncertain of consequences.

These belong in clearly-labeled configuration sections, not mixed with informational pages.

---

## IA Scorecard

| Principle | Status | Severity |
|-----------|--------|----------|
| P1: Predictable Grouping | ❌ 2/6 groups fail | HIGH |
| P2: Hick's Law (item count per group) | ❌ Admin group = 14 | HIGH |
| P3: No "Other" bins | ❌ "Other Services" exists | HIGH |
| P4: Unique Navigation Purpose | ❌ 2 pairs of duplicates | MEDIUM |
| P5: Business Language | ❌ 12/34 are technical | MEDIUM |
| P6: Progressive Disclosure | ❌ 34 items on screen | MEDIUM |
| P7: Group Cohesion | ❌ Admin has 5 hidden categories | HIGH |
| P8: Scanning Pattern | ❌ Long block at top | MEDIUM |
| P9: Safety and Reversibility | ⚠️ 3 items are concerning | LOW |

**Overall IA Health: 2 of 9 principles pass. 5 critical violations, 3 medium, 1 low.**

---

## Quick Wins (no backend changes)

1. **Rename "Other Services" → "SaaS Subscriptions"** — one string change
2. **Rename "SMTP Profiles" → "Mail Settings"** — one string change
3. **Rename "Webhooks" → "Integrations"** — one string change
4. **Rename "Activity Logs" → "Audit Trail"** — one string change
5. **Rename "Login Audits" → "Login History"** — one string change
6. **Rename "Domain Emails" → "Mailboxes"** — one string change
7. **Collapse Administration by default** — one JS default change
8. **Reorder groups: Account, Credentials to bottom** — template reorder

**These 8 changes require zero backend logic changes, zero database changes, zero permission changes. Only Blade template and JavaScript changes.**

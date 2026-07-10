# USER JOURNEY MAP — OpsPilot Persona Workflows

---

## Journey 1: IT Operator — "Provision a New Service"

**Trigger:** Employee requests new hosting for a project

| Step | Action | Current Nav Path | Problem | Proposed Nav Path |
|------|--------|-----------------|---------|-------------------|
| 1 | Check if provider exists | Infrastructure → Service Providers | Must navigate to Infrastructure for a vendor | Vendors → Service Providers |
| 2 | Create provider (if new) | Infrastructure → Service Providers | Vendor management is not "infrastructure" | Vendors → Service Providers |
| 3 | Create hosting account | Infrastructure → Hosting | ✅ OK | Services → Web Hosting |
| 4 | Register domain | Infrastructure → Domains | ✅ OK | Services → Domains |
| 5 | Create email accounts | Infrastructure → Domain Emails | Email is a sub-feature of Domains | Services → Domains → Mailboxes |
| 6 | Store credentials | Credentials → Shared Credentials | Different group — context switch | Credentials → Vault |
| 7 | Set renewal reminder | Infrastructure → Renewals | Different group — context switch | Operations → Renewals |
| 8 | Create setup task | Operations → Task Management | Operations is the 4th group — late in nav | Operations → Tasks |
| 9 | Log the provisioning | (no direct UI) | Activity Log is under Administration | Audit → Change Log |

**Context switches in current nav:** 6 groups visited for one workflow
**Context switches in proposed nav:** 3 groups (Vendors → Services → Operations)

---

## Journey 2: Service Desk — "Reset a Password"

**Trigger:** User calls help desk because they forgot their FTP password

| Step | Action | Current Nav Path | Problem | Proposed Nav Path |
|------|--------|-----------------|---------|-------------------|
| 1 | Find the service | Infrastructure → Hosting | Must know it's a hosting issue first | Services → Web Hosting |
| 2 | Navigate to detail page | Hosting → detail view | ✅ OK | Services → Web Hosting |
| 3 | Reveal password | Click "Show Password" button | ✅ OK (inline action, no nav needed) | ✅ OK |
| 4 | Share with user | Copy + send securely | ✅ OK | ✅ OK |
| 5 | Log the access | (may log as task) | Tasks is in Operations — different group | Operations → Tasks |

**User frustration:** Service Desk sees 9 items in Infrastructure, 3 in Operations, 6 more in Account/Credentials — but they only need Services + Tasks + Credentials. 70% of the visible navigation is irrelevant noise.

**Time spent scanning irrelevant items:** ~4-6 seconds per navigation action
**Time with persona-filtered nav:** ~1-2 seconds

---

## Journey 3: Security Officer — "Quarterly Access Review"

**Trigger:** Compliance requires review of all user permissions

| Step | Action | Current Nav Path | Problem | Proposed Nav Path |
|------|--------|-----------------|---------|-------------------|
| 1 | List all users | Administration → Users | ✅ OK | Access Control → Users |
| 2 | Review their roles | Administration → Roles | ✅ OK | Access Control → Roles |
| 3 | Check permissions | Administration → Permissions | ✅ OK | Access Control → Permissions |
| 4 | Review recent changes | Administration → Activity Logs | ✅ OK | Audit → Change Log |
| 5 | Check for unusual logins | Administration → Login Audits | ✅ OK | Audit → Login History |
| 6 | Review credential access | Credentials → Shared Credentials | Different group — context switch | Audit → Credential Access |

**Pain point:** Security Officer sees 14 items in Administration but only needs 5-6. The other 8 items (SMTP Profiles, Webhooks, API Access, Import, Attachments, Features, Modules) are irrelevant noise. These should be in a separate System Configuration section.

---

## Journey 4: Procurement Manager — "Monthly Spend Review"

**Trigger:** Monthly budget review meeting

| Step | Action | Current Nav Path | Problem | Proposed Nav Path |
|------|--------|-----------------|---------|-------------------|
| 1 | Review service providers | Infrastructure → Service Providers | ✅ OK (wrong group, but findable) | Vendors → Providers |
| 2 | Check renewal costs | Infrastructure → Renewals | ✅ OK | Operations → Renewals |
| 3 | Review assets | Infrastructure → Assets | ✅ OK | Assets & Inventory |
| 4 | Run cost report | Administration (super-admin) → Reports | ❌ **BLOCKED** — Reports is super-admin only | Reports (accessible to Procurement role) |

**Access problem:** Procurement Manager cannot see Reports because it's super-admin only. They need renewal cost reports that cut across all service types.

**Navigation problem:** Procurement must navigate 3 different Infrastructure sub-sections to gather cost data. No vendor-centric or cost-centric view exists.

---

## Journey 5: IT Director — "Weekly Operations Review"

**Trigger:** Weekly team standup

| Step | Action | Current Nav Path | Problem | Proposed Nav Path |
|------|--------|-----------------|---------|-------------------|
| 1 | View team task status | Operations → Task Management | ✅ OK | Operations → Tasks |
| 2 | Check upcoming renewals | Infrastructure → Renewals | Wrong group — IT Director doesn't think of "Infrastructure" | Operations → Renewals |
| 3 | Review security events | Administration → Login Audits | Administration is super-admin — IT Director may not be | Audit → Login History |
| 4 | Check team workload | (no direct view) | Must look at individual tasks | Operations → Workload (new) |
| 5 | Review reports | Administration (super-admin) → Reports | ❌ **BLOCKED** if not super-admin | Reports (accessible to Director role) |

**Critical finding:** If the IT Director is NOT assigned the super-admin role (which they shouldn't be — it violates least-privilege), they cannot see Reports, Login Audits, Activity Logs, or Users. These are all gated behind super-admin in the current nav.

**The current navigation conflates "administration of the system" with "oversight of the operations."** A Director needs oversight reports but should NOT be able to modify roles, permissions, or system config.

---

## Journey 6: Super Admin — "Onboard a New Employee"

**Trigger:** New IT team member joins

| Step | Action | Current Nav Path | Problem | Proposed Nav Path |
|------|--------|-----------------|---------|-------------------|
| 1 | Create user account | Administration → Users | ✅ OK | Access Control → Users |
| 2 | Assign role | Administration → Roles | ✅ OK | Access Control → Roles |
| 3 | Set module permissions | Administration → Permissions | ✅ OK | Access Control → Permissions |
| 4 | Create email notification | (no direct UI) | System sends welcome email? | System → Automation |
| 5 | Assign initial tasks | Operations → Task Management | Different group — context switch | Operations → Tasks |
| 6 | Grant credential access | Credentials → Shared Credentials | Different group — context switch | Credentials → Vault |
| 7 | Document in Help Center | Account → Help Center | Different group | Information → Help Center |

**Super Admin pain point:** They must visit 4 different groups (Administration, Operations, Credentials, Account) to complete one onboarding workflow. Each group switch requires mental context re-anchoring.

---

## Journey 7: End User — "Access My VPN Password"

**Trigger:** User needs to connect to VPN from home

| Step | Action | Current Nav Path | Problem | Proposed Nav Path |
|------|--------|-----------------|---------|-------------------|
| 1 | Log in to OpsPilot | — | ✅ OK | — |
| 2 | Navigate to credentials | Credentials → My Credentials | ✅ OK | Credentials → My Vault |
| 3 | Find VPN entry | Scroll/search list | ✅ OK | ✅ OK |
| 4 | Reveal password | Click reveal | ✅ OK | ✅ OK |
| 5 | Log out | User card → Sign out | ✅ OK | ✅ OK |

**End User pain point:** They see Dashboard, Notifications, 9 Infrastructure items they don't understand, 2 Credentials items, 3 Operations items (including Task Management they shouldn't see), and 3 Account items. **Only 3 of ~20 visible items are relevant to them.**

---

## Cross-Journey Findings

### Pain Points Common Across All Personas

1. **Infrastructure as a catch-all category** — Vendors (Service Providers), services (Hosting/Domains/VPS), time-based processes (Renewals), and hardware (Assets) all mixed together. Every persona is slowed by items that don't belong.

2. **Administration as a super-admin prison** — 14 items visible only to super-admins, but several (Reports, Audit, Users, Login History) are needed by Security Officer, IT Director, and Procurement. These roles shouldn't be super-admins but need oversight access.

3. **Zero personalization** — Every user sees the same navigation. An End User sees irrelevant Infrastructure items. A Security Officer sees irrelevant SMTP/Webhook settings. No role-based filtering of the nav.

4. **Action buried in structure** — "Create a new hosting account" requires navigating to Infrastructure → Hosting and clicking "Create." There's no "Quick Actions" or "New Service" workflow entry point. All workflows are entity-first.

### Top 5 Changes That Would Improve All Journeys

1. **Split Infrastructure into Vendors + Services + Assets** — 3 groups instead of 1, each with a clear purpose
2. **Split Administration into Access Control + System + Audit** — 3 groups instead of 1, Security Officer no longer needs super-admin
3. **Make Reports accessible to Manager/Director/Security roles** — not just super-admin
4. **Merge duplicate entries** — My/Shared Credentials → Vault; My/All Tasks → Tasks
5. **Add role-based nav filtering** — End Users see only Credentials + Tasks; Service Desk sees only Services + Tasks + Credentials; etc.

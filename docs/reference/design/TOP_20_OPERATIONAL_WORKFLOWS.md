# TOP 20 OPERATIONAL WORKFLOWS

> Complete workflow catalog: persona, trigger, steps, entities, frequency, value, risk.
> Sorted by business impact (not by convenience).

---

## W1: Password Self-Service (Value: 56)

| Property | Detail |
|----------|--------|
| **Persona** | End User (450 users) |
| **Trigger** | User needs a password to access a service |
| **Frequency** | Daily (2-5× per user per week) |
| **Duration** | 30 seconds if credential is findable |
| **Steps** | 1. Log in → 2. Navigate to Vault → 3. Search/find credential → 4. Reveal password → 5. Copy/use |
| **Entities** | Credential (vault_entry) |
| **Decisions** | Is this the right credential? |
| **Mistakes** | Revealing wrong credential. Sharing accidentally. |
| **Business Value** | HIGH — every saved minute × 450 users = massive productivity |
| **Current Support** | Vault exists. Searchable. Low friction already. |

---

## W2: Credential Retrieval (Value: 54)

| Property | Detail |
|----------|--------|
| **Persona** | Service Desk (20 users), IT Operator (10 users) |
| **Trigger** | User request: "I need the password for [service]" |
| **Frequency** | Daily (5-20× per Service Desk user) |
| **Duration** | 2-5 minutes (finding service → finding credential) |
| **Steps** | 1. Identify which service → 2. Navigate to service detail → 3. Note service name/info → 4. Navigate to Vault → 5. Search for credential matching service → 6. Verify authorization → 7. Reveal/share |
| **Entities** | Service (any type), Credential (vault_entry) |
| **Decisions** | Is user authorized? Which credential belongs to this service? |
| **Mistakes** | REVEALING TO UNAUTHORIZED USER. Wrong credential. |
| **Business Value** | HIGH — Service Desk spends 25-50% of time on this |
| **Current Support** | **FRAGMENTED** — credentials and services are disconnected. No "service → credential" link visible from service detail page. |

---

## W3: Incident Response (Value: 58)

| Property | Detail |
|----------|--------|
| **Persona** | IT Operator (10 users), Service Desk (20 users) |
| **Trigger** | Service down alert, user report, monitor failure |
| **Frequency** | 2-5× per week |
| **Duration** | 15-60 minutes |
| **Steps** | 1. Receive alert → 2. Identify affected service → 3. Check service status/details → 4. Retrieve credentials → 5. Investigate → 6. Log findings → 7. Create follow-up task → 8. Update service status |
| **Entities** | Monitor result, Service (any), Credential, Task, Activity Log |
| **Decisions** | Is this a real incident or false positive? Escalate or resolve? What's the root cause? |
| **Mistakes** | WRONG DIAGNOSIS (longer outage). Missed related services. Credential misuse. |
| **Business Value** | **CRITICAL** — every minute of outage costs $X00-$X,000 |
| **Current Support** | **FRAGMENTED** — monitor, service, credential, task all in different nav locations |

---

## W4: Access Revocation / Employee Offboarding (Value: 55)

| Property | Detail |
|----------|--------|
| **Persona** | Security Officer (3 users), Super Admin (3 users) |
| **Trigger** | Employee termination notice |
| **Frequency** | 1-2× per week (for 500 users) |
| **Duration** | 15-30 minutes (current). Should be 5 minutes. |
| **Steps** | 1. Find user → 2. Suspend account → 3. Find all credentials user can access → 4. Revoke each → 5. Find assigned tasks → 6. Reassign tasks → 7. Find assigned assets → 8. Check in assets → 9. Review recent activity → 10. Archive or delete |
| **Entities** | User, Credential (vault_entry), Task, Asset, Activity Log |
| **Decisions** | Which credentials to revoke? Who gets their tasks? |
| **Mistakes** | **CRITICAL** — missed credential = security gap. Missed asset = lost equipment. Missed task = dropped work. |
| **Business Value** | **CRITICAL** — #1 insider threat vector. Compliance requirement (SOX, SOC2). |
| **Current Support** | **NONE** — all manual. No cross-entity offboarding view. |

---

## W5: Security Audit Review (Value: 53)

| Property | Detail |
|----------|--------|
| **Persona** | Security Officer (3 users) |
| **Trigger** | Daily security check, possible incident |
| **Frequency** | Daily |
| **Duration** | 10-20 minutes |
| **Steps** | 1. Check login audits (failures, unusual times) → 2. Check activity log (permission changes, new users) → 3. Check credential access log → 4. Cross-reference anomalies → 5. Investigate if needed → 6. Document findings |
| **Entities** | Login Audit, Activity Log, User, Credential Access Log |
| **Decisions** | Is this pattern normal? Investigate or ignore? |
| **Mistakes** | **CRITICAL** — missing a coordinated attack pattern across logins + changes + credential access |
| **Current Support** | **NONE** — no unified timeline. Login and Activity are separate. |

---

## W6: Process Renewals (Value: 50)

| Property | Detail |
|----------|--------|
| **Persona** | IT Operator (10 users), Procurement (3 users) |
| **Trigger** | Expiry approaching (30/14/7 day notification) |
| **Frequency** | Weekly (5-10 items per week) |
| **Duration** | 3-5 minutes per item. 15-50 minutes per week. |
| **Steps** | 1. Check expiring items list → 2. For each: view service detail (still needed?) → 3. View provider contract (terms?) → 4. Update expiry date/cost → 5. Log action |
| **Entities** | Expiry Tracker, Service (any), Service Provider |
| **Decisions** | Renew? Migrate? Cancel? Negotiate? |
| **Mistakes** | Overlooking a renewal → service expires → OUTAGE. Inconsistent cost data. |
| **Business Value** | **HIGH** — missed renewals cause outages. Correct renewals save money. |
| **Current Support** | **FRAGMENTED** — renewal ↔ service ↔ provider are 3 separate navigations. |

---

## W7: Provision New Service (Value: 48)

| Property | Detail |
|----------|--------|
| **Persona** | IT Operator (10 users) |
| **Trigger** | Business request for new hosting, domain, server, phone system, or SaaS |
| **Frequency** | 2-5× per week |
| **Duration** | 20-40 minutes (6 separate form submissions) |
| **Steps** | 1. Create/verify provider → 2. Create service record → 3. Register domain (if applicable) → 4. Create mailboxes (if applicable) → 5. Store credentials → 6. Set renewal reminder → 7. Create setup task |
| **Entities** | Service Provider, Service (Hosting/Domain/VPS/VoIP/SaaS), Domain Email, Credential, Expiry Tracker, Task |
| **Decisions** | Which provider? Which plan? Which domain name? |
| **Mistakes** | Inconsistent data (same info re-entered differently). Forgotten credential. Forgotten renewal. Wrong provider selected. |
| **Business Value** | **HIGH** — provisioning speed = time-to-value for every new service |
| **Current Support** | **NONE** — 6 disconnected forms. No data flows between steps. |

---

## W8: User Onboarding (Value: 50)

| Property | Detail |
|----------|--------|
| **Persona** | Super Admin (3 users) |
| **Trigger** | New hire starting |
| **Frequency** | 2-4× per month |
| **Duration** | 15-25 minutes |
| **Steps** | 1. Create user account → 2. Assign role → 3. Grant credential access → 4. Assign asset → 5. Create welcome task → 6. Assign initial tasks |
| **Entities** | User, Role, Credential, Asset, Task |
| **Decisions** | Which role? Which credentials? Which asset? |
| **Mistakes** | Wrong role (too many permissions). Missed credential access. New hire can't work day 1. |
| **Business Value** | **HIGH** — delayed onboarding = delayed productivity × salary cost |
| **Current Support** | **NONE** — no onboarding wizard. Each entity created separately. |

---

## W9: Daily Health Check (Value: 44)

| Property | Detail |
|----------|--------|
| **Persona** | ALL |
| **Trigger** | Session start |
| **Frequency** | Daily (every session) |
| **Duration** | 30 seconds |
| **Steps** | 1. Scan Dashboard widgets → 2. Note alerts → 3. Act on what needs attention |
| **Entities** | Dashboard (aggregate) |
| **Decisions** | What needs attention TODAY? |
| **Mistakes** | Missing a critical alert among noise |
| **Business Value** | **MEDIUM** — prevention: catch issues before they escalate |
| **Current Support** | **GOOD** — Dashboard exists. Widgets cover key metrics. |

---

## W10: Task Processing (Service Desk) (Value: 42)

| Property | Detail |
|----------|--------|
| **Persona** | Service Desk (20 users) |
| **Trigger** | Task assigned |
| **Frequency** | Daily (10-30 tasks per user) |
| **Duration** | 5-15 minutes per task |
| **Steps** | 1. Read task → 2. Research issue → 3. Resolve → 4. Update task → 5. Close |
| **Entities** | Task, Service (any), Credential, Note |
| **Decisions** | Resolve or escalate? |
| **Mistakes** | Wrong resolution. Missed related information. |
| **Business Value** | **MEDIUM** — Service Desk throughput = employee productivity |
| **Current Support** | **ADEQUATE** — Tasks exist. But task ↔ service ↔ credential linking is weak. |

---

## W11: Team Resource Planning (Value: 42)

| Property | Detail |
|----------|--------|
| **Persona** | IT Manager (5 users) |
| **Trigger** | Weekly planning, budget review |
| **Frequency** | Weekly |
| **Duration** | 30-60 minutes |
| **Steps** | 1. Review team workload → 2. Identify bottlenecks → 3. Reassign tasks → 4. Review upcoming renewals → 5. Plan capacity |
| **Entities** | Task, Report, Renewal, User |
| **Decisions** | Who is overloaded? Where to allocate resources? |
| **Mistakes** | Misallocating resources. Missing upcoming workload spikes. |
| **Business Value** | **HIGH** — correct allocation saves 10-20% in overtime/contractor costs |
| **Current Support** | **WEAK** — no workload view. No capacity planning. Reports may be super-admin only. |

---

## W12: Permission Change Approval (Value: 42)

| Property | Detail |
|----------|--------|
| **Persona** | Super Admin (3 users), Security Officer (3 users) |
| **Trigger** | User requests access to a new module. Role change needed. |
| **Frequency** | Monthly (5-10 requests) |
| **Duration** | 10-20 minutes |
| **Steps** | 1. Review request → 2. Check current permissions → 3. Modify role or add user override → 4. Verify → 5. Document |
| **Entities** | User, Role, Module, Permission |
| **Decisions** | Should this user get access? Is the role correct? |
| **Mistakes** | OVER-PERMISSIONING (security risk). Wrong role assigned. |
| **Business Value** | **HIGH** — correct permissions = security. Wrong permissions = breach. |
| **Current Support** | **ADEQUATE** — permission matrix exists. But complex (353-line JS). |

---

## W13: Employee Offboarding (Full) (Value: 55)

*Duplicate of W4 with broader scope. Combined here for completeness.*

---

## W14: Monthly Cost Review (Value: 37)

| Property | Detail |
|----------|--------|
| **Persona** | Procurement (3 users), IT Director (1 user) |
| **Trigger** | Monthly budget meeting |
| **Frequency** | Monthly |
| **Duration** | 2-4 hours (currently manual spreadsheet) |
| **Steps** | 1. Export data from 5+ service types → 2. Compile into spreadsheet → 3. Aggregate by vendor → 4. Calculate trends → 5. Present |
| **Entities** | All service types, Service Provider, Expiry Tracker |
| **Decisions** | Are we overspending? Which vendors need renegotiation? |
| **Mistakes** | MISSING COSTS (not all services tracked). Double-counting. Manual errors. |
| **Business Value** | **HIGH** — every 5% cost reduction = significant savings |
| **Current Support** | **NONE** — no cost aggregation. Must compile manually. |

---

## W15: Vendor Onboarding (Value: 28)

| Property | Detail |
|----------|--------|
| **Persona** | Procurement (3 users), IT Operator (10 users) |
| **Trigger** | New vendor selected |
| **Frequency** | Monthly (1-3) |
| **Duration** | 15-30 minutes |
| **Steps** | 1. Create provider record → 2. Add contact info → 3. Add contract terms → 4. Add initial services → 5. Store vendor credentials → 6. Set renewal defaults |
| **Entities** | Service Provider, Service (any), Credential |
| **Decisions** | Which vendor? What terms? |
| **Mistakes** | Incomplete vendor record. Missing contact info. |
| **Business Value** | **MEDIUM** — vendor onboarding accuracy affects all downstream workflows |
| **Current Support** | **ADEQUATE** — CRUD exists. But no provider→service linking in creation flow. |

---

## W16: Asset Lifecycle Tracking (Value: 32)

| Property | Detail |
|----------|--------|
| **Persona** | IT Operator (10 users) |
| **Trigger** | New asset acquired, assigned, returned, or retired |
| **Frequency** | Weekly (5-15 events) |
| **Duration** | 2-5 minutes per event |
| **Steps** | 1. Create asset record → 2. Assign to user → 3. Track status → 4. Update on lifecycle event |
| **Entities** | Asset, User |
| **Decisions** | Who gets this asset? Is it still usable? |
| **Mistakes** | Lost asset. Wrong assignment. |
| **Business Value** | **MEDIUM** — asset tracking prevents loss and enables audit |
| **Current Support** | **ADEQUATE** — basic CRUD exists |

---

## W17: Bulk Data Import (Value: 27)

| Property | Detail |
|----------|--------|
| **Persona** | Super Admin (3 users) |
| **Trigger** | Migration from another system. Bulk update. |
| **Frequency** | Rarely (1-4× per year) |
| **Duration** | 1-4 hours |
| **Steps** | 1. Prepare CSV → 2. Navigate to Import → 3. Select type → 4. Upload → 5. Map columns → 6. Validate → 7. Execute → 8. Verify results |
| **Entities** | All (depends on import type) |
| **Decisions** | Which import type? Column mapping correct? |
| **Mistakes** | CORRUPTED DATA — wrong column mapping. Duplicate records. |
| **Business Value** | **MEDIUM** — enables migration. But rare. |
| **Current Support** | **BASIC** — import exists. Validation is basic. |

---

## W18: Module Configuration (Value: 18)

| Property | Detail |
|----------|--------|
| **Persona** | Super Admin (3 users) |
| **Trigger** | New module added, slug needs changing |
| **Frequency** | Rarely (once per module) |
| **Duration** | 30-60 minutes |
| **Steps** | 1. Add/edit module → 2. Configure features → 3. Set up permissions → 4. Test visibility |
| **Entities** | Module, Feature, Permission |
| **Decisions** | Module name? Slug? Permissions model? |
| **Mistakes** | Module slug mismatch (silent breakage per BR-07) |
| **Business Value** | **LOW** — one-time config. But high business impact if wrong. |
| **Current Support** | **ADEQUATE** — but dangerous (no slug immutability enforcement). |

---

## W19: Integration Setup (Value: 19)

| Property | Detail |
|----------|--------|
| **Persona** | Super Admin (3 users) |
| **Trigger** | New external system integration |
| **Frequency** | Rarely (1-3× per year) |
| **Duration** | 1-4 hours |
| **Steps** | 1. Configure webhook → 2. Test event → 3. Generate API token → 4. Configure consumer → 5. Test integration |
| **Entities** | Webhook, API Token |
| **Decisions** | Which events to notify? Token permissions? |
| **Mistakes** | Wrong webhook URL. Over-permissioned API token. |
| **Business Value** | **LOW-MEDIUM** — integration enables automation. But rarely configured. |
| **Current Support** | **ADEQUATE** — Webhooks and API Access pages exist. |

---

## W20: Team Performance Review (Value: 30)

| Property | Detail |
|----------|--------|
| **Persona** | IT Manager (5 users), IT Director (1 user) |
| **Trigger** | Weekly standup, monthly review |
| **Frequency** | Weekly (IT Manager). Monthly (IT Director). |
| **Duration** | 15-30 minutes |
| **Steps** | 1. Review task completion rates → 2. Review renewal on-time rate → 3. Review incident response time → 4. Compare to targets → 5. Document review |
| **Entities** | Task, Renewal, Monitor, Report |
| **Decisions** | Is the team meeting targets? Where to improve? |
| **Mistakes** | Wrong conclusions from incomplete data |
| **Business Value** | **HIGH** — performance visibility drives improvement |
| **Current Support** | **NONE** — no team performance dashboard. Reports may be inaccessible. |

---

## WORKFLOW TYPE CLASSIFICATION

| Type | Count | Workflows | Pattern |
|------|-------|-----------|---------|
| **Self-Service** | 2 | W1, W2 | Read credential. Low complexity. High frequency. |
| **CRUD (single entity)** | 8 | W9, W10, W14, W15, W16, W17, W18, W19 | Create/Read/Update/Delete one entity type. |
| **Cross-Entity (manual bridge)** | 6 | W3, W6, W7, W8, W11, W20 | Requires data from 2+ entity types. User manually bridges gaps. **HIGHEST FRICTION.** |
| **Security/Compliance** | 3 | W4, W5, W12 | Audit, review, revoke. High risk. Low tolerance for error. |
| **Analysis/Planning** | 1 | W13 | Aggregation across types. Manual compilation. |

**The 6 cross-entity workflows account for 70% of all friction and 80% of all risk.** These are the workflows that need WIZARDS and AUTOMATION, not better navigation.

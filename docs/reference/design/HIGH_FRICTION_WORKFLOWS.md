# HIGH-FRICTION WORKFLOWS

> Workflows where the most time is lost, mistakes occur, and user frustration accumulates.
> Each friction point is a candidate for wizard, automation, or dedicated dashboard.

---

## FRICTION #1: Employee Offboarding

### Current UX

```
Receive termination notice
       │
       ▼
Navigate to Users → Find user → Suspend
       │
       ▼
Navigate to Credentials → "Where was this user's access?" → No cross-reference
       │
       ▼
Navigate to Tasks → "Which tasks were assigned?" → Manual reassignment
       │
       ▼
Navigate to Assets → "Which laptop did they have?" → Manual check-in
       │
       ▼
Navigate to Activity Log → "What did they change recently?" → Manual review
       │
       ▼
Done? Probably missed something.
```

### Friction Score: 10/10

| Metric | Value |
|--------|-------|
| Page transitions | 5-7 |
| Entity types touched | 5+ |
| Context switches | 5 |
| Steps that can be forgotten | 4+ (credentials, assets, tasks, activity review) |
| Time to complete | 15-30 minutes |
| Cost of mistake | **SECURITY BREACH** — missed credential revocation |
| Frequency per 500 users | ~1/week |

### The Root Problem

**No cross-entity workflow.** Offboarding touches Users, Credentials, Tasks, Assets, Activity Log. These are 5 separate nav items across 3 groups (Administration, Credentials, Operations, Infrastructure). No single page shows "everything owned by this user."

### What a Wizard Would Look Like

```
[OFFBOARD USER: Sarah Jones]
┌────────────────────────────────────────────┐
│ ✅ Account suspended                       │
│ ────────────────────────────────────────── │
│ □ Credentials: 12 passwords to revoke     │  [Review]
│ □ Tasks: 5 tasks to reassign              │  [Review]
│ □ Assets: 1 laptop, 1 monitor to check in │  [Review]
│ □ Activity log: 47 changes this month     │  [Review]
│                                            │
│  [Complete Offboarding]                     │
└────────────────────────────────────────────┘
```

---

## FRICTION #2: Provision New Service

### Current UX

```
Need a new website
       │
       ▼
Check if provider exists → No? Create provider
       │
       ▼
Create hosting account → Fill in provider, plan, cost, IP
       │
       ▼
Register domain → Fill in registrar, expiry, cost
       │
       ▼
Create mailboxes → Fill in mailbox names, passwords
       │
       ▼
Store credentials → Fill in URLs, usernames, passwords
       │
       ▼
Set renewal reminder → Fill in dates, costs
       │
       ▼
Create task → "Set up completed"
```

### Friction Score: 9/10

| Metric | Value |
|--------|-------|
| Page transitions | 6 |
| Entity types touched | 6 |
| Context switches | 5 |
| Data re-entry | **SIGNIFICANT** — provider name, cost, dates entered 3+ times |
| Time to complete | 20-40 minutes |
| Cost of mistake | Inconsistent data (provider name spelled differently across entities) |
| Frequency per IT Ops | 2-5/week |

### The Root Problem

**No data flow between steps.** Creating a hosting account doesn't pre-fill the provider. Creating a domain doesn't link to the hosting account. Storing credentials doesn't reference the service. Each entity is an ISLAND. The user is the only integration layer — manually copying data between forms.

### What a Wizard Would Look Like

```
[NEW SERVICE: Web Hosting]
┌────────────────────────────────────────┐
│ Step 1: Select Provider                │
│ ○ Existing: Provider ABC              │
│ ○ New provider                        │
│                                        │
│ [Next]                                 │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│ Step 2: Service Details                │
│ Provider: Provider ABC (pre-filled)   │
│ Plan: [________]                       │
│ Cost: [________]                       │
│ IP: [________]                         │
│                                        │
│ Also register a domain? [Yes / No]    │
│ Also create mailboxes? [Yes / No]     │
│                                        │
│ [Next]                                 │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│ Step 3: Credentials                    │
│ Username: [________]                   │
│ Password: [Autogenerate] 🔒           │
│ URL: [pre-filled from IP/domain]      │
│                                        │
│ [Next]                                 │
└────────────────────────────────────────┘

┌────────────────────────────────────────┐
│ Step 4: Renewal Settings               │
│ Renewal date: [________]               │
│ Renewal cost: [pre-filled from step 2] │
│ Reminder: [30 days before]             │
│                                        │
│ Also create setup task? [Yes / No]    │
│                                        │
│ [Complete]                             │
└────────────────────────────────────────┘
```

---

## FRICTION #3: User Onboarding

### Current UX

```
New hire starts Monday
       │
       ▼
Create user account → Name, email, role
       │
       ▼
Assign role → Which role gives right permissions?
       │
       ▼
Assign credential access → Which vault entries?
       │
       ▼
Assign asset → Which laptop?
       │
       ▼
Create welcome task → "Show them around"
       │
       ▼
Assign initial tasks → What should they do day 1?
```

### Friction Score: 8/10

| Metric | Value |
|--------|-------|
| Page transitions | 5 |
| Entity types touched | 5 |
| Context switches | 4 |
| Steps that can be forgotten | 3+ (credential access, asset assignment, initial tasks) |
| Time to complete | 15-25 minutes |
| Cost of mistake | New hire can't work day 1 |
| Frequency per 500 users | 2-4/month |

### The Root Problem

**No onboarding checklist.** The super admin must remember every step. There's no "new employee" workflow — just disconnected CRUD operations across Users, Roles, Vault, Assets, Tasks.

---

## FRICTION #4: Security Audit Investigation

### Current UX

```
Possible breach detected
       │
       ▼
Check Login Audits → "Failed logins at 3 AM"
       │
       ▼
Check Activity Log → "What did this user change?"
       │
       ▼
Check User → "What role do they have?"
       │
       ▼
Check Credential Access → "What did they access?"
       │
       ▼
Check Related Services → "What services did they touch?"
       │
       ▼
No timeline view. No correlation. Manual cross-referencing.
```

### Friction Score: 8/10

| Metric | Value |
|--------|-------|
| Page transitions | 5+ |
| Entity types touched | 5 |
| Context switches | 4 |
| Time to complete | 10-30 minutes |
| Cost of mistake | **MISSING THE BREACH** — correlation is the hardest part |
| Frequency per 500 users | 1-3/month |

### The Root Problem

**No timeline view across entity types.** Login Audits and Activity Logs are separate pages. There's no "show me everything User X did between 2 AM and 4 AM" that spans logins AND changes AND credential access.

---

## FRICTION #5: Renewal Processing

### Current UX

```
Check what's expiring this week
       │
       ▼
Navigate to Renewals → See list of expiring items
       │
       ▼
For each item: Navigate to service detail → Check if still needed
       │
       ▼
Navigate to provider → Check contract terms
       │
       ▼
Navigate back to renewal → Update dates/costs
       │
       ▼
Repeat for next item... (5-10 items per week)
```

### Friction Score: 7/10

| Metric | Value |
|--------|-------|
| Page transitions per item | 3-4 |
| Items per batch | 5-10 |
| Total transitions per week | 15-40 |
| Time per item | 3-5 minutes |
| Total time per week | 15-50 minutes |
| Cost of mistake | Missed renewal = service outage |

### The Root Problem

**Renewal data is fragmented.** The Renewal record shows the expiry date but not the service details or provider contract terms. The user navigates in a triangle: Renewal → Service → Provider → Renewal. Each hop is a different page.

---

## FRICTION #6: Vendor Cost Comparison

### Current UX

```
Annual budget review
       │
       ▼
Navigate to Renewals → Export data
       │
       ▼
Nothing useful. Must manually compile.
       │
       ▼
Navigate to each Provider → Note costs
       │
       ▼
Navigate to each Service → Note costs
       │
       ▼
Build spreadsheet manually
```

### Friction Score: 6/10

| Metric | Value |
|--------|-------|
| Manual data aggregation | EXTREME |
| Time per review | 2-4 hours |
| Frequency | Monthly/quarterly |
| Accuracy risk | HIGH (manual data entry into spreadsheets) |

### The Root Problem

**No cost aggregation.** Costs are stored per-record across 5+ service types. There's no "cost by vendor" or "cost by month" report. Procurement must manually query each type and aggregate.

---

## FRICTION #7: Password Reset (Service Desk)

### Current UX

```
User calls: "I forgot my FTP password"
       │
       ▼
Find the service (Hosting) → Which hosting account?
       │
       ▼
Navigate to credential → Was it stored? Where?
       │
       ▼
Reveal password → Verify authorization
       │
       ▼
Share with user
```

### Friction Score: 5/10

| Metric | Value |
|--------|-------|
| Page transitions | 3-5 |
| Time per request | 3-5 minutes |
| Frequency | 5-20/day |
| Total time per day | 15-100 minutes (25-50% of Service Desk time) |

### The Root Problem

**Credentials are separate from services.** The password for a hosting account is not stored ON the hosting record. It's in the Vault. The Service Desk must navigate to the service FIRST (to find which hosting account), then to the Vault (to find its credential). If the credential isn't named after the hosting account, it's lost.

---

## FRICTION SUMMARY

| Rank | Workflow | Friction Score | Time Lost/Week | Risk | Current Tool Support |
|------|----------|---------------|---------------|------|---------------------|
| 1 | Employee Offboarding | 10/10 | 30 min | SECURITY BREACH | None (manual, 5+ stops) |
| 2 | Provision New Service | 9/10 | 2-4 hrs | Data inconsistency | None (6 separate forms) |
| 3 | User Onboarding | 8/10 | 1-2 hrs | New hire idle | None |
| 4 | Security Audit Investigation | 8/10 | 1-3 hrs | Missed breach | None (manual cross-ref) |
| 5 | Renewal Processing | 7/10 | 1-2 hrs | Service outage | Fragmented |
| 6 | Vendor Cost Comparison | 6/10 | 2-4 hrs/mo | Budget error | None (must export) |
| 7 | Password Reset | 5/10 | 3-15 hrs | Credential leak | Fragmented |

**Total time lost across all workflows:** ~10-25 hours per week across the organization.  
**Total risk exposure:** Unmitigated offboarding gaps, missed renewals, uninvestigated breaches.

**These 7 workflows represent the highest-ROI targets for product improvement.** Not navigation. Not menu labels. Workflow support.

# AUTOMATION OPPORTUNITIES

> Which workflows should be automated, which should be wizards, and which should remain CRUD.
> Based on workflow complexity, frequency, risk, and business value.

---

## Decision Framework

| Characteristic | CRUD | Wizard | Automation | Full Workflow Engine |
|---------------|------|--------|------------|---------------------|
| Single entity | ✅ | ❌ | ❌ | ❌ |
| Multiple entities, sequential | ❌ | ✅ | ❌ | ❌ |
| Multiple entities, conditional branching | ❌ | ✅ | ❌ | ✅ |
| Repetitive, predictable | ❌ | ❌ | ✅ | ✅ |
| High risk of human error | ❌ | ✅ | ✅ | ✅ |
| Requires approval | ❌ | ✅ | ❌ | ✅ |
| Infrequent, complex | ❌ | ✅ | ❌ | ❌ |
| Frequent, simple | ✅ | ❌ | ✅ | ❌ |
| Cross-entity data consistency required | ❌ | ✅ | ✅ | ✅ |

---

## Automation Candidates

### AUTOMATION #1: Provision New Service → Provisioning Wizard (HIGHEST PRIORITY)

| Property | Current | Proposed |
|----------|---------|----------|
| **Type** | 6 separate CRUD forms | **Multi-step wizard** |
| **Data flow** | None — user re-enters data | Provider/name/cost flows between steps |
| **Mistake risk** | Inconsistent data, forgotten steps | Guided, no forgotten steps |
| **Time** | 20-40 minutes | 10-15 minutes |
| **Effort** | 2 weeks | **Wizard implementation** |

**Wizard Steps:**

```
1. Select/Create Provider
2. Service Type (Hosting/Domain/VPS/VoIP/SaaS)
3. Service Details (pre-filled from provider + type template)
4. Optional: Create Domain + Mailboxes (auto-linked to service)
5. Auto-generate Credential (service name + random password)
6. Auto-set Renewal Reminder (service cost + 1 year from now)
7. Optional: Create Setup Task
```

**Automation Within Wizard:**
- Auto-generate strong password
- Auto-calculate renewal date (1 year from creation)
- Pre-fill provider details
- Auto-link credential to service record (not just free text)
- Auto-link expiry tracker to service record

---

### AUTOMATION #2: Employee Offboarding → Offboarding Dashboard (CRITICAL PRIORITY)

| Property | Current | Proposed |
|----------|---------|----------|
| **Type** | Manual 7-stop tour | **Single-page offboarding dashboard** |
| **Entity scan** | User must remember where everything is | System scans ALL user associations |
| **Mistake risk** | EXTREME — missed credential = breach | Checklist with completion tracking |
| **Time** | 15-30 minutes | 5 minutes |
| **Effort** | 2 weeks | **Dashboard implementation** |

**Dashboard Layout:**

```
┌──────────────────────────────────────────────────────┐
│  OFFBOARD: [User Name] — Status: IN PROGRESS         │
│  Termination date: [date]                            │
│                                                       │
│  □ Step 1: Suspend Account                  [DONE]   │
│  □ Step 2: Revoke Credentials (12 found)    [Review] │
│  □ Step 3: Reassign Tasks (5 found)         [Review] │
│  □ Step 4: Check In Assets (2 found)        [Review] │
│  □ Step 5: Review Activity Log              [Review] │
│  □ Step 6: Archive User Data                [Review] │
│                                                       │
│  [Complete Offboarding]   [Generate Report]           │
└──────────────────────────────────────────────────────┘
```

**Automation:**
- Scan all credential access for user → pre-populate revocation list
- Scan all tasks assigned to user → pre-populate reassignment list
- Scan all assets checked out to user → pre-populate check-in list
- Scan recent activity log → show for audit
- Generate offboarding report (PDF) for compliance records

---

### AUTOMATION #3: User Onboarding → Onboarding Wizard (HIGH PRIORITY)

| Property | Current | Proposed |
|----------|---------|----------|
| **Type** | 5 separate CRUD forms | **Multi-step wizard** |
| **Mistake risk** | Forgotten credential access, wrong role | Guided template-based setup |
| **Time** | 15-25 minutes | 8-12 minutes |
| **Effort** | 1-2 weeks | **Wizard implementation** |

**Wizard Steps:**

```
1. User Info (name, email, department)
2. Role Assignment (template with recommended permissions)
3. Credential Access (select from templates by role)
4. Asset Assignment (optional, from available pool)
5. Welcome Task (auto-generated)
6. Initial Tasks (from department template)
```

**Automation:**
- Role templates with pre-configured permission sets
- Credential access templates (by role: "Service Desk gets all shared, IT Ops gets admin credentials")
- Auto-generate welcome task
- Send welcome email
- Add to onboarding report

---

### AUTOMATION #4: Security Audit → Unified Timeline (HIGH PRIORITY)

| Property | Current | Proposed |
|----------|---------|----------|
| **Type** | 3 separate pages, manual cross-ref | **Single timeline view** |
| **Correlation** | Manual (mental) | Automatic (by user, time, action type) |
| **Time** | 10-20 minutes | 3-5 minutes |
| **Effort** | 1 week | **Timeline implementation** |

**Timeline View:**

```
┌─────────────────────────────────────────────────────────┐
│  SECURITY TIMELINE — Last 24 hours                      │
│  Filter: [User: Sarah Jones] [Type: All] [Date: ...]   │
│                                                          │
│  2:00 AM │ ⚠ LOGIN FAILURE  │ IP: 185.220.xxx.xxx     │
│          │ jsmith@example.com │ 3 attempts              │
│  2:05 AM │ ✓ LOGIN SUCCESS   │ IP: same               │
│          │ jsmith@example.com                            │
│  2:10 AM │ 🔑 CREDENTIAL     │ Vault: production-db    │
│          │ ACCESS │ jsmith                              │
│  2:15 AM │ 📝 PERMISSION     │ Role: admin → super-adm │
│          │ CHANGE │ by jsmith                           │
│  2:20 AM │ 📋 ATTACHMENT     │ File: db_export.csv     │
│          │ jsmith attached to Server-DB01               │
└─────────────────────────────────────────────────────────┘
```

**Automation:**
- Correlate login failures with subsequent credential access
- Flag anomalous patterns (login from new IP + credential access)
- Auto-generate investigation report
- Alert on specific patterns (off-hours access + permission change)

---

### AUTOMATION #5: Renewal Processing → Renewal Dashboard (HIGH PRIORITY)

| Property | Current | Proposed |
|----------|---------|----------|
| **Type** | 3-page triangle (renewal → service → provider) | **Single-pane renewal dashboard** |
| **Data density** | One renewal at a time | All renewals with service and provider info inline |
| **Time per item** | 3-5 minutes | 1-2 minutes |
| **Time per week** | 15-50 minutes | 5-15 minutes |
| **Effort** | 3 days | **Dashboard implementation** |

**Dashboard Layout:**

```
┌────────────────────────────────────────────────────────────────────┐
│  RENEWALS THIS MONTH                                               │
│  Total cost: $12,450                                               │
│                                                                     │
│  Service        │ Provider   │ Expires │ Cost │ Status │ Action   │
│ ────────────────────────────────────────────────────────────────── │
│  example.com    │ GoDaddy    │ Jul 15  │ $15  │ ✅ OK  │ Renew → │
│  acme-hosting   │ DigitalOcean│ Jul 22 │ $120  │ ⚠ Review│ Renew → │
│  vpn-prod       │ AWS        │ Jul 30  │ $450  │ ❓ Still needed?   │
│  phone-main     │ RingCentral│ Jul 31  │ $200  │ ✅ OK  │ Renew → │
│                                                                     │
│  [Process Selected] [Generate Cost Report]                          │
└────────────────────────────────────────────────────────────────────┘
```

**Automation:**
- Inline service name, provider, cost (no navigation)
- One-click renewal (extend date, log action, done)
- "Still needed?" flag for services with low activity
- Auto-reminder escalation (7 days → 1 day → expired)
- Cost forecast for next month/quarter

---

### AUTOMATION #6: Cost Aggregation → Cost Report (MEDIUM PRIORITY)

| Property | Current | Proposed |
|----------|---------|----------|
| **Type** | Manual spreadsheet compilation | **Auto-generated cost report** |
| **Data sources** | 5+ service types | All services with cost field |
| **Time** | 2-4 hours/month | 1 click |
| **Effort** | 2-3 days | **Report implementation** |

**Report Output:**

```
┌─────────────────────────────────────────────────┐
│  MONTHLY COST REPORT — July 2026                 │
│                                                   │
│  BY VENDOR:                                      │
│  DigitalOcean    │ $2,450 │ 6 services          │
│  AWS             │ $3,200 │ 12 services          │
│  GoDaddy         │ $450   │ 15 domains           │
│  RingCentral     │ $800   │ 3 accounts           │
│                                                   │
│  BY CATEGORY:                                    │
│  Hosting         │ $4,200 │ 15 accounts           │
│  Domains         │ $450   │ 45 domains            │
│  VPS             │ $1,800 │ 8 instances           │
│  SaaS            │ $3,100 │ 22 subscriptions     │
│                                                   │
│  TREND: +8% vs last month                        │
│  FORECAST: $11,500 next month                    │
└─────────────────────────────────────────────────┘
```

---

### AUTOMATION #7: Service-Credential Auto-Link (MEDIUM PRIORITY)

| Property | Current | Proposed |
|----------|---------|----------|
| **Service Desk password reset** | Navigate service → navigate vault → search | See credential link directly on service detail page |
| **Time per request** | 2-5 minutes | 30 seconds |
| **Time per day** | 15-100 minutes | 5-15 minutes |
| **Effort** | 2 days | **Add credential relation to service models** |

**Implementation:**
- Add `credential_id` or polymorphic `credentialable` to service models
- Show "🔑 Password" button on service detail page (linked to vault entry)
- Service desk sees password with ONE click, not 3 navigations

---

## Automation Priority Matrix

| # | Automation | Value | Effort | Risk Reduction | ROI |
|---|-----------|-------|--------|---------------|-----|
| 1 | Provisioning Wizard | 48 | 2 weeks | MEDIUM | **HIGHEST** |
| 2 | Offboarding Dashboard | 55 | 2 weeks | **CRITICAL** | **HIGHEST** |
| 3 | Onboarding Wizard | 50 | 1.5 weeks | MEDIUM | HIGH |
| 4 | Security Timeline | 53 | 1 week | **CRITICAL** | HIGH |
| 5 | Renewal Dashboard | 50 | 3 days | MEDIUM | HIGH |
| 6 | Cost Report | 37 | 2-3 days | LOW | MEDIUM |
| 7 | Service-Credential Link | 54 | 2 days | HIGH | **HIGHEST** (per-effort) |

**Note:** #7 (Service-Credential Link) has the highest ROI per development hour. A 2-day change eliminates 50% of Service Desk navigation time.

---

## What Should Stay CRUD

| Workflow | Why Not Automate |
|----------|-----------------|
| Module Configuration | Rare (once per module). Configuration decisions need human judgment. |
| Integration Setup | Rare (once per integration). Too variable for wizard. |
| Bulk Data Import | Rare. Tool exists. Good enough. |
| Asset Lifecycle | Simple single-entity CRUD. Acceptable friction. |
| Team Performance Review | Requires human judgment. Data aggregation can be automated (report). |
| Permission Change | Requires approval workflow. Current CRUD is sufficient for rare use. |

---

## Summary: The 7 Automations

```
┌─────────────────────────────────────────────────────────────────┐
│  7 AUTOMATIONS THAT TRANSFORM THE PRODUCT                       │
│                                                                  │
│  WIZARDS (guided multi-step):                                   │
│  1. Provision New Service Wizard          (2 weeks)             │
│  2. User Onboarding Wizard                (1.5 weeks)          │
│                                                                  │
│  DASHBOARDS (single-pane overviews):                            │
│  3. Employee Offboarding Dashboard        (2 weeks)             │
│  4. Renewal Dashboard                     (3 days)              │
│  5. Security Timeline                     (1 week)              │
│                                                                  │
│  REPORTS (auto-aggregated):                                     │
│  6. Cost Report                           (2-3 days)            │
│                                                                  │
│  DATA LINKING (cross-entity relations):                         │
│  7. Service-Credential Link               (2 days)              │
│                                                                  │
│  TOTAL EFFORT: ~7-8 weeks                                       │
│  TOTAL VALUE: 50+ hours/week saved + critical risk reduction   │
│  COMPARED TO NAVIGATION CHANGES: 10x more user impact           │
└─────────────────────────────────────────────────────────────────┘
```

**The single highest-leverage investment is not navigation redesign — it's workflow automation.** A provisioning wizard or offboarding dashboard delivers 10x the user value of any menu reorganization, because it eliminates entire classes of friction and risk rather than just making existing friction slightly faster to reach.

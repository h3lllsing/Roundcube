# DASHBOARD ENTRY POINTS

> Where every persona enters the product, what they see first, and what needs to change.
> Entry point design determines 80% of first-impression satisfaction and 50% of task-start speed.

---

## Current Entry Points

| Persona | Login Destination | First Thing They See | Problem |
|---------|------------------|---------------------|---------|
| **End User** (450) | Dashboard | 6 widgets (renewals, tasks, monitors, counts, etc.) | Dashboard is relevant but overcrowded for users who only need passwords |
| **Service Desk** (20) | Dashboard | Same 6 widgets | Widgets don't prioritize tickets/credential requests |
| **IT Operator** (10) | Dashboard | Same 6 widgets | Widgets missing: expiring services, monitor alerts |
| **IT Manager** (5) | Dashboard | Same 6 widgets | Widgets missing: team workload, performance metrics |
| **IT Director** (1) | Dashboard | Same 6 widgets | Widgets missing: cost trends, team KPIs, compliance status |
| **Security Officer** (3) | Dashboard | Same 6 widgets | Widgets missing: recent security events, failed logins, permission changes |
| **Procurement** (3) | Dashboard | Same 6 widgets | Widgets missing: upcoming renewals by cost, vendor summary |
| **Super Admin** (3) | Dashboard | Same 6 widgets | Needs everything + system health |

**Core problem: One-size dashboard fits nobody perfectly.**

---

## Widget Inventory (Current)

| Widget | Relevance by Persona | |
|--------|---------------------|---|
| **Renewals** | EndUser ✅ ServiceDesk ❌ ITOp ✅ ITMgr ✅ ITDir ✅ SecOff ❌ Proc ✅ SuperAdmin ✅ |
| **Tasks** | EndUser ✅ ServiceDesk ✅ ITOp ✅ ITMgr ✅ ITDir ❌ SecOff ❌ Proc ❌ SuperAdmin ❌ |
| **Monitors** | EndUser ❌ ServiceDesk ❌ ITOp ✅ ITMgr ✅ ITDir ❌ SecOff ❌ Proc ❌ SuperAdmin ✅ |
| **Total Credentials** | EndUser ❌ ServiceDesk ❌ ITOp ❌ ITMgr ❌ ITDir ❌ SecOff ❌ Proc ❌ SuperAdmin ❌ *(useless stat)* |
| **My Providers** | EndUser ❌ ServiceDesk ❌ ITOp ❌ ITMgr ❌ ITDir ❌ SecOff ❌ Proc ✅ SuperAdmin ❌ *(not detailed enough)* |
| **Calendar** | EndUser ❌ ServiceDesk ❌ ITOp ❌ ITMgr ❌ ITDir ❌ SecOff ❌ Proc ❌ SuperAdmin ❌ *(empty for most users)* |

**Useful widgets: 3 out of 6.** The other 3 are noise for nearly everyone.

---

## Proposed Entry Points by Persona

### END USER Dashboard
```
┌──────────────────────────────────────────────┐
│  Welcome back, [Name]                        │
│                                               │
│  ┌─────────────┐  ┌─────────────┐           │
│  │ QUICK ACCESS │  │ MY TASKS    │           │
│  │ (last 5 used │  │ (3 open)    │           │
│  │  credentials)│  │ - Reset MFA │           │
│  │              │  │ - Update DNS│           │
│  └─────────────┘  └─────────────┘           │
│                                               │
│  [Search for a credential...]                 │
└──────────────────────────────────────────────┘
```
- **Primary action**: Search/access credentials (80% of use)
- **Secondary**: View tasks
- **Removed**: Monitors, providers, calendar, total count
- **New**: Quick Access (most recently used credentials)

---

### SERVICE DESK Dashboard
```
┌──────────────────────────────────────────────────────────┐
│  TICKETS TODAY: 12       Avg resolution: 14min          │
│                                                            │
│  ┌────────────────────┐  ┌─────────────────────┐        │
│  │ OPEN TASKS         │  │ RECENT ACTIONS      │        │
│  │ (8 assigned to me) │  │ - Sarah → pass reset │        │
│  │ #8743 - MFA reset  │  │ - John → cred req   │        │
│  │ #8742 - new hire   │  │ - Bob → acc unlock  │        │
│  │ #8741 - VPN issue  │  │                      │        │
│  └────────────────────┘  └─────────────────────┘        │
│                                                            │
│  ┌────────────────────────────────────────┐               │
│  │ 🔑 CREDENTIAL REQUEST QUEUE (5 pending)│               │
│  │ [Approve] [Deny] for each               │               │
│  └────────────────────────────────────────┘               │
└──────────────────────────────────────────────────────────┘
```
- **Primary action**: View open tasks + handle credential requests
- **Secondary**: Track daily metrics (tickets resolved, avg time)
- **Removed**: Monitors, providers, calendar, renewals
- **New**: Credential Request Queue (direct approval from dashboard)

---

### IT OPERATOR Dashboard
```
┌──────────────────────────────────────────────────────────────┐
│  SYSTEM HEALTH                                              │
│  ✅ All 12 monitors OK    ⚠ 1 expiring in 7 days          │
│                                                                │
│  ┌──────────────────┐  ┌──────────────────┐                 │
│  │ ACTIVE MONITORS  │  │ EXPIRING SOON    │                 │
│  │ Web Server  - OK │  │ example.com (7d) │                 │
│  │ Database   - OK  │  │ vpn-prod   (14d) │                 │
│  │ Mail       - WARN│  │ phone-main (21d) │                 │
│  └──────────────────┘  └──────────────────┘                 │
│                                                                │
│  ┌────────────────────────────────────────┐                   │
│  │ PROVISIONING QUEUE (2 pending)          │                   │
│  │ [New Hosting] [New Domain] [New VPS]    │                   │
│  └────────────────────────────────────────┘                   │
└──────────────────────────────────────────────────────────────┘
```
- **Primary action**: Check system health + handle expirations
- **Secondary**: Provision new services
- **Removed**: Total credentials, calendar, tasks unrelated to ops
- **New**: Provisioning quick-start buttons

---

### IT MANAGER Dashboard
```
┌──────────────────────────────────────────────────────────────┐
│  TEAM OVERVIEW — This Week                                    │
│                                                                │
│  ┌────────────────────┐  ┌────────────────────┐             │
│  │ TASKS COMPLETED    │  │ RENEWALS THIS MONTH│             │
│  │ This week: 87      │  │ Total: $12,450     │             │
│  │ Prev week: 92      │  │ On track: 6/8      │             │
│  │ Trend: -5% ▼       │  │ Trend: 0%          │             │
│  └────────────────────┘  └────────────────────┘             │
│                                                                │
│  ┌────────────────────┐  ┌────────────────────┐             │
│  │ TEAM WORKLOAD      │  │ INCIDENT RESPONSE  │             │
│  │ Sarah: 12 tasks    │  │ Avg time: 22min    │             │
│  │ John:  18 tasks 🔴 │  │ Target: 15min      │             │
│  │ Bob:    8 tasks    │  │ SLA: 90% on target  │             │
│  └────────────────────┘  └────────────────────┘             │
└──────────────────────────────────────────────────────────────┘
```
- **Primary action**: Team performance monitoring + budget overview
- **Secondary**: Capacity planning
- **Removed**: Individual credentials, monitors, calendar
- **New**: Team workload + performance trend

---

### IT DIRECTOR Dashboard
```
┌──────────────────────────────────────────────────────────────┐
│  ORGANIZATION OVERVIEW — July 2026                           │
│                                                                │
│  ┌────────────────────┐  ┌────────────────────┐             │
│  │ MONTHLY COST       │  │ COMPLIANCE STATUS  │             │
│  │ $11,500 total      │  │ ✅ SOC2            │             │
│  │ -8% vs last month ▼│  │ ✅ SOX             │             │
│  │ $138k annualized   │  │ ✅ Audits current   │             │
│  └────────────────────┘  └────────────────────┘             │
│                                                                │
│  ┌────────────────────┐  ┌────────────────────┐             │
│  │ TEAM KPIS          │  │ SERVICE HEALTH     │             │
│  │ Task completion: 94%│  │ 45 services        │             │
│  │ On-time renewals: 89%│  │ 3 incidents (30d) │             │
│  │ Avg response: 18min│  │ uptime: 99.97%     │             │
│  └────────────────────┘  └────────────────────┘             │
└──────────────────────────────────────────────────────────────┘
```
- **Primary action**: High-level KPIs + cost overview
- **Secondary**: Compliance check
- **Removed**: Individual tasks, credentials, monitors
- **New**: Cost trend, compliance, team KPIs

---

### SECURITY OFFICER Dashboard
```
┌──────────────────────────────────────────────────────────────┐
│  SECURITY OVERVIEW — Last 24h                                │
│                                                                │
│  ┌────────────────────┐  ┌────────────────────┐             │
│  │ LOGIN EVENTS       │  │ PERMISSION CHANGES │             │
│  │ 145 total          │  │ 2 changes          │             │
│  │ 3 failed           │  │ jsmith → super-adm │             │
│  │ 5 from new IPs 🔴  │  │ bjones → admin     │             │
│  └────────────────────┘  └────────────────────┘             │
│                                                                │
│  ┌────────────────────┐  ┌────────────────────┐             │
│  │ OFFBOARDING QUEUE  │  │ RECENT ALERTS      │             │
│  │ 2 pending          │  │ Off-hours login    │             │
│  │ Sarah M. - today   │  │ Bulk credential    │             │
│  │ John D. - tomorrow │  │   access (12 in 5m)│             │
│  └────────────────────┘  └────────────────────┘             │
│                                                                │
│  [View Full Timeline →]                                      │
└──────────────────────────────────────────────────────────────┘
```
- **Primary action**: Security event monitoring + offboarding oversight
- **Secondary**: Permission change review
- **Removed**: Tasks, renewals, calendar, providers
- **New**: Login events summary, offboarding queue, alerts

---

### PROCUREMENT Dashboard
```
┌──────────────────────────────────────────────────────────────┐
│  PROCUREMENT OVERVIEW — July 2026                            │
│                                                                │
│  ┌────────────────────┐  ┌────────────────────┐             │
│  │ RENEWALS THIS MONTH│  │ VENDOR SUMMARY     │             │
│  │ 8 renewals         │  │ 12 active vendors  │             │
│  │ $12,450 total      │  │ $138k annual spend │             │
│  │ 2 pending review   │  │ Avg contract: $350 │             │
│  └────────────────────┘  └────────────────────┘             │
│                                                                │
│  ┌────────────────────────────────────────┐                   │
│  │ EXPIRING IN 30 DAYS                      │                   │
│  │ Service      │ Cost  │ Vendor  │ Action  │                   │
│  │ example.com  │ $15   │ GoDaddy │ [Review]│                   │
│  │ acme-hosting │ $120  │ DO      │ [Review]│                   │
│  │ vpn-prod     │ $450  │ AWS     │ [Review]│                   │
│  └────────────────────────────────────────┘                   │
└──────────────────────────────────────────────────────────────┘
```
- **Primary action**: Renewal review + vendor cost tracking
- **Secondary**: Contract management
- **Removed**: Tasks, monitors, credentials, calendar
- **New**: Vendor summary, cost summary, inline action buttons

---

### SUPER ADMIN Dashboard
```
┌──────────────────────────────────────────────────────────────┐
│  SYSTEM ADMIN — All Systems                                   │
│                                                                │
│  ┌────────────────────┐  ┌────────────────────┐             │
│  │ SYSTEM HEALTH      │  │ USER MANAGEMENT    │             │
│  │ ✅ All systems OK  │  │ 500 users          │             │
│  │ Memory: 68%        │  │ 3 pending approvals│             │
│  │ Disk:  45%         │  │ 2 roles changed    │             │
│  └────────────────────┘  └────────────────────┘             │
│                                                                │
│  ┌────────────────────┐  ┌────────────────────┐             │
│  │ PENDING TASKS      │  │ RECENT ACTIVITY    │             │
│  │ 5 user onboardings │  │ New user created   │             │
│  │ 2 mass imports     │  │ Module config chng │             │
│  │ 1 integration setup│  │ Permissions update │             │
│  └────────────────────┘  └────────────────────┘             │
│                                                                │
│  [Manage Modules →]  [Import Data →]  [System Config →]      │
└──────────────────────────────────────────────────────────────┘
```
- **Primary action**: System health + pending approvals
- **Secondary**: User management + configuration
- **Removed**: Individual credentials, personal tasks, calendar
- **New**: System health, pending approvals queue, config shortcuts

---

## Entry Point Goals

| Persona | Entry Goal | Success Metric |
|---------|-----------|---------------|
| End User | Get password in under 10 seconds | Time-to-credential |
| Service Desk | See open tickets + credential requests immediately | Time-to-first-task |
| IT Operator | See system health + expirations immediately | Time-to-alert-recognition |
| IT Manager | See team performance at a glance | Time-to-status-assessment |
| IT Director | See org-level KPIs + costs | Time-to-overview |
| Security Officer | See security events + offboarding queue | Time-to-alert-recognition |
| Procurement | See renewals + vendor costs | Time-to-renewal-review |
| Super Admin | See system health + pending approvals | Time-to-pending-action |

**Each dashboard must deliver its persona's primary goal within 3 seconds of login.**

---

## Implementation Approach

**Phase A: Widget Filtering (1-2 days)**
- Keep current dashboard engine
- Allow per-role visibility of widgets
- Remove irrelevant widgets per persona
- Add 1-2 new widgets per persona using existing data

**Phase B: Quick Actions (2-3 days)**
- Add "Quick Actions" widget to relevant dashboards
- Service Desk: "Credential Request Queue" inline approval
- IT Operator: "Provision New Service" button
- Super Admin: "Pending Approvals" + config shortcuts
- End User: "Quick Access" recent credentials

**Phase C: Role-Specific Dashboards (1-2 weeks)**
- Build persona-specific dashboards with unique widgets
- Each dashboard is a view (not separate page) — same URL, different content
- Fallback: "Showing default. Want a different layout?" link

**Phase D: Configurable Layout (future)**
- Allow drag-and-drop widget positioning
- Allow widget selection (within persona constraints)
- Add hide/show toggle for each widget

---

## What Each Persona Loses and Gains

| Persona | Loses (irrelevant widgets) | Gains |
|---------|---------------------------|-------|
| End User | Monitors, Providers, Calendar, Total Count | Quick Access (recent creds) |
| Service Desk | Renewals, Monitors, Providers, Calendar, Total Count | Credential Request Queue |
| IT Operator | Total Credentials, Calendar, Tasks (personal) | Provisioning buttons, Expiry alerts |
| IT Manager | Credentials, Calendar | Team workload, trends, KPIs |
| IT Director | Individual tasks, credentials, monitors, calendar | Cost, compliance, org KPIs |
| Security Officer | Tasks, renewals, providers, calendar | Security events, offboarding queue |
| Procurement | Tasks, monitors, credentials, calendar | Vendor summary, inline review |
| Super Admin | Personal tasks, calendar | System health, pending queue, shortcuts |

**Each persona loses 3-5 irrelevant widgets and gains 2-4 targeted ones.**
**Net: every dashboard becomes useful instead of wallpaper.**

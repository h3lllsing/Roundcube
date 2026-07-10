# WORKFLOW VALUE ANALYSIS

> Every persona traced from first action to last.
> Workflow value measured in business outcomes, not page views.

---

## Persona 1: IT Operator (5-15 users)

### Morning Workflow: Daily Health Check

| Step | Action | Information Needed | Decision | Object | Time |
|------|--------|-------------------|----------|--------|------|
| 1 | Check Dashboard for alerts | Expiring services, failed monitors, assigned tasks | What needs attention TODAY? | Dashboard | 30s |
| 2 | Review expiring services | Which services expire in 7/30/60 days | Which today? Which this week? | Renewals list | 2min |
| 3 | Check monitor failures | Which services are down | Is this a real outage or false positive? | Monitor results | 1min |
| 4 | Review assigned tasks | Task list, priority, deadline | Which task first? | Tasks | 1min |

**Transition count:** 4 pages. **Context switches:** 3 (health → expiry → incidents → tasks).  
**Time lost:** None — this is the daily triage workflow.  
**Mistakes:** Missing a critical expiry because it wasn't on the Dashboard.  

### Mid-Morning Workflow: Process Renewals

| Step | Action | Decision | Objects |
|------|--------|----------|---------|
| 1 | Open expiring item | Is this worth renewing? | Renewal record |
| 2 | View service details | What provider, plan, cost? | Hosting/Domain/VPS |
| 3 | Check provider contact | Who to call/email? | Service Provider |
| 4 | Process renewal (extend date) | New expiry date, cost | ExpiryTracker |
| 5 | Create follow-up task | Who needs to know? | Task |

**Transition count:** 5-7 pages. **Context switches:** 3 (renewal → service → provider → renewal → task).  
**Time lost:** Navigating between service detail and renewal record (not linked on same page).  
**Mistakes:** Renewing a service that was already cancelled (stale data).  

### High-Value Workflow: Respond to Service Incident

| Step | Action | Decision | Risk |
|------|--------|----------|------|
| 1 | Receive notification/task | Is this urgent? | Delayed response = longer outage |
| 2 | Locate affected service | Which hosting/domain/VPS? | Wrong service = wrong fix |
| 3 | Retrieve credentials | Who has access? | Credential exposure |
| 4 | Investigate/log issue | Is this a provider issue? | Wrong diagnosis = wasted time |
| 5 | Create resolution task | Who needs to follow up? | Lost follow-up |

**Transition count:** 4-6 pages. **Context switches:** 4 (notification → service → credentials → task).  
**Time lost:** Switching between credential retrieval and service management (different groups in nav).  
**Mistakes:** Using wrong credentials (credential-retrieval UX). Missing the root cause because related services aren't linked.  

### Complex Workflow: Provision New Service

| Step | Entity | Group | Action |
|------|--------|-------|--------|
| 1 | Service Provider | Vendors | Create if new |
| 2 | Hosting/Domain/VPS/VoIP | Services | Create the service |
| 3 | Domain Email | Services | Create mailboxes (if domain) |
| 4 | Credential | Vault | Store passwords |
| 5 | Expiry Tracker | Renewals | Set renewal reminder |
| 6 | Task | Operations | Create setup task |

**Transition count:** 6 pages. **Context switches:** 5.  
**Time lost:** 6 separate form submissions across 3+ nav groups. No data flows between steps (must re-enter provider name, service name, etc.).  
**Mistakes:** Forgetting a step (no credential stored → password lost). Entering inconsistent data (provider name spelled differently).  
**Business value:** HIGH — provisioning speed directly affects time-to-value for new services.  

---

## Persona 2: IT Manager (2-5 users)

### Morning Workflow: Team Operations Review

| Step | Action | Information | Decision |
|------|--------|-------------|----------|
| 1 | Check Dashboard | Team task status, renewal forecast | Is the team on track? |
| 2 | Review all tasks | Workload distribution, overdue items | Who needs support? |
| 3 | Check renewal forecast | Upcoming costs by month | Any budget surprises? |
| 4 | Review activity log | Recent changes by team | Any unauthorized changes? |

**Transition count:** 4 pages. **Context switches:** 3.  
**Time lost:** No unified "team view." Must switch between Dashboard, Tasks, Renewals, Activity Log.  
**Mistakes:** Missing an overdue renewal because it wasn't surfaced to manager view.  

### Weekly Workflow: Resource Planning

| Step | Action | Objects |
|------|--------|---------|
| 1 | Run workload report | Reports → Tasks |
| 2 | Identify bottlenecks | Reports → Tasks by user |
| 3 | Review vendor spend | Reports → Providers |
| 4 | Adjust team priorities | Task reassignment |

**Transition count:** 3-4 pages. **Context switches:** 2.  
**Time lost:** Reports may be inaccessible (super-admin only). Must export data manually.  
**Business value:** HIGH — correct resource planning saves 10-20% in operational costs.  

---

## Persona 3: Service Desk Technician (5-20 users)

### Morning Workflow: Task Processing

| Step | Action | Objects |
|------|--------|---------|
| 1 | Check My Tasks | Assigned tasks |
| 2 | Read task details | Task description, priority |
| 3 | Research issue | Related service, credentials |
| 4 | Resolve or escalate | Task resolution |
| 5 | Close task | Task status update |

**Transition count:** 3-5 pages per task. **Context switches:** 2-3.  
**Time lost per task:** 30-60 seconds navigating between task page and related service/credentials.  
**Tasks per day:** 10-30. **Time lost per day:** 5-30 minutes.  

### High-Volume Workflow: Credential Retrieval

| Step | Action | Decision |
|------|--------|----------|
| 1 | Find the service | Which service has the password? |
| 2 | Navigate to credential | Shared or My? |
| 3 | Reveal password | Is this user authorized? |
| 4 | Share with requester | Secure channel? |

**Transition count:** 2-3 pages. **Context switches:** 1-2.  
**Frequency:** Multiple times daily.  
**Risk:** Revealing credential to unauthorized user.  
**Business value:** MEDIUM — directly affects user productivity (users can't work without passwords).  

---

## Persona 4: Security Officer (1-3 users)

### Morning Workflow: Security Review

| Step | Action | Information | Decision |
|------|--------|-------------|----------|
| 1 | Review login audits | Failed logins, unusual times/locations | Investigate? |
| 2 | Check activity log | Permission changes, new users | Any unauthorized changes? |
| 3 | Review credential access | Who accessed what? | Any policy violations? |
| 4 | Check user list | New/suspended/active | Any stale accounts? |

**Transition count:** 4 pages. **Context switches:** 3.  
**Time lost:** Login Audits and Activity Logs in different places. Must cross-reference manually.  
**Mistakes:** Missing correlation between login failure and subsequent privilege escalation.  
**Business value:** EXTREMELY HIGH — preventing a breach is worth 1000x the tooling cost.  

### High-Risk Workflow: Access Revocation (Offboarding)

| Step | Action | Objects |
|------|--------|---------|
| 1 | Find user | Users |
| 2 | Review role assignments | Roles |
| 3 | Check credential access | Vault |
| 4 | Revoke credentials | Vault (per-credential) |
| 5 | Reassign/archive tasks | Tasks |
| 6 | Check asset assignments | Assets |
| 7 | Suspend user | Users |

**Transition count:** 5-7 pages. **Context switches:** 4-5.  
**Risk:** EXTREME — missing a credential revocation creates a security gap. Each missed step is a potential breach.  
**Time lost:** No "offboarding workflow." Must manually navigate 5+ sections and remember every step.  
**Business value:** CRITICAL — missed offboarding is the #1 source of insider threat incidents.  

---

## Persona 5: Procurement (1-3 users)

### Weekly Workflow: Renewal Processing

| Step | Action | Decision |
|------|--------|----------|
| 1 | Check upcoming renewals | Any this week? |
| 2 | Review each service | Is this still needed? |
| 3 | Check vendor contract | Pricing, terms |
| 4 | Approve or negotiate | Renew with same vendor? |
| 5 | Update renewal dates | New expiry, cost |

**Transition count:** 3-5 per renewal. **Context switches:** 2-3.  
**Time lost:** No "renewal with cost forecast" view. Must navigate between Renewal, Service, and Provider records manually.  
**Mistakes:** Renewing a service that could have been consolidated. Missing a better price from another vendor.  

---

## Persona 6: IT Director (1 user)

### Weekly Workflow: Strategic Review

| Step | Action | Decision |
|------|--------|----------|
| 1 | Dashboard | Is everything healthy? |
| 2 | Reports | What are the trends? |
| 3 | Team metrics | Is the team productive? |
| 4 | Budget review | Are we on track? |

**Transition count:** 2-3 pages per week. **Context switches:** 1-2.  
**Time lost:** Low (uses system infrequently).  
**Risk:** Reports may not show director-level KPIs. May need to request data from IT Manager.  

---

## Persona 7: Super Admin (1-3 users)

### Daily Workflow: User Management

| Step | Action | Frequency |
|------|--------|-----------|
| 1 | Check new user requests | Daily |
| 2 | Create accounts | Weekly |
| 3 | Assign roles | Weekly |
| 4 | Handle permission changes | Weekly |
| 5 | Audit recent activity | Weekly |

**Transition count:** 2-4 pages. **Context switches:** 2-3.  
**Time lost:** User CRUD involves Users → Roles → Permissions. No onboarding wizard.  

### Rare Workflow: Module Configuration

| Step | Action | Frequency |
|------|--------|-----------|
| 1 | Add/edit module | Once per module |
| 2 | Configure features | Once per module |
| 3 | Set permissions | Once per module |
| 4 | Test visibility | Once per module |

**Frequency:** Once per module (rare). **Complexity:** Medium.  

---

## Persona 8: End User (450-480 users)

### Daily Workflow: Self-Service

| Step | Action | Frequency |
|------|--------|-----------|
| 1 | Check tasks | Daily |
| 2 | Access credential | Daily-weekly |
| 3 | Complete task | Daily |

**Transition count:** 2-3 pages. **Context switches:** 1.  
**Time lost:** Minimal — this workflow is already simple.  
**Business value:** HIGH — every minute 450 users save is 450 person-minutes/day.  

---

## WORKFLOW VALUE RANKING

| Rank | Workflow | Persona | Frequency | Business Value | Current Friction | Risk | Complexity |
|------|----------|---------|-----------|---------------|-----------------|------|-----------|
| 1 | Access Revocation (Offboarding) | Security, Super Admin | Weekly | CRITICAL | EXTREME | EXTREME | HIGH |
| 2 | User Onboarding | Super Admin, IT Ops | Weekly | VERY HIGH | HIGH | HIGH | HIGH |
| 3 | Provision New Service | IT Ops | Weekly | HIGH | EXTREME | MEDIUM | HIGHEST |
| 4 | Process Renewals | IT Ops, Procurement | Weekly | HIGH | HIGH | MEDIUM | MEDIUM |
| 5 | Security Audit Review | Security Officer | Daily | CRITICAL | HIGH | EXTREME | HIGH |
| 6 | Incident Response | IT Ops, Service Desk | Daily | VERY HIGH | HIGH | HIGH | MEDIUM |
| 7 | Credential Retrieval | Service Desk, End User | Daily | HIGH | LOW | HIGH | LOW |
| 8 | Task Processing | Service Desk | Daily | MEDIUM | MEDIUM | LOW | LOW |
| 9 | Team Resource Planning | IT Manager | Weekly | HIGH | HIGH | MEDIUM | MEDIUM |
| 10 | Monthly Cost Review | Procurement, IT Director | Monthly | HIGH | HIGH | LOW | MEDIUM |
| 11 | Employee Offboarding (full) | Super Admin, Security | Weekly | CRITICAL | EXTREME | EXTREME | HIGHEST |
| 12 | Daily Health Check | ALL | Daily | MEDIUM | LOW | LOW | LOW |
| 13 | Vendor Onboarding | Procurement, IT Ops | Monthly | MEDIUM | MEDIUM | LOW | MEDIUM |
| 14 | Module Configuration | Super Admin | Rarely | LOW | LOW | MEDIUM | HIGH |
| 15 | Bulk Data Import | Super Admin | Rarely | MEDIUM | MEDIUM | HIGH | MEDIUM |
| 16 | Integration Setup | Super Admin | Rarely | LOW | LOW | MEDIUM | HIGH |
| 17 | Permission Change Approval | Super Admin, Security | Monthly | HIGH | MEDIUM | HIGH | MEDIUM |
| 18 | Asset Lifecycle Tracking | IT Ops | Weekly | MEDIUM | MEDIUM | LOW | LOW |
| 19 | Password Self-Service | End User | Daily | HIGH | LOW | MEDIUM | LOW |
| 20 | Team Performance Review | IT Manager, IT Director | Monthly | HIGH | MEDIUM | LOW | MEDIUM |

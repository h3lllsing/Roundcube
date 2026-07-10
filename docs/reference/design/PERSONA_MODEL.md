# PERSONA MODEL — OpsPilot v1.0

## 8 Personas Identified

---

## Persona 1: IT Operator

**Role:** System Administrator / Infrastructure Engineer
**Count in 500-user org:** ~5-15

| Question | Answer |
|----------|--------|
| **1. Daily goal** | Keep services running. Provision new resources. Respond to incidents. |
| **2. First screen** | Dashboard (check health, expiring items, recent tasks assigned to me) |
| **3. Top 5 menu items** | Dashboard → Domains → Hosting → VPS → Credentials |
| **4. Should NEVER see** | Role Templates, Privileges, Modules, Features, Reports (unless they're also super-admin) |
| **5. Information they care about** | Service status, expiry dates, credentials, provider contact info, cost, IP addresses, DNS records |
| **6. Decisions they make** | Which provider to use for new hosting; when to renew vs. migrate; whether to upgrade server specs; who gets credential access |
| **7. Reports they need** | Expiring services (next 30/60/90 days); services by provider; credential age report |
| **8. KPIs they monitor** | Number of expiring services; number of active/inactive services; uptime (via monitor checks) |
| **9. Notifications they expect** | Expiring services; monitor check failures; task assignments; credential shared with them |
| **10. Daily workflow** | Morning: check Dashboard for expiry alerts and new notifications → check assigned tasks → process renewals → respond to service requests → provision new resources → update credentials → end of day: log completed tasks |

---

## Persona 2: IT Manager

**Role:** Operations Team Lead / Infrastructure Manager
**Count in 500-user org:** ~2-5

| Question | Answer |
|----------|--------|
| **1. Daily goal** | Oversee team workload. Ensure renewals are on track. Review operational health. |
| **2. First screen** | Dashboard (team task status, renewal forecast, recent changes) |
| **3. Top 5 menu items** | Dashboard → Tasks (all) → Renewals → Calendar → Activity Log |
| **4. Should NEVER see** | Privileges, Role Templates, Modules, Features, SMTP Profiles, Webhooks, API Access |
| **5. Information they care about** | Team task completion rate; upcoming renewal costs; total spend per vendor; who changed what and when |
| **6. Decisions they make** | Which renewals to approve; team task prioritization; vendor consolidation; budget allocation |
| **7. Reports they need** | Renewal forecast (cost by month); team workload distribution; vendor spend summary; change history |
| **8. KPIs they monitor** | Renewals on time percentage; tasks completed per week; total monthly spend; services per provider |
| **9. Notifications they expect** | Task assignments; renewal reminders (30/14/7 day); large changes by team members; new services created |
| **10. Daily workflow** | Morning: review Dashboard renewal forecast → check team task board → approve/reassign tasks → review any priority changes from yesterday → check monitor alerts → weekly: run vendor spend report → monthly: team performance review |

---

## Persona 3: Service Desk Technician

**Role:** Help Desk / Support Analyst
**Count in 500-user org:** ~5-20

| Question | Answer |
|----------|--------|
| **1. Daily goal** | Resolve employee requests quickly. Find credentials. Create and close tasks. |
| **2. First screen** | Tasks (assigned to me) |
| **3. Top 5 menu items** | My Tasks → Credentials → Notifications → Dashboard → Calendar |
| **4. Should NEVER see** | Administration (all 14 items), Reports, Modules, Features, Role Templates, Privileges, Webhooks, API Access, Activity Logs, Login Audits |
| **5. Information they care about** | Credentials for services; task details and priority; who to contact for which service; service status |
| **6. Decisions they make** | Which credentials to reveal; whether to escalate to IT Operator; task priority and deadline |
| **7. Reports they need** | My closed tasks count; credential access log (who accessed what) |
| **8. KPIs they monitor** | Tasks resolved per day; average resolution time; credential access requests |
| **9. Notifications they expect** | New task assignments; task priority changes; @mentions in tasks; credential shared with them |
| **10. Daily workflow** | Morning: check My Tasks for new assignments → process highest-priority tasks → handle credential access requests as they come in → update task statuses → close completed tasks → log any service issues as tasks for IT Operators |

---

## Persona 4: Security Officer

**Role:** IT Security / Compliance Manager
**Count in 500-user org:** ~1-3

| Question | Answer |
|----------|--------|
| **1. Daily goal** | Ensure access is appropriate. Detect unauthorized changes. Review credential hygiene. |
| **2. First screen** | Login Audits (recent access attempts) |
| **3. Top 5 menu items** | Login History → Audit Trail → Users → Credentials → Roles |
| **4. Should NEVER see** | Hosting, Domains, VPS, VoIP, Other Services, Assets, Service Providers, Calendar, Import, Attachments, Help Center |
| **5. Information they care about** | Who logged in and when; who changed what; who has access to which credentials; role assignments; permission changes |
| **6. Decisions they make** | Whether to revoke a user's access; whether a change requires investigation; credential rotation policy enforcement |
| **7. Reports they need** | Access review report (per-user); permission change audit; credential age report; login failure summary; role assignment changes |
| **8. KPIs they monitor** | Number of failed logins; credential age (stale passwords); permission changes per week; user accounts with super-admin access |
| **9. Notifications they expect** | Failed login attempts (threshold exceeded); role/permission changes; new users created; credential revealed by unauthorized user |
| **10. Daily workflow** | Morning: review Login History for unusual patterns → check Audit Trail for recent changes → review any flagged credential accesses → weekly: run access review report → review role assignments for appropriateness → investigate any security events |

---

## Persona 5: Procurement / Vendor Manager

**Role:** IT Procurement / Vendor Relations
**Count in 500-user org:** ~1-3

| Question | Answer |
|----------|--------|
| **1. Daily goal** | Track vendor contracts. Manage renewals. Control IT spend. |
| **2. First screen** | Renewals (upcoming renewal costs) |
| **3. Top 5 menu items** | Renewals → Service Providers → Dashboard → Reports → Calendar |
| **4. Should NEVER see** | My Tasks, Task Management (unless assigned), VPS details, VoIP details, Credentials vault entries, all Administration items except Reports |
| **5. Information they care about** | Renewal dates and costs; provider contract terms; total spend per vendor; service count per provider |
| **6. Decisions they make** | Which contracts to renew; whether to negotiate better pricing; vendor consolidation decisions; budget forecasting |
| **7. Reports they need** | Renewal forecast (next 12 months by month); vendor spend summary; service count by provider; contract expiry timeline |
| **8. KPIs they monitor** | Total monthly IT spend; renewals coming due; cost per service type; number of providers |
| **9. Notifications they expect** | Renewal reminders (60/30/14/7 days); new services added under a provider; cost changes |
| **10. Daily workflow** | Morning: check Renewals for upcoming expirations → review costs → contact providers for quotes → update renewal dates after confirming → weekly: run vendor spend report → monthly: update budget forecast → quarterly: vendor consolidation review |

---

## Persona 6: IT Director / CIO

**Role:** IT Leadership
**Count in 500-user org:** ~1

| Question | Answer |
|----------|--------|
| **1. Daily goal** | Strategic oversight. Budget approval. Team performance review. |
| **2. First screen** | Dashboard (high-level metrics) |
| **3. Top 5 menu items** | Dashboard → Reports → Notifications → Calendar → Users |
| **4. Should NEVER see** | Individual service details (specific domains, hosting, VPS, VoIP), Credentials vault entries, Import, Attachments, Webhooks, API Access |
| **5. Information they care about** | Team productivity; total IT spend; security posture; upcoming major renewals; project progress |
| **6. Decisions they make** | Budget approval; staffing decisions; strategic vendor selection; policy changes |
| **7. Reports they need** | Executive summary (all KPIs); spend trend; team task completion trend; security incident summary; service inventory |
| **8. KPIs they monitor** | Total IT operational cost; renewal on-time rate; team task completion rate; security incidents; service availability |
| **9. Notifications they expect** | Only critical: major security events; budget deviations; team lead escalations; system down alerts |
| **10. Daily workflow** | Morning: scan Dashboard for anomalies → review critical notifications → check team lead updates → weekly: review Reports for trends → monthly: budget review meeting → quarterly: strategic planning |

---

## Persona 7: Super Admin / System Owner

**Role:** OpsPilot system administrator
**Count in 500-user org:** ~1-3

| Question | Answer |
|----------|--------|
| **1. Daily goal** | Configure the system. Manage users and permissions. Ensure system health. |
| **2. First screen** | Dashboard (system health, recent activity) |
| **3. Top 5 menu items** | Users → Roles & Permissions → Activity Log → SMTP / Mail Settings → Notifications |
| **4. Should NEVER see** | (As super-admin, they TECHNICALLY can access everything — but the navigation should hide irrelevant items to reduce cognitive load) |
| **5. Information they care about** | User accounts; permission assignments; system configuration; module status; activity log |
| **6. Decisions they make** | Who gets which role; which modules are active; system configuration changes; access policy |
| **7. Reports they need** | User activity report; permission audit; system configuration report |
| **8. KPIs they monitor** | Active users; permission changes; system errors; module usage |
| **9. Notifications they expect** | New user registrations; permission escalation requests; system errors; failed scheduled tasks |
| **10. Daily workflow** | Morning: check for new user requests → process permission changes → review system health → respond to configuration requests → audit recent changes → end of day: verify scheduled tasks completed |

---

## Persona 8: Regular Employee / End User

**Role:** Non-IT employee using the system for task management and credential access
**Count in 500-user org:** ~450-480

| Question | Answer |
|----------|--------|
| **1. Daily goal** | Access their passwords. Submit IT requests. View their tasks. |
| **2. First screen** | My Credentials or My Tasks |
| **3. Top 5 menu items** | My Credentials → My Tasks → Notifications → Dashboard → Help Center |
| **4. Should NEVER see** | All Infrastructure items (Hosting, Domains, VPS, VoIP, etc.), Service Providers, Renewals, Assets, Task Management (all tasks), Administration (all), Reports, Calendar |
| **5. Information they care about** | Their own passwords; their assigned tasks; company service directory |
| **6. Decisions they make** | Which password to use; when to complete a task |
| **7. Reports they need** | None — they're not analyzing data |
| **8. KPIs they monitor** | Their own task completion |
| **9. Notifications they expect** | Task assigned to them; credential shared with them; password expiry warning |
| **10. Daily workflow** | Check notifications → access needed credential → view assigned tasks → complete tasks → occasionally search Help Center for guides |

---

## Persona-Navigation Matrix

| Menu Item | IT Ops | IT Mgr | Service Desk | Security | Procurement | IT Director | Super Admin | End User |
|-----------|--------|--------|-------------|----------|-------------|-------------|-------------|----------|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Notifications | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Hosting | ✅ | ✅ | ✅ | ❌ | ✅* | ❌ | ❌ | ❌ |
| Domains | ✅ | ✅ | ✅ | ❌ | ✅* | ❌ | ❌ | ❌ |
| VPS / Servers | ✅ | ✅ | ✅ | ❌ | ✅* | ❌ | ❌ | ❌ |
| VoIP | ✅ | ✅ | ✅ | ❌ | ✅* | ❌ | ❌ | ❌ |
| Service Providers | ✅ | ✅ | ❌ | ❌ | ✅ | ✅* | ❌ | ❌ |
| Renewals | ✅ | ✅ | ❌ | ❌ | ✅ | ✅ | ❌ | ❌ |
| Assets | ✅ | ✅ | ✅ | ❌ | ✅* | ✅* | ❌ | ❌ |
| SaaS (Other) | ✅ | ✅ | ❌ | ❌ | ✅* | ❌ | ❌ | ❌ |
| Credentials | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| Tasks | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ |
| Calendar | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ |
| Help Center | ✅ | ✅ | ✅ | ❌ | ✅ | ✅ | ✅ | ✅ |
| Users | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Roles & Permissions | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ |
| System Settings | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| Audit Trail | ❌ | ✅* | ❌ | ✅ | ❌ | ❌ | ✅ | ❌ |
| Login History | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ✅* | ❌ |
| Reports | ❌ | ✅ | ❌ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Profile | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| My Access | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

*✅* = read-only or limited view

---

## Key Insight

**The current navigation serves only 2 of 8 personas well:** IT Operator and Super Admin. The other 6 personas are forced to navigate through irrelevant items to find what they need. A persona-based navigation would serve EVERYONE better by showing only what's relevant to their role.

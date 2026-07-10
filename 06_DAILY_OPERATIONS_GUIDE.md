# Daily Operations Guide

> **Audience:** All Operators (IT Staff, Administrators) — **Purpose:** Execute daily, weekly, and monthly operational procedures

## Table of Contents

- [Morning Checklist](#morning-checklist)
- [Daily Work Procedures](#daily-work-procedures)
- [End-of-Day Checklist](#end-of-day-checklist)
- [Weekly Procedures](#weekly-procedures)
- [Monthly Renewal Management](#monthly-renewal-management)
- [Incident Response Procedures](#incident-response-procedures)

---

## Morning Checklist

### Purpose
Start each day by reviewing the current state of operations and identifying items that need attention.

### When to Use
First thing at the start of every shift.

### Permission Required
View on Dashboard, Tasks, and Calendar modules (varies by role).

### Step-by-Step Workflow

**Step 1: Dashboard Review**
1. Log into OpsPilot
2. Check the Dashboard widgets for anything unusual:

   | Widget | What to Look For |
   |--------|------------------|
   | **Operations** | Sudden changes in service counts (unexpected drops may indicate deleted records) |
   | **Renewals** | Services expiring within 30 days that need action |
   | **Tasks** | New tasks assigned overnight, overdue tasks |
   | **Assets** | Recent assignments or returns that may affect your day |
   | **Quick Actions** | Shortcuts to create new records for today's onboarding |

**Step 2: Calendar Check**
1. Go to **Operations → Calendar**
2. Review today's date for expiring services
3. Note any critical renewals due this week
4. Check task due dates for today

**Step 3: Notification Review**
1. Click the notification bell
2. Read all unread notifications (expiry reminders, task assignments)
3. Mark notifications as read after reviewing
4. Take immediate action on any urgent notifications (e.g., high-priority task assignment)

**Step 4: Task Review**
1. Go to **My Tasks**
2. Review all tasks assigned to you
3. Prioritize: Urgent > High > Medium > Low
4. Check for overdue tasks — these should be your first priority
5. Start the highest-priority task and update its status to **In Progress**

### Best Practices
- Complete the checklist in order — each step builds on the previous one
- Spend no more than 5-10 minutes on the morning review
- The Calendar is your best tool for spotting upcoming work at a glance
- Mark notifications as read immediately after acting — keeps the badge meaningful

### Common Mistakes
- Skipping the Calendar check — you may miss expiring services
- Only checking My Tasks — team tasks in your modules also need attention
- Ignoring notifications — they often contain task assignments and expiry alerts
- Spending too long on review — the checklist is a scan, not a deep dive

### Typical Business Scenario
**Monday morning:** You log in, check the Dashboard (3 domains expiring this week, 5 tasks assigned), review the Calendar (tomorrow: 2 hosting renewals due), read notifications (a new task was assigned overnight), and update your highest-priority task to In Progress. Total time: 5 minutes.

### Expected Result
You have a clear picture of what needs attention today. You have prioritized your work and started on the most important task.

---

## Daily Work Procedures

### Client Onboarding Procedure

**Purpose:** Set up a new client's services in the correct sequence.

**When to Use:** A new client or service needs to be provisioned.

**Permission Required:** Create on the relevant modules.

#### Step-by-Step Workflow

1. **Create Service Provider** (Service Providers → Create)
   - Enter provider name, contact, account credentials
   - This is needed first so it appears in dropdown menus

2. **Register the Domain** (Domains → Create)
   - Enter domain name, link to provider
   - Set registration and expiry dates

3. **Set Up Hosting** (Hosting → Create)
   - Link to domain and provider
   - Enter server credentials (encrypted on save)

4. **Create Email Accounts** (Domain Emails → Create)
   - Link to domain and hosting
   - Set mailbox passwords

5. **Provision VPS** (VPS → Create) — if applicable
   - Enter VPS specs, IP, SSH credentials

6. **Configure VoIP** (VoIP → Create) — if applicable
   - Set up main account and extensions

7. **Create Expiry Trackers** (Expiry Trackers → Create)
   - One tracker per renewable service
   - Configure notification days and recipients

8. **Verify All Records**
   - Search for the client's domain
   - Confirm all related records appear and are linked correctly

#### Best Practices
- Follow the sequence — each step depends on the previous one
- Use consistent Module assignment across all records
- Test credentials by revealing and logging in after creation

#### Common Mistakes
- Creating services before the Service Provider record
- Skipping Expiry Trackers — they will not get reminders
- Mixing different clients in the same Module

---

### Record Update Procedure

**Purpose:** Keep service records current and accurate.

**When to Use:** Service details change, credentials rotate, or corrections are needed.

**Permission Required:** Edit on the relevant module.

#### Step-by-Step Workflow

1. Find the record (search or browse)
2. Open the detail page
3. Click **Edit**
4. Update the relevant fields:

   | Update Type | What to Change |
   |-------------|----------------|
   | **Expiry date after renewal** | Update the date in the service record |
   | **Password rotation** | Enter the new password (encrypted on save) |
   | **Status change** | Update to Active, Suspended, Cancelled, etc. |
   | **Provider change** | Select the new provider |
   | **Correction** | Fix any incorrect data |

5. Click **Save**
6. If applicable, update the linked Expiry Tracker's expiry date

#### Best Practices
- Update records IMMEDIATELY — do not rely on memory
- After changing credentials, verify by revealing and testing
- Add a Note explaining what changed and why

#### Common Mistakes
- Updating a password in the system but not on the actual service
- Forgetting to update linked Expiry Trackers after a renewal

---

### Password Management Procedure

**Purpose:** Securely reveal and update service credentials.

**When to Use:** Client requests credentials, password rotation is due, or troubleshooting requires access.

**Permission Required:** Reveal on the module (to view), Edit (to change).

#### Step-by-Step Workflow

**Revealing a password:**
1. Open the service record's detail page
2. Click the **Reveal** icon next to the password field
3. The decrypted password is displayed
4. Use the password for your task
5. All reveals are automatically logged in Activity Logs

**Changing a password:**
1. Open the service record
2. Click **Edit**
3. Enter the new password
4. Click **Save** — the password is encrypted immediately

#### Best Practices
- Reveal only when necessary — each reveal is audited
- Change passwords immediately after sharing them externally
- Use the Copy button if available to avoid displaying the password on screen
- Never share passwords via unencrypted channels

#### Common Mistakes
- Revealing a password and writing it down
- Not changing a password after sharing it
- Reaching the rate limit (10 reveals per minute)

---

### Task Management Procedure

**Purpose:** Create, track, and complete tasks throughout the day.

**When to Use:** Work needs to be assigned, tracked, or documented.

**Permission Required:** Create, Edit on Tasks module.

#### Step-by-Step Workflow

**Creating a task:**
1. Go to **Operations → Tasks**
2. Click **Create Task**
3. Enter: title, description, module link, assignee, priority, due date
4. Click **Save** — assigned users receive a notification

**Updating task status:**
1. Open the task
2. Change status: **Pending → In Progress → Completed**
3. Optionally add a note about what was done

**Using the Kanban board:**
1. Go to **Tasks → Kanban**
2. Drag tasks between columns to update status
3. Changes save automatically

#### Best Practices
- Update status immediately when starting or finishing work
- Always link tasks to a Module for context
- Use Kanban for a visual overview of team workload

#### Common Mistakes
- Leaving tasks in Pending after starting work
- Creating tasks without an assignee
- Not linking to a Module

---

## End-of-Day Checklist

### Purpose
Close out the day by confirming all work is captured and nothing is overlooked.

### When to Use
At the end of each workday, before logging out.

### Permission Required
View on the relevant modules.

### Step-by-Step Workflow

**Step 1: Finalize Tasks**
1. Go to **My Tasks**
2. Mark any completed tasks as **Completed**
3. Update status on tasks you started but did not finish (leave as In Progress)
4. Verify no tasks are stuck without a status update

**Step 2: Verify New Records**
1. Review any records you created today
2. Confirm:
   - All required fields are filled
   - Module assignment is correct
   - Service Provider is linked (if applicable)
   - Expiry dates are set

**Step 3: Review Tomorrow's Calendar**
1. Go to **Operations → Calendar**
2. Check tomorrow's expiry dates and task due dates
3. Note any early-morning items that need attention

**Step 4: Final Notification Check**
1. Click the notification bell
2. Review any notifications you may have missed
3. Mark all as read

### Best Practices
- Do this 10 minutes before the end of your shift
- Do not skip items — a quick check prevents morning surprises
- If you find incomplete work, add it to tomorrow's priorities

### Common Mistakes
- Logging out without updating task statuses — the team thinks work is still pending
- Forgetting to save records created late in the day — data may be lost
- Not checking the Calendar — you may miss tomorrow's early deadlines

### Expected Result
All work is documented. Tasks reflect their current status. You know what awaits you tomorrow.

---

## Weekly Procedures

### Purpose
Perform routine checks that keep operations running smoothly.

### When to Use
Every Monday (planning) and Friday (review).

### Permission Required
View on relevant modules.

### Monday: Weekly Planning

**Step 1: Renewal Review**
1. Go to **Expiry Trackers**
2. Review expiries for the next 30-60 days
3. Note high-cost or critical renewals that need action this week
4. Create tasks for any renewals that require manual processing

**Step 2: Task Planning**
1. Go to **Tasks**
2. Review all open tasks (not just your own)
3. Prioritize tasks for the week
4. Identify tasks that need reassignment if someone is out

**Step 3: Asset Review** (if applicable)
1. Go to **Assets**
2. Check recent assignments and returns
3. Verify asset statuses are up to date

### Friday: Weekly Review

**Step 1: Week in Review**
1. Confirm all work from this week is captured in records
2. Verify tasks reflect actual completion status
3. Check that new records have all required fields

**Step 2: Identify Rollover Items**
1. Review tasks that are still In Progress
2. Identify any unfinished work that carries into next week
3. Adjust priorities as needed

**Step 3: Clean Up**
1. Mark completed tasks as Completed
2. Archive or close tasks that are no longer relevant
3. Review and file any exported reports

### Best Practices
- Block 30 minutes on Monday morning and Friday afternoon for these reviews
- Use the results to inform your daily checklists
- If you consistently have the same items roll over, consider re-prioritizing

### Common Mistakes
- Skipping Monday planning — you start the week without direction
- Not tagging incomplete items — they get lost
- Doing the Friday review too early — wait until end of day

---

## Monthly Renewal Management

### Purpose
Systematically manage service renewals to prevent expired services and missed payments.

### When to Use
Throughout each month, following the 4-week cycle.

### Permission Required
View and Edit on Expiry Trackers and relevant service modules.

### Week 1: Review and Plan

1. Go to **Expiry Trackers**
2. Filter by status: Active, Pending Renewal
3. Review all expiries for the next 60 days
4. Highlight:
   - High-cost renewals that need budget approval
   - Critical services (domains, SSL certificates) that cannot lapse
   - Services that are no longer needed and should be cancelled
5. Create tasks for renewals that need manual action

### Week 2: Quote and Approve

1. Contact service providers for renewal quotes (if required)
2. Update renewal costs in Expiry Tracker records
3. Get internal approval for high-cost renewals
4. Confirm renewal dates with providers

### Week 3: Process Renewals

1. Process payments or authorizations with providers
2. Update Expiry Tracker records with:
   - New expiry dates (after renewal is processed)
   - Updated costs (if pricing changed)
   - Status (set to Active if it was Pending Renewal)
3. Update the corresponding service record's expiry date

### Week 4: Verify and Close

1. Verify all renewals were processed correctly
2. Check for any services that expired and were not renewed
3. Update expired services to status: Expired or Cancelled
4. Review the next month's renewals to prepare for the new cycle

### Configuring Automated Notifications

For each Expiry Tracker, ensure notifications are configured:

1. Open the Expiry Tracker record
2. Set **Notify Days Before** (e.g., 30, 14, 7)
3. Configure recipients:
   - **Assigned User** — The person responsible
   - **Notify Admins** — All admin users
   - **Custom Emails** — Specific addresses (e.g., finance@company.com)
4. Enable **Email Notifications**
5. Test by clicking **Test Email** — verify you receive it
6. If notifications fail to send, contact your Super Admin (SMTP configuration is their responsibility)

### Best Practices
- Create Expiry Trackers for EVERY renewable service
- Set multiple notification thresholds (30 days, 14 days, 7 days)
- Assign a specific team member to each tracker
- Review notification history monthly to verify delivery

### Common Mistakes
- Not creating Expiry Trackers for new services — they will not get reminders
- Setting notification days but not configuring recipients — no one gets the email
- Forgetting to update the expiry date after renewal — future reminders will be wrong
- Ignoring failed notification delivery — the tracker appears configured but no emails are sent

---

## Incident Response Procedures

### Service Outage

**Purpose:** Respond to and document a service outage.

**When to Use:** A client reports a service is down or unavailable.

**Permission Required:** View on relevant modules, Create on Tasks.

#### Step-by-Step Workflow

1. **Identify the affected service**
   - Ask the client for the service name
   - Search in OpsPilot to find the record
   - Check the Status and Expiry Date

2. **Check the provider**
   - Open the linked Service Provider record
   - Check provider contact information
   - Verify if the provider has a status page

3. **Create an incident task**
   - Create a Task: "Outage: [service name]"
   - Include: start time, symptoms, affected users
   - Assign to the appropriate team member

4. **Update the service record**
   - Change status to "Under Maintenance" or "Suspended" if appropriate
   - Add a Note with the incident details

5. **Monitor and resolve**
   - Check the service periodically
   - When resolved, update the task to Completed
   - Add a resolution note

### Security Incident

**Purpose:** Respond to a credential compromise or unauthorized access.

**When to Use:** A password is suspected to be compromised, or unauthorized access is detected.

**Permission Required:** Edit on relevant modules.

#### Step-by-Step Workflow

1. **Immediately rotate affected passwords**
   - Open each affected service record
   - Click **Edit**
   - Enter a new, strong password
   - Click **Save**

2. **Document the incident**
   - Add a Note to each affected record: "Password reset due to security incident on [date]"
   - The password reveal/edit is automatically logged in Activity Logs

3. **Create a security follow-up task**
   - Create a Task for investigation
   - Include: what happened, which records were affected, what action was taken

4. **Notify your Super Admin**
   - Create a task or contact directly
   - Provide details of the incident and actions taken

### Data Discrepancy

**Purpose:** Correct records that contain incorrect information.

**When to Use:** A record has wrong data (IP address, provider, status, etc.).

**Permission Required:** Edit on the relevant module.

#### Step-by-Step Workflow

1. **Verify the correct information**
   - Confirm with the source (client, provider portal, documentation)
   - Double-check before making changes

2. **Update the record**
   - Open the record
   - Click **Edit**
   - Correct the field(s)
   - Click **Save**

3. **Document the change**
   - Add a Note: "Corrected [field] from [old value] to [new value] — reason: [reason]"

4. **If you lack Edit permission:**
   - Create a Task for someone with Edit permission
   - Include the record name, module, and the correction needed

### Best Practices for All Incidents
- **Document everything** — use Notes to capture what happened and what you did
- **Create a Task** for follow-up items that cannot be resolved immediately
- **Do not delete records** during an incident — soft-delete hides data you may need for investigation
- **Escalate early** — if the issue is beyond your scope, create a task for Admin or Super Admin

### Common Mistakes
- Panic-deleting records during an incident — soft-delete is reversible but causes confusion
- Not documenting the incident — future troubleshooting starts from zero
- Forgetting to check linked services — a hosting outage may affect multiple domains
- Not notifying stakeholders — if you fixed it, tell the affected people

---

## Related Pages

- [Quick Start Guide](01_QUICK_START_GUIDE.md) — First-day orientation
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Service desk procedures
- [Admin Guide](03_ADMIN_GUIDE.md) — Daily operations management
- [Workflow Guide](10_WORKFLOW_GUIDE.md) — Cross-module operating procedures

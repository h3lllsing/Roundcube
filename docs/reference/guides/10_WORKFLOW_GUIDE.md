# Workflow Guide

> **Audience:** All Users — **Purpose:** Execute cross-module operational procedures that span multiple service areas

## Table of Contents

- [Onboarding a New Client](#onboarding-a-new-client)
- [Managing a Hosting Account Lifecycle](#managing-a-hosting-account-lifecycle)
- [Handling Domain Renewals](#handling-domain-renewals)
- [Password Rotation](#password-rotation)
- [Creating Support Tickets via Tasks](#creating-support-tickets-via-tasks)
- [Responding to a Website Down Report](#responding-to-a-website-down-report)
- [Employee Offboarding](#employee-offboarding)
- [Monthly Security Audit](#monthly-security-audit)

---

## Onboarding a New Client

### Purpose
Provision all required services for a new client in the correct sequence.

### When to Use
A new client signs up and needs one or more services (domain, hosting, email, VPS, VoIP).

### Roles Involved
IT Staff or Administrator.

### Permission Required
Create on: Service Providers, Domains, Hosting, Domain Emails, VPS (if applicable), VoIP (if applicable), Expiry Trackers.

### Step-by-Step Workflow

**Phase 1: Create the Provider Record**

1. Go to **Service Providers → Create**
2. Enter:
   - Provider name (e.g., your company or the upstream provider)
   - Website URL
   - Contact information
   - Your account credentials (username + password)
3. Click **Save**

> **Why first:** The Service Provider record must exist before you can link domains, hosting, and other services to it. Without this, dropdown menus in subsequent forms will be empty.

**Phase 2: Register the Domain**

1. Go to **Domains → Create**
2. Enter:
   - Domain name (e.g., client-example.com)
   - Service Provider (select from Phase 1)
   - Registration date
   - Expiry date
   - Status: Active
   - Module: Assign to the client's module
3. Click **Save**

**Phase 3: Set Up Hosting**

1. Go to **Hosting → Create**
2. Enter:
   - Domain (select from Phase 2)
   - Service Provider (select from Phase 1)
   - Server type, IP, username, password
   - Status: Active
   - Module: Same as domain
3. Click **Save**

**Phase 4: Create Email Accounts**

1. Go to **Domain Emails → Create**
2. For each mailbox:
   - Full email address
   - Domain (select from Phase 2)
   - Hosting (select from Phase 3)
   - Password
   - Module: Same as domain
3. Click **Save**

**Phase 5: Provision VPS (if applicable)**

1. Go to **VPS → Create**
2. Enter name, provider, IP, OS, specifications, SSH credentials
3. Module: Same as domain
4. Click **Save**

**Phase 6: Configure VoIP (if applicable)**

1. Go to **VoIP → Create**
2. Enter account details, main credentials
3. Add extensions with usernames and passwords
4. Module: Same as domain
5. Click **Save**

**Phase 7: Create Expiry Trackers**

1. Go to **Expiry Trackers → Create**
2. Create one tracker per renewable service:
   - Domain (set notify 30, 14, 7 days before expiry)
   - Hosting (set notify 30, 14 days before expiry)
   - SSL certificate (if tracked separately)
3. For each tracker:
   - Enable Email Notifications
   - Configure recipients (assigned user, notify admins)
   - Set notification days
4. Click **Save**

**Phase 8: Verify Everything**

1. Search for the client's domain name
2. Confirm all related records appear
3. Click through each record to verify links are correct
4. Send a test email on each Expiry Tracker to verify notification delivery

### Best Practices
- Follow the phases in order — each depends on the previous
- Use a consistent Module name across all records
- Set Expiry Trackers during onboarding, not later
- Test credentials by revealing immediately after creation

### Common Mistakes
- Skipping Phase 1 (Service Provider) — subsequent forms lack dropdown options
- Creating services without linking them to the domain — records float without context
- Forgetting Expiry Trackers — no one gets renewal reminders
- Inconsistent Module assignment — team members may not see all records

### Typical Business Scenario
A new business client signs up for your managed services. You complete all 8 phases in 20 minutes. The client now has domain registration, hosting, email, and renewal tracking — all documented and linked.

### Expected Result
The client has complete records across all applicable modules. All services are linked to each other and to the provider. Expiry Trackers will send automated renewal reminders at the configured intervals.

### Related Pages
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Creating service records
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Client onboarding procedure

---

## Managing a Hosting Account Lifecycle

### Purpose
Track a hosting account from provisioning through decommissioning.

### When to Use
A hosting account is created, modified, expires, or is decommissioned.

### Roles Involved
IT Staff, Admin, or Super Admin.

### Permission Required
View, Create, Edit, Delete (depending on stage). Reveal for password access.

### Step-by-Step Workflow

**Stage 1: Provision**
1. Go to **Hosting → Create**
2. Enter: provider, domain link, server details, credentials
3. Set status: Active
4. Create an Expiry Tracker for the renewal date

**Stage 2: Active Use**
- View the record to check server details
- Reveal password when configuring cPanel/SSH access
- Edit resource limits or IP as needed
- Add Notes for maintenance events

**Stage 3: Renewal**
1. When the Expiry Tracker notifies you:
   - Check the renewal cost
   - Process payment with the provider
   - Update the hosting record's expiry date
   - Update the Expiry Tracker with the new date

**Stage 4: Suspension or Cancellation**
1. If the client stops paying or requests cancellation:
   - Update status to Suspended or Cancelled
   - Add a Note explaining why and when
   - Do not delete — the record may be needed for reference

**Stage 5: Decommissioning (if applicable)**
1. If the hosting account is permanently removed:
   - Delete the record (soft-delete)
   - Only Super Admin can force-delete permanently

### Expected Result
The hosting record accurately reflects the current state at every stage. The Expiry Tracker ensures renewal is never missed.

### Related Pages
- [Admin Guide](03_ADMIN_GUIDE.md) — Managing Hosting
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Monthly renewal workflow

---

## Handling Domain Renewals

### Purpose
Ensure domains are renewed before expiry to prevent service disruption.

### When to Use
- An Expiry Tracker notifies you of an upcoming domain renewal
- During weekly or monthly renewal reviews
- A client reports their domain has expired

### Roles Involved
Admin or IT Staff (processing), Read Only (monitoring).

### Permission Required
View on Domains and Expiry Trackers. Edit on Domains (to update expiry date).

### Step-by-Step Workflow

**Step 1: Receive Notification**
1. The system sends an email notification (from the Expiry Tracker)
2. OR you see the upcoming expiry on the Dashboard Renewals widget
3. OR you see it on the Calendar

**Step 2: Review Domain Details**
1. Go to **Domains**
2. Open the domain record
3. Review: registrar, expiry date, linked service provider

**Step 3: Process Renewal (outside OpsPilot)**
1. Log into the registrar's website (use Service Provider record for login URL and credentials)
2. Process the renewal payment
3. Note the new expiry date

**Step 4: Update OpsPilot Records**
1. Edit the domain record
2. Update the **Expiry Date** to the new date
3. If the Expiry Tracker is configured correctly, it automatically recalculates future notifications

**Step 5: Document the Action**
1. Add a Note to the domain: "Renewed on [date] — new expiry: [date]"
2. If the cost changed, update the Expiry Tracker's cost field

### Best Practices
- Renew domains at least 30 days before expiry
- Set multiple Expiry Tracker notifications (30, 14, 7 days)
- Always update the expiry date immediately after renewal
- Verify the new date is correct by checking the registrar's confirmation

### Common Mistakes
- Renewing but not updating the expiry date in OpsPilot — future reminders will be wrong
- Renewing at the last minute — some registrars have grace periods, but it is risky
- Not documenting the renewal — nobody knows when or by whom it was done

### Expected Result
The domain is renewed before expiry. The OpsPilot record is updated with the new expiry date. The Expiry Tracker automatically adjusts future reminder schedules.

### Related Pages
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Monthly renewal workflow
- [Admin Guide](03_ADMIN_GUIDE.md) — Managing Expiry Trackers

---

## Password Rotation

### Purpose
Systematically update service credentials to maintain security.

### When to Use
- Scheduled security maintenance (e.g., quarterly)
- After a security incident
- When an employee with access leaves the company
- Client requests credential rotation

### Roles Involved
IT Staff or Admin (with Edit permission on the relevant modules).

### Permission Required
Edit to update the password. Reveal to view the current password (if needed).

### Step-by-Step Workflow

**Step 1: Identify Records to Rotate**
1. Search for records by type: Hosting, VPS, VoIP, Domain Emails, Service Providers
2. You can use the module's index page and browse the list
3. Prioritize: shared credentials > admin-level accounts > individual accounts

**Step 2: Change the Password on the Service (outside OpsPilot)**
1. Log into the actual service (server, provider portal, email control panel)
2. Change the password to a new, strong value
3. Verify the new password works

**Step 3: Update the Password in OpsPilot**
1. Open the service record
2. Click **Edit**
3. Enter the new password in the password field
4. Click **Save** — the password is encrypted immediately

**Step 4: Document the Rotation**
1. Add a Note to the record: "Password rotated on [date] by [your name]"
2. If the rotation was part of a security incident, reference the incident task

### Best Practices
- Rotate shared credentials more frequently than individual ones
- Always change the password on the actual service BEFORE updating OpsPilot
- Use strong, unique passwords for each service
- Do not reuse old passwords

### Common Mistakes
- Updating OpsPilot first — the record now has a password the service does not accept
- Not testing the new password before closing — you may lock yourself out
- Skipping the Note — nobody knows the password was rotated or why

### Expected Result
The service's password is updated on the actual service and in OpsPilot. The record reflects the new credentials. The rotation is documented with a timestamp and reason.

### Related Pages
- [Admin Guide](03_ADMIN_GUIDE.md) — Revealing passwords
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Password management

---

## Creating Support Tickets (via Tasks)

### Purpose
Create a trackable work item for a task that needs to be completed by a team member.

### When to Use
- Work needs to be assigned to a specific person
- An issue needs follow-up after initial troubleshooting
- A request comes in that cannot be handled immediately
- You need to document that work was requested

### Roles Involved
All users can create tasks.

### Permission Required
Create on the Tasks module.

### Step-by-Step Workflow

**Step 1: Create the Task**
1. Go to **Operations → Tasks**
2. Click **Create Task**
3. Fill in:

   | Field | Required | Instructions |
   |-------|----------|--------------|
   | **Title** | Yes | Clear summary of what needs to be done |
   | **Description** | Recommended | Details, troubleshooting already done, relevant context |
   | **Module** | Recommended | Link to the relevant service module so the task is findable |
   | **Assigned To** | Recommended | Select the person who should do the work |
   | **Priority** | Yes | Low, Medium, High, Urgent |
   | **Due Date** | Recommended | When the work should be completed by |

4. Click **Save**

**Step 2: Work Begins**
1. The assignee receives a notification
2. They open the task and update status to **In Progress**
3. They complete the work

**Step 3: Task Completion**
1. The assignee updates status to **Completed**
2. Optionally adds a resolution note in the description
3. The task creator is notified

### Best Practices
- Always assign a **Module** — unlinked tasks are hard to find
- Assign to a specific person — unassigned tasks may be ignored
- Include all relevant context in the first message — saves back-and-forth
- Use priority honestly — if everything is Urgent, nothing is

### Common Mistakes
- Creating a task without an assignee — nobody owns it
- Vague titles like "Fix issue" — be specific: "Reset email password for client@example.com"
- Not linking to a module — the task is invisible to team members browsing by module
- Setting everything to High priority — real emergencies get lost

### Typical Business Scenario
A client calls with a request. You cannot handle it immediately, so you create a Task: "Configure SPF record for example.com" with Medium priority, assigned to the DNS team, due in 3 days. The team handles it, marks it Completed, and you are notified.

### Expected Result
The task appears in the Tasks list and Kanban board. Assigned users receive a notification. Status changes are visible. A complete audit trail exists.

### Related Pages
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Viewing and updating tasks
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Task management

---

## Responding to a Website Down Report

### Purpose
Diagnose and resolve a client's website outage using OpsPilot records.

### When to Use
A client reports their website is not loading.

### Roles Involved
IT Staff (first responder), Admin (if escalation needed).

### Permission Required
View on Hosting and Domains. Reveal if password checks are needed.

### Step-by-Step Workflow

**Step 1: Identify the Service**
1. Ask the client for their domain name
2. Type it in the **Global Search** bar
3. Open the Domain record — check if it is expired
4. Open the linked Hosting record

**Step 2: Check Hosting Status**
1. Review the hosting record's **Status** field
2. Check the **Expiry Date** — has the hosting plan expired?
3. If a monitoring URL is configured, click **Monitor** to ping the server
4. Check the **Service Provider** — is the provider itself having an outage?

**Step 3: Check Domain**
1. Verify the domain's **Expiry Date**
2. If expired, the domain may have been taken offline by the registrar

**Step 4: Resolve**
- **Expired domain:** Notify the client, arrange renewal, process payment, update expiry date
- **Expired hosting:** Same process — renew with provider
- **Server issue:** Check with provider, create a task to track the incident
- **Credentials issue:** Reveal and verify credentials, update if needed

**Step 5: Document**
1. Add a Note to the relevant record(s) explaining the issue and resolution
2. If follow-up is needed, create a Task

### Best Practices
- Check the Expiry Date first — it is the most common cause
- Use Monitor to quickly test connectivity
- Document everything in Notes — the same issue may recur

### Common Mistakes
- Diving deep into server troubleshooting when the domain simply expired
- Not checking the Service Provider status — the issue may be upstream
- Forgetting to update the record after resolution

### Expected Result
The root cause is identified (expired domain, server issue, provider outage, etc.) and resolved. The record is updated with notes. A task is created if follow-up is needed.

### Related Pages
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Troubleshooting procedures
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Incident response

---

## Employee Offboarding

### Purpose
Securely remove an employee's access and reassign their responsibilities.

### When to Use
An employee leaves the company or transfers to a different role.

### Roles Involved
Super Admin (user management), Admin/IT Staff (asset and task reassignment).

### Permission Required
Super Admin for user suspension/deletion. Edit on Assets and Tasks for reassignment.

### Step-by-Step Workflow

**Step 1: Reassign Assets**
1. Go to **Assets**
2. Find assets assigned to the departing employee
3. For each asset: click **Return** and record the condition
4. Mark as Available for reassignment

**Step 2: Reassign Tasks**
1. Go to **Tasks**
2. Filter by assigned to the departing employee
3. For open tasks: edit and assign to remaining team members
4. Completed tasks can be left as-is

**Step 3: Transfer Vault Entries (if applicable)**
1. If the employee has shared vault entries, ensure a manager has access
2. If the employee has personal entries they need to keep, export before deactivation

**Step 4: Suspend the User Account (Super Admin)**
1. Go to **Administration → Users**
2. Open the employee's profile
3. Click **Suspend**
4. This preserves all their data while preventing login

**Step 5: Clean Up**
1. Review any Notes by the departing employee — leave module-attached ones, archive personal
2. Notify the team that the employee has been offboarded

### Best Practices
- Suspend immediately when the employee gives notice — do not wait for the last day
- Reassign tasks before suspending — prevents orphaned work items
- Suspend, do not delete — preserves audit trail and data relationships

### Common Mistakes
- Deleting the user immediately — breaks task and record associations
- Not reassigning tasks — open tasks become orphaned
- Forgetting to collect assets — the Offboarding workflow covers this

### Expected Result
The employee's account is suspended. All assets are returned and available. Open tasks are reassigned. No data is lost — the employee's past contributions remain in the audit trail.

### Related Pages
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — User suspension
- [Admin Guide](03_ADMIN_GUIDE.md) — Asset management

---

## Monthly Security Audit

### Purpose
Systematically review system activity and verify security controls.

### When to Use
Monthly, as part of regular governance procedures.

### Roles Involved
Super Admin (primary), Read Only (can do preliminary data review).

### Permission Required
Super Admin for Activity Logs, Login Audits, and Reports.

### Step-by-Step Workflow

**Step 1: Review Activity Logs**
1. Go to **Administration → Activity Logs**
2. Filter by the past month
3. Look for:
   - Password reveals — any unusual patterns (same record revealed many times)
   - Deletions — were any records deleted without explanation?
   - Permission changes — were any roles or overrides modified?

**Step 2: Review Login Audits**
1. Go to **Administration → Login Audits**
2. Filter by the past month
3. Look for:
   - Failed login attempts — brute force patterns
   - Suspended account access attempts
   - Logins from unusual IP addresses or times

**Step 3: Review User Accounts**
1. Go to **Administration → Users**
2. Check for:
   - Inactive users who should be suspended
   - Users with inappropriate role assignments
   - Override permissions that are no longer needed

**Step 4: Run Reports**
1. Go to **Reports**
2. Run relevant reports (cost summary, task summary, service counts)
3. Export for documentation

**Step 5: Document Findings**
1. Note any issues discovered
2. Create Tasks for items that need action:
   - Suspicious activity — investigate
   - Stale user accounts — suspend or delete
   - Expired records — clean up

### Best Practices
- Perform the audit on the same day each month (e.g., first Monday)
- Document findings in a consistent format for trend analysis
- Follow up on tasks created during the previous month's audit
- Keep exported reports for at least 12 months

### Common Mistakes
- Only reviewing the first page of Activity Logs — use filters to narrow, not truncate
- Ignoring login failures — they may indicate an attack in progress
- Not following up on previous month's action items — issues compound

### Expected Result
A complete picture of system activity for the month. Suspicious patterns are flagged. Stale accounts are identified for cleanup. An audit trail of the review itself exists.

### Related Pages
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — Activity auditing
- [Read Only Guide](05_READ_ONLY_GUIDE.md) — Compliance audit procedures

---

## Related Pages

- [Quick Start Guide](01_QUICK_START_GUIDE.md) — First-day orientation
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Checklists and procedures
- [FAQ](07_FAQ.md) — Problem resolution
- [Permission Reference](08_PERMISSION_REFERENCE.md) — Permission definitions

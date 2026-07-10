# IT Staff Guide

> **Audience:** IT Support Engineers — **Purpose:** Process service requests and manage client infrastructure records

## Table of Contents

- [Viewing and Working with Assigned Tasks](#viewing-and-working-with-assigned-tasks)
- [Creating a New Service Record](#creating-a-new-service-record)
- [Updating an Existing Service Record](#updating-an-existing-service-record)
- [Revealing a Client Password](#revealing-a-client-password)
- [Searching for Client Records](#searching-for-client-records)
- [Adding a Note to a Record](#adding-a-note-to-a-record)
- [Client Onboarding — Full Workflow](#client-onboarding--full-workflow)
- [Troubleshooting a Service Issue](#troubleshooting-a-service-issue)
- [Escalating to Administration](#escalating-to-administration)
- [What You Cannot Do](#what-you-cannot-do)

---

## Viewing and Working with Assigned Tasks

### Purpose
See tasks assigned to you and update their progress so the team knows what is being worked on.

### When to Use
- At the start of your shift — see what needs to be done
- When you begin working on a task — update status to In Progress
- When you finish work — mark as Completed
- When you need prioritization — check task priority levels

### Permission Required
You see tasks assigned to you (cross-module) plus tasks in modules you can access. If your role does not include the Tasks module, the Tasks menu is hidden.

### Step-by-Step Workflow

**Viewing your tasks:**
1. Go to **Operations → My Tasks**
2. Review the list showing only tasks where you are an assignee
3. Use filters to narrow: **Status** (Pending, In Progress, Completed), **Priority** (Low, Medium, High, Urgent), **Due Date**

**Updating task status when you start work:**
1. Open the task by clicking its title
2. Click the **Status** dropdown
3. Select **In Progress**
4. This tells the team you are working on it

**Updating task status when you finish:**
1. Open the task
2. Click the **Status** dropdown
3. Select **Completed**
4. Optionally add a resolution note in the Description field

**Using Kanban view:**
1. Go to **Operations → Tasks**
2. Click **Kanban**
3. Drag your task to the correct column (Pending → In Progress → Completed)
4. Changes save automatically

### Best Practices
- Check **My Tasks** first thing every morning — use it as your daily to-do list
- Update status to **In Progress** immediately when you start — prevents duplicate work
- Mark tasks **Completed** as soon as you finish — do not batch them at end of day
- If blocked, add a comment to the task rather than leaving it in Pending

### Common Mistakes
- Leaving tasks in "Pending" after starting work — the team thinks no one is handling it
- Not checking task priority — a high-priority task may be waiting
- Completing a task but not updating the status — skews reporting and confuses the team

### Typical Business Scenario
**Start of shift review:** You log in, go to My Tasks, and see 3 tasks assigned — two Pending (one High priority, one Low) and one In Progress. You start the High priority task first, update it to In Progress, and begin work.

### Expected Result
Your assigned tasks are visible in one place. Status changes are immediately visible to anyone viewing the task. The Kanban board reflects your updates.

---

## Creating a New Service Record

### Purpose
Add a new domain, hosting, VPS, VoIP, service provider, or domain email record to the system.

### When to Use
- A client orders a new service
- A new service is provisioned and needs documentation
- A record exists externally but is missing from OpsPilot

### Permission Required
**Create** permission on the specific module (granted by default on the 6 IT Support modules).

### Step-by-Step Workflow

1. Go to the relevant module in the sidebar (e.g., **Infrastructure → Domains**)
2. Click **Create**
3. Fill in the form fields:

   | Common Fields | What to Enter |
   |---------------|---------------|
   | **Name / Identifier** | The service name or domain |
   | **Service Provider** | Select the provider from the dropdown (create it first if new) |
   | **Username** | Login username for the service |
   | **Password** | Login password (encrypted on save) |
   | **Status** | Usually "Active" for new services |
   | **Module** | Select the appropriate client module so the right team sees it |

4. Click **Save**

> **After saving:** The record appears in the module's list immediately. If you set a password, it is encrypted. Other team members with module access can see it.

### Best Practices
- Create the **Service Provider** record before creating linked services (Domains, Hosting) — you need it in the dropdown
- Fill in all relevant fields the first time — it saves time later
- Verify the Module assignment — this determines who can see the record
- Use the same naming convention the team uses (e.g., always lowercase domain names)

### Common Mistakes
- Forgetting to select a Module — the record may not be visible to the right team
- Creating a duplicate record — search first to verify it does not exist
- Leaving the password field blank — you will have to edit the record later
- Selecting the wrong Service Provider — creates cross-reference confusion

### Typical Business Scenario
**Adding a new domain:** A client registers "newclient.com". You go to Domains, click Create, enter the domain name, select the provider, set the expiry date, choose the client's module, and save. The domain is now in the system.

### Expected Result
The new record appears in the module's index page. It is visible to all team members with access to that module. If the module has Expiry Trackers, a tracker can now be linked.

---

## Updating an Existing Service Record

### Purpose
Modify service details when information changes or when corrections are needed.

### When to Use
- A client's service details change (IP, plan, credentials)
- A renewal date needs updating
- A record has incorrect information that needs correction
- A service status changes (Active → Suspended, etc.)

### Permission Required
**Edit** permission on the specific module.

### Step-by-Step Workflow

1. Navigate to the service record (use Search or browse the module)
2. Click the record to open its **detail page**
3. Click **Edit**
4. Update the necessary fields:

   | Common Updates | What to Change |
   |----------------|----------------|
   | **Status** | Active, Suspended, Cancelled, Expired |
   | **Expiry Date** | After renewal at the provider |
   | **Password** | After rotating credentials |
   | **Service Provider** | If switching providers |
   | **Module** | If re-organizing records |

5. Click **Save**

> **Changes are logged:** Every edit is recorded in Activity Logs with the old and new values. Super Admin can review the change history.

### Best Practices
- Update the record **immediately** when a change happens — do not rely on memory
- After changing a password in the system, verify it by revealing and testing
- Add a Note explaining why the change was made (e.g., "Password reset per client request")
- Update Expiry Trackers when the corresponding service's expiry date changes

### Common Mistakes
- Changing a password in the system but not on the actual service — credentials become out of sync
- Not updating the status when a service is cancelled — skews active counts
- Changing the Module assignment without checking who needs access — may hide the record from some team members

### Typical Business Scenario
**Renewing a domain:** A client renews "example.com" for another year. You update the Expiry Date in the Domain record to the new date. The linked Expiry Tracker automatically uses the new date for future reminders.

### Expected Result
The record's fields are updated. The change appears in Activity Logs. Other team members see the updated information immediately.

---

## Revealing a Client Password

### Purpose
View a decrypted password to configure a service for a client or troubleshoot an issue.

### When to Use
- A client needs credentials to access their service
- You are configuring a new email client on the user's device
- You need to log into a service to troubleshoot

### Permission Required
**Reveal** permission on the specific module. IT Support has Reveal enabled by default on the 6 modules.

### Step-by-Step Workflow

1. Navigate to the service record's **detail page** (Hosting, VPS, VoIP, Service Provider, or Domain Email)
2. Find the password field — it appears as masked dots (******)
3. Click the **Reveal** icon or **Show Password** button
4. The password is decrypted and displayed in plain text
5. Use the password for your task (enter it into the service, share with client, etc.)
6. **Important:** All reveals are logged — only reveal when necessary

> **Rate limit:** You can reveal up to 10 passwords per minute. If you see a rate-limit warning, wait 60 seconds before trying again.

> **Audit trail:** Every reveal is recorded in Activity Logs with your name, the record ID, and the timestamp. This CANNOT be disabled.

### Best Practices
- Reveal the password directly in the system rather than storing it elsewhere
- If sharing with a client, tell them to change the password after use
- For security, change the password after sharing it externally
- Use the Copy button if available — it avoids displaying the password on screen

### Common Mistakes
- Revealing a password and writing it down on paper — defeats the purpose of encryption
- Revealing unnecessarily — each reveal is audited; only do it when required for your work
- Sharing passwords over unencrypted chat or email — use secure channels
- Not changing the password after sharing — once shared, it is no longer secret

### Typical Business Scenario
**Client needs cPanel access:** A client calls asking for their cPanel login. You open their Hosting record, click Reveal, read the password to them over a verified phone number, and recommend they change it after logging in.

### Expected Result
The password is visible for your immediate use. The reveal is recorded in Activity Logs for audit purposes. The password remains encrypted in the database.

---

## Searching for Client Records

### Purpose
Quickly find any service record without browsing through module menus.

### When to Use
- A client calls and you need to pull up their information fast
- You know the service name but not which module it is in
- You need to verify whether a record exists before creating a duplicate

### Permission Required
You see results only from modules where you have **View** permission. Your search is automatically scoped.

### Step-by-Step Workflow

1. Click the **search bar** at the top of any page
2. Type at least **2 characters** of what you are looking for
   - Best results: client name, domain name, service identifier
3. Results appear grouped by module as you type
4. Click any result to open its detail page

> **What your search includes:**
> - ALL records in modules you have access to (module-wide access)
> - PLUS records you own (for personal modules)
> - Tasks assigned to you

### Best Practices
- Use search as your **primary navigation tool** — it is faster than clicking through menus
- Search by domain name for the most reliable results — domains are unique
- If a record does not appear, verify you typed at least 2 characters and have View permission on the module

### Common Mistakes
- Searching for "all" or "a" — minimum 2 characters is required
- Assuming "no results" means the record does not exist — you may lack access to the module
- Browsing through menus instead of searching — takes significantly longer

### Typical Business Scenario
**Client call about hosting:** A client calls to ask about their hosting plan. You type their domain name in the search bar. The domain record, hosting record, and any related notes all appear. You click the hosting record and have the information ready before the client finishes explaining.

### Expected Result
Matching records appear grouped by module. Each result links directly to the record's detail page. The search is scoped to your permissions.

---

## Adding a Note to a Record

### Purpose
Attach information to a service record for future reference by the team.

### When to Use
- You performed a maintenance action that should be documented
- A client gave specific instructions over the phone
- There is important context about the service that others should know
- You completed a troubleshooting step that may be relevant later

### Permission Required
All authenticated users can create notes. Module-attached notes are visible to anyone with access to that module.

### Step-by-Step Workflow

1. Navigate to the service record's **detail page**
2. Find the **Notes** section or tab
3. Click **Add Note**
4. Write your note — be specific about what happened, when, and why
5. The note automatically links to the current module
6. Click **Save**

> **Module-attached vs global notes:**
> - Notes linked to a module are visible to everyone with module access
> - Notes not linked to a module are visible only to you

### Best Practices
- Always include the **date and your name** in the note text
- Be specific: "Reset password after security incident on 2024-01-15" not "Reset password"
- Link notes to the module so the whole team benefits from the context
- Use notes to document one-time events, not regular maintenance

### Common Mistakes
- Writing vague notes — "Fixed something" is not helpful to future readers
- Leaving notes unlinked to a module — only you can see them
- Using notes for passwords or sensitive data — notes are not encrypted like password fields
- Creating a note instead of updating the record — if the record has a field for it, update the field

### Typical Business Scenario
**Documenting a support call:** A client called about email issues. You resolved it by clearing the mailbox queue. You add a note: "Cleared mail queue on 2024-06-15 — client reported slow delivery. Issue resolved." Now anyone seeing this record knows what happened.

### Expected Result
The note appears on the record's detail page. Any team member with access to this module can read it.

---

## Client Onboarding — Full Workflow

### Purpose
Set up all services for a new client in the correct sequence.

### When to Use
A new client signs up and needs: domain, hosting, email, and optionally VPS and VoIP.

### Permission Required
Create permission on Domains, Hosting, VPS, VoIP, Service Providers, and Domain Emails.

### Step-by-Step Workflow

1. **Create the Service Provider** (if new)
   - Go to **Service Providers → Create**
   - Enter the provider name, contact info, and your account credentials
   - This ensures the provider appears in dropdowns for other records

2. **Register the Domain**
   - Go to **Domains → Create**
   - Enter domain name, link to the service provider
   - Set registration date and expiry date

3. **Set Up Hosting**
   - Go to **Hosting → Create**
   - Enter server details, link to the domain and provider
   - Store the control panel credentials

4. **Create Email Accounts**
   - Go to **Domain Emails → Create**
   - Create mailboxes linked to the domain and hosting
   - Set passwords for each mailbox

5. **Provision VPS** (if applicable)
   - Go to **VPS → Create**
   - Enter VPS specs, IP, SSH credentials
   - Link to the client's module

6. **Configure VoIP** (if applicable)
   - Go to **VoIP → Create**
   - Set up the main account and extensions
   - Enter SIP credentials

7. **Set Up Expiry Trackers**
   - Create trackers for each renewable service
   - Configure notification days and recipients

8. **Verify Everything**
   - Search for the client's domain to confirm all records are linked
   - Check that all modules are correctly assigned

### Best Practices
- Follow the order above — each step builds on the previous one
- Use a consistent Module name for all records belonging to this client
- Create the Service Provider first — it is needed in dropdown menus
- Set up Expiry Trackers immediately — do not wait until renewal is imminent

### Common Mistakes
- Creating services before the Service Provider record — you have to go back and link them
- Mixing different clients' records in the same Module — use separate modules per client/team
- Skipping Expiry Trackers — they will not get renewal reminders
- Not testing credentials after creation — verify by revealing and logging in

### Typical Business Scenario
**Full onboarding:** A new business client signs up. You create their Service Provider (your company), register 2 domains, set up shared hosting, create 5 email accounts, and configure Expiry Trackers. The entire process takes 15 minutes.

### Expected Result
The client has complete records in every applicable module. All services are linked, credentials are stored, and Expiry Trackers will send automated renewal reminders.

---

## Troubleshooting a Service Issue

### Purpose
Diagnose and resolve a client's service problem using OpsPilot records.

### When to Use
- A client reports their website is down
- Email is not sending or receiving
- A service is unreachable
- Credentials are not working

### Permission Required
View on the relevant modules. Reveal if passwords need checking.

### Step-by-Step Workflow

**Step 1: Identify the affected service**
1. Ask the client for the domain name or service name
2. Type it in the **Global Search** bar
3. Open the matching record

**Step 2: Check the service details**
1. Review the **Status** field — is it Active, Suspended, or Expired?
2. Check the **Expiry Date** — is it past due?
3. Verify the **Service Provider** — is the provider itself having issues?

**Step 3: Check hosting/connectivity**
1. Open the linked **Hosting** record (if applicable)
2. Verify the server IP and status
3. If the record has a monitoring URL, click **Monitor** to ping the service

**Step 4: Verify credentials**
1. If the issue involves login, click **Reveal** to check the stored password
2. If the password is wrong, reset it on the service and update the record
3. Note: reveals are audited — only do this when troubleshooting

**Step 5: Log your findings**
1. Add a **Note** to the record describing the issue and resolution
2. If the issue requires follow-up, create a **Task** assigned to the responsible team member

**Step 6: Escalate if needed**
1. If you cannot resolve the issue, create a Task for the Admin or Super Admin
2. Include all findings and what you already tried

### Best Practices
- Always check the **Status** and **Expiry Date** first — most issues are expired services
- Use **Monitor** (if available) to ping the service before diving deeper
- Document EVERY troubleshooting step in Notes — future issues may follow the same pattern
- Check the Service Provider's status — the problem may be outside your control

### Common Mistakes
- Resetting a password without updating the record — the system still has the old one
- Not checking the Expiry Date — the domain may have expired
- Spending too long troubleshooting before escalating — create a task if stuck after 15 minutes
- Not documenting steps — the next person starts from zero

### Typical Business Scenario
**Website down:** A client reports their site is down. You search their domain, check the Hosting record, find the server is online. You check the Domain — it expired yesterday. You notify the client, arrange renewal, and update the expiry date after payment.

### Expected Result
You identify the root cause (expired domain, server issue, credentials, etc.) and either resolve it or create a task for escalation. The record is updated with notes documenting the event.

---

## Escalating to Administration

### Purpose
Pass unresolved issues to an Admin or Super Admin when they require higher privileges.

### When to Use
- You need a record restored from trash
- A module or feature is not available and you lack permissions
- You need data exported
- A new client module needs to be set up
- A user needs their account created or permissions changed
- You encounter a technical issue you cannot resolve

### Permission Required
You only need the ability to create Tasks (typically available to all users).

### Step-by-Step Workflow

1. Go to **Operations → Tasks**
2. Click **Create Task**
3. Fill in:

   | Field | Instructions |
   |-------|--------------|
   | **Title** | Clear summary like "Need domain restored for client X" |
   | **Description** | Include: what you need, why, which module/record, any relevant IDs |
   | **Module** | Select the relevant module so the Admin sees context |
   | **Assigned To** | Leave unassigned or assign to a specific Admin if known |
   | **Priority** | Set based on urgency |
   | **Due Date** | When this needs to be done by |

4. Click **Save**

The Admin or Super Admin will see the task and take action.

### Best Practices
- Include ALL relevant information in the first message — save back-and-forth
- Specify exactly what action you need (restore, export, create, etc.)
- Use a consistent task title format like "Request: [action needed] for [client/record]"
- Set an appropriate priority — not everything is Urgent

### Common Mistakes
- Creating a vague task like "Need help" — wastes everyone's time
- Not specifying which record or module — the Admin has to ask for details
- Setting everything to High priority — urgent items get lost in the noise
- Using email instead of Tasks — the task system keeps everything tracked

### Typical Business Scenario
**Need record restored:** You accidentally deleted a client's domain record. You create a task "Request: Restore domain example.com" with the description "Deleted by mistake on 2024-06-15. Module: ClientABC." The Super Admin sees the task, restores the record, and marks it Completed.

### Expected Result
The task appears in the Admin's task list. They take the requested action and update the task status. You get a notification when it is resolved.

---

## What You Cannot Do

The IT Support role has deliberate restrictions. Here is what is blocked:

| Action | Why |
|--------|-----|
| **Delete records** | Delete is Off by default for IT Support |
| **Export data** | Export is Off by default |
| **Manage users, roles, permissions** | Super Admin only |
| **View Activity Logs or Login Audits** | Super Admin only |
| **Manage SMTP, webhooks, reports** | Super Admin only |
| **Restore or force-delete** | Super Admin only |
| **View or manage Other Services** | Denied by default IT Support template |
| **View or manage Expiry Trackers** | Denied by default IT Support template |
| **View or manage Assets** | Denied by default IT Support template |
| **Access Vault/Tasks/Notes/Calendar** | Denied by default (unless customized) |
| **Access any Administration module** | Super Admin only |
| **Access any Integration module** | Super Admin only |

> **If you need access:** Your Super Admin can grant additional permissions via User Permission Overrides. Create a task explaining what you need.

---

## Related Pages

- [Admin Guide](03_ADMIN_GUIDE.md) — How Admins manage operations (compare with your role)
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — System configuration
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Checklists and workflows
- [Workflow Guide](10_WORKFLOW_GUIDE.md) — Cross-module operating procedures
- [Quick Start Guide](01_QUICK_START_GUIDE.md) — First-day orientation

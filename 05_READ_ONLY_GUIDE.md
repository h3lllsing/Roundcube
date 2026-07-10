# Read Only Guide

> **Audience:** Read Only Users (Auditors, Compliance Officers, Managers) — **Purpose:** Locate and verify information across the portal without making changes

## Table of Contents

- [Browsing Infrastructure Records](#browsing-infrastructure-records)
- [Using Search to Find Information](#using-search-to-find-information)
- [Reviewing Task Status](#reviewing-task-status)
- [Viewing Vault Entries](#viewing-vault-entries)
- [Viewing Notes](#viewing-notes)
- [Using the Calendar for Expiry Overview](#using-the-calendar-for-expiry-overview)
- [Performing a Compliance Audit](#performing-a-compliance-audit)
- [Reporting Data Issues](#reporting-data-issues)
- [What You Cannot Do](#what-you-cannot-do)

---

## Browsing Infrastructure Records

### Purpose
View all service records in modules you have access to for verification, auditing, or situational awareness.

### When to Use
- You need to verify that records exist and are complete
- Management asks for a count of services by type
- You are reviewing data quality as part of an audit
- You want to monitor operational status without taking action

### Permission Required
**View** permission on the module is required. Read Only users have View on all 9 infrastructure modules (Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails, Other Services, Expiry Trackers, Assets) and all 5 productivity modules (Tasks, Notes, Vault, Calendar, Notifications).

### Step-by-Step Workflow

1. In the sidebar, click the module you want to browse (e.g., **Infrastructure → Domains**)
2. The **index page** shows a list of all records in that module
3. Use the available tools to navigate:

   | Tool | How to Use |
   |------|------------|
   | **Search within module** | Type in the search bar above the list |
   | **Filters** | Filter by status, date range, or other fields |
   | **Sort** | Click column headers to sort ascending/descending |
   | **Pagination** | Use page numbers at the bottom to browse through results |

4. Click any record to open its **detail page** and view all information

> **What you see:** For infrastructure modules, you see ALL records in the module — not just records created by a specific person. This is called **module-wide access**.

### Best Practices
- Use filters to narrow down large datasets — 500 domains are hard to browse as a single list
- Click into records to see full details — the index page only shows summary columns
- Note that password fields always appear masked — you cannot reveal them
- Use the Export button if you have Export permission (may not be available)

### Common Mistakes
- Expecting to see only your records — infrastructure modules show ALL records to anyone with View
- Trying to click Edit or Delete buttons — they are hidden for Read Only users
- Wasting time browsing manually — use Search for specific records

### Typical Business Scenario
**Quarterly service audit:** You are an auditor. You browse through Domains, Hosting, and VPS modules to verify that all records have complete information (status, expiry dates, provider links). You note any records with missing fields for follow-up.

### Expected Result
The module's index page displays all records you have permission to see. Detailed information is available on each record's show page. No modification buttons are visible.

---

## Using Search to Find Information

### Purpose
Quickly locate specific records across all accessible modules without browsing through individual module lists.

### When to Use
- You need to find a specific domain, service, or note
- You are cross-referencing information between modules
- You want to verify a record exists before reporting an issue

### Permission Required
Your search is automatically scoped to modules where you have **View** permission.

### Step-by-Step Workflow

1. Click the **search bar** at the top of any page
2. Type at least **2 characters** (domain name, client name, service ID)
3. Results appear grouped by module as you type

> **How search scoping works for Read Only:**
> - **Infrastructure modules** — You see ALL matching records (module-wide access)
> - **Vault** — You see only your own entries (personal access)
> - **Tasks** — You see tasks in accessible modules + tasks assigned to you
> - **Notes** — You see module-attached notes for accessible modules + your own global notes

4. Click any result to open the record's detail page

### Best Practices
- Search is faster than browsing menus — use it as your primary navigation tool
- Be specific — "clientname.com" gives better results than "client"
- If a record does not appear in search results, you may not have View on that module
- Search results include records from all accessible modules in one view

### Common Mistakes
- Searching with only 1 character — the system requires a minimum of 2
- Assuming search covers ALL modules — it only covers modules where you have View
- Not checking the module filter — results are grouped by module; make sure you check all groups

### Typical Business Scenario
**Cross-referencing a client's services:** You need to verify all services for a client. You search their domain name. The results show the domain, hosting account, email accounts, and any related notes — all from a single search.

### Expected Result
A list of matching records from across all accessible modules. Each result shows which module it belongs to and links to the full record.

---

## Reviewing Task Status

### Purpose
View task progress and completion status to monitor team workload or audit completed work.

### When to Use
- You want to see what tasks are open and who is working on them
- You are auditing completed work by a specific team member
- You need to verify that a task was completed on a certain date

### Permission Required
View on the Tasks module. Read Only users see tasks in modules they can access plus tasks assigned to them.

### Step-by-Step Workflow

1. Go to **Operations → Tasks**
2. You see the full task list scoped to your access
3. Use filters to narrow your view:

   | Filter | Use Case |
   |--------|----------|
   | **Status** | View only Pending, In Progress, or Completed tasks |
   | **Assigned To** | See tasks assigned to a specific person |
   | **Priority** | Filter by urgency level |
   | **Date Range** | See tasks from a specific period |
   | **My Tasks** | See only tasks assigned to you |

4. Click any task to view its full details, including:
   - Description and resolution notes
   - Status history
   - Linked module
   - Assignee(s)

5. Optionally view the **Kanban** board for a visual overview of task status distribution

> **Read Only limitation:** You can view all task details but cannot change status, edit, create, or delete tasks. All action buttons are hidden.

### Best Practices
- Use the **Completed** status filter to audit work that has been finished
- Check task notes and descriptions for context on what was done
- Use date range filters to review work within a specific period
- The Kanban board gives a quick visual of team workload balance

### Common Mistakes
- Trying to drag tasks on the Kanban board — this requires Edit permission
- Expecting to see all tasks in the system — you only see tasks in modules you can access
- Confusing "assigned to" with "created by" — use the correct filter

### Typical Business Scenario
**Auditing completed support tasks:** A manager asks you to verify that all support tasks for June were completed. You filter Tasks by date range (June 1-30) and status (filter to see Completed), then review the list.

### Expected Result
Tasks are displayed with their current status and details. You can view, filter, and read all task information without making changes.

---

## Viewing Vault Entries

### Purpose
View stored credentials in the vault — your own entries and entries in modules you can access.

### When to Use
- You need to find your own stored credentials
- You are verifying that vault entries exist for certain services
- You are auditing vault usage

### Permission Required
View on the Vault module. Read Only users see their own vault entries (personal access) plus entries in vault modules they can access.

### Step-by-Step Workflow

1. Go to **Credentials → Shared Credentials**
   - This shows vault entries in modules you can access
2. Or go to **Credentials → My Credentials**
   - This shows only entries you created
3. Browse the list or use the search/filter
4. Click any entry to view its details

> **On the detail page you see:**
> - Service name and URL
> - Username
> - Password field (masked — you CANNOT reveal it)
> - Notes
> - Module assignment

### Best Practices
- Use **My Credentials** for your own entries and **Shared Credentials** for team entries
- Verify that vault entries are properly categorized by module
- If you see incorrect information, report it via a task (you cannot edit it yourself)

### Common Mistakes
- Expecting to see all vault entries — you only see your own unless you have broader module access
- Trying to reveal a password — Read Only users cannot reveal; the button is hidden
- Thinking vault is the same as password reveal on service records — they are separate features

### Typical Business Scenario
**Auditing credential storage:** You check the Vault module to verify that all shared service credentials are stored securely. You confirm each entry has a service name, username, and is assigned to the correct module.

### Expected Result
Vault entries appear in a list. Details are viewable but passwords remain masked. No modification buttons are visible.

---

## Viewing Notes

### Purpose
Read notes attached to records or modules for context on past actions and decisions.

### When to Use
- You need context on why a record was modified
- You are reviewing the history of a service
- You want to see team communication about a specific record

### Permission Required
View on the Notes module. Read Only users see module-attached notes for modules they can access, plus their own global notes.

### Step-by-Step Workflow

**Viewing notes on a record:**
1. Open a service record (domain, hosting, etc.)
2. Look for the **Notes** section on the detail page
3. All module-attached notes are visible to you

**Viewing all notes:**
1. Go to **Notes** in the sidebar
2. Browse notes grouped by module
3. Use filters to narrow by module or creator

> **Note visibility rule:**
> - **Module-attached notes** — Visible to anyone with access to that module
> - **Global notes** (not linked to a module) — Visible only to the note creator

### Best Practices
- Notes often contain important context — read them before reporting an issue
- Use the Notes module to see all notes across modules in one place
- Note that notes are not encrypted — do not expect passwords or secrets to appear here

### Common Mistakes
- Expecting to see all notes ever written — you only see notes in modules you can access
- Confusing notes with activity logs — notes are user-written; activity logs are system-generated
- Thinking you can add notes — Read Only users cannot create notes

### Typical Business Scenario
**Investigating service history:** A domain's status changed recently. You open the domain record and find a note: "Client requested cancellation on 2024-06-10. Domain will expire naturally." This explains the status change without needing to involve the team.

### Expected Result
Notes associated with accessible records or modules are visible. You can read the full content of each note.

---

## Using the Calendar for Expiry Overview

### Purpose
View upcoming service expiries and task due dates in a single monthly view.

### When to Use
- You want to see what services are expiring this month
- You need an overview of upcoming renewal activity
- You are verifying that expiry dates are set correctly across modules

### Permission Required
View on the Calendar and on the relevant modules. Read Only users see expiry dates for modules they can access.

### Step-by-Step Workflow

1. Go to **Operations → Calendar**
2. You see a monthly calendar grid with markers for:
   - **Red/highlighted dates** — Service expiry dates from accessible modules
   - **Task due dates** — For tasks visible to you
3. Click a date to see the list of expiries or tasks due on that day
4. Click any item to open the corresponding record

> **What you see is scoped:** You only see expiry dates for modules you have View permission on. If you cannot access Other Services, their expiry dates do not appear.

### Best Practices
- Use the Calendar as your high-level overview — check it at the start of each week
- Click through to records for full details
- Note that you cannot edit expiry dates from the Calendar — it is a view-only tool

### Common Mistakes
- Expecting to see ALL expiries — you only see expiries in modules you can access
- Trying to add or modify calendar events — the Calendar is display-only
- Confusing task due dates with service expiry dates — they appear together but are different record types

### Typical Business Scenario
**Weekly review:** On Monday, you open the Calendar to see what is coming up. You notice 3 domains expiring next week and 5 hosting accounts expiring this month. You report this to the Admin team so they can process renewals.

### Expected Result
A monthly calendar shows all expiry and due dates within your scope. Clicking a date reveals the specific records. No modification is possible.

---

## Performing a Compliance Audit

### Purpose
Systematically review records across modules to verify data completeness and accuracy.

### When to Use
- Monthly or quarterly compliance checks
- Before external audit
- When management requests a data quality review
- As part of regular governance procedures

### Permission Required
View on the modules you need to audit (typically all infrastructure modules).

### Step-by-Step Workflow

**1. Dashboard Review**
- Log in and check the Dashboard
- Note counts: how many domains, hosting accounts, VPS, etc.
- Check the Renewals widget for upcoming expiries
- Review the Tasks widget for completion rates

**2. Module-by-Module Check**

For each module (Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails, Other Services, Expiry Trackers, Assets):

1. Open the module's index page
2. Review each record for:
   - **Status** — Is it set correctly? (Active, Expired, etc.)
   - **Expiry Date** — Is it populated and reasonable?
   - **Service Provider** — Is a provider linked?
   - **Module Assignment** — Does it belong in this module?
   - **Required Fields** — Are all required fields filled in?

**3. Expiry Tracker Verification**
1. Go to **Expiry Trackers**
2. Verify that all renewable services have a tracker
3. Check that notification settings are configured
4. Review notification history for failed sends

**4. Task Review**
1. Go to **Tasks**
2. Check for tasks in "Pending" status that are past their due date
3. Review completed tasks for proper documentation

**5. Documentation**
1. Create a note or document outside OpsPilot with your findings
2. If you find issues, report them via the [Reporting Data Issues](#reporting-data-issues) procedure

### Best Practices
- Follow the same checklist each time for consistency
- Take screenshots of any issues as evidence
- Document the date of your audit and your findings
- Focus on data quality — missing fields, incorrect statuses, orphaned records

### Common Mistakes
- Trying to fix issues you find — report them instead; you cannot edit
- Auditing too many modules at once — do one module at a time
- Not documenting findings — verbal reports are forgotten
- Checking only records you created — you see ALL records in each module; audit them all

### Typical Business Scenario
**Monthly compliance audit:** You review all 9 infrastructure modules. In Domains, you find 3 records missing expiry dates. In Expiry Trackers, you find 5 services without trackers. You create a task (via your Admin contact) listing the issues for correction.

### Expected Result
You have a complete picture of data quality across all modules. Issues are documented and reported to the team for action.

---

## Reporting Data Issues

### Purpose
Notify the operations team about records that need correction or attention.

### When to Use
- You find a record with missing or incorrect data
- An expiry date seems wrong
- A record appears to be orphaned (no linked provider or domain)
- You notice a security concern (e.g., a record visible that should not be)

### Permission Required
You only need to be logged in. Create a Task if you have access, or contact an Admin directly.

### Step-by-Step Workflow

If you have access to the Tasks module:
1. Go to **Operations → Tasks**
2. Click **Create Task**
3. Title: "Data issue: [description]"
4. Description: Include the record name, what is wrong, and what should be changed
5. Assign to: Leave unassigned or assign to a specific person
6. Click **Save**

If you do not have access to Tasks:
- Contact an Admin or Super Admin directly via your organization's normal communication channel
- Provide them with: the record name, the module it is in, what is wrong, and what the correct value should be

### Best Practices
- Be specific — "Domain example.com has no expiry date" is better than "Some domains have issues"
- Include the module name so the person fixing it can find the record quickly
- If you have multiple issues, group them by module in a single report
- Follow up after a reasonable time if the issue is not resolved

### Common Mistakes
- Reporting issues without specific record names — the Admin has to search
- Expecting immediate fixes — the operations team prioritizes based on workload
- Reporting through unofficial channels — use Tasks so everything is tracked

### Typical Business Scenario
**Missing expiry date:** While auditing Domains, you find 3 records without expiry dates. You create a task: "Data issue: Domains missing expiry dates" listing each domain name and its current (missing) expiry date. The Admin updates them and marks the task Complete.

### Expected Result
The issue is documented in a task. The responsible team member reviews and corrects it. You are notified when the task is completed.

---

## What You Cannot Do

As a Read Only user, the following actions are blocked:

| Action | Why |
|--------|-----|
| **Create records** | Create permission is Off on all modules |
| **Edit records** | Edit permission is Off on all modules |
| **Delete records** | Delete permission is Off on all modules |
| **Reveal passwords** | Reveal permission is Off on all modules |
| **Export data** | Export permission is Off on all modules |
| **Bulk actions** | Bulk action UI is hidden |
| **Restore / force-delete** | Super Admin only |
| **Create or edit notes** | Write permissions are Off |
| **Change task status** | Edit permission is Off on Tasks |
| **Access Administration modules** | Super Admin only |
| **Access Integration modules** | Super Admin only |
| **See Server Health widget** | Super Admin only |
| **See SMTP Status widget** | Super Admin only |

> **If you need something changed:** Report it via a Task or contact an Admin. Do not attempt to work around the restrictions — the portal enforces them at the server level.

---

## Related Pages

- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — System configuration
- [Admin Guide](03_ADMIN_GUIDE.md) — Daily operations management  
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Service desk procedures
- [Quick Start Guide](01_QUICK_START_GUIDE.md) — First-day orientation
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Team workflows

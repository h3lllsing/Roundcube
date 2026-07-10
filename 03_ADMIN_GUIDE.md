# Admin Guide

> **Audience:** Administrators — **Purpose:** Manage daily infrastructure operations across all 9 infrastructure modules

## Table of Contents

- [Managing Domain Records](#managing-domain-records)
- [Managing Hosting Accounts](#managing-hosting-accounts)
- [Managing VPS Records](#managing-vps-records)
- [Managing VoIP Accounts](#managing-voip-accounts)
- [Managing Service Providers](#managing-service-providers)
- [Managing Domain Emails](#managing-domain-emails)
- [Managing Other Services](#managing-other-services)
- [Managing Expiry Trackers](#managing-expiry-trackers)
- [Managing Assets](#managing-assets)
- [Revealing Passwords](#revealing-passwords)
- [Managing Tasks](#managing-tasks)
- [Exporting Data](#exporting-data)
- [Using Bulk Actions](#using-bulk-actions)
- [What You Cannot Do](#what-you-cannot-do)

---

## Managing Domain Records

### Purpose
Record and maintain domain registration information for all client and company domains.

### When to Use
- A new domain is registered for a client
- Domain details change (nameservers, registrar, expiry date)
- A domain is transferred to a different provider
- A domain is no longer in use

### Permission Required
You need **View** to see domains, **Create** to add new ones, **Edit** to modify existing ones, **Delete** to remove.

### Step-by-Step Workflow

**Creating a domain:**
1. In the sidebar, go to **Infrastructure → Domains**
2. Click **Create**
3. Fill in the required fields:

   | Field | Instructions |
   |-------|--------------|
   | **Name** | Full domain (e.g., example.com) |
   | **Service Provider** | Select the registrar/provider from the dropdown |
   | **Registration Date** | Date the domain was registered |
   | **Expiry Date** | Date the domain registration expires |
   | **Status** | Active, Inactive, Expired, Suspended |
   | **Cost** | Monthly/yearly cost (optional) |
   | **Cloudflare Status** | Not configured, Enabled, Disabled, Unknown |
   | **Module** | Select the module this domain belongs to |
   | **Hosting** | Link to the associated hosting account (optional) |
   | **User** | Assign to a specific user (optional) |
   | **Auto Renew** | Check if the domain has auto-renewal enabled |
   | **DNS Servers** | Comma-separated list of DNS server names |
   | **Notes** | Any relevant information |

4. Click **Save**

**Editing a domain:**
1. Navigate to the domain's detail page
2. Click **Edit**
3. Update any field (nameservers, status, contact info)
4. Click **Save**

**Deleting a domain:**
1. Navigate to the domain's detail page
2. Click **Delete**
3. Confirm — the domain is soft-deleted (Super Admin can restore)

### Best Practices
- Always link a domain to its Service Provider for easy reference
- Set accurate expiry dates — Expiry Trackers rely on these for renewal notifications
- Update status promptly when a domain expires or is transferred
- Use the Module field to organize domains by team or client group

### Common Mistakes
- Forgetting to link the domain to a service provider — the record becomes hard to find
- Leaving placeholder expiry dates — Expiry Trackers will send incorrect reminders
- Deleting a domain that still has linked services — the links break

### Typical Business Scenario
**Adding a new client domain:** A client registers example.com through your company. You create a Domain record, link it to your company as the Service Provider, set the registration and expiry dates, and assign it to the client's module.

### Expected Result
The domain appears in the Domains list with all details. Other team members with Domains access can see it. Expiry Trackers will use the date for renewal reminders.

---

## Managing Hosting Accounts

### Purpose
Record and maintain hosting account information including server credentials.

### When to Use
- A new hosting account is provisioned for a client
- Server resources or IP addresses change
- A hosting account is cancelled or transferred

### Permission Required
View, Create, Edit, Delete (Delete may be disabled depending on your role configuration). Reveal for viewing passwords.

### Step-by-Step Workflow

**Creating a hosting account:**
1. Go to **Infrastructure → Hosting**
2. Click **Create**
3. Fill in:

   | Field | Instructions |
   |-------|--------------|
   | **Domain** | Link to the primary domain on this account |
   | **Service Provider** | Select the hosting provider |
   | **Server Type** | Shared, VPS, Dedicated, etc. |
   | **Server IP** | IP address of the server |
   | **Username** | Control panel username |
   | **Password** | Control panel password (encrypted on save) |
   | **Status** | Active, Suspended, Cancelled, etc. |
   | **Module** | Organize by team or client group |

4. Click **Save**

**Updating hosting details:**
1. Open the hosting record
2. Click **Edit**
3. Update resource limits, IP, credentials, or linked domain
4. Click **Save**

### Best Practices
- Link hosting to the correct **Domain** — this creates a cross-reference visible from both records
- Store the control panel URL in the Notes field for quick access
- Update passwords in the system immediately after changing them on the server
- Use the correct Module assignment so the record is visible to the right team

### Common Mistakes
- Not linking to a domain — the hosting record floats without a client reference
- Storing incorrect IP addresses — this affects monitoring and troubleshooting
- Not updating passwords after server-side changes — credentials become out of sync

### Typical Business Scenario
**Provisioning shared hosting:** A client orders shared hosting. You create the Hosting record, link it to their domain and provider, set the username/password, and assign the module. The client's IT Staff can now see and manage this record.

### Expected Result
The hosting account appears in the list. Password is encrypted. Linked domain and provider are cross-referenced.

---

## Managing VPS Records

### Purpose
Track VPS instances including SSH credentials and specifications.

### When to Use
- A new VPS is provisioned
- VPS resources (CPU, RAM, disk) are upgraded
- A VPS is decommissioned

### Permission Required
View, Create, Edit, Delete. Reveal for SSH/root passwords.

### Step-by-Step Workflow

1. Go to **Infrastructure → VPS**
2. Click **Create**
3. Enter:

   | Field | Instructions |
   |-------|--------------|
   | **Name/Label** | Server hostname or label |
   | **Provider** | Select the VPS provider |
   | **IP Address** | Primary IP address |
   | **OS** | Operating system (Ubuntu, CentOS, etc.) |
   | **Specifications** | CPU cores, RAM, disk space |
   | **Username** | SSH username (usually root or admin) |
   | **Password** | SSH password or key passphrase |
   | **Status** | Active, Suspended, Decommissioned |
   | **Module** | Team or client group assignment |

4. Click **Save**

### Best Practices
- Keep specifications accurate — they are used for cost analysis
- Store the SSH port if it is non-standard
- Update the IP immediately if it changes — other records may reference it
- Use the Notes field to track maintenance windows

### Common Mistakes
- Storing weak SSH passwords in the system — use the same strong standards as production
- Not specifying the OS version — important for vulnerability tracking
- Forgetting to update status after decommissioning — skews active counts

### Typical Business Scenario
**New VPS for development:** A developer requests a VPS for staging. You create the record with the provider details, SSH credentials, and link it to the development team's module.

### Expected Result
The VPS record is created with encrypted password. Team members with access can see specs, IP, and status.

---

## Managing VoIP Accounts

### Purpose
Track VoIP phone lines, extensions, and credentials.

### When to Use
- A new phone line is provisioned for a client
- Extension details change
- A phone line is cancelled

### Permission Required
View, Create, Edit, Delete. Reveal for main password and extension passwords.

### Step-by-Step Workflow

1. Go to **Infrastructure → VoIP**
2. Click **Create**
3. Enter:

   | Field | Instructions |
   |-------|--------------|
   | **Service Name** | Account identifier |
   | **Provider** | VoIP service provider |
   | **Main Username** | SIP username or account ID |
   | **Main Password** | SIP password (encrypted) |
   | **Extensions** | Add one or more extensions with usernames and passwords |
   | **Status** | Active, Suspended, Cancelled |
   | **Module** | Team or client group assignment |

4. Click **Save**

To **reveal extension passwords**, open the record and click the reveal icon next to the extension password field (requires Reveal permission).

### Best Practices
- Add all extensions at creation time — they can be added later but it is easier during setup
- Label extensions with the user's name or desk location
- Test credentials after saving by revealing and verifying

### Common Mistakes
- Forgetting to add extensions — the account record exists but the line details are missing
- Storing wrong extension passwords — creates confusion during troubleshooting
- Not linking to a client module — the record may not be visible to the right team

### Typical Business Scenario
**Setting up office phones:** A client needs 5 phone lines. You create a VoIP account with the provider details, add 5 extensions with usernames and passwords, and link to the client's module.

### Expected Result
The VoIP account appears with all extensions. Passwords are encrypted. Team members can reveal passwords when configuring desk phones.

---

## Managing Service Providers

### Purpose
Maintain a directory of all service providers (registrars, hosting companies, ISPs, etc.).

### When to Use
- A new provider is contracted
- Provider contact details change
- A provider is no longer used

### Permission Required
View, Create, Edit, Delete. Reveal for provider account passwords.

### Step-by-Step Workflow

1. Go to **Infrastructure → Service Providers**
2. Click **Create**
3. Enter:

   | Field | Instructions |
   |-------|--------------|
   | **Name** | Provider company name |
   | **Website** | Provider's website URL |
   | **Contact Info** | Sales/support email, phone number |
   | **Username** | Your account username with this provider |
   | **Password** | Your account password (encrypted) |
   | **Module** | Team or client group assignment |

4. Click **Save**

### Best Practices
- Keep provider contact info current — you will need it during outages
- Store the account URL (e.g., https://cloud.example.com/login) in the Notes field
- Update passwords immediately when changed on the provider's website
- List all providers even if infrequently used — they may be needed during emergencies

### Common Mistakes
- Storing outdated contact info — useless during an outage
- Not storing the login URL — adds friction when you need to access the provider portal
- Sharing the same provider record across unrelated clients — use separate modules

### Typical Business Scenario
**Adding a new domain registrar:** Your company starts using a new registrar. You create a Service Provider record with the login URL, credentials, and support contact. Now any team member can access the registrar account.

### Expected Result
The provider record is saved with encrypted credentials. It is now available in dropdown menus when creating Domains, Hosting, or other linked records.

---

## Managing Domain Emails

### Purpose
Track email mailbox accounts for client domains.

### When to Use
- A new mailbox is created for a client
- An email password needs updating
- A mailbox is removed

### Permission Required
View, Create, Edit, Delete. Reveal for email passwords.

### Step-by-Step Workflow

1. Go to **Infrastructure → Domain Emails**
2. Click **Create**
3. Enter:

   | Field | Instructions |
   |-------|--------------|
   | **Email Address** | Full email address |
   | **Domain** | Link to the domain record |
   | **Hosting** | Link to the hosting account (if applicable) |
   | **Password** | Mailbox password (encrypted) |
   | **Status** | Active, Suspended, Deleted |
   | **Module** | Team or client group assignment |

4. Click **Save**

### Best Practices
- Link to both the Domain and Hosting for complete cross-referencing
- Use Reveal only when configuring a new email client — then change the password
- Add Notes for mailbox quota or special settings

### Common Mistakes
- Not linking to the domain — the email floats without a parent reference
- Storing the wrong password — verify by revealing and testing immediately
- Creating duplicate email records — search before adding

### Typical Business Scenario
**Setting up client email:** A client needs 3 mailboxes. You create 3 Domain Email records, linked to their domain and hosting account. The client's IT Staff can now reveal passwords for Outlook/Thunderbird setup.

### Expected Result
Email accounts appear linked to the domain. Passwords are encrypted. Reveal is available with the correct permission.

---

## Managing Other Services

### Purpose
Track any service that does not fit into the predefined categories.

### When to Use
- Managing services like CDN accounts, monitoring tools, backup services
- The service has credentials that need storage
- No other module fits the service type

### Permission Required
View, Create, Edit, Delete. Reveal for passwords.

### Step-by-Step Workflow

1. Go to **Infrastructure → Other Services**
2. Click **Create**
3. Enter:

   | Field | Instructions |
   |-------|--------------|
   | **Name** | Service name (e.g., "CloudFlare Account") |
   | **Service Type** | Category or type description |
   | **URL** | Service login URL |
   | **Username** | Login username |
   | **Password** | Login password (encrypted) |
   | **Module** | Team or client group assignment |

4. Click **Save**

### Best Practices
- Use the Name field descriptively so others understand what this record is
- Always include the login URL — it saves time when someone needs to access the service
- Use a consistent naming convention for Service Type to enable filtering

### Common Mistakes
- Creating records in Other Services that belong in another module — check the 9 infrastructure modules first
- Not including the URL — the record becomes a credential graveyard

### Typical Business Scenario
**Tracking CDN credentials:** Your company uses a CDN service. You create an Other Services record with the login URL, account credentials, and assign it to the appropriate module.

### Expected Result
The service appears in Other Services with encrypted credentials. Team members can access login details when needed.

---

## Managing Expiry Trackers

### Purpose
Set up automated renewal reminders for services that have expiration dates.

### When to Use
- A new renewable service is added (domain, SSL, hosting plan)
- An existing service needs reminder configuration
- Notification recipients need updating

### Permission Required
View, Create, Edit. Delete may be available depending on your role.

### Step-by-Step Workflow

**Creating an expiry tracker:**
1. Go to **Infrastructure → Expiry Trackers**
2. Click **Create**
3. Set up the tracker:

   | Field | Instructions |
   |-------|--------------|
   | **Name** | Description of what is being tracked |
   | **Service Provider** | Link to the provider (optional) |
   | **Expiry Date** | The next renewal date |
   | **Notify Days Before** | When to send reminders (e.g., 7, 14, 30) |
   | **Assigned User** | Person responsible for this renewal |
   | **Email Notifications** | Enable/disable automated reminders |
   | **Module** | Team or client group assignment |

4. Click **Save**

**Configuring notifications:**
1. Open the tracker's detail page
2. Expand the notification configuration section
3. Set:
   - **Notify Admins** — All admin users receive the reminder
   - **Custom Emails** — Enter specific email addresses
4. Click **Save**

**Sending a manual reminder:**
1. Open the tracker's detail page
2. Click **Send Reminder Now**
3. The system sends notifications to all configured recipients immediately

**Sending a test email:**
1. Open the tracker's detail page
2. Click **Test Email**
3. A test notification is sent to your email address — verify it arrives

### Best Practices
- Create Expiry Trackers for EVERY renewable service — they are the backbone of proactive renewal management
- Set multiple notification days (e.g., 30, 14, 7) for important renewals
- Assign a specific team member as responsible for each tracker
- Review notification history monthly to confirm reminders are being sent

### Common Mistakes
- Enabling notifications but not configuring recipients — no one gets the reminder
- Setting the wrong expiry date — notifications will trigger at the wrong time
- Not checking notification history — you may not know reminders are failing
- Relying on default SMTP without verifying it works — ask your Super Admin to test

### Typical Business Scenario
**Tracking domain renewals:** You create Expiry Trackers for all client domains set to notify 30, 14, and 7 days before expiry. The system now automatically emails the assigned team member and admin group when renewal is approaching.

### Expected Result
The Expiry Tracker is active. On the configured notification days, the system sends email reminders. You can view notification history and send manual reminders as needed.

---

## Managing Assets

### Purpose
Track physical and digital assets, including specifications, assignments, and condition monitoring.

### When to Use
- Hardware is purchased and needs tracking
- An asset is assigned to an employee
- An asset is returned or decommissioned

### Permission Required
View, Create, Edit, Delete. Reveal for viewing linked vault credentials.

### Step-by-Step Workflow

**Creating an asset:**
1. Go to **Infrastructure → Assets**
2. Click **Create**
3. Enter:

   | Field | Instructions |
   |-------|--------------|
   | **Asset Tag** | Leave blank to auto-generate, or enter a custom tag |
   | **Category** | Select the asset category (e.g., Laptop, Network Device) — determines available specification fields |
   | **Type** | Select the specific model/brand within the chosen category |
   | **Serial Number** | Manufacturer serial number |
   | **QR / Barcode ID** | Optional QR code or barcode identifier for physical inventory scanning |
   | **Status** | Available, Assigned, Lost, Decommissioned |
   | **Condition** | New, Good, Fair, Poor, Damaged |
   | **Location** | Optional physical location |
   | **Department** | Optional department name |
   | **Issue Date** | Date the asset was issued to a user |
   | **Return Date** | Expected or actual return date |
   | **Vault Credentials** | Optionally link a vault entry containing login credentials |
   | **Module** | Team or client group assignment |
   | **User** | Assign the asset to a specific user |
   | **Specifications** | Dynamic fields that appear based on the selected category (e.g., processor, RAM, OS for laptops; MAC address, IP for network devices) |
   | **Notes** | Any relevant information |
   | **Primary Image** | Upload a JPEG, PNG, or WebP image of the asset |

4. Click **Save**

**Assigning an asset to a user:**
1. Open the asset's detail page (or use the User field during creation)
2. Set the **User** field to the person assigned to this asset
3. Set the **Issue Date** to when the asset was given out
4. Optionally set an expected **Return Date**
5. Click **Save**

**Recording a return:**
1. Open the asset's detail page
2. Clear the **User** field and update **Status** to Available
3. Update **Condition** to reflect wear on return
4. Click **Save**

### Best Practices
- Create assets immediately upon purchase — before they are assigned
- Use the Condition field to track wear and tear over time
- Record serial numbers and QR/barcode IDs for inventory management
- Take advantage of category-specific specification fields — they auto-populate based on the category you select
- Link vault credentials to assets that require login access

### Common Mistakes
- Not recording assignments — you lose track of who has what
- Forgetting to record returns — assets show as "Assigned" when they are not
- Using vague Asset Tags — be specific: "Dell Latitude 5420 - Service Tag ABC123"
- Not updating condition on return — you cannot hold users accountable for damage
- Not selecting a Category first — the Type and Specification fields depend on it

### Typical Business Scenario
**Issuing a laptop to a new employee:** You create the asset record when the laptop arrives, selecting the Laptop category which reveals specification fields for processor, RAM, storage, and OS. When the employee starts, you assign the user and set an issue date. When the employee leaves, you clear the user field, update the status to Available, and update the condition based on wear.

### Expected Result
Assets are tracked from purchase through assignment to return with full specifications. Category-dependent fields adapt automatically. The activity log shows who has been assigned each asset and when it was returned.

---

## Revealing Passwords

### Purpose
View decrypted passwords for service records to perform configuration or troubleshooting.

### When to Use
- You need to log into a service (cPanel, SSH, email, VoIP portal)
- A client requests their credentials
- You are performing password rotation

### Permission Required
**Reveal** permission on the specific module. This is independent of View permission — you can see the record but not the password without explicit Reveal permission.

### Step-by-Step Workflow

1. Navigate to the record's **detail page** (e.g., Hosting, VPS, VoIP, Service Provider, Domain Email)
2. Find the password field — it shows as masked (****)
3. Click the **Reveal** icon or **Show Password** button
4. The password is decrypted and displayed
5. Use the password as needed for your task

> **Password reveal is always audited.** The system records in Activity Logs: who revealed, which record, and when. This is not configurable.

> **Rate limit:** Password reveal is limited to prevent abuse (10 attempts per minute). If you reach the limit, wait before trying again.

### Best Practices
- Only reveal passwords when you have an immediate need — unnecessary reveals bloat the audit log
- Change passwords after using them for external sharing (e.g., giving to a client)
- Never share revealed passwords through insecure channels (email, chat without encryption)
- Use the **Copy** button if available to avoid displaying the password on screen

### Common Mistakes
- Revealing a password and forgetting to record the new one after rotation — store it in the system immediately
- Exceeding the rate limit by repeatedly revealing the same record — reveal once and make a note
- Assuming Reveal permission gives you the password permanently — it shows it once

### Typical Business Scenario
**Configuring a client's email client:** The client needs their email password to configure Outlook. You open the Domain Email record, click Reveal, read the password to them over a secure call, then suggest they change it.

### Expected Result
The password is decrypted and visible. The activity log shows you performed the reveal. If you have the Copy button, you can paste it without it being visible to bystanders.

---

## Managing Tasks

### Purpose
Create, track, and complete operational tasks across modules.

### When to Use
- Work needs to be assigned to a team member
- You are tracking progress on a multi-step process
- A follow-up is needed after client onboarding
- You want to log work completed for reporting

### Permission Required
**View** to see tasks (module-scoped + assigned tasks). **Create** to make new tasks. **Edit** to modify. **Delete** to remove.

### Step-by-Step Workflow

**Creating a task:**
1. Go to **Operations → Tasks**
2. Click **Create Task**
3. Fill in:

   | Field | Instructions |
   |-------|--------------|
   | **Title** | Short description of what needs to be done |
   | **Description** | Detailed instructions or notes |
   | **Module** | Link the task to a specific module (so the team can find it) |
   | **Assigned To** | Select one or more team members |
   | **Priority** | Low, Medium, High, Urgent |
   | **Due Date** | When the task should be completed |

4. Click **Save**

**Updating task status:**
1. Open the task
2. Click the **Status** dropdown
3. Select: **Open → In Progress → Completed → Closed**
4. Optionally add a status note

**Using the Kanban board:**
1. Go to **Operations → Tasks**
2. Click **Kanban**
3. Drag-and-drop tasks between columns: Pending, In Progress, Completed, Cancelled
4. Changes save automatically

**Viewing your tasks:**
1. Go to **Operations → My Tasks**
2. This shows only tasks where you are an assignee
3. Use filters to view by status, priority, or due date

### Best Practices
- Always assign a **Module** — this helps other team members find the task
- Set realistic **Due Dates** — the Calendar views depend on them
- Update status promptly when work begins — keeps the team informed
- Use **My Tasks** as your daily to-do list
- Add descriptive information to the Description field — save context for future reference

### Common Mistakes
- Creating a task without assigning it to anyone — it appears in the module but nobody gets notified
- Setting the wrong Module — the task may not be visible to the relevant team
- Leaving tasks in "Open" status when work is complete — skews reporting
- Forgetting to use the Module field — the task floats without context

### Typical Business Scenario
**Tracking a support request:** A client reports their website is slow. You create a task "Investigate slow loading on example.com" assigned to the IT Staff, linked to the Hosting module, with High priority. The IT Staff sees the task, updates to In Progress, investigates, and marks Completed with a resolution note.

### Expected Result
The task appears in the Tasks list and Kanban board. Assigned users receive a notification. Status changes are visible to the team. The task history provides an audit trail.

---

## Exporting Data

### Purpose
Download module data as a CSV file for analysis in spreadsheets or external reporting.

### When to Use
- Management requests a list of all domains
- You need to analyze costs offline
- You need to share data with someone who does not have portal access

### Permission Required
**Export** permission on the specific module.

### Step-by-Step Workflow

1. Navigate to the module's **index page** (e.g., Domains, Hosting)
2. Click the **Export** button (usually near the search bar)
3. The system generates and downloads a CSV file
4. Open the CSV in your spreadsheet application

> **Export behavior:**
> - Exports reflect whatever is visible to you — if you can see only some modules, the export only includes those records
> - Export is rate-limited — if you export frequently, you may need to wait
> - Passwords are NOT included in exports — security measure

### Best Practices
- Apply filters before exporting to narrow the dataset
- Check the export file for completeness before sharing
- Be aware that the download contains data — handle it according to your organization's data policies
- Use the file name the system generates — it includes the date and module name

### Common Mistakes
- Exporting without applying filters — you may get too much data
- Expecting passwords in the export — passwords are never included for security
- Sharing CSV files over unsecured channels — the data may be sensitive

### Typical Business Scenario
**Monthly domain report:** Management wants a list of all active domains. You go to Domains, filter by Status = Active, click Export, and share the resulting CSV with the manager.

### Expected Result
A CSV file downloads containing the visible records with their details. Open it in Excel or Google Sheets for filtering, sorting, and analysis.

---

## Using Bulk Actions

### Purpose
Perform the same action on multiple records at once instead of updating each individually.

### When to Use
- Updating the status of multiple services (e.g., marking several domains as "Expired")
- Deleting multiple records at once
- Changing the status of multiple tasks

### Permission Required
- **Bulk delete** — Requires **Delete** permission on the module
- **Bulk update status** — Requires **Edit** permission on the module
- **Restore or force-delete** — Super Admin only

### Step-by-Step Workflow

1. Navigate to the module's **index page**
2. Select the records using the **checkbox** next to each row
3. Look for the **Bulk Actions** dropdown or toolbar
4. Select the action you want to perform:

   | Action | What It Does |
   |--------|--------------|
   | **Update Status** | Changes the status field for all selected records |
   | **Delete** | Soft-deletes all selected records |
   | **Restore** | Restores soft-deleted records (Super Admin only) |
   | **Force Delete** | Permanently removes records (Super Admin only) |

5. Confirm the action when prompted

> **Important:** Bulk delete checks your Delete permission, not record ownership. If you have Delete permission on the module, you can delete records you did not create.

### Best Practices
- Double-check your selection before confirming — bulk actions apply to all selected records
- Use filters before selecting to ensure you have the right set of records
- Bulk update status is safer than bulk delete — status can be changed back
- Test with a small selection first if you are unsure of the outcome

### Common Mistakes
- Selecting the wrong records — always review the count before confirming
- Using bulk delete when you meant to bulk update status — deletion requires restoration by Super Admin
- Expecting restore to work — it is Super Admin only
- Not realizing bulk delete removes records regardless of ownership

### Typical Business Scenario
**Bulk cleanup:** 20 domains expired last month. You filter by Status = Active and Expiry Date < today, select all, and use Bulk Actions to change their status to "Expired" in one operation instead of editing each one.

### Expected Result
The selected records all have the action applied. For status updates, the change is visible immediately. For deletes, records move to the trash view (visible with "Trashed" filter).

---

## What You Cannot Do

The following operations are **Super Admin only**. If you need any of these, contact a Super Admin.

| Operation | Why You Cannot Access It |
|-----------|-------------------------|
| **Manage Users** | Routes are behind `role:super-admin` middleware |
| **Manage Roles** | Routes are behind `role:super-admin` middleware |
| **Manage Module Permissions** | Routes are behind `role:super-admin` middleware |
| **View Activity Logs** | Routes are behind `role:super-admin` middleware |
| **View Login Audits** | Routes are behind `role:super-admin` middleware |
| **Manage SMTP Profiles** | Routes are behind `role:super-admin` middleware |
| **Manage Webhooks** | Routes are behind `role:super-admin` middleware |
| **View Reports** | Routes are behind `role:super-admin` middleware |
| **Import Data** | Only Super Admin can upload CSV imports |
| **Configure Features/Modules** | Routes are behind `role:super-admin` middleware |
| **Restore / Force-Delete** | Only Super Admin can reverse soft-deletes |
| **Server Health widget** | Dashboard widget only renders for Super Admin |
| **SMTP Status widget** | Dashboard widget only renders for Super Admin |

---

## Related Pages

- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md) — System configuration procedures
- [IT Staff Guide](04_IT_STAFF_GUIDE.md) — Service desk procedures
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Checklists and workflows
- [Workflow Guide](10_WORKFLOW_GUIDE.md) — Cross-module operating procedures
- [Permission Reference](08_PERMISSION_REFERENCE.md) — Complete permission reference
- [Quick Start Guide](01_QUICK_START_GUIDE.md) — First-day orientation

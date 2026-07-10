# OpsPilot Quick Start Guide

## What is OpsPilot?

OpsPilot is a web-based IT operations portal. It helps you manage:

- **Infrastructure** — domains, hosting, VPS, VoIP, service providers
- **Credentials** — passwords, logins, vault
- **Operations** — tasks, calendar, expiry reminders
- **Administration** — users, roles, permissions, activity logs

---

## How to Login

1. Open your browser and go to the portal URL (provided by your admin)
2. Click **Login**
3. Enter your **Email** and **Password**
4. Click **Sign In**

If you forget your password, click **Forgot Password** on the login page and follow the email link.

---

## Dashboard Overview

After login, you see the **Dashboard**. It shows:

- **Operations Widget** — total active services, monthly cost, services expiring soon
- **Renewals Widget** — expiry tracker status, SMTP health
- **Tasks Widget** — your pending tasks, overdue tasks
- **Assets Widget** — assigned/returned assets
- **Vault Widget** — recent credential reveals
- **Quick Actions** — buttons to create common records
- **Activity Widget** — recent activity in the system
- **Server Health Widget** — PHP version, database status, disk usage

---

## Sidebar Navigation

The sidebar is on the left. It has groups:

| Group | What You See |
|-------|-------------|
| **Infrastructure** | Service Providers, Hosting, Domains, Domain Emails, VPS, VoIP, Other Services, Renewals, Assets |
| **Credentials** | My Credentials, Shared Credentials |
| **Operations** | My Tasks, Task Management, Calendar |
| **Administration** (Super Admin only) | Users, Roles, Role Templates, Privileges, Modules, Permissions, Features, SMTP Profiles, Activity Logs, Login Audits, Import, Attachments, Webhooks, API Access |
| **Reports** (Super Admin only) | Reports |
| **Account** | My Profile, My Access, Help Center |

You can collapse/expand groups. Use **Ctrl+K** to open the command palette and search pages or records.

---

## First Day Setup (For Super Admin)

If you are setting up OpsPilot for the first time:

1. **Login** with your Super Admin credentials
2. Go to **SMTP Profiles** → **Create SMTP Profile** and add your email server
3. Go to **Service Providers** → **Create** and add your providers (GoDaddy, Namecheap, etc.)
4. Go to **Users** → **Create** and add your team members
5. Go to **Roles** → assign roles to users
6. Verify permissions in **Module Permissions**
7. Test email by going to an Expiry Tracker → **Test Email**

Detailed steps are in `OPSPILOT_WORKFLOW_GUIDE.md`.

---

## What is My Role?

| Role | What You Can Do |
|------|----------------|
| **Super Admin** | Everything. No restrictions. |
| **Admin** | Manage infrastructure records. See records by module permission. |
| **User** (default) | See all records in modules with Read permission. Create/Edit/Delete as permitted by module permissions. |
| **Read-Only** | View records but cannot create, edit, or delete. |

Your admin assigns your role. You can check your access at **My Access** in the sidebar.

---

## Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+K` | Open command palette (search pages and records) |
| `Escape` | Close modals, command palette |

---

## Need Help?

- Check `OPSPILOT_MODULE_GUIDE.md` for module-by-module instructions
- Check `OPSPILOT_COMMON_MISTAKES_AND_FAQ.md` for common issues
- Ask your Super Admin for role-specific questions

---

*First time reading? Next: `OPSPILOT_MODULE_GUIDE.md` — learn what each module does.*

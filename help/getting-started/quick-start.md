# Quick Start Guide

> **Audience:** All Users — **Purpose:** Complete your first login and learn how to navigate the portal.

## Logging In

Open the portal URL in your browser. Enter your email address and password, then click **Sign In**. If you have not verified your email, check your inbox for a verification message.

After successful login, you land on the **Dashboard**.

## Sidebar Navigation

The sidebar on the left organises every module into groups. Click a group header to expand it and see the modules inside. The sidebar adapts to your screen — on mobile it collapses into a hamburger menu.

Modules you do not have Access to are hidden from the sidebar.

## Global Search and Command Palette

Press **Ctrl+K** (or **Cmd+K** on Mac) to open the command palette from any page. You can:

- Search for records across all modules you have Access to
- Navigate directly to any module page
- Find help topics

The search bar in the top navigation also supports cross-module record search.

## Dashboard

The Dashboard is your starting point. It shows widgets relevant to your role:

- **Operations Summary** — active services, monthly costs, upcoming expiries
- **Renewal Summary** — renewal tracker stats and expiry forecast
- **Monitoring** — service health, online/offline status
- **Tasks** — overdue tasks, due this week, your pending items
- **Asset Summary** — asset inventory and recent assignments
- **Vault Summary** — credential overview and recent reveals
- **Quick Actions** — one-click links to create common records
- **Recent Activity** — latest changes across your modules

Widgets vary by role. Super Admins see all widgets plus SMTP Profiles and Server Health panels.

Each widget has a **View Full Report** link that takes you to the relevant module for deeper analysis.

## Opening Records

Click any module in the sidebar to see its record list. Tables support sorting, searching, and pagination. Click a record name or the **View** action to open its detail page.

## Action Menus

Every record row has an action menu (three dots) with available operations. The options depend on your Manage permission:

- **Edit** — modify the record
- **Delete** — Super Admin only for business records
- **View** — open the detail page
- **Reveal Password** — available where you have reveal permission

## My Permissions

Click your avatar in the top-right corner, then select **My Permissions** to see your effective permissions. The page lists every module you can access, along with your Access, Manage, Import, and Export status. See *Understanding Permissions* and *My Permissions* for details.

## Help Center

The Help Center is available from the sidebar under **Account > Help Center (Guide)**. It contains:

- Getting started guides
- Module documentation
- Permission reference
- Credential reveal reference
- Changelog

Use the search bar in the Help Center to find specific topics.

## Security Expectations

- Passwords are encrypted at rest using AES-256
- Revealing a password is logged and notified
- Suspended users cannot access the portal
- Email verification is required
- Login attempts are audited
- Session management follows Laravel security best practices

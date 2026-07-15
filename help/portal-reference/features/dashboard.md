# Dashboard

> **Audience:** All Users — **Purpose:** Understand the Dashboard layout, available widgets, and what actions you can take from it.

## Overview

The Dashboard is the first page you see after logging in. It displays a set of cards (widgets) that summarise key data from across the portal. Widgets are cached and refresh automatically.

## Widgets by Role

The widgets you see depend on your role. Super Admins see all widgets; other roles see a subset:

| Widget | SA | Admin | Editor | User | Customer |
|--------|----|-------|--------|------|----------|
| Operations Summary | ✓ | ✓ | | ✓ | |
| Renewal Summary | ✓ | ✓ | | | |
| Monitoring | ✓ | ✓ | ✓ | ✓ | |
| Tasks | ✓ | ✓ | ✓ | ✓ | ✓ |
| Asset Summary | ✓ | ✓ | ✓ | | |
| Vault Summary | ✓ | ✓ | ✓ | ✓ | ✓ |
| Quick Actions | ✓ | ✓ | ✓ | ✓ | ✓ |
| Recent Activity | ✓ | ✓ | ✓ | ✓ | ✓ |
| SMTP Profiles | ✓ | | | | |
| Server Health | ✓ | | | | |

## Each Widget in Detail

### Operations Summary

Shows a snapshot of all tracked services:

- **Active Services** — total active records across Domains, Hostings, VPS, VoIP, Service Providers, Domain Emails, Other Services, and Expiry Trackers
- **Monthly Cost** — combined monthly cost of all active services
- **Expiring (30d)** — services due to expire within the next 30 days
- **Active Providers** — number of active service providers
- **Chart** — doughnut chart of services by type
- **Upcoming Expiries** — top 5 soonest expiring services per type

**Navigation:** Click **View Full Report** to go to the Domains report page.

### Renewal Summary

Tracks renewal notifications and expiry dates:

- **Total Trackers** — all configured expiry trackers
- **Manual Today** — manually triggered notifications today
- **Auto Today** — cron-triggered notifications today
- **Failed Today** — notifications that failed today (highlighted in red if > 0)
- **Upcoming Renewals** — top 10 trackers expiring within 30 days with days remaining
- **30-Day Stats** — sent and failed notification counts
- **SMTP Profiles (SA only)** — total configured SMTP profiles
- **Chart** — bar chart of expiry forecast for the next 6 months

**Navigation:** Click **View Full Report** to go to the Renewals report page.

### Monitoring

Tracks service health across all monitored resources:

- **Total Monitored** — records with a monitoring URL configured
- **Online** — responded within the last 2 hours
- **Offline** — last response more than 2 hours ago
- **Unchecked** — never pinged
- **Offline Services** — top 5 offline items with type, name, and last-ping time (clickable links to the resource detail page)

**Navigation:** Click **View All** to go to the Monitoring module.

### Tasks

Summarises task status across the system:

- **Total Tasks**
- **Overdue** — past due date and not completed
- **Due This Week** — due within the current week
- **My Pending** — tasks assigned to you that are not completed
- **Chart** — doughnut chart of tasks by status

**Navigation:** Click **View Full Report** to go to the Tasks report page.

### Asset Summary

Tracks hardware and software assets:

- **Total Assets**
- **Assigned Today** — assignments created today
- **Returned Today** — returns processed today
- **Available** — unassigned assets
- **Chart** — doughnut chart of assets by status
- **Recent Assignments** — top 5 currently assigned items with asset tag, assignee, and time

**Navigation:** Click **View Full Report** to go to the Assets report page.

### Vault Summary

Summarises credential vault activity:

- **Total Entries** — all vault entries (Super Admin) or your entries (other roles)
- **Revealed Today** — passwords revealed today
- **My Entries** — entries you own
- **Reveals (30d)** — total reveals in the last 30 days
- **Recent Reveals** — last 5 reveal events with causer name and timestamp

### Quick Actions

One-click buttons to create new records. Available actions depend on your permissions:

- **Feature** — Super Admin only
- **Module** — Super Admin only
- **User** — Super Admin only
- **Task** — always available
- **Domain** — if you have Manage on Domains
- **Hosting** — if you have Manage on Hostings
- **VPS** — if you have Manage on VPS
- **VoIP** — if you have Manage on VoIP
- **Vault Entry** — if you have Manage on Vault
- **Asset** — if you have Manage on Assets

### Recent Activity

The last 10 activity log entries across the system (your own activity for non-Super Admin users). Each entry shows the person who performed the action, a description, and the timestamp. Clickable entries navigate to the related record's detail page.

Activity covers creates, updates, deletes, restores, password reveals, and other auditable events.

### SMTP Profiles (Super Admin Only)

Collapsible panel showing SMTP mail profile status:

- **Total Profiles**
- **Active** — profiles with `is_active = true`
- **Failed Tests** — profiles whose last test failed
- **In Use** — profiles currently assigned to expiry trackers
- **Active Profile Statuses** — list of each active profile with last test result badge

### Server Health (Super Admin Only)

Collapsible panel showing system-level health metrics:

- **PHP Version** — current PHP runtime version
- **Laravel Version** — application framework version
- **App Version** — deployed application version
- **Cache Driver** — active cache backend
- **Session Driver** — active session backend
- **Queue Driver** — active queue backend
- **Database** — connection type and connectivity status
- **Mail Status** — Working / Configured (untested) / Configured (inactive) / Not configured
- **Disk Usage** — percentage used, free space, total space
- **Scheduler** — last time the task scheduler ran

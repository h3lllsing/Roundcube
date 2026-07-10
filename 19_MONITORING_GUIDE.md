# Monitoring Guide

> **Audience:** All Users — **Purpose:** Monitor service uptime and check availability of infrastructure services

## Table of Contents

- [Accessing Monitoring](#accessing-monitoring)
- [Understanding Status Indicators](#understanding-status-indicators)
- [Performing an On-Demand Check](#performing-an-on-demand-check)
- [Interpreting Results](#interpreting-results)
- [Filtering and Searching](#filtering-and-searching)
- [Monitoring Dashboard Widget](#monitoring-dashboard-widget)
- [Best Practices](#best-practices)
- [Common Mistakes](#common-mistakes)

---

## Accessing Monitoring

### Purpose
View the current uptime status of all infrastructure services (domains, hosting, VPS, VoIP, etc.) from a single screen.

### When to Use
- At the start of each shift to verify all services are online
- When a client reports a service is down
- After making configuration changes to verify services are reachable
- During incident response to check affected services

### Permission Required
You must have **View** permission on the relevant infrastructure modules. You will only see monitoring results for modules you can access.

### Step-by-Step Workflow

1. Click **Monitoring** in the sidebar
2. The overview page shows all services that have a monitoring URL configured
3. Review the summary cards at the top:
   - **Total** — All monitored services
   - **Online** — Services that responded successfully within the last 2 hours
   - **Offline** — Services that have not responded in over 2 hours
   - **Unchecked** — Services that have never been checked
4. Scroll through the service list to see individual statuses

> **Note:** A service must have a **Monitoring URL** configured on its record to appear in Monitoring. If a service is missing from the list, edit the service record and add a URL in the "Monitoring URL" field.

### Best Practices
- Check Monitoring at the start of each shift as part of your morning routine
- Investigate offline services promptly — a service marked offline means no successful check in 2+ hours
- Configure monitoring URLs for all customer-facing services

### Common Mistakes
- Expecting to see all services — only services with a monitoring URL configured will appear
- Confusing "Unchecked" with "Offline" — Unchecked means never tested, not necessarily down
- Forgetting that monitoring is on-demand only — the system does not automatically re-check

### Typical Business Scenario
**Morning check:** An IT Staff member opens Monitoring at 9 AM. They see 45 total services: 42 online, 2 offline, 1 unchecked. They click the Check button on the 2 offline services to verify if they are truly down or just had a temporary blip last night.

### Expected Result
A searchable, filterable list of all monitored services with color-coded status indicators and the ability to perform on-demand checks.

### Related Pages
- [Admin Guide](03_ADMIN_GUIDE.md) — Configuring monitoring URLs on service records
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Morning checklist procedures

---

## Understanding Status Indicators

| Status | Color | Meaning |
|--------|-------|---------|
| **Online** | Green | Service responded successfully within the last 2 hours |
| **Offline** | Red | Service has not responded in over 2 hours (last check failed or timed out) |
| **Unchecked** | Amber | Service has a monitoring URL but has never been tested |

> **Tip:** The 2-hour window means a service marked Online may have been available up to 2 hours ago. For real-time status, perform an on-demand check.

---

## Performing an On-Demand Check

### Purpose
Immediately test whether a specific service is reachable, without waiting for the 2-hour status window.

### When to Use
- A client reports an issue and you need to verify service availability right now
- After making changes to a service (DNS, server config) to confirm it is reachable
- When investigating an "Offline" status that may be outdated

### Step-by-Step Workflow

1. Navigate to **Monitoring**
2. Find the service you want to check (use search or filter if needed)
3. Click the **Check** button next to that service
4. Wait a few seconds for the check to complete
5. Review the result:
   - **Success** — Service is reachable (status updates to Online)
   - **Failure** — Service is unreachable (status may change to Offline)

> **Rate limit:** On-demand checks are limited to prevent abuse. If you perform too many checks quickly, you may see a rate limit error. Wait 1 minute before trying again.

### Best Practices
- Use on-demand checks for troubleshooting, not routine monitoring
- Check the service URL manually first (visit in browser, ping) to confirm the issue before using the system check
- Document offline findings in tasks or notes

### Common Mistakes
- Running checks on services without a monitoring URL configured — the Check button will not appear
- Expecting instant results — network checks can take several seconds depending on the service
- Overusing on-demand checks — the rate limit is in place for a reason

### Typical Business Scenario
**Incident verification:** A client calls saying their website is down. The IT Staff member opens Monitoring, finds the client's hosting record (showing "Offline"), and clicks Check. The result shows "Failure" — confirming the service is indeed unreachable. They create a task to investigate.

### Expected Result
The system performs a connectivity check on the specified service and returns a success or failure result within a few seconds.

---

## Interpreting Results

### What "Online" Means
The monitoring system successfully connected to the service URL. For most services, this means:
- The server is powered on and responding
- The web server or service is accepting connections
- The network path is functional

### What "Offline" Means
The monitoring system could not establish a connection. Possible causes:
- Server is powered off or crashed
- Service (web server, database) has stopped
- Network issue (firewall, routing, DNS) blocking access
- Temporary blip — try an on-demand check to confirm

### What "Unchecked" Means
The service has a monitoring URL configured but no check has ever been performed. This is normal for:
- Newly added services
- Services where monitoring was recently enabled
- Services that have not yet been reached by the 2-hour window logic

> **Important:** Unchecked does NOT mean down. It simply means "no data yet." Perform an on-demand check to establish a baseline.

---

## Filtering and Searching

### Purpose
Quickly find specific services in the monitoring list without scrolling through all entries.

### Step-by-Step Workflow

1. On the Monitoring page, use the **Search** box to type a service name or URL
2. Use the **Type** dropdown to filter by service type (Domain, Hosting, VPS, etc.)
3. Use the **Status** dropdown to filter by current status (Online, Offline, Unchecked)
4. Click **Filter** to apply your filters
5. Click **Clear** to remove all filters

### Best Practices
- Combine filters for precise results (e.g., Status=Offline + Type=Hosting)
- Use search when you know the service name or URL
- Sort columns by clicking column headers

---

## Monitoring Dashboard Widget

### Purpose
View a quick summary of monitoring status from the Dashboard without navigating to the full Monitoring page.

### When to Use
- During your morning Dashboard review
- When you want a quick at-a-glance status check

### What You See
A Dashboard card showing:
- **Online** count (green)
- **Offline** count (red)
- **Unchecked** count (amber)

Click on any count to navigate directly to the filtered Monitoring page.

---

## Best Practices

- **Configure monitoring URLs** for ALL customer-facing services during onboarding
- **Check monitoring daily** as part of your morning routine
- **Investigate offline services** promptly — 2 hours is a significant gap
- **Document findings** in tasks or notes when investigating offline services
- **Use on-demand checks** for troubleshooting, not routine status checks

---

## Common Mistakes

- **Not configuring monitoring URLs** — services without a URL never appear in Monitoring
- **Ignoring offline status** — a service marked offline has been unreachable for 2+ hours
- **Confusing unchecked with online** — unchecked means no data; always perform an initial check
- **Over-checking** — running checks every few minutes does not speed up the 2-hour status window

---

## Related Pages

- [Quick Start Guide](01_QUICK_START_GUIDE.md) — First-day orientation
- [Daily Operations Guide](06_DAILY_OPERATIONS_GUIDE.md) — Morning checklist
- [FAQ](07_FAQ.md) — Troubleshooting common issues

# OpsPilot Software Team Guide

## Who is the Software Team?

The Software Team uses OpsPilot to:

- Track development and staging infrastructure
- Manage deployment credentials
- Create and complete development tasks
- Document system architecture via Notes
- Monitor server health

---

## What You Can Do

As a Software Team member (typically User role with module permissions), you can:

- View and manage infrastructure records for development/staging
- Create and track tasks
- Store deployment credentials in Vault
- Add technical notes
- View server health dashboard

---

## Useful Modules for Developers

### Vault
Store credentials for:
- Development databases
- Staging server access
- API keys for third-party services
- Deployment tokens

**Best practice:** Create a Shared Vault entry for team credentials. Set the module so the whole team can access.

### Tasks
Track development work:
- Feature implementation
- Bug fixes
- Code reviews
- Deployment tasks

Use priority levels: Low, Medium, High, Urgent.

### Notes
Document technical information:
- Server architecture
- Deployment procedures
- Environment configuration
- API documentation

### Server Health (Dashboard)
Monitor:
- PHP version
- Database connection status
- Disk usage
- Schedule (cron) status

### VPS
Track development and staging servers:
- Server specifications
- IP addresses
- Operating system
- Installed services

### Webhooks (Super Admin only)
Connect OpsPilot events to:
- Slack channels (deployments, task assignments)
- CI/CD pipelines
- Monitoring tools

---

## Common Workflows

### Deploying to a New Server

1. Add the server as a **VPS** record
2. Store SSH credentials in **Vault** (shared)
3. Create a **Task** for the deployment
4. Add **Notes** with deployment steps
5. Set up an **Expiry Tracker** for the server subscription

### Tracking a Bug Fix

1. Go to **Tasks → Create**
2. Title: "Fix login page CSS issue"
3. Priority: Medium
4. Due Date: set appropriately
5. When fixed → mark status as Completed

### Documenting Architecture

1. Go to **Notes → Create**
2. Write the architecture documentation
3. Example:
```
System Architecture (2026-06-27):
- Web Server: Nginx on web-01 (VPS)
- App Server: PHP 8.2 on app-01 (VPS)
- Database: MySQL 8 on db-01 (VPS)
- Cache: Redis on cache-01 (VPS)
```

---

## Permissions for the Software Team

Typical module permissions for developers:

| Module | Permission |
|--------|-----------|
| VPS | Read, Create, Update |
| Domains | Read |
| Hosting | Read |
| Other Services | Read, Create, Update |
| Vault | Read, Create, Update, Reveal |
| Tasks | Read, Create, Update |
| Notes | Read, Create, Update, Delete |
| Assets | Read (if tracking dev hardware) |

Your Super Admin sets these based on your team's needs.

---

## Using the API

If you need programmatic access:

1. Ask your Super Admin to create an API token
2. Go to **Administration → API Access**
3. Create a token with appropriate abilities
4. Copy the token immediately (it is shown only once)

Use the token in API requests:
```
Authorization: Bearer your-token-here
Content-Type: application/json
```

API documentation is available at `/docs` (Swagger UI).

### Common API Uses

- Task automation (create tasks from CI/CD)
- Data export for analysis
- Integrating with other tools
- Bulk operations

---

## Development Best Practices

### Naming Conventions
- Use clear, consistent names for servers and services
- Include environment: "web-staging-01", "db-production-01"

### Credentials
- Never hardcode passwords in code
- Use Vault for shared team credentials
- Use environment variables for application secrets

### Tasks
- Create tasks for all significant work
- Link tasks to modules (e.g., "VPS" for server work)
- Use descriptive titles and descriptions

### Documentation
- Keep Notes updated when architecture changes
- Include dates in documentation notes
- Document troubleshooting steps

---

## What to Do When...

### You Need Server Access Details
1. Check VPS record for IP and basic info
2. Check Vault for SSH credentials
3. Check Notes for access instructions

### A Deployment Fails
1. Check Server Health on Dashboard
2. Check if disk is full or database is down
3. Create a Task to track the fix
4. Add a Note documenting the issue and solution

### You Find a System Bug
1. Report to your Super Admin
2. Create a Task describing the bug
3. Include steps to reproduce

### You Need a New Server
1. Ask your Admin to provision the server
2. Create the VPS record in OpsPilot
3. Store credentials in Vault
4. Add Notes on server purpose and configuration

---

## Integration Ideas

- **CI/CD Pipeline:** Use webhooks to notify team when tasks are assigned
- **Slack Integration:** Webhook sends deployment events to a channel
- **Monitoring:** Use the Monitor Check feature to verify services are running
- **Calendar Sync:** Expiry dates can be exported for external calendar tools

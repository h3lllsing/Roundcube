# 1. System Overview

## What This Portal Does

This is a **centralized IT infrastructure management portal** that tracks and manages all digital services, subscriptions, credentials, assets, and renewal dates for an organization. It consolidates scattered hosting accounts, domains, VPS servers, VoIP services, domain emails, software subscriptions, and hardware assets into a single management interface.

## Who Uses It

| Role | Capabilities |
|---|---|
| **Super Admin** | Full access to everything. Can create/edit/delete users, roles, features, modules, permissions. Sees all data across all users. Can restore and force-delete records. |
| **Admin** | Module-level access based on assigned permissions. Can see records in modules they have `can_read` permission for (across all users). Cannot manage users, roles, permissions, or system config. |
| **User** | Ownership-scoped access. Can only see their own records. Module permissions restrict what they can create/read/update/delete. |
| **Customer** | Limited role with create/read/update access to own records. No delete permission. |

## Main Business Purpose

1. **Track expiry dates** of all services (domains, hosting, VPS, VoIP, emails, software) and send automated renewal reminders.
2. **Store credentials securely** (passwords encrypted at rest) with controlled reveal access.
3. **Manage hardware assets** with assignment tracking and QR code identification.
4. **Organize services by modules and features** for logical grouping.
5. **Control access via granular module-level permissions** with role-based defaults and per-user overrides.
6. **Log all significant activity** for audit trails.
7. **Monitor service availability** via ping checks.

## Main Modules (as permission groups)

The system organizes data into **features** (logical groups) and **modules** (functional areas with permissions). The current modules are:

- **Domains** — domain name registration tracking with registrar, expiry, DNS, Cloudflare status
- **Hosting** — web hosting accounts with cPanel, plan details, IP addresses
- **VPS** — virtual private servers with specs, IPs, login credentials
- **VoIP** — voice over IP services with extensions, phone numbers
- **Domain Emails** — email accounts linked to domains
- **Other Services** — miscellaneous software/services not fitting other categories
- **Service Providers** — vendor/provider companies
- **Vault** — encrypted password vault for any credential
- **Assets** — hardware asset tracking with assignments
- **Tasks** — task management with assignees and kanban board
- **Notes** — polymorphic notes attached to any record
- **Attachments** — file attachments on any record
- **Renewals (Expiry Trackers)** — unified renewal tracking, linked or standalone
- **Webhooks** — outgoing webhook integrations
- **Users** — user management
- **Roles & Permissions** — RBAC configuration

## What Data the System Manages

| Entity | Key Data |
|---|---|
| Domains | Name, registrar, expiry, DNS, Cloudflare status, cost, hosting link, provider link |
| Hosting Accounts | Name, cPanel URL, plan, IPs, username/password, expiry, cost |
| VPS Servers | Name, IP, OS, RAM/disk/CPU specs, location, credentials, expiry |
| VoIP Services | Name, phone number, extensions, server IP, direction, credentials |
| Domain Emails | Email address, password, storage, domain link, expiry |
| Other Services | Name, type, login URL, credentials, cost, expiry |
| Service Providers | Company name, type, contact info, website, credentials |
| Password Vault | Service name, URL, username, encrypted password, module link |
| Assets | Tag, serial, category/type, assignment history, QR identifier |
| Tasks | Title, description, status, priority, due date, assignees |
| Notes | Content, polymorphic attachment to any entity |
| Attachments | File, polymorphic attachment to any entity |
| Expiry Trackers | Linked or standalone renewals with notification settings |
| Users | Name, email, roles, module permission overrides |
| Webhooks | URL, events, active status |

## What Problems the System Solves

1. **Forgotten renewals** — automated reminders prevent service expiration outages.
2. **Credential sprawl** — centralized encrypted vault with controlled reveal and audit logging.
3. **Scattered providers** — single view of all service providers and their relationships to services.
4. **Access control** — granular module permissions prevent unauthorized access to sensitive data.
5. **Asset tracking** — hardware assignment lifecycle with QR-based identification.
6. **Audit trail** — every significant action (reveal, create, update, delete) is logged.
7. **Monitoring** — availability checks for services with monitoring URLs.
8. **Team collaboration** — task assignment, notes, and shared visibility within permission boundaries.

## What Must Never Be Broken by a Frontend Redesign

- **Backend permission enforcement** — frontend must never make authorization decisions. All permission checks happen server-side.
- **Super admin bypass** — super admin sees ALL data regardless of ownership or module permissions. Frontend must not restrict super admin views.
- **Sensitive permission confirmations** — delete, reveal, approve, and import actions require special handling and backend validation.
- **Renewal linked-item read-only fields** — when an ExpiryTracker is linked to a source service (via trackable), certain fields are owned by the source and must be read-only.
- **trackable polymorphic sync** — linked ExpiryTrackers auto-sync from source services. Frontend must not create duplicate linked trackers.
- **MorphMap aliases** — polymorphic types use specific aliases registered in AppServiceProvider. Frontend must use these exact aliases.
- **Activity logging** — every create/update/delete must continue to be logged via spatie/activitylog.
- **Password reveal logging** — every password reveal must create an activity log event.
- **FK select rules** — when combining `->with()` and `->select()`, all belongsTo foreign keys must be included.
- **Route names** — frontend must use named routes, not hardcoded URLs.
- **CSRF protection** — all web forms and AJAX POST/PUT/DELETE requests must include CSRF token.
- **File upload limits** — attachment uploads have size constraints enforced server-side.
- **Encryption** — all password fields use Laravel's `encrypted` cast. Frontend receives only masked values unless explicitly revealed.
- **No frontend-only authorization** — hiding a UI element does not prevent access. Backend always re-checks permissions.
- **Expiry tracker slug** — the route slug `expiry-trackers` is intentionally preserved. New frontend must continue using this slug.

# Release Notes — v1.0.0

> **Audience:** All Users

## Release Date

Initial release.

## Overview

OpsPilot v1.0.0 is the first stable release of the operations management platform. It provides centralized management of IT infrastructure, tasks, credentials, and expiry tracking.

## New Features

### Core Platform
- User authentication with login, registration, and password reset
- Role-based access control with 5 default roles
- Dashboard with widget-based data summaries
- Global search across all modules

### Infrastructure Management
- **Service Providers** — Manage provider contacts and credentials
- **Domains** — Domain registration, DNS, and expiry tracking
- **Hosting** — Server account management with password reveal
- **VPS** — Virtual private server management
- **VoIP** — Phone system management with extension passwords
- **Domain Emails** — Email account management
- **Other Services** — Miscellaneous service tracking
- **Expiry Trackers** — Renewal date tracking with email notifications
- **Assets** — Hardware tracking with assign/return workflow

### Productivity
- **Tasks** — Task management with Kanban board
- **Vault** — Secure credential storage
- **Notes** — Personal and module-attached notes
- **Calendar** — Monthly view of expiries and task due dates
- **Monitor** — On-demand service availability checks

### Administration (Super Admin)
- User management (create, edit, suspend, clone, delete)
- Role management with privilege assignment
- Module permission configuration
- Role templates for quick setup
- Activity logs
- Login audits
- SMTP profiles
- Webhooks
- Reports
- Import/Export

## Default Roles

| Role | Description |
|------|-------------|
| **Super Admin** | Unrestricted access to all modules and features |
| **Admin** | Full CRUD and export on operational modules |
| **Customer** | Create, read, update, export (no delete) |
| **Editor** | Create, read, update (no delete, export, reveal) |
| **User** | Create, read (no update, delete, export, reveal) |

## Known Limitations

- Monitor performs on-demand checks only (no scheduled monitoring)
- API controllers do not have RBAC scoping applied (planned for v1.1)
- Reports are available to Super Admin only
- Email notification delivery depends on SMTP profile configuration

## Upgrade Notes

This is the initial release. No upgrade path from previous versions.

---

## Related Modules

- [Version History](15_VERSION_HISTORY.md)
- [Quick Start Guide](01_QUICK_START_GUIDE.md)

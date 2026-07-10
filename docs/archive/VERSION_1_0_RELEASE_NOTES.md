# OpsPilot v1.0 — Release Notes

> **Release Date:** 2026-07-03
> **Version:** 1.0.0
> **Codebase:** OpsPilot (codename: unknow)
> **Framework:** Laravel 12.62.0 | PHP 8.2 | MySQL/MariaDB

---

## Overview

OpsPilot is an enterprise IT operations management platform. It centralizes infrastructure inventory, domain management, hosting, VPS, VoIP, assets, credentials, renewal tracking, monitoring, and team task management into a single workspace.

This is the **initial production release** after a full development cycle spanning architecture, UI design, feature implementation, audit, cleanup, and runtime verification.

---

## What's Included

### Infrastructure Management

| Module | Features |
|---|---|
| **Domains** | Full CRUD, Cloudflare status, expiry tracking, service provider linking, search, filter, pagination, export |
| **Hostings** | Full CRUD, cPanel URL, IP addresses (domain/mail/cPanel), credentials, password reveal, expiry tracking |
| **VPS** | Full CRUD, IP, OS, RAM/Disk/CPU specs, department/location, login IDs, additional IPs, password reveal, expiry tracking |
| **VoIP** | Full CRUD, extensions, extension password, direction, server IP, number status, outbound code, team details |
| **Service Providers** | Full CRUD, type, provider, website, email, password reveal |
| **Domain Emails** | Full CRUD, storage, password reveal, domain linking |
| **Other Services** | Full CRUD, service type, login URL, password reveal, expiry tracking |

### Asset Management

| Feature | Details |
|---|---|
| Asset categories, types, locations | Configurable taxonomies |
| Full CRUD with assignment tracking | Check-in/check-out with reason codes |
| Assignment history | Track who had what and when |

### Operations

| Feature | Details |
|---|---|
| **Tasks** | CRUD, Kanban board, My Tasks view, assignees, status workflow, priority, due dates, overdue detection, notifications |
| **Calendar** | Task and event overview |
| **Notifications** | In-app notification center with read/unread, bulk actions |
| **Expiry Trackers** | Configurable renewal tracking with email reminders, multi-recipient, SMTP profile selection, preview, test email |
| **Monitoring** | URL ping monitoring with failure notifications |
| **Vault** | Password manager with encrypted storage, reveal logging, module-scoped access control |

### Administration

| Feature | Details |
|---|---|
| **Users** | CRUD, suspend/unsuspend, clone, permission overrides |
| **Roles** | CRUD, privilege assignment, protected system roles (admin, super-admin) |
| **Privileges** | Granular permission definitions |
| **Module Permissions** | Role-based access per module (view/create/edit/delete) |
| **Role Templates** | Pre-configured role templates for rapid provisioning |
| **Reports** | Asset, domain, hosting, renewal, task, user, VPS reports with CSV export |
| **Activity Logs** | Full audit trail of all CRUD and sensitive operations |
| **Login Audits** | Authentication attempt logging (success, failure, suspended) |
| **SMTP Profiles** | Multiple SMTP configurations for outbound email |
| **Webhooks** | Event-driven HTTP callouts for integrations |
| **API Tokens** | Sanctum token management for API access |

### Cross-Cutting

| Feature | Details |
|---|---|
| **Global Search** | Unified search across all resource types |
| **Bulk Actions** | Multi-select delete, status update, export |
| **Import** | CSV import for supported resource types |
| **Export** | CSV export for all resource types |
| **Attachments** | File uploads attached to any resource |
| **Help Center** | Built-in documentation and module guides |
| **Design System** | Dark mode, consistent component library (x-card, x-button, x-table, x-form, etc.) |
| **Command Palette** | Keyboard-driven navigation (Ctrl+K) |

---

## Technical Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 12.62.0 (PHP 8.2+) |
| **Frontend** | Vite + Alpine.js + Tailwind CSS |
| **Database** | MySQL 8.0+ / MariaDB 10.3+ |
| **Queue** | Database-driven (no Redis/Supervisor required) |
| **Cache** | File-based |
| **Sessions** | File (database recommended for production) |
| **Auth** | Session-based web + Sanctum token API |
| **Email** | SMTP (configurable) |
| **Storage** | Local filesystem with public symlink |

---

## Deployment Requirements

- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.3+
- Apache with mod_rewrite
- cPanel or similar shared hosting
- Composer (for `composer install --no-dev`)
- Node.js (for initial `npm run build`)
- Cron access (for scheduler + queue worker)

Full deployment documentation: `CPANEL_DEPLOYMENT_GUIDE.md`

---

## Statistics

| Metric | Count |
|---|---|
| PHP source lines | 36,120 |
| Blade template lines | 12,594 |
| JavaScript lines | 569 |
| CSS lines | 341 |
| Models | 27 |
| Controllers | 70 |
| Migrations | 56 |
| Database tables | ~40 |
| Web routes | ~165 |
| API routes | ~249 |
| Total routes | 414 |
| Feature tests | 78 |
| Unit tests | 38 |
| Total tests | 116 |
| Artisan commands | 6 custom |

---

## Pre-Deployment Checklist

Before deploying to production, resolve these **BLOCKER** items:

1. **`public/storage` symlink** — Run `php artisan storage:link`
2. **`APP_ENV`** — Set to `production`
3. **`APP_DEBUG`** — Set to `false`
4. **`MAIL_MAILER`** — Set to `smtp` with real credentials
5. **`APP_URL`** — Set to production URL (HTTPS)
6. **SSL certificate** — Install and force HTTPS

See `PRODUCTION_CONFIGURATION_GUIDE.md` and `PRE_DEPLOYMENT_SANITY_CHECK.md` for the full checklist.

---

## Release Signoff

| | Status |
|---|---|
| Development | ✅ Complete |
| Runtime verification | ✅ Passed (15/15 pages) |
| Browser verification | ✅ Passed (0 errors) |
| Architecture review | ✅ Complete |
| Production documentation | ✅ Complete |
| Codebase frozen | ✅ |
| **Ready for deployment** | ✅ (after blocker resolution) |

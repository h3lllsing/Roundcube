# Release Notes — OpsPilot v1.0.0

**Release Date:** 2026-06-27  
**Version:** 1.0.0  
**Status:** Production Ready

---

## Overview

OpsPilot is a role-based access control and resource management system built on Laravel 12. It provides a complete IT operations portal with domain/hosting/VoIP/VPS management, task tracking, password vault, asset inventory, expiry/renewal notifications, CSV import/export, REST API, role-based access control, enterprise reporting, and global search.

---

## New in v1.0.0

### Enterprise Reporting Center (Phase 8B)
- 15 reports across 7 categories (Domains, Hosting, VPS, Renewals, Assets, Tasks, Users)
- Readable URLs: `/reports/domains/active`, `/reports/renewals/today`, etc.
- CSV export for every report
- Shared filter bar component
- Dashboard widget deep-links ("View Full Report →")
- Global Search integration for reports

### Enterprise Global Search (Phase 7B)
- Unified search across 15 module types
- Relevance ordering (exact → starts-with → contains)
- Ownership scoping (user, module, task, super-admin-only)
- 6 filter categories with color-coded badges
- AJAX-powered cmd+K palette
- Search results highlighting with `<mark>` tags

### Asset Management (Phase 7)
- Full asset lifecycle (categories, types, locations)
- Assignment tracking with timestamps
- Bulk actions for all resource types
- QR identifier field (future-ready)

### Notifications & Renewal Engine (Phase 6)
- SMTP profile management with encrypted credentials
- Configurable expiry notification rules
- Notification history with success/failure tracking
- In-app notification system with rich rendering

### Import/Export & API (Phase 5)
- CSV import with field mapping
- CSV export for every module
- Full REST API with Sanctum authentication
- Swagger/OpenAPI documentation
- API token management

### Core CRUD (Phases 3-4)
- Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails, Other Services, Expiry Trackers
- Task management with kanban board
- Password vault with reveal auditing
- Polymorphic notes and attachments
- URL monitoring with status tracking
- Calendar view
- Webhooks

### RBAC (Phases 2A-2C)
- Role-based access control with module-level permissions
- User-level permission overrides
- Role templates for quick provisioning
- Hardened super-admin protections

### Security Baseline (Phase 9.1)
- Created `SECURITY_BASELINE.md` with comprehensive security governance
- 17 sections covering environment hardening, authentication, RBAC rules, vault/SMTP/asset/renewal security, search/report access, session/cookie security, backup security, logging/audit rules, deployment checklist, incident response, change control, known limitations, and v1.1 recommendations

---

## Known Limitations (v1.0.0)

- **No XLSX export** — CSV only (PhpSpreadsheet not included)
- **No PDF export** — requires additional package
- **No background workers** — shared-hosting compatible (database queue only)
- **No multi-factor authentication** — session + token only
- **No Elasticsearch/Meilisearch** — LIKE-based search only
- **No email templating** — plain text + basic HTML reminders
- **No dark mode toggle persistence** — uses localStorage only

---

## Upgrading from Previous Versions

This is the first production release. No upgrade path from pre-1.0.0 exists.

---

## System Requirements

| Requirement | Minimum |
|-------------|---------|
| PHP | 8.2+ |
| Database | MySQL 8.0 / MariaDB 10.6 / SQLite 3.x |
| Web Server | Apache 2.4+ / Nginx 1.20+ |
| Composer | 2.x |
| Storage | 100 MB for application + 50 MB per 10k records |
| PHP Extensions | BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD (for charts) |

---

## Installation

See [`INSTALLATION.md`](INSTALLATION.md) for full installation instructions.

---

## License

Proprietary — All rights reserved.

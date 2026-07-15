# Changelog

> **Audience:** All Users — **Purpose:** Track platform updates and new features

## Version 2.0 — Help Center Rebuild

Complete rewrite of the Help Center documentation with zero-based, registry-driven architecture.

### What Changed

- **New documentation registry:** Single source of truth in `config/help-center.php` — 30+ documents, 36 module mappings, legacy redirects
- **All 14 legacy `.md` files replaced** with verified, codebase-accurate documentation
- **Foundation docs:** Quick Start, Understanding Permissions, My Permissions, Dashboard, Permission Reference, Credential Reveal
- **Portal Reference docs:** Domains, Hostings, Monitoring, VPS, VoIP, Service Providers, Domain Emails, Other Services, Expiry Trackers, Assets, G·Mails, Vault, Tasks, Notes, Calendar, Global Search
- **Administrator docs:** Users, Roles, Module Permissions, Privileges, Role Templates, Activity Logs, Login Audits, Features, Modules, Import/Export, Bulk Actions, Reports, Webhooks, SMTP Profiles, API Tokens, Attachments
- **Cross-module docs:** FAQ, Changelog
- **Developer docs:** `docs/developer/` — search indexing, Markdown rendering, permission middleware, SPA architecture
- **UI improvements:** Alpine.js SPA doc viewer, category sidebar, live search, contextual help links

## Version 1.0

Initial release of the OpsPilot portal.

### Features

- Domain, hosting, VPS, and VoIP management
- Credential vault with role-based reveal
- Task and notes management
- Calendar integration
- Monitoring and alerting
- Role-based access control with four permission levels
- Import/export support for business records
- Activity logging and login audits

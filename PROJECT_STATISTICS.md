# Project Statistics — OpsPilot v1.3.0

**Generated:** 2026-07-09  
**Version:** 1.3.0  
**Status:** Production Ready (post-audit correction)

---

## Codebase Metrics

| Category | Count | Details |
|----------|-------|---------|
| **Models** | 30 | Asset, AssetAssignment, AssetCategory, AssetLocation, AssetType, Attachment, Domain, DomainEmail, ExpiryTracker, ExpiryTrackerNotification, Feature, GMail, Hosting, LoginAudit, Module, ModuleRolePermission, Note, OtherService, RoleTemplate, ServiceProvider, SmtpProfile, Task, User, UserModulePermission, VaultEntry, Voip, Vps, Webhook, Monitor, Dashboard |
| **Controllers (Web)** | 38 | ActivityLog, Asset, Attachment, Auth, BulkAction, Calendar, Dashboard, Domain, DomainEmail, ExpiryTracker, Export, Feature, GMail, Hosting, Import, LoginAudit, Module, ModulePermission, Monitor, Note, Notification, OtherService, Privilege, Report, Role, RoleTemplate, Search, ServiceProvider, SmtpProfile, Task, Token, User, Vault, Voip, Vps, Webhook, BaseResourceController (abstract), SslCheck, WebCheck |
| **Controllers (API)** | 34 | ActivityLog, Asset, Attachment, Auth, BulkAction, Dashboard, Domain, DomainEmail, ExpiryTracker, Export, Feature, GMail, Hosting, Import, LoginAudit, Module, ModulePermission, Monitor, Note, Notification, OtherService, PasswordReset, Profile, Report, Search, ServiceProvider, SmtpProfile, Task, Token, Users, Vault, Voip, Vps, Webhook, Dashboard |
| **Services** | 38 | Asset, Attachment, BaseResourceService (abstract), BulkAction, Dashboard, Domain, DomainEmail, ExpiryNotification, ExpiryTracker, Feature, GlobalSearch, GMail, Hosting, Module, ModulePermission, Monitor, Note, Notification, OtherService, Reminder, RenewalNotification, Report, Search, ServiceProvider, SslCheck, Task, Vault, Voip, Vps, WebCheck, Webhook, plus supporting helpers: ModuleCache, SearchHelper, UrlResolver, UserService, ModulePermissionService, PermissionHelper, VaultAuditService, DashboardWidgetService |
| **Dashboard Widgets** | 10 | Activity, Assets, Operations, QuickActions, Renewals, ServerHealth, SmtpStatus, Tasks, Vault, ActivityWidget |
| **Report Providers** | 8 | 7 providers (Asset, Domain, Hosting, Renewal, Task, User, Vps) + 1 base (ReportProvider) |
| **Database Migrations** | 73 | Spanning from project inception |
| **Seeders** | 7 | AssetCategory, AssetType, Database, DemoData, FeatureModule, RolePermission, RoleTemplate |
| **Blade Views** | 186 | Including components, layouts, widgets, vendors |
| **Named Web Routes** | — | 444 total routes (all methods) |
| **RBAC Features** | 4 | Services, Credentials, Work, Administration |
| **RBAC Modules** | 27 | Domains, Hosting, VPS, VoIP, Domain Emails, Other Services, Service Providers, Expiry Trackers, Vault, Assets, Tasks, Notes, Attachments, Monitoring, Webhooks, Calendar, Search, Users, Roles, Modules, Features, Role Templates, SMTP Profiles, Activity Logs, Notifications, Reports, Privileges |
| **Role Templates** | 4 | Super Admin, Admin, IT Support, Read Only |
| **Asset Categories** | 4 | Laptop, Headphone, Mouse, Network Device |
| **Asset Types** | 19 | Various models across categories |
| **Composer Prod Deps** | 5 | laravel/framework, hasinhayder/tyro, spatie/laravel-activitylog, darkaonline/l5-swagger, laravel/sanctum |
| **Composer Dev Deps** | 10 | |

---

## Test Suite Statistics

| Metric | Count |
|--------|-------|
| **Test Files** | ~121 (80 Feature + 41 Unit) |
| **Total Tests** | ~448 |
| **Passing Tests** | 442 |
| **Failures** | 0 (post-fix) |
| **Assertions** | ~873 |

---

## Technology Stack

| Layer | Technology | Version |
|-------|-----------|---------|
| **Framework** | Laravel | 12.x |
| **PHP** | PHP | 8.2+ |
| **Database** | MySQL / MariaDB / SQLite | 8.0+ / 10.6+ / 3.x |
| **RBAC Engine** | Tyro (hasinhayder/tyro) | 1.6+ |
| **Activity Logs** | Spatie Activitylog | 4.x |
| **API Auth** | Laravel Sanctum | 4.x |
| **API Docs** | L5-Swagger (OpenAPI) | 11.x |
| **Frontend Build** | Vite | 6.x |
| **Styling** | Tailwind CSS | 4.x |
| **CSS Framework** | DaisyUI | 5.x |
| **Charts** | Chart.js | 4.x |
| **Icons** | Heroicons | 2.x |

---

## Architecture Lock Summary

```
app/
├── Console/Commands/     # Artisan commands
├── Dashboard/            (10 widget classes)
├── Events/               # Task events, vault reveals
├── Exports/              # CSV export formats
├── Helpers/              # ModuleCache, SearchHelper, UrlResolver
├── Http/
│   ├── Controllers/
│   │   ├── Api/          (34 API controllers)
│   │   └── Web/          (38 Web controllers)
│   ├── Middleware/        # auth, suspended, role, rate-limit
│   └── Requests/         # FormRequest validation
├── Listeners/            # Event handlers
├── Models/               (30 Eloquent models)
├── Notifications/        # Database/mail notifications
├── OpenApiSchemas/       # Swagger schema classes (27)
├── Reports/              # 7 providers + 1 base
├── Services/             (38 service classes)
└── Traits/               # Blameable, HasAttachments, HasModulePermissions

database/
├── factories/
├── migrations/           (73 migration files)
└── seeders/              (7 seeders)

resources/views/          (186 Blade templates)
routes/
├── web.php
├── api.php
└── console.php

tests/
├── Feature/              (~80 feature tests)
└── Unit/                 (~41 unit tests)
```

---

## Development Timeline

| Period | Phase | Deliverable |
|--------|-------|-------------|
| May 23 - May 24 | Phase 1 | Foundation & Authentication |
| May 24 - May 25 | Phase 2A-2C | RBAC Core + Extensions + Hardening |
| May 24 - Jun 22 | Phase 3 | Core CRUD & Resources |
| Jun 22 - Jun 23 | Phase 4 | Advanced Features |
| Jun 23 | Phase 5 | Import/Export, REST API, Swagger docs |
| Jun 23 - Jun 25 | Phase 6 | SMTP Profiles, Renewal Engine, Notifications |
| Jun 25 - Jun 26 | Phase 7 | Asset Management |
| Jun 26 | Phase 7B | Enterprise Global Search |
| Jun 26 | Phase 8A | Architecture Review & Reporting Master Plan |
| Jun 26 - Jun 27 | Phase 8B | Enterprise Reporting Center |
| Jun 27 | Phase 9 | Production Release & Project Freeze |
| Jul 9 | Sprint 1+2 | Show page + Copy button standardization |
| Jul 9 | Audit 1.0 | Documentation vs Code audit, corrections |

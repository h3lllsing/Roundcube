# FINAL PROJECT STRUCTURE

> Canonical directory tree of D:\xampp\htdocs\unknow after cleanup.
> Generated: 2026-07-03 | Status: Proposed (no changes made)

---

## Legend

```
📁 Directory
📄 File
🔧 Config
⚠️  Needs review
🗑️  Safe to delete
```

---

## Root Level

```
unknow/
├── 📁 .github/                   CI/CD workflows (deploy: exclude)
├── 📁 app/                       Application source code
├── 📁 bootstrap/                 Framework bootstrap
├── 📁 config/                    Configuration files
├── 📁 database/                  Migrations, seeds, factories
├── 📁 docs/                      → PROPOSED: relocated documentation
├── 📁 public/                    Web server document root
├── 📁 resources/                 Views, JS, CSS source
├── 📁 routes/                    Route definitions
├── 📁 storage/                   Runtime storage
├── 📁 tests/                     Test suite
├── 📁 vendor/                    Composer dependencies
├── 📁 _can_delete/               🗑️ Quarantine — remove before deploy
│
├── 📄 .env                       🔧 Environment (deploy: exclude)
├── 📄 .env.example               🔧 Template
├── 📄 .gitattributes             Git config
├── 📄 .gitignore                 Git ignore rules
├── 📄 artisan                    Laravel CLI
├── 📄 composer.json              Composer manifest
├── 📄 composer.lock              Composer lock
├── 📄 package.json               NPM manifest
├── 📄 package-lock.json          NPM lock
├── 📄 vite.config.js             Vite bundler config
├── 📄 README.md                  Repo readme
│
├── 📄 01_QUICK_START_GUIDE.md    → System guide (loaded at runtime)
├── 📄 01_SYSTEM_OVERVIEW.md      → System guide
├── 📄 ... (01–18 system guides)  → System guides (keep at root)
│
├── 📄 CHANGELOG.md               → PROPOSED: move to docs/
├── 📄 CONTRIBUTING.md            → PROPOSED: move to docs/
├── 📄 INSTALLATION.md            → PROPOSED: move to docs/
├── 📄 USER_GUIDE.md              → PROPOSED: move to docs/
├── 📄 DEPLOY.md                  → PROPOSED: move to docs/
├── 📄 DEPLOYMENT_GUIDE.md        → PROPOSED: move to docs/
├── 📄 SECURITY_BASELINE.md       → PROPOSED: move to docs/
│
├── 📄 CAN_DELETE_REPORT.md       → Quarantine report
├── 📄 PROJECT_STRUCTURE_CLEANUP.md   This report
├── 📄 DEPLOY_EXCLUDE_LIST.md     → This report
├── 📄 FINAL_PROJECT_STRUCTURE.md → This report
```

---

## `app/` — Application Core

```
app/
├── 📁 Console/
│   ├── 📄 Kernel.php (bootstrap/app.php in L11)
│   └── 📁 Commands/
│       ├── 📄 CheckExpiries.php          Active (scheduled daily@08:00)
│       ├── 📄 CheckOverdueTasks.php      Active (scheduled daily@09:00)
│       ├── 📄 ExpiryBackfill.php         🗑️ Unscheduled — safe to delete
│       ├── 📄 MonitorCheck.php           Active (scheduled hourly)
│       └── 📄 SendEmailReminders.php     Active (scheduled daily@02:00)
│
├── 📁 Exceptions/                       🗑️ Empty — Laravel 11 style in bootstrap/app.php
│
├── 📁 Helpers/
│   ├── 📄 MarkdownHelper.php             Active (17.4 KB)
│   ├── 📄 RbacScope.php                  Active (1.1 KB)
│   └── 📄 SearchHelper.php               Active (420 B)
│
├── 📁 Http/
│   ├── 📁 Controllers/
│   │   ├── 📁 Api/                       API controllers
│   │   └── 📁 Web/                       Web controllers
│   ├── 📁 Middleware/                    HTTP middleware
│   ├── 📁 Requests/                      Form requests
│   └── 📁 View/
│       └── 📁 Composers/
│           └── 📄 SidebarComposer.php    ⚠️ Single file in its own dir
│
├── 📁 Mail/
│   └── 📄 ExpiryTrackerReminder.php     ⚠️ Single file in its own dir
│
├── 📁 Models/                           Eloquent models
│
├── 📁 Notifications/                    Notification classes
│
├── 📁 Providers/                        Service providers
│
├── 📁 Services/
│   ├── 📄 AssetService.php              Active
│   ├── 📄 AttachmentService.php         Active
│   ├── 📄 BulkActionService.php         Active
│   ├── 📄 DomainEmailService.php        Active
│   ├── 📄 DomainService.php             Active
│   ├── 📄 ExpiryNotificationService.php Active
│   ├── 📄 ExpiryTrackerService.php      Active
│   ├── 📄 FeatureService.php            Active
│   ├── 📄 GlobalSearchService.php       Active
│   ├── 📄 HelpService.php               Active
│   ├── 📄 HostingService.php            Active
│   ├── 📄 ModulePermissionService.php   Active
│   ├── 📄 ModuleService.php             Active
│   ├── 📄 MonitorService.php            Active
│   ├── 📄 NoteService.php               Active
│   ├── 📄 OtherServiceService.php       Active
│   ├── 📄 RenewalNotificationService.php Active
│   ├── 📄 RenewalSyncService.php        Active
│   ├── 📄 ReportService.php             Active
│   ├── 📄 ServiceProviderService.php    Active
│   ├── 📄 TaskService.php               Active
│   ├── 📄 VaultService.php              Active
│   ├── 📄 VoipService.php               Active
│   ├── 📄 VpsService.php                Active
│   └── 📄 WebhookService.php            Active
│
├── 📁 Traits/
│   ├── 📄 Blameable.php                 Active
│   ├── 📄 HasAttachments.php            Active
│   └── 📄 HasModulePermissions.php      Active
│
└── 📁 View/
    └── 📁 Components/
        └── 📄 ActivityTimeline.php      Active (single PHP component)
```

---

## `bootstrap/` — Framework Bootstrap

```
bootstrap/
├── 📄 app.php                    Laravel 11 app bootstrap (exception handling, routing)
├── 📄 providers.php              Service provider list
└── 📁 cache/                     🗑️ Regenerable — exclude from deploy
    ├── 📄 .gitignore             Placeholder (keep)
    ├── 📄 packages.php           🗑️ Regenerable
    └── 📄 services.php           🗑️ Regenerable
```

---

## `config/` — Configuration

```
config/
├── 📄 app.php
├── 📄 activitylog.php
├── 📄 auth.php
├── 📄 cache.php
├── 📄 cors.php
├── 📄 database.php
├── 📄 filesystems.php
├── 📄 l5-swagger.php
├── 📄 logging.php
├── 📄 mail.php
├── 📄 queue.php
├── 📄 sanctum.php
├── 📄 services.php
├── 📄 session.php
└── 📄 (other config files)
```

---

## `docs/` — Documentation (PROPOSED structure)

```
docs/
├── 📄 CHANGELOG.md
├── 📄 CONTRIBUTING.md
├── 📄 INSTALLATION.md
├── 📄 USER_GUIDE.md
├── 📄 DEPLOY.md
├── 📄 DEPLOYMENT_GUIDE.md
├── 📄 SECURITY_BASELINE.md
└── (system guides stay at root — runtime dependency)
```

---

## `public/` — Web Server Root

```
public/
├── 📁 build/                     Vite build output (gitignored, deploy: include)
│   ├── 📁 assets/
│   │   ├── 📄 app-Cdu7BxLG.css   (180 KB)
│   │   └── 📄 app-DBHOz0_q.js    (264 KB)
│   └── 📄 manifest.json
│
├── 📁 css/                       Legacy static assets
│   └── 📄 help-center.css        (10 KB — actively used)
│
├── 📁 js/
│   └── 📄 help-center.js         (8.9 KB — actively used)
│
├── 📁 images/
│   └── 📁 login/
│       └── 📄 dark.jpg           (1.3 MB — used in login view)
│
├── 📄 .htaccess                  Apache rewrite rules
├── 📄 favicon.ico                ⚠️ Empty (0 B) — needs replacement
├── 📄 index.php                  Laravel entry point
└── 📄 robots.txt                 Allows all
```

---

## `resources/` — Source Assets & Views

```
resources/
├── 📁 css/
│   └── 📄 app.css                (19.5 KB — Vite entry point, Tailwind)
│
├── 📁 js/
│   ├── 📄 app.js                 Vite entry point (imports below)
│   ├── 📄 bootstrap.js           Bootstrap/theme setup
│   ├── 📄 charts.js              Chart.js integration
│   ├── 📄 command-palette.js     Command palette
│   └── 📄 permissions.js         Permission UI logic
│
└── 📁 views/
    ├── 📁 activity-logs/
    ├── 📁 assets/
    ├── 📁 attachments/
    ├── 📁 auth/
    ├── 📁 calendar/
    ├── 📁 components/
    │   ├── 📄 action.blade.php
    │   ├── 📄 activity-timeline.blade.php
    │   ├── 📄 alert.blade.php
    │   ├── 📄 badge.blade.php
    │   ├── 📄 breadcrumbs.blade.php
    │   ├── 📄 bulk-actions.blade.php
    │   ├── 📄 button.blade.php
    │   ├── 📄 card.blade.php
    │   ├── 📄 command-palette.blade.php
    │   ├── 📄 confirm-dialog.blade.php
    │   ├── 📄 dark-toggle.blade.php
    │   ├── 📄 date.blade.php
    │   ├── 📄 empty-state.blade.php
    │   ├── 📄 field.blade.php
    │   ├── 📄 filter-input.blade.php
    │   ├── 📄 filter-select.blade.php
    │   ├── 📄 loading-overlay.blade.php
    │   ├── 📄 monitor-button.blade.php
    │   ├── 📄 monitor-result.blade.php
    │   ├── 📄 money.blade.php
    │   ├── 📄 nav-link.blade.php
    │   ├── 📄 page-header.blade.php
    │   ├── 📄 report-filter-bar.blade.php
    │   ├── 📄 sidebar-header.blade.php
    │   ├── 📄 sidebar-nav-groups.blade.php
    │   ├── 📄 sidebar-search.blade.php
    │   ├── 📄 stat-card.blade.php
    │   ├── 📄 table.blade.php
    │   ├── 📄 toast.blade.php
    │   ├── 📄 user-card.blade.php
    │   ├── 📁 form/
    │   │   ├── 📄 checkbox.blade.php
    │   │   ├── 📄 input.blade.php
    │   │   ├── 📄 password.blade.php
    │   │   ├── 📄 select.blade.php
    │   │   └── 📄 textarea.blade.php
    │   └── 📁 permissions/
    │       ├── 📄 category-accordion.blade.php
    │       ├── 📄 diff-panel.blade.php
    │       ├── 📄 filter-chip.blade.php
    │       ├── 📄 inline-editor.blade.php
    │       ├── 📄 modal.blade.php
    │       ├── 📄 module-row.blade.php
    │       ├── 📄 role-warning.blade.php
    │       ├── 📄 sensitive-criteria.blade.php
    │       ├── 📄 stats-bar.blade.php
    │       ├── 📄 summary-collapsible.blade.php
    │       └── 📄 unsaved-bar.blade.php
    │
    ├── 📁 dashboard/
    │   ├── 📄 index.blade.php
    │   └── 📁 widgets/           (9 active partials)
    ├── 📁 domain-emails/
    ├── 📁 domains/
    ├── 📁 emails/
    ├── 📁 errors/
    ├── 📁 expiry-trackers/
    ├── 📁 features/
    ├── 📁 help/
    ├── 📁 hostings/
    ├── 📁 import/
    ├── 📁 layouts/
    ├── 📁 login-audits/
    ├── 📁 module-permissions/
    ├── 📁 modules/
    ├── 📁 notes/
    ├── 📁 notifications/
    ├── 📁 other-services/
    ├── 📁 privileges/
    ├── 📁 reports/
    ├── 📁 role-templates/
    ├── 📁 roles/
    ├── 📁 search/
    ├── 📁 service-providers/
    ├── 📁 smtp-profiles/
    ├── 📁 tasks/
    ├── 📁 tokens/
    ├── 📁 users/
    ├── 📁 vault/
    ├── 📁 vendor/
    │   ├── 📁 l5-swagger/        (Swagger UI override — keep)
    │   └── 📁 pagination/
    │       ├── 📄 tailwind.blade.php         KEEP
    │       └── 📄 simple-tailwind.blade.php  KEEP
    │       └── 🗑️ 7 Bootstrap/Semantic-UI files → delete
    │
    ├── 📁 voip/
    ├── 📁 vps/
    ├── 📁 webhooks/
    │
    ├── 📄 auth/my-permissions.blade.php
    ├── 📄 auth/profile.blade.php              ⚠️ raw glass-card class
    ├── 📄 calendar/index.blade.php
    ├── 📄 design-system.blade.php
    ├── 📄 guide.blade.php                     🗑️ Dead view (36.7 KB)
    └── 📄 welcome.blade.php
```

---

## `routes/` — Route Definitions

```
routes/
├── 📄 web.php                    Web routes (auth, CRUD, dashboard)
├── 📄 api.php                    API routes (Sanctum-protected)
└── 📄 console.php                Artisan command schedule
```

---

## `storage/` — Runtime Storage

```
storage/
├── 📁 app/
│   ├── 📄 .gitignore
│   ├── 📁 private/
│   └── 📁 public/
│
├── 📁 api-docs/                  ⚠️ Non-standard — generated Swagger doc
│   └── 📄 api-docs.json          (324 KB — gitignore recommended)
│
├── 📁 framework/
│   ├── 📁 cache/
│   │   ├── 📄 .gitignore
│   │   └── 📁 data/              🗑️ Stale hash dirs (26 empty + 24 stale files)
│   ├── 📁 sessions/              Runtime session files
│   ├── 📁 testing/               Test fixtures
│   └── 📁 views/                 🗑️ Compiled Blade cache (regenerable)
│
└── 📁 logs/                      🗑️ Runtime logs (regenerable)
    └── 📄 .gitignore
```

---

## `tests/` — Test Suite

```
tests/
├── 📁 Feature/                   Feature tests
│   └── 📁 Dashboard/
│       └── 📄 DashboardPageTest.php
├── 📁 Unit/                      Unit tests
├── 📁 coverage-xml/              🗑️ Generated XML coverage (tracked in git)
├── 📄 TestCase.php               Base test class
├── 📄 coverage.txt               🗑️ Generated text coverage
└── 📄 coverage.xml               🗑️ Generated XML coverage
```

---

## Deploy-Ready Project Size Estimate

| Component | Size |
|---|---|
| `app/` | 0.7 MB |
| `bootstrap/` | 0.03 MB |
| `config/` | 0.06 MB |
| `database/` | 0.4 MB |
| `public/` | 1.8 MB |
| `resources/` (views only) | 0.9 MB |
| `routes/` | 0.03 MB |
| `vendor/` | 90 MB |
| `node_modules/` (build only) | 69 MB |
| **Total (with vendor)** | **~163 MB** |
| **Total (excl vendor + node)** | **~4 MB** |

# System Logic Explanation Dossier — Master Index

Generated: July 2026
Purpose: Comprehensive domain reference for future frontend/UI/UX redesign.
Status: **Complete**

## Documents

| # | File | Description |
|---|---|---|
| **00** | `00_SYSTEM_LOGIC_DOSSIER_INDEX.md` | **This file** — master index and summary |
| **01** | `01_SYSTEM_OVERVIEW.md` | What the portal does, user roles, main modules, data managed, problems solved, what must never break |
| **02** | `02_ARCHITECTURE_LAYERING.md` | Technology stack, directory structure, layering & data flow, FK Select Rule, controller→view pattern, auth/session flow, caching strategy |
| **03** | `03_DATABASE_RELATIONSHIPS.md` | Entity-relationship diagram (textual), per-table FK details, registered MorphMap aliases (complete list), polymorphic relationship types, indexes |
| **04** | `04_MODULE_BUSINESS_LOGIC.md` | Detailed per-module breakdown (Domains, Hosting, VPS, VoIP, Domain Emails, Other Services, Service Providers, Vault, Assets, Tasks, Notes, Attachments, Webhooks, Activity Log, Login Audit, Users & Roles) |
| **05** | `05_PERMISSION_SYSTEM.md` | HasModulePermissions trait, module definitions, sensitive modules/permissions, Gate enforcement, Blade @can, API permissions, Super Admin bypass, inheritance & override rules |
| **06** | `06_RENEWAL_CENTER.md` | ExpiryTracker — linked vs standalone types, key field behavior per type, renewal statuses, notification system (SendRenewalNotifications command), creation flow, config renewals.php |
| **07** | `07_DATA_FLOWS.md` | Complete data lifecycle flows: Domain creation, ExpiryTracker linking, renewal notification dispatch, password reveal, soft delete/restore, activity logging, file upload, task status update, polymorphic note creation, user creation |
| **08** | `08_ROUTES_API.md` | Web routes (complete table), named routes, custom routes per module, API endpoints (secondary), route-to-controller mapping, middleware stack, CSRF protection |
| **09** | `09_VALIDATION_RULES.md` | Common validation patterns, module-specific rules (per request class), 10 important edge cases with behavioral details |
| **10** | `10_NOTIFICATIONS_LOGGING.md` | Notification architecture, channels, renewal notification class, database notifications table, activity logging (spatie/activitylog), observer pattern, password reveal logging, error logging, what users are NOT told about |
| **11** | `11_FRONTEND_CONTRACT.md` | Data format expectations (dates, numbers, booleans, nullables, encrypted fields, soft deletes), backend responses, pagination, search, AJAX endpoints, session/auth, Blade components (active + deleted), layout structure, CSS/JS deps, frontend prohibitions (10 rules), query conventions |
| **12** | `12_DO_NOT_BREAK_LIST.md` | 21 items rated CRITICAL (security/data loss), HIGH (functional bugs), MEDIUM (display/UX issues), items already fixed, items that will break if changed |
| **13** | `13_UI_REDESIGN_READINESS_SUMMARY.md` | Current state, what's ready, 10 challenges for a new frontend, 9 things NOT to build, estimated effort areas, order-of-implementation recommendation, testing strategy |

## Key Metrics (as of July 2026)

| Metric | Value |
|---|---|
| Backend cleanliness score | 9/10 |
| Test suite | 1951 tests, 4950 assertions, 0 failures |
| Query count (full page load, all relations) | ~27 (was ~267 before FK fix) |
| Controllers with FK select bug (fixed) | 11 |
| Dead code items removed | 10 (Sprint B) |
| Dead Blade components deleted | 2 |
| Frozen RFC topics | 7 |
| Documentation files in dossier | 14 |

## Quick Navigation for Redesign

- **Start here:** `01_SYSTEM_OVERVIEW.md` for understanding the system.
- **For frontend behavior rules:** `11_FRONTEND_CONTRACT.md`.
- **For things to never break:** `12_DO_NOT_BREAK_LIST.md`.
- **For the big picture readiness:** `13_UI_REDESIGN_READINESS_SUMMARY.md`.
- **For Renewal Center complexity:** `06_RENEWAL_CENTER.md`.
- **For permission model:** `05_PERMISSION_SYSTEM.md`.
- **For database relationships (MorphMap critical):** `03_DATABASE_RELATIONSHIPS.md`.
- **For route URLs and naming:** `08_ROUTES_API.md`.
- **For data flow edge cases:** `07_DATA_FLOWS.md`.
- **For validation rules:** `09_VALIDATION_RULES.md`.
- **For per-module business logic:** `04_MODULE_BUSINESS_LOGIC.md`.
- **For notification and logging behavior:** `10_NOTIFICATIONS_LOGGING.md`.

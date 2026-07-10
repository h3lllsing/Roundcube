# Version History

> **Audience:** All Users

## Current Version

**Patch 1.1.4 — Operations Manual Rewrite** (Latest)

## Version Table

| Version | Date | Summary |
|---------|------|---------|
| **Patch 1.1.4** | Current | Complete rewrite of all 10 user-facing guides as operations manuals — every section uses Purpose/When to Use/Permission Required/Step-by-Step/Best Practices/Common Mistakes/Expected Result format; feature descriptions replaced with operational procedures; Super Admin teaches portal configuration; Admin teaches daily operations; IT Staff teaches ticket processing; Read Only teaches information location |
| **Patch 1.1.2** | — | Final documentation polish — safety protections documented, Read Only role corrected for route-level restrictions, terminology finalization, DNS wording fix, password reset dependency noted |
| **Patch 1.1.1** | — | Documentation consistency audit — terminology standardization, removal of implementation details from user docs, new reference docs (Glossary, Troubleshooting, Backup/Restore, Architecture, Developer RBAC Reference) |
| **Patch 1.1.0** | — | Operational visibility overhaul — module-wide access for 9 infrastructure modules; RBAC scoping consistency across controllers, calendar, search, dashboard, and bulk actions |
| **Patch 1.0.9-F** | — | RBAC visibility rule audit and report |
| **Patch 1.0.9-E** | — | Bulk action `_method` fix for nested forms |
| **v1.0.0** | — | Initial stable release |

## Patch 1.1.4 Details

### Changes
- Complete rewrite of all 10 user-facing guides (01-10) as operations manuals
- Every section now follows the standard format: **Purpose → When to Use → Permission Required → Step-by-Step Workflow → Best Practices → Common Mistakes → Typical Business Scenario → Expected Result**
- **01 Quick Start Guide**: Replaced feature descriptions with login/navigation/profile/notification/logout procedures; added role-specific first-day procedures for each role
- **02 Super Admin Guide**: Replaced feature descriptions with configuration procedures (user CRUD, role CRUD, module permissions, SMTP, import, reports, webhooks, safety procedures)
- **03 Admin Guide**: Replaced feature descriptions with operational procedures for all 9 infrastructure modules, plus task management, export, and bulk actions
- **04 IT Staff Guide**: Replaced feature descriptions with ticket-processing procedures (task workflow, creating/updating records, password reveal, search, troubleshooting, escalation)
- **05 Read Only Guide**: Replaced feature descriptions with information-location procedures (browsing, search, task review, vault review, calendar, compliance audit, reporting issues)
- **06 Daily Operations Guide**: Enhanced existing procedures with standardized format; added morning/evening checklists, daily work procedures, weekly/monthly workflows, incident response
- **07 FAQ**: Transformed from Q&A format to **Problem Resolution Guide** organized by problem category (login, permission, visibility, password, module access, error messages)
- **08 Permission Reference**: Added procedure for checking effective permissions; retained reference tables with operational context
- **09 Role Matrix**: Retained as reference with added operational headers and role comparison
- **10 Workflow Guide**: Reformatted all workflows with standardized sections; added related pages per workflow
- **DOCUMENTATION_INDEX.md**: Updated to reflect operations manual structure

### Key Design Decisions
- All feature descriptions ("what" the system does) replaced with procedures ("how" to use it)
- Every role guide teaches role-specific operating procedures aligned with job function
- Super Admin guide focuses on **configuration**; Admin on **daily operations**; IT Staff on **ticket processing**; Read Only on **information location**
- Reference documents (08, 09) retained but reformatted with operational context
- FAQ transformed into procedural problem-resolution guide

### Changes
- Documented Super Admin safety protections (last SA, self-demotion, protected roles, password reveal audit)
- Corrected Read Only role: Users, Activity Logs, Webhooks, and Reports are Super Admin only at the route level — removed from Read Only accessible list
- Fixed Role Matrix legend terminology (Read → View, Create/Read/Update → Create/View/Edit)
- Removed remaining implementation details from Role Matrix (SQL language, middleware refs)
- Updated password reset documentation to note environment dependency
- Fixed DNS wording in Daily Operations Guide

## Patch 1.1.1 Details

### Changes
- Terminology standardization across all 10 user-facing guides
- Removed implementation details (RbacScope, Blade directives, middleware, PHP classes) from user docs
- Moved technical content to 17_ARCHITECTURE_OVERVIEW.md and 18_DEVELOPER_RBAC_REFERENCE.md
- Removed .env/artisan/config exposure from Super Admin Guide
- Removed URL pattern exposure from FAQ
- Created 8 new reference documents (Glossary through Developer RBAC Reference)

## Patch 1.1.0 Details

### Changes
- **Module-wide access**: Users with View permission on infrastructure modules now see ALL records, not just their own
- **9 modules affected**: Domains, Hosting, VPS, VoIP, Service Providers, Domain Emails, Other Services, Expiry Trackers, Assets
- **Calendar**: Non-Super Admin users see only expiry dates from accessible modules and tasks they are assigned to
- **Dashboard widgets**: All widgets scoped to accessible module IDs
- **Bulk actions**: Ownership check skipped for infrastructure module types when user has the required permission
- **Global Search**: Uses module access scoping for operational records

### Upgrade Note
After updating to Patch 1.1.0, users with View permission on infrastructure modules will automatically see all records. No configuration changes are required.

## Patch 1.0.9-F Details

### Changes
- Conducted comprehensive RBAC visibility audit
- Documented how each controller, widget, and service handles scope
- Created visibility implementation plan

## Patch 1.0.9-E Details

### Changes
- Restructured 20 index views to prevent nested form method leakage
- Moved bulk form to close before table open
- Used HTML5 form attribute on checkboxes

## v1.0.0 Details

Initial stable release with full infrastructure management, role-based access control, task management, vault, calendar, and administration features.

---

## Related Modules

- [Release Notes v1.0](14_RELEASE_NOTES_v1.0.md)
- [Quick Start Guide](01_QUICK_START_GUIDE.md)

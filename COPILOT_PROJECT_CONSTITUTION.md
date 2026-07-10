# OPSPILOT PROJECT CONSTITUTION

**Version:** 1.0
**Status:** Permanent Governing Document
**Scope:** All OpsPilot implementation, review, and decision-making

---

## Table of Contents

1. [Project Overview](#1-project-overview)
2. [Project Goals](#2-project-goals)
3. [Business Scope](#3-business-scope)
4. [Business Rules](#4-business-rules)
5. [Architecture Principles](#5-architecture-principles)
6. [Permission Principles](#6-permission-principles)
7. [Global Master Records](#7-global-master-records)
8. [Personal Modules](#8-personal-modules)
9. [Coding Standards](#9-coding-standards)
10. [Delivery Mode](#10-delivery-mode)
11. [Implementation Workflow](#11-implementation-workflow)
12. [Verification Workflow](#12-verification-workflow)
13. [Regression Workflow](#13-regression-workflow)
14. [Signoff Workflow](#14-signoff-workflow)
15. [Automation Policy](#15-automation-policy)
16. [Allowed Recommendations](#16-allowed-recommendations)
17. [Forbidden Recommendations](#17-forbidden-recommendations)
18. [Engineering Principles](#18-engineering-principles)
19. [Security Principles](#19-security-principles)
20. [Database Principles](#20-database-principles)
21. [Performance Principles](#21-performance-principles)
22. [Testing Standards](#22-testing-standards)
23. [Deployment Standards](#23-deployment-standards)
24. [Documentation Standards](#24-documentation-standards)
25. [Reporting Standards](#25-reporting-standards)
26. [Tooling Policy](#26-tooling-policy)
27. [Definition of Done](#27-definition-of-done)
28. [Version History](#28-version-history)
29. [Future Feature Requests](#29-future-feature-requests)
30. [Project Constitution](#30-project-constitution)

---

## 1. Project Overview

OpsPilot is a Laravel-based IT operations management platform. It provides:

- Service management (domains, hosting, VPS, VoIP, service providers, domain emails, other services)
- Asset tracking and assignment lifecycle
- Renewal and expiry tracking with notification scheduling
- Monitoring dashboard with uptime and SSL checking
- Vault for encrypted credential storage and reveal logging
- Role-based access control with module-level permission overrides
- Activity logging for audit trail
- Webhook integration for event-driven automation
- Multi-user task management with Kanban board
- Import/export capabilities
- Help and guide system

The project uses Laravel 11, MySQL, Alpine.js, and Vite for asset compilation.

---

## 2. Project Goals

1. Complete notes → description migration + polymorphic notes system.
2. Auto-post notes on key entity events (status change, password update, cost change, expiry date change).
3. Functional monitoring dashboard widget with offline service list and SSL expiry warnings.
4. Maintain zero test regressions across all sprints.

All goals are considered complete. Future work is driven by verified bugs, security issues, or critical reliability gaps — not feature requests.

---

## 3. Business Scope

### 3.1 Scope Is Frozen

The existing product is considered feature complete. The following are never acceptable as implementation tasks:

- New Modules
- New CRUD Resources
- New Dashboards
- New Business Features
- New Navigation Items
- New Personas
- New Workflows
- New Product Ideas
- New SaaS Capabilities

### 3.2 Feature Requests

If a missing capability is identified, it may be recorded as a Feature Request but must never be implemented unless explicitly approved by the user as a new sprint goal. Feature Requests go in the Future Feature Requests appendix only.

---

## 4. Business Rules

### 4.1 Notes System

| Rule | Detail |
|------|--------|
| Old `notes` column | Renamed to `description` (static identity text on each entity) |
| New notes | Use polymorphic `Note` model (user + timestamp + pin thread) |
| Pin permission | Super-admin OR user with module `update` permission |
| Delete permission | Note owner OR super-admin |
| Notes must reference a valid user_id (FK to users table) |

### 4.2 Auto-Notes (HasAutoNotes Trait)

| Rule | Detail |
|------|--------|
| Trigger | `updated()` Eloquent event on tracked entities |
| Tracked fields | `status`, `password`, `cost`, `expiry_date` |
| Guard | If no auth user exists (`!$user`), silently return — prevents CLI/queue errors |
| Infinite recursion guard | `Note` model does NOT use `HasAutoNotes` trait, so creating a note never triggers another note |
| Password safety | Only checks `isset($changes['password'])` — never exposes the encrypted value |
| Auth user must exist | Trait checks `Auth::user()` before creating note |

### 4.3 Renewal Notes

Renewal notes are created explicitly in the controller (ExpiryTrackerController::renew), not via the trait. This allows a custom message format: `"Renewal processed: expiry extended to Y-m-d by UserName"`.

### 4.4 Monitoring

- SSL expiry tracking is optional. Only relevant if the user manages paid SSL certificates (not cPanel AutoSSL or Cloudflare origin-free setups).
- Offline status: last_ping_at > 2 hours ago = offline.
- Widget shows top 5 offline services + top 5 SSL-expiring services inline.
- SSL badge colors: ≤7d (red), ≤14d (amber), >14d (yellow).

### 4.5 Renewal Sync

- Linked trackers sync expiry_date from source entity.
- Standalone trackers manage their own expiry_date.
- `loadMorph` is used to eager-load polymorphic trackable relationships.
- Pagination uses select() with specific columns for query efficiency.

---

## 5. Architecture Principles

### 5.1 General

- Laravel MVC conventions must be followed.
- Business logic belongs in Service classes, not Controllers or Models.
- Controllers are thin: validate → authorize → delegate to service → respond.
- Models define relationships, scopes, accessors, mutators, and event handlers only.
- Blade views contain minimal logic — prefer Blade components and Alpine.js.

### 5.2 Traits Over Observers

- `HasAutoNotes` is a trait, not an observer.
- Traits are opt-in per model (add `use HasAutoNotes`).
- Eloquent boot method convention (`bootHasAutoNotes()`) is used for event registration.
- Observers should only be used when logic applies to ALL instances of a model unconditionally.

### 5.3 Components

- Blade components are self-contained.
- Permission checks happen inside the component, not in the caller.
- Example: `x-notes-thread` handles its own pin/delete permission logic.

### 5.4 API

- API controllers return Resource classes for consistent transformation.
- OpenAPI (OA) attributes annotate API controllers for Swagger documentation.
- All API responses include proper status codes and structured error bodies.

---

## 6. Permission Principles

### 6.1 RBAC

- Super-admin role bypasses all permission checks.
- Admin role has elevated but not full access.
- Regular users see only their own records (user_id scope).
- Module-level permissions: role baselines + user overrides.

### 6.2 Permission Gates

| Action | Scope |
|--------|-------|
| `read` | View records |
| `create` | Create records |
| `update` | Edit records (also grants note pin/unpin) |
| `delete` | Delete records (also grants note deletion by non-owners) |
| `export` | Export data |
| `reveal` | Reveal vault passwords |
| `import` | Import data |
| `approve` | Approve workflows |

### 6.3 Override Rules

- User overrides are stored in `user_module_permissions` table.
- Overrides win over role baseline.
- Reset all overrides restores role baseline.
- Super-admin can always edit any user's permissions.
- Permission changes are logged in activity log.

---

## 7. Global Master Records

These modules have records visible to all users with `read` permission, regardless of creator:

- Activity Logs
- Attachments
- Login Audits
- Module Permissions
- Notes
- Notifications
- Privileges
- Roles
- Users

These are NOT scoped by `user_id`. Permission is purely role-based.

---

## 8. Personal Modules

These modules scope records to `user_id` (ownership):

- Service Providers
- Domains
- Hosting
- VPS
- VoIP
- Domain Emails
- Other Services
- Renewals (Expiry Trackers)
- Assets
- Vault
- Webhooks
- Tasks

Records are visible only to their owner, unless the viewer has a higher role (admin sees owned-for-others, super-admin sees all).

---

## 9. Coding Standards

### 9.1 PHP

- Strict typing enabled (`declare(strict_types=1)`)
- PSR-12 coding style
- Type hints on all function parameters and return types
- PHPDoc for complex return types (collections, morphs)
- No inline comments unless explaining a non-obvious business rule
- Single responsibility per method
- Maximum method length: 30 lines (soft limit)

### 9.2 Blade

- Use `x-` prefix for custom Blade components
- No complex PHP logic in templates — pre-compute in controller or view composer
- Use `@php` blocks only for view-local computations
- Use Alpine.js `x-data` for interactive UI state

### 9.3 JavaScript

- Import and register Alpine.js components in `resources/js/app.js`
- Use `fetch` for API calls (not axios unless existing pattern)
- Always send `X-CSRF-TOKEN` from `<meta name="csrf-token">` in layout head
- Handle both success and error response paths
- Use async/await or Promise.then().catch()

### 9.4 CSS

- Use Tailwind utility classes
- Dark mode via `dark:` prefix
- Component-specific overrides in `resources/css/app.css`

### 9.5 Imports

- Group imports: PHP core → Framework classes → App classes
- Order alphabetically within each group
- Remove unused imports

---

## 10. Delivery Mode

Every sprint follows this exact sequence:

```
Preflight → Implement → Verify → Signoff → Next Sprint
```

### 10.1 Preflight

- Understand what exists (read files, search codebase)
- Identify gaps and constraints
- Document the current state
- Plan the change

### 10.2 Implement

- Make the minimum change required
- Follow existing patterns (mimic code style, use existing libraries)
- Never add comments unless asked
- Keep responses concise (under 4 lines of text, not counting tool use)

### 10.3 Verify

- Run lint/typecheck commands (if configured)
- Run relevant tests
- Fix any regressions
- Ensure zero new failures

### 10.4 Signoff

- Present result
- Wait for explicit approval before starting next sprint

### 10.5 Next Sprint

- Ask user what to work on next
- Propose options if user is unsure
- Never assume the next sprint without asking

---

## 11. Implementation Workflow

1. Check if relevant files exist (read, glob, grep)
2. Read existing files to understand patterns
3. Mimic existing code style, naming conventions, and library usage
4. Check package.json / composer.json for available dependencies before using new libraries
5. Make the change
6. Verify with tests

**Forbidden:** Adding explanatory comments after code changes. Do not output code explanations unless asked.

---

## 12. Verification Workflow

1. Run the full test suite or targeted test files
2. Check for pre-existing vs. new failures
3. Document any new failures with root cause
4. Fix new failures before proceeding
5. Pre-existing failures may be noted but are not blocking

---

## 13. Regression Workflow

1. Before making changes, note which tests pass/fail
2. After changes, re-run same tests
3. Any previously-passing test that now fails is a regression
4. Regressions must be fixed before signoff
5. If a regression requires reverting a change, revert and reconsider approach

---

## 14. Signoff Workflow

1. Present what was done (summary of changes)
2. Present test results (count of passed/failed, any pre-existing)
3. Get explicit approval from user
4. Only then proceed to next sprint

---

## 15. Automation Policy

### 15.1 Allowed

Automation is permitted ONLY if it automates an EXISTING workflow. Never create a new workflow.

Examples:
- Renewal reminders (existing: manual expiry checking)
- SSL expiry reminders (existing: manual certificate tracking)
- Queue retry with backoff (existing: failed jobs)
- Failed job recovery (existing: manual job inspection)
- Activity log archival (existing: logs accumulate)
- Webhook retry with exponential backoff (existing: fire-once webhooks)
- Backup verification (existing: untested backups)
- Scheduled cleanup of soft-deleted records (existing: orphan accumulation)
- Health monitoring endpoint (existing: manual server checks)
- Security alert aggregation (existing: isolated alerts)
- Rate limit tuning (existing: static limits)
- Session cleanup (existing: stale sessions)

### 15.2 Forbidden

Creating a new workflow under the guise of automation is prohibited.

---

## 16. Allowed Recommendations

- Bug fixes
- Security patches
- Performance improvements
- Data integrity improvements
- Reliability improvements
- Code maintainability improvements
- Test coverage improvements
- Documentation updates
- Configuration corrections
- Automations of existing workflows (see Automation Policy)
- Tooling that solves verified problems (see Tooling Policy)

---

## 17. Forbidden Recommendations

- New modules, features, dashboards, navigation, personas, workflows
- New CRUD resources
- New product ideas or SaaS capabilities
- Expanding business scope
- Implementation of unrequested features
- Refactoring without a verified problem
- Redesign without business justification
- Any recommendation that increases project scope

---

## 18. Engineering Principles

1. **Minimum change principle:** Make the smallest possible change to achieve the goal.
2. **Pattern matching:** Always match existing code style, naming, and structure.
3. **No mystery:** Every behavior should be traceable to its source.
4. **Fail fast:** Validate inputs early. Fail with clear messages.
5. **Single responsibility:** One class, one job. One method, one concern.
6. **Defense in depth:** Validate at entry points AND at business logic boundaries.
7. **Idempotency where possible:** Repeated operations should produce the same result.
8. **Explicit over implicit:** Favor explicit relationships, explicit authorization checks, and explicit error handling.

---

## 19. Security Principles

1. **Never log secrets:** Passwords, API keys, tokens must never appear in logs, activity logs, or error messages.
2. **Encrypt at rest:** All credential fields use Laravel's `encrypted` casting.
3. **CSRF protection:** All web routes require CSRF token. Include `<meta name="csrf-token">` in layout head for fetch-based submissions.
4. **XSS prevention:** All user content rendered in Blade uses `{{ }}` (escaped) unless explicitly marked as safe with `{!! !!}` after sanitization.
5. **Mass assignment protection:** Use `$fillable` not `$guarded`. Never mark sensitive fields as fillable.
6. **Authorization checks:** Every mutating action checks permission at the controller level, not just in the UI.
7. **Super-admin protection:** Certain operations (role assignment, suspension, permission overrides) are restricted to super-admin only.
8. **Rate limiting:** Auth endpoints, password operations, and reveal actions have rate limits.
9. **File upload validation:** Validate mime types, file size, and path traversal on all uploads.
10. **CORS:** API routes have proper CORS configuration for intended origins.

---

## 20. Database Principles

1. **Foreign keys:** All cross-table references use foreign key constraints with `cascadeOnDelete` or `restrictOnDelete` as appropriate.
2. **Migrations are permanent:** Never modify or delete a migration after it has been run in production. Create new migrations for changes.
3. **Column renames:** Use `renameColumn()` in migrations for column renames. Data is preserved.
4. **Indexes:** Foreign key columns must be indexed. Columns used in `WHERE`, `ORDER BY`, and `JOIN` clauses should be indexed.
5. **Soft deletes:** Use `SoftDeletes` trait for all user-facing data. Hard delete only for automated cleanup.
6. **Timestamps:** All tables have `created_at` and `updated_at`. Soft-deleted tables have `deleted_at`.
7. **Morph maps:** Use `Relation::enforceMorphMap()` with aliases (not full class names) in `AppServiceProvider`.
8. **Avoid polymorphic foreign keys without constraints** — use `morphs()` or `nullableMorphs()` with manual index.
9. **Data integrity:** Use transactions for operations that update multiple related records.

---

## 21. Performance Principles

1. **Eager load relationships:** Use `with()` to prevent N+1 queries.
2. **Paginate collections:** Never use `get()` without limit on user-facing queries.
3. **Select only needed columns:** Use `select()` or `->get(['col1', 'col2'])` instead of `SELECT *`.
4. **Cache expensive queries:** Use Laravel's cache facade with appropriate TTL.
5. **Avoid N+1 in views:** Check Blade templates for lazy-loaded relationships inside loops.
6. **Use `loadMissing()`:** Load relationships only if not already loaded.
7. **Use `loadMorph()`:** For polymorphic relationships across different model types.
8. **Monitor query count:** Index pages and API listings should stay under 15 queries.
9. **Chunk large datasets:** Use `chunk()` or `cursor()` for memory-intensive operations.
10. **Database indexing strategy:** Index `WHERE` clauses, `ORDER BY` columns, and foreign keys.

---

## 22. Testing Standards

1. **Run tests before and after changes** to distinguish pre-existing vs. new failures.
2. **Document pre-existing failures** in sprint notes but do not block on them.
3. **Use `RefreshDatabase`** for feature tests that modify the database.
4. **One assertion per test** is preferred but not required.
5. **Test names should describe the scenario** (`test_admin_cannot_delete_other_users_record`)
6. **Factory definitions** must produce valid records that pass all validation and foreign key constraints.
7. **Fix test regressions** immediately. Do not proceed to the next sprint with test failures.
8. **Don't mock what you don't own** — prefer integration tests for framework interactions.
9. **Query count assertions** are acceptable for performance-critical pages.
10. **Test the behavior, not the implementation.**

---

## 23. Deployment Standards

1. **Shared hosting compatible:** No dependency on system binaries that may not be available on shared hosting.
2. **Environment-based configuration:** All environment-specific values in `.env`, never hardcoded.
3. **Migration order:** Migrations must run in sequence without gaps.
4. **Artifact compilation:** `npm run build` (Vite) must be run before deployment.
5. **Cache clear:** `php artisan optimize:clear` on deploy.
6. **Queue worker:** Required for job processing. Use `php artisan queue:work` or supervisor.
7. **Scheduler:** `php artisan schedule:run` every minute via cron.
8. **Storage link:** `php artisan storage:link` for public file access.

---

## 24. Documentation Standards

1. **README.md** at project root covers setup, configuration, and quick start.
2. **API documentation** uses OpenAPI/Swagger (`OA` attributes in controllers).
3. **Help system** (`/help/*` routes) provides user-facing operational guides.
4. **No proactive documentation creation** — only create documentation files when explicitly requested.
5. **No emojis in code or documentation** unless explicitly requested.
6. **Concise documentation** — prefer code that documents itself over separate docs.

---

## 25. Reporting Standards

1. Report test results as: `X passed, Y failed (Z pre-existing)`
2. Report changes as a summary (what was done, what files changed)
3. Report bugs with file path, root cause, and suggested fix
4. Report security issues with evidence, impact, and reproduction steps
5. Keep reports concise — bullet points over paragraphs
6. Never include unnecessary preamble or postamble

---

## 26. Tooling Policy

Tooling recommendations are allowed ONLY if they solve a VERIFIED problem identified during work.

| Tool | Valid Use Case |
|------|---------------|
| PHPStan / Larastan | Codebase has type safety issues |
| Psalm | Codebase has implicit type coercion bugs |
| Pint | Codebase has inconsistent style |
| Rector | Codebase has upgrade blockers |
| Blackfire | Performance bottlenecks identified |
| Laravel Pulse | Production monitoring gaps |
| Laravel Telescope | Debugging gaps |
| Sentry / Inspector | Error visibility gaps |
| Prometheus / Grafana | Metrics gaps |
| GitHub Actions | CI/CD gaps |
| Dependabot | Dependency vulnerability gaps |
| OpenTelemetry | Distributed tracing gaps |

"This tool is popular" is not a valid justification. A tool recommendation without a matching verified problem is invalid.

---

## 27. Definition of Done

A task is done when ALL of the following are true:

1. **Preflight complete:** Understanding of existing code documented
2. **Implementation complete:** Change made following all coding standards
3. **Verification complete:** Tests pass (matching or improving pre-existing state)
4. **No regressions:** Previously passing tests still pass
5. **Lint/typecheck passes:** If configured, lint and typecheck commands pass
6. **Change is minimal:** No scope creep, no unrelated changes
7. **User approved:** Explicit signoff received

---

## 28. Version History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2026-07-05 | Enterprise Review Board | Initial constitution — all project rules codified from inception |

---

## 29. Future Feature Requests

*This section is reserved for feature requests identified during work that are outside the current business scope. No entries yet.*

---

## 30. Project Constitution

### 30.1 Constitution Status

This document is the permanent source of truth for the OpsPilot project.

Every new chat session, every implementation, and every decision must comply with this constitution.

### 30.2 Conflict Resolution

If any future instruction conflicts with this constitution:

1. **Pause** immediately
2. **Explain** the conflict to the user
3. **Ask** for explicit approval before changing the constitution
4. **Document** the change in Version History with reason

### 30.3 Amendment Rules

- Never overwrite this document silently
- Never remove previously approved rules
- Append version history for every change
- Document every major philosophy change with rationale
- Small clarifications may be added without version bump (but must note the change)

### 30.4 Governance

This document may only be amended by explicit user approval. The AI must never modify the constitution to bypass its own constraints.

---

*This constitution is permanent. It governs all work on OpsPilot regardless of session, tooling, or context.*

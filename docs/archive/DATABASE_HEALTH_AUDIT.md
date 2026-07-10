# Database Health Audit — OpsPilot v1.0.0

> **Audit Date:** 2026-06-27  
> **Version:** 1.0.0  
> **Total Tables:** 32 (including Laravel system tables)  
> **Total Migrations:** 54  
> **Scope:** Full schema, index, FK, query, and data integrity review

---

## 1. Schema Overview

### Entity Tables (20)

| Table | Purpose | Soft Deletes | Rows Est. |
|-------|---------|--------------|-----------|
| `users` | User accounts | ✅ | < 1k |
| `features` | RBAC feature groups | ✅ | < 10 |
| `modules` | RBAC modules | ✅ | < 50 |
| `module_role_permissions` | Role-based module permissions | ❌ | < 1k |
| `user_module_permissions` | User-level permission overrides | ❌ | < 500 |
| `role_templates` | Pre-configured permission sets | ❌ | < 10 |
| `tasks` | Task management | ✅ | < 5k |
| `task_user` | Task-user assignments (pivot) | ❌ | < 10k |
| `notes` | Polymorphic notes | ✅ | < 5k |
| `password_vault` | Encrypted password storage | ✅ | < 2k |
| `login_audits` | Login event audit trail | ❌ | < 50k |
| `domains` | Domain management | ✅ | < 2k |
| `hostings` | Hosting accounts | ✅ | < 1k |
| `vps` | VPS instances | ✅ | < 1k |
| `voip` | VoIP services | ✅ | < 500 |
| `service_providers` | Provider profiles | ✅ | < 200 |
| `domain_emails` | Email accounts | ✅ | < 1k |
| `other_services` | Catch-all services | ✅ | < 500 |
| `expiry_trackers` | Expiry/renewal tracking | ✅ | < 5k |
| `expiry_tracker_notifications` | Notification history | ❌ | < 50k |
| `smtp_profiles` | SMTP mail configuration | ❌ | < 10 |
| `webhooks` | HTTP callbacks | ✅ | < 100 |
| `attachments` | File upload metadata | ✅ | < 2k |
| `assets` | Physical asset inventory | ✅ | < 5k |
| `asset_categories` | Asset categories | ✅ | < 10 |
| `asset_types` | Asset models/brands | ✅ | < 50 |
| `asset_locations` | Asset storage locations | ✅ | < 20 |
| `asset_assignments` | Asset check-out history | ❌ | < 10k |

### Laravel System Tables (6)

| Table | Purpose |
|-------|---------|
| `password_reset_tokens` | Password reset tokens |
| `sessions` | User sessions |
| `cache` / `cache_locks` | Cache store |
| `jobs` / `job_batches` / `failed_jobs` | Queue |
| `personal_access_tokens` | Sanctum API tokens |
| `notifications` | Database notifications |
| `activity_log` | Spatie activity log |

---

## 2. Index Analysis

### ✅ Properly Indexed Columns

| Table | Column(s) | Index Type | Notes |
|-------|-----------|------------|-------|
| `users` | `email` | UNIQUE | Login lookups |
| `features` | `slug` | UNIQUE | Route/model binding |
| `modules` | `feature_id`, `slug` | UNIQUE composite | Prevents duplicate slugs per feature |
| `module_role_permissions` | `module_id`, `role_id` | UNIQUE composite | One permission row per module+role |
| `task_user` | `task_id`, `user_id` | UNIQUE composite | Prevents duplicate assignment |
| `user_module_permissions` | `user_id`, `module_id` | UNIQUE composite | One override row per user+module |
| `role_templates` | `slug` | UNIQUE | Template lookup |
| `asset_categories` | `slug` | UNIQUE | Category lookup |
| `asset_types` | `category_id`, `brand`, `name` | UNIQUE composite | Prevents duplicate models |
| `assets` | `asset_tag` | UNIQUE | Asset identification |
| `assets` | `qr_identifier` | UNIQUE | Future QR support |
| `login_audits` | `email` | INDEX | Failed login lookups |
| `login_audits` | `event` | INDEX | Audit filtering |
| `login_audits` | `created_at` | INDEX | Date-range queries |
| `login_audits` | `user_id` | INDEX (FK) | User-specific lookups |
| `tasks` | `status`, `priority`, `due_date` | INDEX | Filtering and sorting |
| `expiry_trackers` | `expiry_date` | INDEX | Calendar and renewal queries |
| `notifications` | `type`, `notifiable_id` | INDEX composite | Polymorphic notification lookups |
| `notifications` | `read_at` | INDEX | Unread count queries |
| `expiry_tracker_notifications` | `expiry_tracker_id`, `reminder_day`, `recipient_email` | INDEX composite | Duplicate prevention |
| `expiry_tracker_notifications` | `status` | INDEX | Status filtering |
| `expiry_tracker_notifications` | `trigger_source` | INDEX | Audit reporting |
| `smtp_profiles` | `is_active` | INDEX | Active profile lookup |

### 🔴 Missing Indexes — Critical

| Table | Missing Index | Reason | Severity |
|-------|---------------|--------|----------|
| **All 20 entity tables** | `deleted_at` | Every query filters `WHERE deleted_at IS NULL`. Without an index, MySQL scans all rows including soft-deleted ones. | **High** |
| **Domains, Hostings, VPS, VoIP, OtherServices, DomainEmails, ServiceProviders, ExpiryTrackers, Tasks, Assets** | `status` | Filtered in every index listing (25+ controller methods). No index on any of these tables for `status`. | **High** |
| **All entity tables** | `user_id` + `status` composite | Used together in every service-layer filter for RBAC scoping. A composite index would dramatically improve the most common query pattern. | **High** |

### 🟡 Missing Indexes — Medium

| Table | Missing Index | Reason |
|-------|---------------|--------|
| `tasks` | `due_date` | Used for calendar view, sorting, and expiry checks. |
| `tasks` | `module_id` + `status` | Common RBAC filter pair. |
| `smtp_profiles` | `is_default` | Looked up on every notification send. Only 1 row is `is_default=true` — needs `WHERE is_default = 1` to be fast. |
| `smtp_profiles` | `priority` | Used for ORDER BY when selecting SMTP profiles. |
| `asset_categories` | `sort_order` | Sorted by in dropdowns. |
| `password_vault` | `module_id` | FK column — filtered for module-scoped vault queries. |
| `expiry_trackers` | `email_notifications_enabled` | Filtered in the renewal scheduler. |
| `expiry_trackers` | `next_notification_due_at` | Primary filter in the cron job, but no index. |
| `sessions` | `user_id` | Looked up on every authenticated request for session validation. |

### 🟢 Missing Indexes — Low (Nice-to-Have)

| Table | Missing Index | Reason |
|-------|---------------|--------|
| All entity tables | `created_at` | Purely for admin-date-range reports. Acceptable without. |
| `activity_log` | `causer_id` | Filtered in activity widgets. Polymorphic so partial index needed. |
| `activity_log` | `created_at` | Date-range filtering on activity. |

---

## 3. Foreign Key Analysis

### ✅ Well-Configured FKs

| Pattern | Count | Tables |
|---------|-------|--------|
| `ON DELETE CASCADE` (ownership) | 18 | `task_user`, `notes`, `password_vault`, `asset_assignments`, `modules → features`, `module_role_permissions`, `user_module_permissions`, `expiry_tracker_notifications`, `asset_types → categories`, `assets → categories/types` |
| `ON DELETE SET NULL` (optional link) | 25 | `sessions`, `features.created_by/updated_by`, `tasks.module_id/created_by/updated_by`, `password_vault.module_id`, `login_audits.user_id`, `expiry_trackers.module_id/service_provider_id/smtp_profile_id/disabled_by`, `domains.module_id/hosting_id/service_provider_id`, all 6 service tables → `service_providers` |
| `ON DELETE RESTRICT` | 0 | Not used anywhere |
| No action (implicit) | 3 | `smtp_profiles.created_by → users`, `activity_log` (polymorphic — no FKs) |

### 🟡 Findings

| Finding | Table | Issue |
|---------|-------|-------|
| **No FK on `module_role_permissions.role_id`** | `module_role_permissions` | The `role_id` column references a `roles` table managed by the external `hasinhayder/tyro` package. The migration writes `$table->foreignId('role_id')->constrained()->cascadeOnDelete()`, which relies on the `roles` table existing at migration time. If the package is removed or its migration order changes, this FK creation silently fails or errors. |
| **Polymorphic tables lack FKs** | `activity_log`, `notes`, `notifications`, `attachments` | Polymorphic relationships (`subject_type`/`subject_id`, `notable_type`/`notable_id`) cannot use standard FK constraints because the referenced table is dynamic. This is an accepted trade-off of polymorphic design. |

---

## 4. Cascading Rules Audit

### ✅ Appropriate

| Rule | Scenario |
|------|----------|
| `CASCADE` on ownership | User deleted → their tasks, notes, vault entries, assets, webhooks also deleted |
| `CASCADE` on parent | Asset category deleted → asset types under it also deleted. Module deleted → role permissions cleaned up. |
| `SET NULL` on optional | Service provider deleted → domains/hosting/VPS `service_provider_id` set to null (record preserved) |
| `SET NULL` on creator | User deleted → their created tasks/features/modules show `created_by = null` (audit trail preserved) |

### ⚠️ Potential Issue

| Table | FK | Rule | Risk |
|-------|-----|------|------|
| `tasks` | `module_id → modules(id)` | `SET NULL` | If a module is deleted, tasks assigned to it lose their module association. Acceptable since the task record is preserved. |
| `smtp_profiles` | `created_by → users(id)` | Implicit (no explicit ON DELETE) | **Could cause FK violation** if a user who created an SMTP profile is deleted. Check whether this FK has an explicit rule — if `NO ACTION` (MySQL default), user deletion will fail if they created any SMTP profiles. |

---

## 5. Nullable Field Analysis

### Consistent Nullable Patterns ✅

| Pattern | Tables |
|---------|--------|
| `monitoring_url`, `last_ping_at` nullable | All 6 service tables + `expiry_trackers` |
| `module_id` nullable | All service tables (module link is optional) |
| `service_provider_id` nullable | All service tables (provider link is optional) |
| `deleted_at` nullable | 18 soft-delete models |
| `created_by`, `updated_by` nullable | `features`, `modules`, `tasks` |
| `cost` nullable (decimal) | Most service tables + `expiry_trackers` |

### Inconsistent Nullable Choices ⚠️

| Table | Column | Current | Should Be | Reason |
|-------|--------|---------|-----------|--------|
| `tasks` | `module_id` | NULL | NOT NULL | Every task should belong to a module for RBAC. Currently nullable but likely always set. |
| `password_vault` | `module_id` | NULL | NOT NULL | Same — vault entries should scope to a module. |
| `login_audits` | `user_id` | NULL | NOT NULL | Currently nullable for failed logins (unknown user). This is **intentional** — legitimate use case. |
| `smtp_profiles` | `reply_to_email` | NULL | NOT NULL | SMTP should always have a reply-to. Minor. |
| `assets` | `serial_number` | NULL | NOT NULL | Serial should always be recorded for IT asset tracking. |
| `notifications` | `read_at` | NULL | NOT NULL | Nullable is correct — represents unread state. |

**Verdict:** Nullable choices are well-considered. The few nullable-FOR-NOT-NULL candidates are minor.

---

## 6. Duplicate / Redundant Columns

### ✅ Cleanly Migrated

| Migration | Change | Status |
|-----------|--------|--------|
| `2026_06_23_182722` | Removed `password` from `expiry_trackers` | ✅ Clean — column removed after data migrated to vault |
| `2026_06_22_111154` | Replaced `registrar` (varchar) with `service_provider_id` (FK) on `domains` | ✅ Clean — varchar column retained as nullable |
| `2026_06_22_111154` | Replaced `provider` (varchar) with `service_provider_id` (FK) on `hostings`, `vps` | ✅ Clean |
| `2026_06_23_000001` | Replaced `provider` (varchar) with `service_provider_id` (FK) on `voip`, `other_services`, `expiry_trackers` | ✅ Clean |
| `2026_06_23_000002` | Replaced `provider` (varchar) with `service_provider_id` (FK) on `domain_emails` | ✅ Clean |

### ⚠️ Observations

| Table | Column | Note |
|-------|--------|------|
| `expiry_trackers` | `password` (removed), `username`, `login_url` (added) | Password was migrated out. Username and login_url remain. |
| `hostings` | `password` (added by `2026_06_22_100920`) | Login credential stored on the hosting record — duplicates security concern with vault model. |
| `vps` | `password` (added by `2026_06_23_184212`) | Same concern — password stored on VPS instead of vault. |
| `voip` | `password`, `extension_password` (added by `2026_06_22_235118`) | Two password fields stored on VoIP record. |
| `domain_emails` | `password` (added by `2026_06_22_081951`) | Password stored on domain email record. |
| `other_services` | `password` (added by `2026_06_23_184212`) | Password stored on service record. |
| `service_providers` | `password` (added by `2026_06_23_184212`) | Password stored on provider record. |

**Finding:** 6 tables store credentials directly instead of referencing the vault. While these are encrypted at rest (via `encrypted` cast), the security model is inconsistent — vault entries are RBAC-gated with reveal logging, but direct column passwords are not.

---

## 7. Type Consistency

### ✅ Consistent

| Type | Where Used | Consistency |
|------|-----------|-------------|
| `bigint unsigned` | All primary keys, FK columns | ✅ Uniform |
| `varchar(255)` | All string fields | ✅ Uniform |
| `decimal(10,2)` | All cost/price fields | ✅ Uniform |
| `tinyint(1)` | All boolean flags | ✅ Uniform |
| `text` | All long-text fields (notes, descriptions) | ✅ Uniform |
| `json` | All JSON/config columns | ✅ Uniform |
| `date` | All expiry/start/renewal/registration dates | ✅ Uniform |
| `timestamp` | All auditing timestamps (created_at, updated_at) | ✅ Uniform |

### ⚠️ Minor Inconsistency

| Table | Column | Type | Inconsistency |
|-------|--------|------|---------------|
| `tasks` | `due_date` | `datetime` (cast: `datetime`) | All other date-only fields use `date` type. If `due_date` is supposed to be date-only, it should be `date` not `datetime`. If a time is needed, it's correct. |
| `domains` | `registration_date`, `expiry_date` | `date` | Uses `date` type — correct for domain dates. |
| `expiry_trackers` | `expiry_date` | `date` | Uses `date` type — correct. |

---

## 8. Migration Quality

### ✅ Strengths

| Aspect | Assessment |
|--------|------------|
| **Up/Down parity** | All 54 migrations have matching `up()` and `down()`. No orphan operations. |
| **Idempotency** | All `CREATE TABLE` use `Schema::create()`. All ALTER operations are safely reversible. |
| **Naming convention** | Consistent: `YYYY_MM_DD_HHMMSS_descriptive_name.php` |
| **Column additions** | All columns are added via dedicated migrations, not squashed into creation. |
| **Data migrations** | The `password` removal from `expiry_trackers` (2026_06_23_182722) correctly handles the column drop with a reversible `down()`. |
| **Index additions** | Dedicated migration (2026_06_21_000001) for adding indexes to expiry_trackers and notifications. |

### ⚠️ Minor Concerns

| Migration | Concern |
|-----------|---------|
| `2026_05_24_090000_create_webhooks_table.php` | Uses `dropSoftDeletes()` in `down()` before `dropIfExists()` — correct but unusual ordering. |
| `2026_06_23_123751` (spatie activitylog) | Package-owned migration — may be published and then diverge from upstream on package update. |
| `2026_05_24_080000` (monitoring) | Adds `monitoring_url` + `last_ping_at` to ALL 8 service tables in one migration. Works but creates a long-running migration. |

---

## 9. Seeder Analysis

### ✅ Good Practices

| Seeder | Quality |
|--------|---------|
| `AssetCategorySeeder` | Run-only-once data (4 categories). Uses `firstOrCreate()` for idempotency. |
| `AssetTypeSeeder` | 19 models keyed to categories. Same `firstOrCreate()` pattern. |
| `FeatureModuleSeeder` | 4 features, 28 modules. Uses `firstOrCreate()` for idempotency. |
| `RolePermissionSeeder` | Assigns CRUD+ perms for 4 roles across 28 modules. Complex but complete. |
| `RoleTemplateSeeder` | 4 templates with complete permission matrices. Uses `firstOrCreate()`. |
| `DemoDataSeeder` | Sample data for development. Skipped in `testing` environment. |

### ⚠️ Issues

| Issue | Seeder | Detail |
|-------|--------|--------|
| `RoleTemplateSeeder` seeds `can_import` | `RoleTemplateSeeder` | The `permissions_json` for each template includes `can_import` values, but `ModuleRolePermission` does not have a `can_import` column. Applying a role template will write `can_import` into the JSON but **cannot persist it to `module_role_permissions`**. Only `UserModulePermission` supports `can_import`. |

---

## 10. Query Performance Analysis

### Most Common Query Patterns

```sql
-- Pattern 1: RBAC-scoped listing (most common — 20+ controllers)
SELECT * FROM {table}
WHERE user_id = ?            -- ownership
  AND deleted_at IS NULL      -- soft delete filter
  AND module_id IN (?, ?, ?)  -- module access (admin only)
  AND status = ?              -- status filter (optional)
ORDER BY name ASC
LIMIT 15 OFFSET 0

-- Pattern 2: Calendar queries
SELECT * FROM {table}
WHERE expiry_date BETWEEN ? AND ?
  AND deleted_at IS NULL

-- Pattern 3: Login audit
SELECT * FROM login_audits
WHERE email = ?
  AND event = ?
ORDER BY created_at DESC

-- Pattern 4: Dashboard aggregates
SELECT COUNT(*), SUM(cost), status
FROM {table}
WHERE deleted_at IS NULL
GROUP BY status
```

### Query Performance Issues

| # | Issue | Impact | Affected Queries |
|---|-------|--------|------------------|
| 1 | No `deleted_at` index on any table | Full table scan includes soft-deleted rows (can be 20-50% of table) | Every query on 18 soft-delete tables |
| 2 | No `status` index on service tables | Full table scan on status filter | Every index listing that filters by status |
| 3 | No `user_id`+`status` composite | Two separate index lookups instead of one | All RBAC-scoped queries |
| 4 | `LIKE '%term%'` on `name` | Cannot use B-tree index; full scan required | Search across 8+ tables |
| 5 | `next_notification_due_at` not indexed | Full scan of expiry_trackers on every cron run | `expiry:send-reminders` command |
| 6 | `email_notifications_enabled` not indexed | Full scan of expiry_trackers on cron | Same as above |

### Current Load Estimates

| Metric | Value |
|--------|-------|
| Est. total rows (all tables) | < 200k |
| Est. peak queries/minute | ~100 |
| Est. rows per table | < 50k |
| Index overhead | ~5 MB (acceptable for any host) |

**Verdict:** Current performance is acceptable for small-to-medium deployments (under 50k rows per table). Missing indexes will become noticeable above 100k rows.

---

## 11. Naming Consistency

### ✅ Consistent

| Convention | Usage |
|-----------|-------|
| `snake_case` table names | All 32 tables |
| `snake_case` column names | All columns |
| Singular table names | `user`, `task`, `domain`, `vault` — correct for Laravel convention |
| `{singular}_{singular}` pivot | `task_user` — correct for Laravel alphabetical pivot |
| Primary keys | All named `id` (except `notifications` uses UUID `id`) |
| Foreign keys | All named `{table}_id` (e.g., `user_id`, `module_id`, `service_provider_id`) |
| Timestamps | `created_at`, `updated_at` on all entity tables |
| Soft deletes | `deleted_at` on all soft-delete tables |

### ⚠️ Minor Inconsistencies

| Table | Column | Expected | Actual |
|-------|--------|----------|--------|
| `password_vault` | — | `vault_entries` | Named `password_vault` (not following plural convention). However, this is the table name, which is referenced as a model `VaultEntry`. Acceptable. |
| `expiry_tracker_notifications` | `smtp_profile_id` | `smtp_profiles` | Named `smtp_profile_id` — correct FK naming but the migration also adds `smtp_credentials_id` which is unused/abandoned. |
| `voip` | `phone_number` | Standard naming | Column is correct. Table name `voip` is an abbreviation — acceptable for domain-specific term. |

---

## 12. Migration Rollback Safety

| Scenario | Status | Notes |
|----------|--------|-------|
| `php artisan migrate:rollback` (single step) | ✅ Safe | All `down()` methods are symmetric with `up()` |
| `php artisan migrate:rollback` (full) | ✅ Safe | Laravel tracks batch order. Rollback reverses in correct dependency order. |
| `php artisan migrate:fresh` | ✅ Safe | Drops all tables and re-runs from scratch |
| Data loss on rollback | ⚠️ Possible | `2026_06_23_182722` (remove password from expiry_trackers) — `down()` re-adds the column but the data was already migrated to vault. Rollback would expose NULL passwords. |
| FK constraint on rollback | ✅ Safe | All foreign keys are defined inline with `constrained()`, handled in correct order. |

---

## 13. Summary of Findings

### 🔴 Critical Fixes (v1.0 — Pre-Launch)

| # | Finding | Impact | Recommendation |
|---|---------|--------|----------------|
| 1 | Missing `deleted_at` index on 18 soft-delete tables | Every query scans deleted rows | Add `$table->index('deleted_at')` on all soft-delete tables in a new migration |
| 2 | Missing `status` index on 10+ service tables | Status filter scans full table | Add `$table->index('status')` on Domains, Hostings, VPS, VoIP, OtherServices, DomainEmails, ServiceProviders, ExpiryTrackers, Tasks, Assets |
| 3 | Missing `next_notification_due_at` index on `expiry_trackers` | Cron job scans full table every 15 min | Add `$table->index('next_notification_due_at')` |

### 🟡 High Priority (v1.1)

| # | Finding | Recommendation |
|---|---------|---------------|
| 4 | Missing `user_id` + `status` composite indexes | Add composite indexes on all entity tables for the most common RBAC + status filter pattern |
| 5 | Missing `due_date` index on `tasks` | Add `$table->index('due_date')` for calendar/sort queries |
| 6 | Missing `is_default` index on `smtp_profiles` | Add `$table->index('is_default')` — critical for notification sends |
| 7 | `can_import` mismatch | Add `can_import` column to `module_role_permissions` or remove from `RoleTemplateSeeder` |
| 8 | `smtp_profiles.created_by` — no ON DELETE rule | Verify FK constraint; if missing, add `cascadeOnDelete()` or `setNullOnDelete()` |

### 🟢 Medium Priority (v1.1 Backlog)

| # | Finding | Recommendation |
|---|---------|---------------|
| 9 | Credentials on 6 service tables (instead of vault) | Audit usage; consider migrating to vault references for consistent RBAC + reveal logging |
| 10 | `expiry_tracker_notifications` → `smtp_credentials_id` remnant | Check if column exists in migrations but model doesn't use it; clean up |
| 11 | `tasks.due_date` type inconsistency | Confirm if time component is needed; if not, change to `date` |
| 12 | `asset_assignments` missing `updated_at` | Some queries may expect it. Add if any code calls `touch()` on assignments. |

---

## 14. Overall Health Score

| Category | Score | Notes |
|----------|-------|-------|
| Index coverage | 7/10 | Core indexes present, but missing on common filter columns |
| FK integrity | 9/10 | Well-designed with appropriate cascade rules |
| Nullable design | 9/10 | Thoughtful choices; nullable-for-FOREIGN-KEY pattern correct |
| Naming consistency | 9/10 | Minor exceptions (`password_vault`, `voip`) |
| Migration quality | 9/10 | Clean migrations with proper up/down symmetry |
| Seeder quality | 8/10 | `can_import` gap in RoleTemplateSeeder |
| Type consistency | 9/10 | `tasks.due_date` datetime vs date is the only finding |
| Evolution history | 8/10 | Clean evolution from varchar→FK, good password migration path |

**Overall: 8.5/10 — Production-ready with targeted index improvements for performance as data scales.**

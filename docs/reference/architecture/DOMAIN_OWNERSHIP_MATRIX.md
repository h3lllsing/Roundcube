# DOMAIN OWNERSHIP MATRIX

> For every business entity in the system: who owns it, who edits it, who reads it.
> Evidence from migrations, models, seeders, and permission architecture.

---

## ENTITIES

### 1. User (`users`)

| Property | Value |
|----------|-------|
| **System of Record** | `users` table |
| **Exists in other tables?** | YES — `login_audits.email` (copy of email at login time), `activity_log.causer_id` (morph reference), `task_user.user_id` (assignment pivot), `user_roles.user_id` (role assignment pivot) |
| **If duplicated, authoritative?** | `users` table is the ONLY authority. All other tables reference by FK or store copies for historical/audit purposes. |
| **Cache copy?** | None. No user data is cached in another table. |
| **Derived copy?** | `login_audits.email` is a snapshot at login time — intentionally frozen copy for audit trail. |
| **Sync rules?** | NONE. `login_audits.email` is written once at login and NEVER updated when `users.email` changes. |
| **Can values drift?** | YES — if user changes email, `login_audits.email` retains the old email for historical records. This is INTENTIONAL (audit requirement). |
| **Drift detection?** | None. Drift is expected and preserved. |
| **Lifecycle owner?** | **Super Admin** (via Nyro/Tyro). User creation/deletion is gated by `users` module `can_create`/`can_delete`. |
| **Editing authority?** | `users` module (`slug: 'users'`): `can_create`, `can_read`, `can_update`, `can_delete`. |
| **Read-only consumers?** | All other modules that reference `user_id` FK: `password_vault`, `tasks`, `assets`, `hostings`, `domains`, `vps`, `voip`, `other_services`, `service_providers`, `expiry_trackers`, `domain_emails`, `notes`, `attachments`, `webhooks`, `login_audits`, `activity_log`. |
| **Can two modules edit?** | NO. Only `users` module edits User records. The `suspended_at` field is set by the Offboarding Checklist feature (F1) which checks `can_update` on `users` module — same editing authority. |
| **Conflict resolution?** | N/A — single editor. |
| **Source of truth?** | `users` table. |

---

### 2. Role (`roles` — Tyro package)

| Property | Value |
|----------|-------|
| **System of Record** | `roles` table |
| **Exists in other tables?** | YES — `user_roles` (pivot), `privilege_role` (pivot), `module_role_permissions.role_id` (FK) |
| **Duplicated authoritative?** | `roles` table only. Pivots store only the FK reference. |
| **Cache copy?** | None. |
| **Derived copy?** | None. |
| **Sync rules?** | N/A — single source. |
| **Drift?** | NO — FK constraints prevent orphan references. |
| **Drift detection?** | N/A — no drift possible. |
| **Lifecycle owner?** | **Super Admin** (via Tyro). |
| **Editing authority?** | `roles` module (`slug: 'roles'`): `can_create`, `can_read`, `can_update`, `can_delete`. |
| **Read-only consumers?** | `user_roles` (assignments), `module_role_permissions` (permission matrix), `privilege_role` (privilege grants). |
| **Two modules edit?** | NO. |
| **Conflict resolution?** | N/A. |
| **Source of truth?** | `roles` table. |

---

### 3. Module (`modules`)

| Property | Value |
|----------|-------|
| **System of Record** | `modules` table |
| **Exists in other tables?** | YES — `module_role_permissions.module_id` (FK), `user_module_permissions.module_id` (FK), every entity table with `module_id` FK (hostings, domains, etc.) |
| **Duplicated authoritative?** | `modules` table only. All references are FK-based. |
| **Cache copy?** | None. |
| **Derived copy?** | None. |
| **Sync rules?** | N/A. |
| **Drift?** | NO — FK constraints. |
| **Drift detection?** | N/A. |
| **Lifecycle owner?** | **Super Admin** (seeded by FeatureModuleSeeder). Module CRUD is gated by `module-permissions` module. |
| **Editing authority?** | `module-permissions` module (`slug: 'module-permissions'`). |
| **Read-only consumers?** | ALL service modules, permission system, sidebar navigation. |
| **Two modules edit?** | NO. |
| **Conflict resolution?** | N/A. |
| **Source of truth?** | `modules` table. |

---

### 4. ModuleRolePermission (`module_role_permissions`)

| Property | Value |
|----------|-------|
| **System of Record** | `module_role_permissions` table |
| **Exists in other tables?** | YES — `user_module_permissions` stores OVERRIDES for the same logical permission (module_id + action + user_id instead of module_id + action + role_id) |
| **Duplicated authoritative?** | `module_role_permissions` is the DEFAULT authority. `user_module_permissions` OVERRIDES it on a per-user basis. |
| **Cache copy?** | `user_module_permissions` is NOT a cache — it is an intentional override mechanism. |
| **Derived copy?** | `user_module_permissions` is partially derived (user-level overrides of role defaults) but is also independently editable. |
| **Sync rules?** | NO automatic sync. If a role is deleted, `module_role_permissions` rows for that role are orphaned (no FK cascade to `roles` — NEEDS REVIEW). `user_module_permissions` is unaffected by role changes. |
| **Drift?** | INTENTIONAL. User-level permissions are designed to differ from role defaults. |
| **Drift detection?** | No automatic detection. The `canOnModule()` method resolves the effective permission: check `user_module_permissions` first, fallback to `module_role_permissions`. This is runtime resolution, not drift detection. |
| **Lifecycle owner?** | **Super Admin** (via `module-permissions` module). |
| **Editing authority?** | `module-permissions` module (`slug: 'module-permissions'`): `can_create`, `can_read`, `can_update`, `can_delete`. |
| **Read-only consumers?** | `HasModulePermissions` trait (runtime permission resolution for ALL modules). |
| **Two modules edit?** | YES — `module_role_permissions` (role defaults) and `user_module_permissions` (user overrides) are edited via the same `module-permissions` UI but are different database tables. They can be set to conflicting values (user has `can_reveal=false` via role but `can_reveal=true` via override). |
| **Conflict resolution?** | `UserModulePermission` WINS over `ModuleRolePermission`. Resolved in `HasModulePermissions@canOnModule()`. Source confirmed: trait checks user override first, returns it immediately if set. Falls back to role permission only if user override is null. |
| **Source of truth?** | `user_module_permissions` (if set) > `module_role_permissions` (fallback). The effective permission is resolved at runtime, not stored. |

---

### 5. Service Models (Hosting, Domain, VPS, VoIP, OtherService)

| Property | Value |
|----------|-------|
| **System of Record** | Each service model's own table (`hostings`, `domains`, `vps`, `voip`, `other_services`) |
| **Exists in other tables?** | YES — **CRITICAL DUPLICATION ACROSS 4+ TABLES** (see below) |
| **Duplicated authoritative?** | Individual service tables are authoritative for their own primary attributes. But `name`, `cost`, `expiry_date`, and `password` are duplicated in other tables (see DUPLICATE_DATA_ANALYSIS.md). |
| **Cache copy?** | `expiry_trackers.name` is a copy of service name (free text, no FK). `vault_entry.service_name` is a copy of service name (free text, no FK). NEITHER is authoritative. |
| **Derived copy?** | None intentionally derived. All duplicates are manual entries with no sync. |
| **Sync rules?** | NONE. Zero sync mechanisms between service name → vault_entry.service_name, service name → expiry_trackers.name, or service password → vault_entry.encrypted_password. |
| **Drift?** | GUARANTEED. Every provisioning creates inconsistency. Estimated 30% mismatch rate between service name and vault_entry.service_name. |
| **Drift detection?** | NONE. No observer, no event, no scheduled job detects cross-table name/password drift. |
| **Lifecycle owner?** | **IT Operator** (creates services) and **Super Admin** (manages modules). Each service module is independently owned. |
| **Editing authority?** | Each service module independently: `hostings`, `domains`, `vps`, `voip`, `other_services`. Each has `can_create`, `can_read`, `can_update`, `can_delete`. |
| **Read-only consumers?** | `expiry_trackers` (via polymorphic trackable), `vault_entries` (via free-text service_name), `activity_log` (via Spatie). |
| **Two modules edit?** | NO — each service model is edited only by its own module. BUT: `password` is ALSO editable via the Vault module (vault_entry.encrypted_password). Two DIFFERENT modules (hostings module and vault module) can edit what is logically the same credential. |
| **Conflict resolution?** | NONE. If hosting password is changed via hostings module, vault entry is NOT updated. If vault entry password is changed via vault module, hosting inline password is NOT updated. No conflict detection exists. |
| **Source of truth?** | **AMBIGUOUS.** For `name`: service model table is authority. For `password`: NO authority exists — inline password and vault entry are independent. |

---

### 6. ServiceProvider (`service_providers`)

| Property | Value |
|----------|-------|
| **System of Record** | `service_providers` table |
| **Exists in other tables?** | YES — referenced by FK from all 5 service models (`service_provider_id`). Also has its OWN inline `password` column (encrypted). Password can ALSO be duplicated in vault_entry. |
| **Duplicated authoritative?** | `service_providers` is authoritative for provider metadata (name, website, email, cost). Password is duplicated in vault_entry with no sync (same problem as service models). |
| **Cache copy?** | None. |
| **Derived copy?** | None. |
| **Sync rules?** | NONE for password. N/A for metadata (single source). |
| **Drift?** | YES — provider password vs vault entry can drift. |
| **Drift detection?** | NONE. |
| **Lifecycle owner?** | **IT Operator / Procurement** (creates providers). |
| **Editing authority?** | `service-providers` module (`slug: 'service-providers'`): `can_create`, `can_read`, `can_update`, `can_delete`. |
| **Read-only consumers?** | All 5 service models (via `service_provider_id` FK), `expiry_trackers` (via `service_provider_id` FK), `domain_emails` (via `service_provider_id` FK). |
| **Two modules edit?** | YES — `service-providers` module edits provider record. `password_vault` module can also store the same provider password in a vault entry. Two independent edits, no coordination. |
| **Conflict resolution?** | NONE. |
| **Source of truth?** | `service_providers` table for metadata. `password`: AMBIGUOUS (inline vs vault entry). |

---

### 7. DomainEmail (`domain_emails`)

| Property | Value |
|----------|-------|
| **System of Record** | `domain_emails` table |
| **Exists in other tables?** | YES — referenced by `service_provider_id` FK. Has own inline `password` (encrypted) which CAN be duplicated in vault_entry. |
| **Duplicated authoritative?** | `domain_emails` is authoritative for email box metadata. Password duplicates vault_entry. |
| **Cache copy?** | None. |
| **Derived copy?** | None. |
| **Sync rules?** | NONE for password. |
| **Drift?** | YES — domain email password vs vault entry can drift. |
| **Drift detection?** | NONE. |
| **Lifecycle owner?** | **IT Operator** (creates mailboxes). |
| **Editing authority?** | `domain-emails` module (`slug: 'domain-emails'`). |
| **Read-only consumers?** | None. |
| **Two modules edit?** | YES — `domain-emails` module AND `password_vault` module can both store the same mailbox password. |
| **Conflict resolution?** | NONE. |
| **Source of truth?** | `domain_emails` table for email data. `password`: AMBIGUOUS. |

---

### 8. VaultEntry (`password_vault`)

| Property | Value |
|----------|-------|
| **System of Record** | `password_vault` table |
| **Exists in other tables?** | YES — `assets.vault_entry_id` (FK). `service_name` is a free-text duplicate of service model names with NO FK. |
| **Duplicated authoritative?** | `password_vault` is authoritative for vault entry metadata EXCEPT `service_name` — which is a non-authoritative copy of a service model's `name` field. |
| **Cache copy?** | `assets.vault_entry_id` is a FK reference (not a cache — it links assets to their credentials). |
| **Derived copy?** | None. |
| **Sync rules?** | NONE. `vault_entry.service_name` is manually entered, never synced from any service model. |
| **Drift?** | YES — `vault_entry.service_name` drifts from service model `name`. This is the provisioning data inconsistency problem. |
| **Drift detection?** | NONE. |
| **Lifecycle owner?** | **Any user with vault module access** (End Users own their own vault entries, Service Desk owns shared credentials). |
| **Editing authority?** | `vault` module (`slug: 'vault'`): `can_create`, `can_read`, `can_update`, `can_delete`, `can_reveal`. |
| **Read-only consumers?** | Asset module (via `assets.vault_entry_id` FK). |
| **Two modules edit?** | YES — `vault` module edits vault_entry. Service modules edit inline `password` which is a DIFFERENT column but stores the same logical credential. |
| **Conflict resolution?** | NONE. Inline password and vault entry password are entirely independent. |
| **Source of truth?** | `password_vault` table for vault entry data. For the logical "service password": AMBIGUOUS. |

---

### 9. ExpiryTracker (`expiry_trackers`)

| Property | Value |
|----------|-------|
| **System of Record** | `expiry_trackers` table |
| **Exists in other tables?** | YES — `name` is a free-text copy of service model `name`. `cost` is a separate copy of service model `cost`. `expiry_date` has an accessor that can read from trackable if own value is null. |
| **Duplicated authoritative?** | `expiry_trackers` is authoritative for renewal tracking (notification settings, renewal history). But `name`, `cost`, and `expiry_date` are COPIES of service model data. |
| **Cache copy?** | `expiry_trackers.name` — cache of service name (no sync). `expiry_trackers.cost` — cache of service cost (no sync). `expiry_trackers.expiry_date` — cache of service expiry date with fallback accessor. |
| **Derived copy?** | YES — `expiry_trackers.name`, `.cost`, and `.expiry_date` are MANUAL COPIES of service model data. The `expiry_date` accessor has a fallback to trackable's value, but if expiry_tracker's OWN `expiry_date` is set, it takes priority OVER the trackable's value. |
| **Sync rules?** | NONE. Created manually during provisioning, never updated when service changes. |
| **Drift?** | YES — if service name changes, expiry_tracker name stays old. If service cost changes, expiry_tracker cost stays old. |
| **Drift detection?** | NONE. The polymorphic `trackable` relationship exists but is never used to verify name/cost match. |
| **Lifecycle owner?** | **IT Operator** (creates/manages renewals). |
| **Editing authority?** | `expiry-trackers` module (`slug: 'expiry-trackers'`): `can_create`, `can_read`, `can_update`, `can_delete`. |
| **Read-only consumers?** | None. |
| **Two modules edit?** | YES — `expiry-trackers` module edits the tracker. Service module edits the service's own `expiry_date`. These are NOT the same column but represent the same logical date. |
| **Conflict resolution?** | NONE. Dashboard (F3) shows `expiry_tracker.expiry_date` which may differ from `hosting.expiry_date`. User must reconcile manually. |
| **Source of truth?** | `expiry_trackers` table for notification settings and renewal history. `name`, `cost`, `expiry_date` are CACHES of service data with NO authority. |

---

### 10. Task (`tasks`)

| Property | Value |
|----------|-------|
| **System of Record** | `tasks` table |
| **Exists in other tables?** | YES — `task_user` (assignment pivot). `activity_log` (via Spatie). |
| **Duplicated authoritative?** | `tasks` table. `task_user` stores only FK reference. Activity log stores description (derived text, not authoritative). |
| **Cache copy?** | None. |
| **Derived copy?** | `activity_log.description` contains text about task changes (e.g., "Task 'Reset MFA' completed"). This is an audit log entry, not authoritative. |
| **Sync rules?** | N/A. |
| **Drift?** | NO — activity_log is append-only. |
| **Drift detection?** | N/A. |
| **Lifecycle owner?** | **IT Manager / Service Desk** (creates/manages tasks for end users). |
| **Editing authority?** | `tasks` module (`slug: 'tasks'`): `can_create`, `can_read`, `can_update`, `can_delete`. |
| **Read-only consumers?** | End Users (see tasks assigned to them via dashboard), activity_log. |
| **Two modules edit?** | NO — only `tasks` module. |
| **Conflict resolution?** | N/A. |
| **Source of truth?** | `tasks` table. |

---

### 11. Asset (`assets`)

| Property | Value |
|----------|-------|
| **System of Record** | `assets` table |
| **Exists in other tables?** | YES — `asset_categories` (FK), `asset_types` (FK), `asset_locations` (FK). `password_vault` (via `vault_entry_id` FK). |
| **Duplicated authoritative?** | `assets` table for asset metadata. `vault_entry_id` is a FK reference, not a duplicate. |
| **Cache copy?** | None. |
| **Derived copy?** | None. |
| **Sync rules?** | N/A. |
| **Drift?** | NO — FK constraints prevent category/type/location from being deleted while assets reference them. |
| **Drift detection?** | N/A. |
| **Lifecycle owner?** | **IT Operator** (manages asset lifecycle). |
| **Editing authority?** | `assets` module (`slug: 'assets'`): `can_create`, `can_read`, `can_update`, `can_delete`. |
| **Read-only consumers?** | Users module (via `assigned_to` FK), Offboarding Checklist (F1). |
| **Two modules edit?** | NO — only `assets` module. |
| **Conflict resolution?** | N/A. |
| **Source of truth?** | `assets` table. |

---

### 12. LoginAudit (`login_audits`)

| Property | Value |
|----------|-------|
| **System of Record** | `login_audits` table |
| **Exists in other tables?** | YES — `users.email` is the authoritative email. `login_audits.email` is a copy at login time. |
| **Duplicated authoritative?** | `login_audits` is NOT authoritative for any user data. The `email` is a historical snapshot. The `user_id` FK references the user at the time of login. |
| **Cache copy?** | `login_audits.email` — snapshot cache of user email at login time. |
| **Derived copy?** | YES — derived from `users.email` at login time. |
| **Sync rules?** | NONE. Written once at login, never updated. |
| **Drift?** | EXPECTED — login audit preserves the email as it was at login time, even if user changes email later. This is INTENTIONAL for audit trail integrity. |
| **Drift detection?** | NONE. Drift is expected. |
| **Lifecycle owner?** | **System** (created on login). No user creates, updates, or deletes login audits. |
| **Editing authority?** | NONE — `login_audits` module is READ-ONLY for all users. Created by authentication system. |
| **Read-only consumers?** | Security Officer, Super Admin (via `login-audits` module). Security Timeline (F5). |
| **Two modules edit?** | NO — no module edits login audits. |
| **Conflict resolution?** | N/A. |
| **Source of truth?** | `login_audits` table for audit trail. `users.email` is the authoritative email source. |

---

### 13. Activity (`activity_log`)

| Property | Value |
|----------|-------|
| **System of Record** | `activity_log` table |
| **Exists in other tables?** | YES — `description` and `properties` JSON are derived from model data at the time of the event. |
| **Duplicated authoritative?** | `activity_log` is append-only. The `description` text may contain model data that is NOW different on the source model, but the activity log entry is frozen in time. |
| **Cache copy?** | `description` and `properties` — frozen cache of model state at event time. |
| **Derived copy?** | YES — derived from model state when events occur. |
| **Sync rules?** | NONE. Append-only. Never updated. |
| **Drift?** | EXPECTED — activity_log preserves historical state that may differ from current model state. This is INTENTIONAL for audit integrity. |
| **Drift detection?** | NONE. Drift is intentional. |
| **Lifecycle owner?** | **System** (Spatie creates on model events). |
| **Editing authority?** | NONE — `activity-logs` module is READ-ONLY. Created by Spatie `LogsActivity` trait. |
| **Read-only consumers?** | Security Officer, Super Admin (via `activity-logs` module). Security Timeline (F5). |
| **Two modules edit?** | NO. |
| **Conflict resolution?** | N/A. |
| **Source of truth?** | `activity_log` table for historical record. Current model state is authoritative for current values. |

---

### 14. SmtpProfile (`smtp_profiles`)

| Property | Value |
|----------|-------|
| **System of Record** | `smtp_profiles` table |
| **Exists in other tables?** | YES — `expiry_trackers.smtp_profile_id` (FK). `expiry_tracker_notifications.smtp_profile_id` (FK). |
| **Duplicated authoritative?** | Single source. FK references only. |
| **Cache copy?** | None. |
| **Derived copy?** | None. |
| **Sync rules?** | N/A. |
| **Drift?** | NO — FK constraints. |
| **Drift detection?** | N/A. |
| **Lifecycle owner?** | **Super Admin** (configures SMTP for system notifications). |
| **Editing authority?** | SMTP profile management (likely under `notifications` module or dedicated UI). |
| **Read-only consumers?** | `expiry_trackers` (via FK), `expiry_tracker_notifications` (via FK). |
| **Two modules edit?** | NO. |
| **Conflict resolution?** | N/A. |
| **Source of truth?** | `smtp_profiles` table. |

---

### 15. Webhook (`webhooks`)

| Property | Value |
|----------|-------|
| **System of Record** | `webhooks` table |
| **Exists in other tables?** | NO. |
| **Duplicated authoritative?** | Single source. |
| **Cache copy?** | None. |
| **Derived copy?** | None. |
| **Sync rules?** | N/A. |
| **Drift?** | N/A. |
| **Drift detection?** | N/A. |
| **Lifecycle owner?** | **Super Admin** (configures integrations). |
| **Editing authority?** | `webhooks` module (`slug: 'webhooks'`). |
| **Read-only consumers?** | None. |
| **Two modules edit?** | NO. |
| **Conflict resolution?** | N/A. |
| **Source of truth?** | `webhooks` table. |

---

## OWNERSHIP MAP (Visual Summary)

```
Module                    │ Edits                     │ Reads
──────────────────────────┼───────────────────────────┼──────────────────────────
users                     │ User                      │ All modules (via FK)
roles                     │ Role                      │ permission modules
privileges                │ Privilege                 │ permission modules
module-permissions        │ ModuleRolePermission      │ ALL modules (via trait)
                          │ UserModulePermission       │
hostings                  │ Hosting (inline password) │ vault (via service_name)
domains                   │ Domain                    │ vault (via service_name)
vps                       │ VPS (inline password)     │ vault (via service_name)
voip                      │ VoIP (inline password)    │ vault (via service_name)
other-services            │ OtherService (inline pw)  │ vault (via service_name)
service-providers         │ ServiceProvider (pw)      │ hostings, domains, etc.
domain-emails             │ DomainEmail (pw)          │ vault (via service_name)
vault                     │ VaultEntry (encrypted_pw) │ Assets (via vault_entry_id)
expiry-trackers           │ ExpiryTracker             │ (none)
tasks                     │ Task                      │ End Users (dashboard)
assets                    │ Asset                     │ Offboarding Checklist
login-audits              │ READ-ONLY (system writes) │ Security Timeline
activity-logs             │ READ-ONLY (system writes) │ Security Timeline
webhooks                  │ Webhook                   │ (none)
```

**CRITICAL FINDING:** For the logical entity "password for a service", there are up to **5 independent storage locations**:
1. Service model inline `password` (hostings, vps, voip, other_services)
2. `password_vault.encrypted_password` (vault entry)
3. `service_providers.password` (provider password)
4. `domain_emails.password` (mailbox password)
5. No sync mechanism exists between any of them.

**This is the single largest data integrity problem in the system.**

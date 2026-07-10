# SOURCE OF TRUTH MATRIX

> For every BUSINESS ATTRIBUTE across every entity: where is the authoritative value?
> Attribute-level tracing, not table-level.

---

## IDENTITY ATTRIBUTES

| Logical Attribute | Source Table | Column | Also Stored In | Authoritative? |
|---|---|---|---|---|
| User ID | `users` | `id` | All FK references | `users.id` — FK constraints enforce |
| User name | `users` | `name` | `activity_log.description` (text, may contain name) | `users.name` |
| User email | `users` | `email` | `login_audits.email` (snapshot at login) | `users.email` — login_audit.email is historical |
| User password | `users` | `password` | (none — hashed, not stored elsewhere) | `users.password` |
| User suspended_at | `users` | `suspended_at` | (none) | `users.suspended_at` |
| User suspension_reason | `users` | `suspension_reason` | (none) | `users.suspension_reason` |
| Role name | `roles` | `name` | (none) | `roles.name` |
| Role slug | `roles` | `slug` | (none) | `roles.slug` |
| User role assignment | `user_roles` | `user_id` + `role_id` | (none) | `user_roles` pivot |
| Module name | `modules` | `name` | Sidebar navigation (config/cache) | `modules.name` |
| Module slug | `modules` | `slug` | `module_role_permissions`, `user_module_permissions`, all entity FKs | `modules.slug` |
| Module permission (role default) | `module_role_permissions` | `can_create`...`can_reveal` | `user_module_permissions` (overrides) | `module_role_permissions` unless `user_module_permissions` is set |

---

## SERVICE ATTRIBUTES

| Logical Attribute | Source Table | Column | Also Stored In | Authoritative? |
|---|---|---|---|---|
| Service ID | `hostings`/`domains`/`vps`/`voip`/`other_services` | `id` | ExpiryTracker `trackable_id` (polymorphic) | Service table |
| Service name | `hostings`/`domains`/`vps`/`voip`/`other_services` | `name` | `vault_entry.service_name` (free text) | **SERVICE TABLE** — vault_entry copy is NOT authoritative |
| Service name (DomainEmail) | `domain_emails` | `email` | `vault_entry.service_name` (free text) | `domain_emails.email` |
| Service name (ExpiryTracker) | `expiry_trackers` | `name` | (actually a COPY of service name, not authoritative) | **AMBIGUOUS** — expiry_tracker.name was manually entered, service table name is the true source |
| Service provider link | `hostings`/`domains`/`vps`/`voip`/`other_services` | `service_provider_id` | (none — FK) | Service table FK |
| Service password (inline) | `hostings`/`vps`/`voip`/`other_services` | `password` | `password_vault.encrypted_password` (independently stored) | **NO AUTHORITY** — both are independently editable |
| Service password (DomainEmail) | `domain_emails` | `password` | `password_vault.encrypted_password` | **NO AUTHORITY** |
| Service password (ServiceProvider) | `service_providers` | `password` | `password_vault.encrypted_password` | **NO AUTHORITY** |
| Service cost | `hostings`/`domains`/`vps`/`voip`/`other_services` | `cost` | `expiry_trackers.cost` (copy) | **SERVICE TABLE** — expiry_tracker cost is a copy |
| Service cost (ExpiryTracker) | `expiry_trackers` | `cost` | (copy of service cost) | **NOT authoritative** — value may differ from service cost |
| Service expiry_date | `hostings`/`domains`/`vps`/`voip`/`other_services` | `expiry_date` | `expiry_trackers.expiry_date` (with fallback accessor) | **SERVICE TABLE** — expiry_tracker value is a copy WITH accessor fallback; if both set, expiry_tracker's value takes display priority in its own UI |
| Service status | `hostings`/`domains`/`vps`/`voip`/`other_services` | `status` | (none) | Service table |
| Service plan | `hostings`/`vps` | `plan` | (none) | Service table |
| Service start_date | all service models | `start_date` | (none) | Service table |
| Service monitoring_url | all service models + providers + expiry_trackers | `monitoring_url` | 8 tables have this column | **EACH TABLE IS AUTHORITATIVE FOR ITS OWN ROW** — not a duplicate, each service type has its own monitoring URL |
| Service last_ping_at | all service-adjacent models | `last_ping_at` | 8 tables | Same as monitoring_url — per-type |

---

## VAULT / CREDENTIAL ATTRIBUTES

| Logical Attribute | Source Table | Column | Also Stored In | Authoritative? |
|---|---|---|---|---|
| Vault entry ID | `password_vault` | `id` | `assets.vault_entry_id` (FK) | `password_vault.id` |
| Vault service_name | `password_vault` | `service_name` | Service model `name` (the original) | **SERVICE MODEL** — vault entry copy is a manually-entered duplicate |
| Vault username | `password_vault` | `username` | (none) | `password_vault.username` |
| Vault encrypted_password | `password_vault` | `encrypted_password` | Service model inline `password` (independently stored) | **NO AUTHORITY** — both are independently editable |
| Vault service_url | `password_vault` | `service_url` | (none) | `password_vault.service_url` |
| Asset vault_entry_id | `assets` | `vault_entry_id` | (none — FK) | `assets.vault_entry_id` |
| can_reveal permission | `module_role_permissions` or `user_module_permissions` | `can_reveal` | Two tables (override pattern) | `user_module_permissions` (if set) > `module_role_permissions` (fallback) |

---

## RENEWAL ATTRIBUTES

| Logical Attribute | Source Table | Column | Also Stored In | Authoritative? |
|---|---|---|---|---|
| Expiry tracker ID | `expiry_trackers` | `id` | `expiry_tracker_notifications.expiry_tracker_id` (FK) | `expiry_trackers.id` |
| Expiry tracker name | `expiry_trackers` | `name` | Service model `name` (the original source) | **SERVICE MODEL** — expiry_tracker name is a copy |
| Expiry tracker cost | `expiry_trackers` | `cost` | Service model `cost` (the original source) | **SERVICE MODEL** — expiry_tracker cost is a copy |
| Expiry tracker expiry_date | `expiry_trackers` | `expiry_date` | Service model `expiry_date` (the original source) | **AMBIGUOUS** — accessor prefers expiry_tracker's own value over trackable's; user can set independently |
| Expiry tracker renewal_date | `expiry_trackers` | `renewal_date` | (none) | `expiry_trackers.renewal_date` |
| Expiry tracker status | `expiry_trackers` | `status` | (none) | `expiry_trackers.status` |
| Expiry tracker smtp_profile_id | `expiry_trackers` | `smtp_profile_id` | (none — FK) | `expiry_trackers.smtp_profile_id` |
| Expiry tracker notification config | `expiry_trackers` | `notify_days_before`, `notify_on_expiry_day`, etc. | (none) | `expiry_trackers` |
| Expiry tracker polymorphic link | `expiry_trackers` | `trackable_type` + `trackable_id` | (none) | `expiry_trackers` — links to service model |
| Link between service and renewal | N/A (polymorphic) | `trackable_type` + `trackable_id` | (none — morph) | `expiry_trackers` side of the morph |
| Notification record | `expiry_tracker_notifications` | all columns | (none) | `expiry_tracker_notifications` |

---

## TASK ATTRIBUTES

| Logical Attribute | Source Table | Column | Also Stored In | Authoritative? |
|---|---|---|---|---|
| Task ID | `tasks` | `id` | `task_user.task_id` (FK) | `tasks.id` |
| Task title | `tasks` | `title` | `activity_log.description` (copy) | `tasks.title` |
| Task description | `tasks` | `description` | `activity_log.properties` (copy) | `tasks.description` |
| Task status | `tasks` | `status` | `activity_log.properties` (copy) | `tasks.status` |
| Task priority | `tasks` | `priority` | (none) | `tasks.priority` |
| Task due_date | `tasks` | `due_date` | (none) | `tasks.due_date` |
| Task assignment | `task_user` | `user_id` + `task_id` | (none) | `task_user` pivot |

---

## ASSET ATTRIBUTES

| Logical Attribute | Source Table | Column | Also Stored In | Authoritative? |
|---|---|---|---|---|
| Asset ID | `assets` | `id` | (none) | `assets.id` |
| Asset tag | `assets` | `asset_tag` | (none) | `assets.asset_tag` |
| Asset serial_number | `assets` | `serial_number` | (none) | `assets.serial_number` |
| Asset status | `assets` | `status` | (none) | `assets.status` |
| Asset assigned_to | `assets` | `assigned_to` | (none — FK to users) | `assets.assigned_to` |
| Asset category | `assets` | `category_id` | `asset_categories.name` | `assets.category_id` (FK) |
| Asset vault_entry_id | `assets` | `vault_entry_id` | `password_vault.id` | `assets.vault_entry_id` (FK) |

---

## AUDIT ATTRIBUTES

| Logical Attribute | Source Table | Column | Also Stored In | Authoritative? |
|---|---|---|---|---|
| Login audit event | `login_audits` | `event`, `email`, `ip_address`, `user_agent` | (none) | `login_audits` — single source |
| Activity log entry | `activity_log` | `description`, `event`, `properties` | (none) | `activity_log` — single source |
| Activity causer | `activity_log` | `causer_id` + `causer_type` | `users.id` (resolved at read time) | `activity_log` records the FK; `users` is the current user state |
| Activity subject | `activity_log` | `subject_id` + `subject_type` | Service model `id` (resolved at read time) | `activity_log` records the FK; service model is current state |

---

## SUMMARY: ATTRIBUTES WITH NO SOURCE OF TRUTH

These attributes exist in multiple tables with NO sync, NO authority, and NO drift detection:

| Attribute | Located In | Tables | Problem |
|-----------|-----------|--------|---------|
| **Service password** | Inline on 4 service models + vault_entry + service_providers + domain_emails | 7+ tables | **AMBIGUOUS** — no single authority. Changes to one do NOT propagate. |
| **Service name** | Service model + vault_entry.service_name + expiry_tracker.name | 7+ tables | **AMBIGUOUS** — vault_entry.service_name is free text, no FK. ExpiryTracker.name is free text, no FK. |
| **Service cost** | Service model + expiry_tracker.cost | 6+ tables | **AMBIGUOUS** — service cost and expiry_tracker cost can differ. ExpiryTracker dashboard (F3) shows expiry_tracker value, not service value. |
| **Service expiry_date** | Service model + expiry_tracker.expiry_date | 6+ tables | **AMBIGUOUS** — accessor has fallback logic but both can be independently set. |

**Total attributes with ambiguous authority: 4.**
**Total tables affected by ambiguous authority: 7+.**

---

## RUNTIME RESOLUTION OF PERMISSIONS (Designed Override)

| Effective Attribute | Resolution Order | Tables | Mechanism |
|---|---|---|---|
| Can user perform action on module? | 1. `user_module_permissions` (if set) | `user_module_permissions` + `module_role_permissions` | `HasModulePermissions@canOnModule()` trait |
| | 2. `module_role_permissions` (fallback) | | |

This is a DESIGNED override pattern, not a data integrity problem. The two tables are intentionally independent.

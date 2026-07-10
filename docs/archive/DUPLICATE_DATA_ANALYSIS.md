# DUPLICATE DATA ANALYSIS

> Every instance of data duplication across tables, with drift potential, detection status, and remediation.
> Organized by duplication type.

---

## TYPE 1: DESIGNED DUPLICATION (Intentional, No Action Required)

### 1A. Permission Override Pattern

| Attribute | Primary Table | Duplicate Table | Intent |
|-----------|--------------|-----------------|--------|
| Module permission (can_create, can_read, etc.) | `module_role_permissions` | `user_module_permissions` | User-level override of role default. Resolved at runtime by `canOnModule()`: user override wins. |

**Drift potential:** HIGH by design. `user_module_permissions` is SUPPOSED to differ from `module_role_permissions`.
**Detection:** NONE needed — drift is the feature.
**Remediation:** NONE.

### 1B. Historical Audit Snapshots

| Attribute | Source Table | Snapshot Table | Intent |
|-----------|-------------|----------------|--------|
| User email | `users.email` | `login_audits.email` | Freeze email at login time for audit trail. If user changes email later, login history preserves the original. |
| Task title/description | `tasks.title`, `tasks.description` | `activity_log.description`, `activity_log.properties` | Freeze task state at time of change for audit trail. |
| Model state at event time | All models | `activity_log.properties` | Freeze model attribute values at the time of each CRUD event. |

**Drift potential:** EXPECTED. The snapshot is supposed to be different from current state (it's historical).
**Detection:** NONE needed — drift is the purpose.
**Remediation:** NONE.

### 1C. Polymorphic References

| Attribute | Owning Table | Referencing Table | Mechanism |
|-----------|-------------|-------------------|-----------|
| Service identity | `hostings.id`, `domains.id`, etc. | `expiry_trackers.trackable_id` + `trackable_type` | Polymorphic morph. The FK is stored on expiry_tracker side. |
| Causer identity | `users.id` | `activity_log.causer_id` + `causer_type` | Polymorphic morph. |
| Subject identity | Various model IDs | `activity_log.subject_id` + `subject_type` | Polymorphic morph. |

**Drift potential:** MEDIUM. If the source record is soft-deleted, the morph resolves to null. The reference survives but the target becomes inaccessible.
**Detection:** None. The `loadMorph()` pattern handles null gracefully.
**Remediation:** NONE — FK constraints prevent hard deletion while references exist.

---

## TYPE 2: ACCIDENTAL DUPLICATION (Unintentional, Requires Action)

### 2A. Service Name Triangle

**Tables involved:** 7+ tables

| Table | Column | How It Gets Set | Sync Mechanism |
|-------|--------|-----------------|----------------|
| `hostings.name` | VARCHAR | User enters when creating hosting | Source of truth |
| `domains.name` | VARCHAR | User enters when creating domain | Source of truth |
| `vps.name` | VARCHAR | User enters when creating VPS | Source of truth |
| `voip.name` | VARCHAR | User enters when creating VoIP | Source of truth |
| `other_services.name` | VARCHAR | User enters when creating SaaS | Source of truth |
| `password_vault.service_name` | VARCHAR (FREE TEXT) | User enters when creating vault entry | **NONE** — no FK, no observer |
| `expiry_trackers.name` | VARCHAR (FREE TEXT) | User enters when creating expiry tracker | **NONE** — no FK, no observer |

**Drift mechanism:** User changes service `name` via service edit form. Vault entry and expiry tracker retain the OLD name. If user changes name in vault entry to match, they must do so manually — and if they change vault entry FIRST, the service name doesn't update either.

**Drift probability:** 100% over time. Every provisioning event creates a new opportunity for inconsistency. Estimated 30% of provisioning events produce at least one name discrepancy.

**Detection status:** NONE. No query, no report, no alert detects name mismatches.

**Impact:**
- Service Desk cannot find credentials by searching service name (vault entry has different name)
- Renewal dashboard shows wrong service name if expiry_tracker.name differs
- Cross-referencing is unreliable

**Remediation:**

| Priority | Action | Effort | Impact |
|----------|--------|--------|--------|
| P0 | Add FK/link between vault_entry and service model | 2 days (pivot table) | Eliminates future name drift for linked records |
| P1 | Backfill existing vault_entry.service_name = service name | 1 day (Artisan command + manual verification) | Corrects historical drift |
| P2 | Add observer to sync name changes | 1 day | Prevents future drift |
| P3 | Auto-fill expiry_tracker.name from trackable.name in provisioning | Part of Quick Provision (F4) | Prevents future drift for new records |

### 2B. Service Password Heptagon

**Tables involved:** 7 tables

| Table | Column | How It Gets Set | Sync Mechanism |
|-------|--------|-----------------|----------------|
| `hostings.password` | VARCHAR (encrypted) | User enters when creating/editing hosting | Source of truth for this table |
| `vps.password` | VARCHAR (encrypted) | User enters when creating/editing VPS | Source of truth for this table |
| `voip.password` | VARCHAR (encrypted) | User enters when creating/editing VoIP | Source of truth for this table |
| `other_services.password` | VARCHAR (encrypted) | User enters when creating/editing SaaS | Source of truth for this table |
| `service_providers.password` | TEXT (encrypted) | User enters when creating/editing provider | Source of truth for this table |
| `domain_emails.password` | TEXT (encrypted) | User enters when creating/editing mailbox | Source of truth for this table |
| `password_vault.encrypted_password` | TEXT (encrypted) | User enters when creating/editing vault entry | **NONE** — no FK to any service |

**Drift mechanism:** The same logical password (e.g., "root password for Acme Web Server") can be stored in BOTH `hostings.password` AND `password_vault.encrypted_password`. If someone changes the hosting password via the hostings module, the vault entry does NOT update. If someone changes the vault entry, the hosting password does NOT update. No mechanism alerts the user that a duplicate exists.

**Drift probability:** GUARANTEED. Every password change creates a divergence if both copies exist.

**Detection status:** NONE. No query detects which passwords are duplicated.

**Impact:**
- Service Desk reveals the inline password (F2 feature) → password is CURRENT
- Service Desk reveals the vault entry → password is STALE
- User gets wrong password, calls back, wastes 5+ minutes
- Security audit finds "vault entry contains credential for service X but hosting.inline password is different" → compliance finding

**Remediation:**

| Priority | Action | Effort | Impact |
|----------|--------|--------|--------|
| P0 | **ELIMINATE inline passwords.** Declare vault as the single credential store. | 5-10 days | Eliminates the duplication at source |
| P0 | Add `credential_service` pivot table for vault→service linking | 2 days | Makes the relationship explicit |
| P1 | Migrate all existing inline passwords INTO vault entries | 3-5 days (data migration + validation) | Consolidates historical data |
| P2 | Remove `password` columns from service models | 2 days (6 migrations + model changes) | Eliminates the column entirely |
| P2 | When user edits service module, redirect password field to vault | 1-2 days (UI change) | Consistent UX |

**Alternative to elimination:** If inline passwords serve a different purpose than vault entries (inline = service's own control panel password, vault = shared team credential), then the duplication is INTENTIONAL but UNMANAGED. In this case:

| Priority | Action | Effort | Impact |
|----------|--------|--------|--------|
| P1 | Add `service_password_id` FK to service model pointing to vault entry | 1 day | Makes the relationship explicit |
| P2 | Add "Linked vault entry" indicator on service detail page | 1 day | Shows user: "This service also has a vault entry" |
| P3 | Add "Update both" toggle when changing password | 2 days | Coordinated update |

### 2C. Service Cost Duo

**Tables involved:** 6 tables (5 service models + expiry_trackers)

| Table | Column | Sync |
|-------|--------|------|
| `hostings.cost` | DECIMAL | Source of truth for hosting cost |
| `domains.cost` | DECIMAL | Source of truth for domain cost |
| `vps.cost` | DECIMAL | Source of truth for VPS cost |
| `voip.cost` | DECIMAL | Source of truth for VoIP cost |
| `other_services.cost` | DECIMAL | Source of truth for SaaS cost |
| `expiry_trackers.cost` | DECIMAL | **Copy with no sync** |

**Drift mechanism:** User creates expiry tracker with cost = $120. Later, they update the hosting cost to $150. The expiry tracker still shows $120.

**Drift probability:** MODERATE. Cost changes are infrequent (quarterly/annually) but when they happen, the drift is silent.

**Detection status:** NONE.

**Impact:**
- Renewal Dashboard (F3) shows `expiry_tracker.cost` = $120 (stale)
- Cost report uses service cost (correct) or expiry tracker cost (stale) depending on which report
- Budget decisions made on stale data

**Remediation:**

| Priority | Action | Effort |
|----------|--------|--------|
| P1 | Auto-fill `expiry_tracker.cost` from service cost on creation | 0.5 days (Quick Provision F4 scope) |
| P2 | Add `cost_last_synced_at` to expiry_tracker | 0.5 days |
| P3 | Add "Cost differs from service record" warning on expiry tracker detail | 0.5 days |

### 2D. Service Expiry Date Duo

**Tables involved:** 6 tables (5 service models + expiry_trackers)

| Table | Column | Relationship |
|-------|--------|-------------|
| Service model | `expiry_date` | Source of truth |
| `expiry_trackers` | `expiry_date` | Copy with accessor fallback |
| `expiry_trackers` | `trackable.expiry_date` (via accessor) | Fallback when own value is null |

**Drift mechanism:** The accessor creates a FALSE sense of consistency. When `expiry_trackers.expiry_date` is null, the accessor reads from `trackable.expiry_date` — they appear consistent. But if BOTH are set (which happens when user manually edits expiry_tracker date), they can diverge silently.

**Drift probability:** LOW (the accessor hides the problem until both are set). But once both are set, drift is permanent.

**Detection status:** NONE. The accessor is a runtime fallback, not a drift detection mechanism.

**Impact:**
- Renewal Dashboard shows `expiry_tracker.expiry_date` which may differ from actual service expiry
- Notification schedule uses `expiry_tracker.expiry_date` — could send renewal reminders at wrong time
- If service is renewed via service module (updating service.expiry_date) but expiry_tracker.expiry_date is not updated, the tracker believes the old expiry date is still current

**Remediation:**

| Priority | Action | Effort |
|----------|--------|--------|
| P1 | Remove `expiry_trackers.expiry_date` independent column. Make it a READ-ONLY display of `trackable.expiry_date`. | 0.5 days (model change, migration to drop column) |
| P1 | Store `renewal_date` separately on expiry_tracker (for history) | Already in schema — `renewal_date` exists |
| P2 | Add observer: when service.expiry_date changes, update linked expiry_trackers | 1 day |

---

## TYPE 3: CROSS-REFERENCE DUPLICATION (FK-based, No Drift)

| Reference | Source Table | Target Table | Mechanism | Drift? |
|-----------|-------------|--------------|-----------|--------|
| Service → Provider | `hostings.service_provider_id` | `service_providers.id` | FK with nullOnDelete | NO — FK constraint |
| Domain → Hosting | `domains.hosting_id` | `hostings.id` | FK with nullOnDelete | NO — FK constraint |
| Asset → VaultEntry | `assets.vault_entry_id` | `password_vault.id` | FK with nullOnDelete | NO — FK constraint |
| Task → User | `task_user.user_id` | `users.id` | FK with cascadeOnDelete | NO — FK constraint |
| ExpiryTracker → ServiceProvider | `expiry_trackers.service_provider_id` | `service_providers.id` | FK with nullOnDelete | NO — FK constraint |
| User → Role | `user_roles.role_id` | `roles.id` | FK (Tyro) | NO — FK constraint |

All FK-based references are PROTECTED against drift by database-level foreign key constraints. The only exception is polymorphic `morphs()` which are NOT enforced at the database level but are consistent at the application level.

---

## SUMMARY: DUPLICATION INVENTORY

| # | Duplicate Pair | Drift Type | Drift Probability | Current Detection | Recommended Action |
|---|---|---|---|---|---|
| D1 | Service name → vault_entry.service_name | ACCIDENTAL | 100% over time | NONE | Add pivot table (P0), backfill (P1) |
| D2 | Service name → expiry_tracker.name | ACCIDENTAL | 100% over time | NONE | Auto-fill from service (F4 scope) |
| D3 | Inline password → vault entry password | ACCIDENTAL | GUARANTEED | NONE | Eliminate inline passwords OR add FK |
| D4 | Service cost → expiry_tracker.cost | ACCIDENTAL | MODERATE | NONE | Auto-fill from service + drift warning |
| D5 | Service expiry → expiry_tracker.expiry | ACCIDENTAL | LOW (hidden by accessor) | NONE | Remove independent expiry date from expiry_tracker |
| D6 | User email → login_audit.email | DESIGNED | EXPECTED | NONE (intentional) | None |
| D7 | Model state → activity_log.properties | DESIGNED | EXPECTED | NONE (intentional) | None |
| D8 | ModuleRolePermission → UserModulePermission | DESIGNED | HIGH (intentional) | Runtime resolution | None |

**RED FLAG:** Items D1-D5 have ZERO detection mechanisms. No query, no report, no dashboard, no alert tells the user that data has diverged. The system silently accumulates inconsistencies that erode trust in every cross-entity feature.

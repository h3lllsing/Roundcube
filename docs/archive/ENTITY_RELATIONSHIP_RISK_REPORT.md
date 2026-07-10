# ENTITY RELATIONSHIP RISK REPORT

## Entity Relationship Map

```
User ──┬──<> Domain (user_id FK CASCADE)        ← RISK: CASCADE DELETES CORPORATE RECORDS
       ├──<> Hosting (user_id FK CASCADE)        ← RISK: CASCADE DELETES CORPORATE RECORDS
       ├──<> Vps (user_id FK CASCADE)            ← RISK: CASCADE DELETES CORPORATE RECORDS
       ├──<> Voip (user_id FK CASCADE)           ← RISK: CASCADE DELETES CORPORATE RECORDS
       ├──<> ServiceProvider (user_id FK CASCADE)← RISK: CASCADE DELETES CORPORATE RECORDS
       ├──<> DomainEmail (user_id FK CASCADE)    ← RISK: CASCADE DELETES CORPORATE RECORDS
       ├──<> OtherService (user_id FK CASCADE)   ← RISK: CASCADE DELETES CORPORATE RECORDS
       ├──<> Asset (user_id FK CASCADE)          ← RISK: CASCADE DELETES CORPORATE RECORDS
       ├──<> ExpiryTracker (user_id FK CASCADE)  ← RISK: CASCADE DELETES CORPORATE RECORDS
       ├──<> VaultEntry (user_id FK CASCADE)     ← CORRECT: personal ownership
       ├──<> Task (via task_user pivot)
       ├──<> Note (user_id FK CASCADE)
       └──<> Webhook (user_id FK CASCADE)

Module ──┬──<> Domain (module_id FK SET NULL)    ← RISK: Module deletion = invisible records
         ├──<> Hosting (module_id FK SET NULL)
         ├──<> Vps (module_id FK SET NULL)
         ├──<> Voip (module_id FK SET NULL)
         ├──<> ServiceProvider (module_id FK SET NULL)
         ├──<> DomainEmail (module_id FK SET NULL)
         ├──<> OtherService (module_id FK SET NULL)
         ├──<> Asset (module_id FK SET NULL)
         ├──<> ExpiryTracker (module_id FK SET NULL)
         ├──<> VaultEntry (module_id FK SET NULL)
         ├──<> ModuleRolePermission (module_id FK CASCADE) ← CORRECT: permission bound to module
         └──<> UserModulePermission (module_id FK CASCADE) ← CORRECT: permission bound to module

ServiceProvider ──┬──<> Domain (service_provider_id FK SET NULL)
                  ├──<> Hosting (service_provider_id FK SET NULL)
                  ├──<> Vps (service_provider_id FK SET NULL)
                  ├──<> Voip (service_provider_id FK SET NULL)
                  ├──<> DomainEmail (service_provider_id FK SET NULL)
                  ├──<> OtherService (service_provider_id FK SET NULL)
                  └──<> ExpiryTracker (service_provider_id FK SET NULL)

Domain ──<> DomainEmail (domain_id FK SET NULL)
Hosting ──<> Domain (hosting_id FK SET NULL)

Asset ──┬──<> AssetAssignment (asset_id FK CASCADE)
        ├──<> AssetCategory (category_id FK CASCADE)
        ├──<> AssetType (type_id FK CASCADE)
        ├──<> AssetLocation (location_id FK SET NULL)
        ├──<> VaultEntry (vault_entry_id FK SET NULL)  ← RISK: Cross-domain reference
        └──<> User (assigned_to FK SET NULL)           ← CORRECT

ExpiryTracker ──┬──<> SmtpProfile (smtp_profile_id FK SET NULL)
                └──<> (polymorphic) trackable (trackable_type + trackable_id)

Feature ──<> Module (feature_id FK CASCADE)
```

---

## Relationship Risks (Prioritized)

### CRITICAL

#### R1: `user_id` FK CASCADE on ALL 9 global tables

**Impact**: Deleting any user cascades to delete ALL corporate records they ever touched.

**Example**: An employee who created 200 corporate records over 3 years leaves. Admin soft-deletes the user. 200 corporate records are permanently lost.

**Fix**: Migration to `SET NULL` + add `created_by` as audit trail.

#### R2: Module deletion = Silent Corporate Record Loss

**Impact**: `module_id` FK is SET NULL on all 9 tables. After this, RbacScope `WHERE module_id IN (...)` returns no rows. Records exist but are invisible.

**Example**: An admin deletes the "Domains" module. ALL domain records immediately disappear from every non-super-admin user's view. No error. No warning.

**Fix**: Prevent module deletion (toggle `is_active`). Soft-delete modules should not cascade.

#### R3: API > Web > Dashboard > Export — Four Different Visibility Rules

**Impact**: Same user, same data, four different queries, four different result sets.

| Layer | Visibility Rule | Risk |
|-------|----------------|------|
| Web Controllers | RbacScope (module_id IN) | Correct |
| Web Dashboard | OperationsWidget (module_id IN) | Correct |
| API Dashboard | `WHERE user_id = ?` | **Wrong** — shows zero records after Phase 3 |
| API Controllers | `WHERE user_id = ?` | **Wrong** — shows zero records after Phase 3 |
| Web Export | Falls back to `user_id` for non-admin | **Wrong** — shows zero records after Phase 3 |
| API Export | Falls back to `user_id` for non-admin | **Wrong** — shows zero records after Phase 3 |

**Fix**: Standardize ALL visibility layers to use module_id-based filtering identical to RbacScope.

---

### HIGH

#### R4: ServiceProvider is a Universal Hub

ServiceProvider has 7 `hasMany` relationships. It is the most-coupled table in the schema. Deletion or corruption of ServiceProvider data impacts 7 dependent record types. The `service_provider_id` FK is SET NULL, preventing cascade delete — but the semantic coupling means a provider change requires updates across 7 tables.

#### R5: No Unique Business Key Constraints

No global table has a unique constraint on its natural business key:
- `domains.name` — multiple "example.com" records possible
- `hosting.domain` — multiple records for same domain
- `domain_emails.email` — multiple records for same email
- `vps.ip_address` — multiple records for same IP

Soft deletes make this worse: deleted records don't block creation of duplicates.

#### R6: Polymorphic ExpiryTracker with No FK Integrity

`trackable_type` / `trackable_id` has no foreign key constraint. A source record can be deleted without the linked ExpiryTracker being updated. The tracker becomes orphaned. The `getExpiryDateAttribute()` accessor handles this gracefully, but the data is inconsistent.

---

### MEDIUM

#### R7: Asset.vault_entry_id Cross-Domain Reference

Assets can reference VaultEntry. Vault uses ownership scope. Assets use module scope. If a user creates a Vault entry and links it to an Asset, another user with Asset access but no Vault access will see a vault_entry_id they cannot decrypt. The `canAccessVault()` check exists in the show view but not in the API.

#### R8: `assigned_to` on Asset vs `user_id` on Asset

`assets` has TWO user FKs:
- `user_id` (NOT NULL, CASCADE) — "creator" (misnamed)
- `assigned_to` (nullable, SET NULL) — actual current custodian

This is confusing at best. Two FKs to the same table with similar naming but radically different semantics.

#### R9: No department/location normalization

`vps.department`, `vps.location`, `assets.department` are free-text strings. No FK, no lookup table. This will produce data quality issues over time ("IT", "I.T.", "Information Technology" as three separate values).

---

## Normalization Violations

| Violation | Location | Detail |
|-----------|----------|--------|
| 1NF — JSON columns | vps, asset, expiry_tracker | `login_ids`, `additional_ips`, `specifications`, `notify_days_before` are JSON arrays with structured data that should be child tables |
| 2NF — Partial dependency | domain_emails | `storage_mb` depends on the service provider plan, not the email account itself |
| 2NF — Partial dependency | All global tables | `monitoring_url` and `last_ping_at` are monitoring concerns, not record data. Should be a separate `monitoring_statuses` table |
| 3NF — Transitive dependency | vps, hosting | `service_provider_id` → provider details are in another table (correct), but cost depends on both plan AND provider — partial violation |
| BCNF — Overlapping FKs | asset | `user_id` (creator) and `assigned_to` (custodian) both reference users but have completely different semantics in the same table |

## Recommended Normalization Changes

1. **Extract monitoring**: Create `monitoring_statuses` table (model_type, model_id, url, last_ping_at) — polymorphic, removes 6 columns from 9 tables
2. **Extract department/location**: Create `departments` and `locations` tables (or use existing `asset_locations`)
3. **Prevent module deletion**: Business logic rule rather than schema change
4. **Add unique business keys**: Composite unique indexes with `deleted_at` for soft-delete-aware uniqueness

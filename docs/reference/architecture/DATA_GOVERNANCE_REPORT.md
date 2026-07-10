# DATA GOVERNANCE REPORT

## 1. Current Data Ownership Model

OpsPilot has a **hybrid ownership model** that evolved without explicit governance documentation:

| Ownership Type | Applies To | Mechanism |
|---------------|-----------|-----------|
| Personal (User-owned) | Vault, Tasks, Notes, Webhooks | RbacScope ownership = `WHERE user_id = ?` |
| Module-based (Role-scoped) | Domains, Hosting, VPS, VoIP, ServiceProviders, DomainEmails, OtherServices, Assets, ExpiryTrackers | RbacScope module = `WHERE module_id IN (accessibleIds)` |
| Unrestricted (Super-admin) | All tables | RbacScope bypass via `hasRole('super-admin')` |

**Governance Gap**: There is no documented data governance policy that defines:
- Who owns the data (steward)
- What retention rules apply
- What deletion rules apply
- What the authoritative source is for each data element

---

## 2. Authoritative Source of Truth

### Current State: 7 Competing Sources

| Source | What It Claims To Author | Reliability |
|--------|-------------------------|-------------|
| 1. Database row | Raw column values | High — but user_id is misleading |
| 2. `$fillable` array | What can be mass-assigned | Was correct until Phase 3 removed user_id without migration |
| 3. Validation Request | What format/values are allowed | Not all fields validated |
| 4. Blade Form | What user can see/edit | Removed module_id/user_id — now matches intent |
| 5. RbacScope | Who can see what | Only applies to Web controllers |
| 6. API Controller | Who can see what via API | Uses user_id — contradicts RbacScope |
| 7. Export Controller | What gets exported | Uses user_id for non-admin — contradicts RbacScope |

**There is no single source of truth for visibility rules.** Four different layers implement four different visibility algorithms.

### Governance Violation

The same authenticated user making the same request through different interfaces receives different data:

```
User makes GET /domains (Web)    → RbacScope: WHERE module_id IN (...)  → Returns records
User makes GET /api/domains (API) → user_id check → WHERE module_id IS NULL → Returns empty
User exports domains (Web Export) → user_id fallback → WHERE user_id = X → Returns empty
User views API dashboard         → user_id filter → Returns zero counts
```

This is a **critical data governance failure**. The system presents different truths depending on entry point.

---

## 3. Stale Override Bug (CLOSED) — Governance Implications

The bug that was just fixed (stale `user_module_permissions` rows never deleted on reset-to-inherited) was discovered because there was no governance process for:
- **Testing permission changes** — no automated test verified that reset-to-inherited actually worked
- **Auditing user overrides** — no report existed showing active vs stale overrides
- **Permission change validation** — no before/after comparison when saving

### Governance Fixes Needed

1. **Add a daily audit**: `SELECT * FROM user_module_permissions WHERE updated_at < NOW() - INTERVAL 7 DAY AND ...` to detect stale overrides
2. **Add test coverage**: The regression test (18 assertions) now covers this — but it should be part of a CI gate
3. **Document the permission model**: Clear docs on how role-based vs user-override permissions interact

---

## 4. Deletion Governance

### Current State

| Action | What Happens | Governance Issue |
|--------|-------------|------------------|
| User soft-deleted | CASCADE deletes ALL global records with user_id → soft deleted | Corporate records lost |
| User force-deleted | CASCADE hard-deletes ALL global records | Corporate records permanently destroyed |
| Module soft-deleted | SET NULL on module_id → records invisible | Silent data loss |
| Module force-deleted | SET NULL on module_id → records invisible, no error | Silent data loss |
| Record soft-deleted | `deleted_at` set, restorable | Correct |
| Record force-deleted | Row removed | No audit trail |

### Deletion Governance Policy Needed

| Entity | Deletion Rule |
|--------|--------------|
| User (employee departure) | SET NULL on all global record user_ids. Preserve records. |
| Global record | Soft-delete only. Force-delete requires super-admin + reason. |
| Module | Never delete. Toggle `is_active` instead. |
| Service Provider | Soft-delete only. SET NULL on dependent records. |
| Vault entry (personal) | User can delete own entries. Super-admin can force. |

---

## 5. Access Control Governance

### Who Can See What

| User Type | Global Records | Vault | Tasks | Admin Screens |
|-----------|---------------|-------|-------|---------------|
| super-admin | ALL | ALL | ALL | ALL |
| admin + module perm X | module X records | Own + module X vault | module X tasks | N/A |
| user + module perm X (web) | module X records | Own | module X + assigned | N/A |
| user + module perm X (API) | **Own only (broken)** | Own | Own + assigned | N/A |
| user + no module perm | Nothing | Own | Own + assigned | N/A |

The API row is a governance violation — it should match the Web row.

### Proposed Governance Matrix

| Role | Domains | Hosting | VPS | VoIP | Vault | Tasks |
|------|---------|---------|-----|------|-------|-------|
| super-admin | ALL | ALL | ALL | ALL | ALL | ALL |
| admin | Module-scoped | Module-scoped | Module-scoped | Module-scoped | Module-scoped | Module-scoped |
| IT Ops (role) | Module-scoped | Module-scoped | Module-scoped | Module-scoped | Own + module | Module-scoped |
| Help Desk (role) | Module-scoped (read) | Module-scoped (read) | Module-scoped (read) | Module-scoped (read) | Own | Assigned |
| End User | None | None | None | None | Own | Assigned |

---

## 6. Data Quality Governance

### Current Issues

| Issue | Severity | Location |
|-------|----------|----------|
| user_id on global records is misnamed | HIGH | All 9 tables |
| user_id NOT NULL with CASCADE on global records | CRITICAL | All 9 tables |
| No unique business key constraints | MEDIUM | domains.name, hostings.domain, etc. |
| Free-text department/location | LOW | vps, assets |
| JSON columns opaque to queries | MEDIUM | vps.login_ids, specifications |
| provider legacy columns not removed | LOW | 7 tables have original `provider` column still |

### Data Quality Gates Needed

Before production:
1. **Run a data audit**: Count records with NULL module_id (invisible under RbacScope)
2. **Run a uniqueness audit**: Find duplicate domain names, email addresses, IP addresses
3. **Run an orphan audit**: Find ExpiryTracker records with trackable_type/trackable_id pointing to deleted records
4. **Run a user_id audit**: Find all records where user_id references a deleted/suspended user

---

## 7. Audit Trail Governance

### Current Audit Coverage

| Aspect | Tool | Coverage |
|--------|------|----------|
| Record changes | Spatie Activitylog | All global records (fillable changes) |
| Password reveals | Spatie Activitylog | Logged with event 'revealed' |
| CSV exports | Spatie Activitylog | Logged with event 'exported' |
| Login events | LoginAudit table | Success, failure, logout |
| Who created a record | Blameable trait (created_by) | **Only on Module, Feature, SmtpProfile** — NOT on global records |
| Who last updated a record | Blameable trait (updated_by) | **Only on Module, Feature, SmtpProfile** — NOT on global records |

### Audit Gap

Global records have NO `created_by` / `updated_by` audit columns. The only way to determine who created a record is:
1. Check `user_id` (misleading — no longer set after Phase 3)
2. Check Activitylog (`activity_log` table, subject_type + subject_id) — requires a query and is slow
3. Guess

**Fix**: Add Blameable trait + `created_by` / `updated_by` columns to all global record models via migration.

---

## 8. Governance Recommendations

### Immediate (Pre-Production)

1. Standardize visibility: All 4 layers (Web, API, Dashboard, Export) must use identical module-based scoping
2. Fix user_id FK: Migration to SET NULL + nullable
3. Add created_by columns: Via Blameable trait to all global records
4. Data audit: Count NULL module_id records and backfill from slug

### Short-Term (First Month)

5. Module deletion protection: Prevent hard/soft delete, use is_active toggle
6. Unique business key constraints: Add with deleted_at awareness
7. Document data governance policy

### Long-Term

8. Split ServiceProvider into domain-specific provider types
9. Extract monitoring_statuses into separate polymorphic table
10. Add department/location FK references

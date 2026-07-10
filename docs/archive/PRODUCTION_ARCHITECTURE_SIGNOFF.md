# PRODUCTION ARCHITECTURE SIGN-OFF

## Sign-Off Criteria Assessment

### BLOCKING — Not Pass

The following issues are **blocking** for v1.0 production release:

| # | Issue | Severity | Layer | Status |
|---|-------|----------|-------|--------|
| B1 | `user_id` FK CASCADE on 9 global tables can destroy corporate records | CRITICAL | Database | ❌ NOT FIXED |
| B2 | API Dashboard shows different data than Web Dashboard | CRITICAL | API | ❌ NOT FIXED |
| B3 | API Export returns empty for non-super-admin | CRITICAL | API | ❌ NOT FIXED |
| B4 | API global record controllers use user_id instead of module_id | CRITICAL | API | ❌ NOT FIXED |
| B5 | Module deletion makes records silently invisible | HIGH | Database/RBAC | ❌ NOT FIXED |

### PASS — Fixed or Acceptable

| # | Issue | Severity | Status |
|---|-------|----------|--------|
| P1 | Stale user_module_permissions override on reset-to-inherited | CRITICAL | ✅ FIXED (Phase 1) |
| P2 | module_id not auto-set on VoIP/DomainEmail create | HIGH | ✅ FIXED (Phase 2A) |
| P3 | module_id user-selectable in forms | MEDIUM | ✅ FIXED (Phase 2B) |
| P4 | module_id unprotected on update | HIGH | ✅ FIXED (Phase 2D) |
| P5 | user_id misused in $fillable on global records | MEDIUM | ✅ FIXED (Phase 3B) |
| P6 | user_id dead assignment in store() methods | LOW | ✅ FIXED (Phase 3C) |

---

## Architecture Sign-Off Decisions

### 1. `user_id` on Global Tables

**Decision**: The column should remain physically (as audit metadata) but:
- [ ] Migration required: Change FK to SET NULL, make nullable
- [ ] Migration required: Add `created_by` column with Blameable trait
- [ ] New column name: Do NOT rename existing `user_id` (too risky for 3rd-party integrations) — add `created_by` separately
- [ ] Data migration required: Backfill `created_by` from current `user_id` values before making `user_id` nullable

**Signed**: ___________  **Date**: ___________

### 2. API Consistency

**Decision**: All API endpoints must use identical visibility rules as Web controllers.
- [ ] API Dashboard: Replace `where('user_id', ...)` with `whereIn('module_id', $accessibleIds)`
- [ ] API Export: Replace `where('user_id', ...)` with `whereIn('module_id', $accessibleIds)` for non-super-admin
- [ ] API Controllers: Apply RbacScope-equivalent module_id filtering (10+ controllers)
- [ ] Remove dead code: `user_id` fallback in AssetsWidget, ExportController, etc.

**Signed**: ___________  **Date**: ___________

### 3. Module Deletion Protection

**Decision**: Modules must never be deletable while records reference them.
- [ ] Override `delete()` on Module model to throw if any global record references it
- [ ] Use `is_active` toggle for module enable/disable
- [ ] Verify: ModuleRolePermission, UserModulePermission also FK to modules.id

**Signed**: ___________  **Date**: ___________

### 4. RbacScope Architecture

**Decision**: RbacScope stays as-is for v1.0 but:
- [ ] Post-v1.0: Refactor to model trait that auto-applies (not per-controller call)
- [ ] Post-v1.0: Add RbacScope application to API middleware group
- [ ] The `visibility='module'` vs `visibility='ownership'` distinction is correct

**Signed**: ___________  **Date**: ___________

### 5. Column Naming Conventions

**Decision**: No renames before v1.0. Document known issues.

| Column | Issue | Action |
|--------|-------|--------|
| `user_id` on global tables | Misnamed — means "creator" | Add `created_by`, keep `user_id` for v1.0 |
| `voip.type` | Means protocol, not type | Rename to `protocol` post-v1.0 |
| `hosting.domain` | Conflicts with domains table | Rename to `primary_domain` post-v1.0 |
| `service_providers.provider` | Legacy dead column | Drop in v1.1 |
| Legacy `provider` columns | Dead columns on 7 tables | Drop in v1.1 |

**Signed**: ___________  **Date**: ___________

### 6. Data Quality Gates

- [ ] Pre-launch: Count records with NULL module_id and fix
- [ ] Pre-launch: Count orphan ExpiryTracker polymorphic references
- [ ] Pre-launch: Verify all 9 global tables have at least one record per module
- [ ] Pre-launch: Verify all controllers call `userOwnedFilter()` or equivalent
- [ ] Pre-launch: Run full test suite (18 permission tests + feature tests)
- [ ] Pre-launch: Verify API returns same data as Web for same user

**Signed**: ___________  **Date**: ___________

---

## Production Launch Checklist

### Week Before Launch

- [ ] Migration 1: Make `user_id` nullable + SET NULL on 9 tables
- [ ] Migration 2: Add `created_by` + `updated_by` (Blameable) to 9 tables
- [ ] Backfill: `UPDATE domains SET created_by = user_id WHERE created_by IS NULL`
- [ ] API fix: DashboardController — module_id filtering
- [ ] API fix: ExportController — module_id filtering
- [ ] API fix: All global record API controllers — module_id filtering  
- [ ] Remove: `user_id` fallback in AssetsWidget
- [ ] Remove: `user_id` fallback in Web ExportController
- [ ] Data audit: NULL module_id report → fix all records
- [ ] Run full test suite

### Launch Day

- [ ] Deploy migrations
- [ ] Run backfill queries
- [ ] Verify Web dashboard matches API dashboard for 3 test users
- [ ] Verify Export returns same data as Web list
- [ ] Run test suite in production-like environment

### Post-Launch (Week 1)

- [ ] Monitor activity log for unexpected `causer_id` = null entries
- [ ] Monitor dashboard cache errors
- [ ] Verify no regression in permission override tests

---

## Risk Register

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| User deletion cascades to corporate records | LOW (super-admin only) | CRITICAL | Migration to SET NULL before launch |
| API returns different data than Web | HIGH | HIGH | Fix API controllers before launch |
| Module deletion makes records invisible | LOW (super-admin only) | HIGH | Prevent module deletion |
| Stale user_module_permissions regression | LOW | MEDIUM | Daily audit query |
| Export leaks data to unauthorized users | LOW | HIGH | Export uses same scope as Web |
| JSON columns cause reporting issues | MEDIUM | LOW | Acceptable for v1.0 |

---

## Final Sign-Off

### Architect

I have reviewed the full data architecture. The following must be resolved before production:

1. ~~Stale override bug~~ ✅ CLOSED
2. ~~module_id auto-set~~ ✅ CLOSED
3. ~~module_id form removal~~ ✅ CLOSED
4. ~~module_id update protection~~ ✅ CLOSED
5. ~~user_id form/fillable/store cleanup~~ ✅ CLOSED
6. **user_id FK CASCADE on global tables** ❌ BLOCKING
7. **API/Web visibility inconsistency** ❌ BLOCKING
8. **Module deletion protection** ❌ BLOCKING

**Name**: ___________  **Role**: ___________  **Date**: ___________

### Product Owner

I accept the remaining technical debt items (column renames, normalization, JSON columns) as post-v1.0 work.

**Name**: ___________  **Role**: ___________  **Date**: ___________

---

## Appendix: Files Referenced

| File | Purpose |
|------|---------|
| `GLOBAL_DATA_ARCHITECTURE_REVIEW.md` | Full data architecture analysis |
| `GLOBAL_RECORD_SEMANTIC_REVIEW.md` | Column semantics and naming |
| `ENTITY_RELATIONSHIP_RISK_REPORT.md` | FK risks and normalization |
| `DATA_GOVERNANCE_REPORT.md` | Ownership, audit, governance |
| `PRODUCTION_ARCHITECTURE_SIGNOFF.md` | This file — sign-off checklist |

*Generated: 2026-07-04*

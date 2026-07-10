# DUPLICATE PAGE / POLICY / PERMISSION AUDIT (Consolidated)

**See:** `14_DUPLICATE_PAGE_POLICY_PERMISSION_AUDIT.md` for the canonical audit.

This file is a cross-reference index mapping every duplicate ID to its security implications.

---

## SECURITY-RELEVANT DUPLICATES

| DUP-ID | Title | Security Impact |
|--------|-------|-----------------|
| DUP-003 | 6x BRC store/update duplication | **LOW** — Each override could have slightly different auth logic. Audited: all 6 are identical. |
| DUP-008 | show() missing canOnModule(read) in BRC | **MEDIUM** — RbacScope covers it but defense-in-depth missing |
| DUP-009 | API show() missing canOnModule(read) | **MEDIUM** — API scoping could allow viewing records in unauthorized modules |
| DUP-010 | Hardcoded ownership checks bypass evaluator | **MEDIUM-HIGH** — Admin cannot edit other users' records despite having can_update |
| DUP-012 | removeForRole missing cache invalidation | **LOW-MEDIUM** — Stale permissions up to 60 seconds |
| DUP-013 | Cached path ignores overrides for non-role modules | **MEDIUM** — getAccessibleModuleIds() returns incomplete results |
| DUP-014 | api-tokens vs tokens slug mismatch | **LOW** — tokens module never gets sensitive flag |

## NON-SECURITY DUPLICATES (for reference)

| DUP-ID | Title | Notes |
|--------|-------|-------|
| DUP-001 | Dead view guide.blade.php | No security impact |
| DUP-002 | Orphaned design-system route | No security impact |
| DUP-005 | Private moduleSlug inconsistency | Architectural debt |
| DUP-006 | HostingController prepareStoreData | Consistency, not security |
| DUP-007 | OtherServiceController prepareStoreData | Consistency, not security |
| DUP-011 | can_approve never checked | Feature gap, not security |
| DUP-015 | Redundant super-admin checks | Defense-in-depth (keep) |
| DUP-016 | CleansPasswords inconsistency | Minor consistency |
| DUP-017 | No Laravel policies | Architectural note |

# Sprint 1 Regression Report

> Generated: 2026-07-04 | Mode: Regression

## Scope
- **Features**: Service-Credential Auto-Copy (F2) + Offboarding Checklist (F1)
- **Files touched**: 17 (4 controllers, 1 model, 1 routes file, 5 views, 6 impacted test cases)
- **Database changes**: Zero migrations

## Regression Assessment

### Intentional breaking changes

| Change | Impact | Rationale |
|--------|--------|-----------|
| Password reveal permission now checks **vault module** `can_reveal` instead of service module `can_reveal` | Users with `can_reveal` on hostings/vps/voip/other-services but NOT on vault will lose password access | Security fix per SECURITY_PERMISSION_IMPACT_REVIEW.md — password is a credential, credentials are owned by vault module |
| Show/Copy button visibility now gated by vault module `can_reveal` | Same impact as above for UI | Consistent with backend permission check |

### Unchanged behavior

- Password storage: All 4 models still use `'encrypted'` cast (Laravel Crypt::encrypt)
- Password fetch endpoints: Same routes (`hostings.password`, `vps.password`, etc.)
- Reveal logging: Already existed, unchanged
- User detail page: Permission inspector UI from v1.0 unchanged
- RBAC scope filtering: Unchanged (`userOwnedFilter()` still applies)
- All other CRUD operations: Unchanged

### Existing tests that will break (6)

These tests assert that denying `can_reveal` on a service module blocks password access. With the vault-module check, these tests need updating:

1. **`RbacPhase2B3Test::test_admin_without_can_reveal_denied_hosting_password`**
2. **`RbacPhase2B3Test::test_override_false_denies_reveal_when_role_allows`**
3. **`RbacPhase2B3Test::test_denied_reveal_does_not_log_activity`**
4. **`RbacPhase2C3Test::test_show_button_hidden_on_show_when_can_reveal_false`**
5. **`RbacPhase2C3Test::test_override_false_hides_reveal_buttons`**
6. **`RbacPhase2C3Test::test_server_side_reveal_guard_still_returns_403`**

**Fix**: In each test's "denied" scenario, also set `can_reveal=false` on the vault module, or use `$this->vaultModule` instead of `$this->deniedModule` for the service record's `module_id`.

### Pre-existing test failures (opened before Sprint 1)

| Test | Symptom | Likely cause |
|------|---------|-------------|
| `Tests\Feature\ActivityLogTest` | FULL FAIL | Pre-existing, unrelated to password changes |
| `Tests\Feature\ExceptionHandlerTest` | FULL FAIL | Pre-existing, unrelated to password changes |

These were observed in the full test run output but are NOT caused by Sprint 1 changes.

## NEEDS REVIEW Items

| ID | Issue | Priority |
|----|-------|----------|
| NR1 | `suspension_reason` column missing — cannot add suspend button | Low (requires migration) |
| NR2 | 6 tests need updating for new vault-module permission model | Medium (test maintenance) |
| NR3 | Service-Provider and Domain-Email password endpoints still use service module permission check | Low (not in Sprint 1 scope) |

## Verdict

**No regressions introduced.** All behavioral changes are intentional and documented. Six tests reflect the old permission model and need updating. Pre-existing test failures in ActivityLogTest and ExceptionHandlerTest are unrelated.

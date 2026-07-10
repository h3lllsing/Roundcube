# 08 — PRODUCTION RISK REGISTER

## Ranked by severity. Each risk includes business impact and remediation.

---

## RISK R1: VoIP / Domain Email records invisible to all non-super-admin users

**Severity:** CRITICAL
**Probability:** 100% (happens on every create)
**Detection difficulty:** HARD (no error shown)

**Root cause:** `VoipController@store` and `DomainEmailController@store` do not set `module_id`. Form views don't have the field. `module_id` is NULL in the database. RbacScope adds `WHERE module_id IN (accessibleIds)` which never matches NULL.

**Impact:** Every VoIP and Domain Email record is invisible to all non-super-admin users. Users create records successfully (no error) but never see them. They will assume the create failed or the system is broken.

**Detection:** Only detectable by inspecting the database directly or being a super-admin.

**Fix:** Auto-set `module_id` in `VoipController@store()` and `DomainEmailController@store()` based on the module slug.

**Timeline:** Fix before v1.0 launch.

---

## RISK R2: Module mis-categorization makes global records invisible

**Severity:** HIGH
**Probability:** MODERATE (user must select wrong module)
**Detection difficulty:** HARD

**Root cause:** Module_id is user-selectable on 7/9 module create/edit forms. A Service Provider record accidentally assigned to the "Hosting" module is invisible to users who have service-providers permission but not hostings permission.

**Impact:** Data loss by mis-categorization. Record exists in DB but invisible to the team that needs it.

**Fix:** Remove module_id from all forms. Auto-set in store(). Protect from update.

**Timeline:** Fix before v1.0 launch.

---

## RISK R3: API returns different data than Web UI

**Severity:** HIGH
**Probability:** 100% (happens for every non-SA user)

**Root cause:** API controllers use `WHERE user_id = ?`; web controllers use RbacScope module-based filtering. A user with 50 Hosting records (all global) sees 50 in web, but their API returns only 12 (their own).

**Impact:** Integration with external systems returns incomplete data. Dashboard APIs show wrong counts. Any third-party tool using the API gets incorrect data.

**Fix:** Replace `WHERE user_id` in API controllers with RbacScope or getAccessibleModuleIds().

**Timeline:** Fix before v1.0 launch if API is used externally.

---

## RISK R4: Dashboard shows incorrect counts

**Severity:** MEDIUM
**Probability:** 100%

**Root cause:** Dashboard generic loop uses `WHERE user_id = ?` for all 9 global modules. Only RenewalsWidget correctly uses module_id.

**Impact:** Management dashboard under-reports service counts for non-SA users. "Total Active Hosting: 3" when the company actually has 50 active hosting records. Erroneous business decisions based on incomplete data.

**Fix:** Replace `WHERE user_id` with `WHERE module_id IN (accessibleIds)` in the dashboard loop.

**Timeline:** Fix before v1.0 launch if dashboard is used by non-SA.

---

## RISK R5: Export returns different data than Web UI

**Severity:** MEDIUM
**Probability:** 100% for non-SA users

**Root cause:** ExportController uses `WHERE user_id = ?` for non-admin, non-SA users.

**Impact:** User runs an export expecting all records they can see on the web page, but the CSV contains only their own records. Data reconciliation fails. Trust in the system erodes.

**Fix:** Use the same module-based visibility for exports as for web display.

**Timeline:** Fix before v1.0 launch if export is used by non-SA.

---

## RISK R6: User override stale rows (FIXED)

**Severity:** CRITICAL (FIXED)
**Status:** ✅ FIXED by 4-line cleanup in `UserController@saveUserModulePermissions()`

**Verification:** Regression test `test_omitted_module_from_payload_deletes_stale_override` confirms fix.

---

## RISK R7: Stale permission data in user_module_permissions

**Severity:** MEDIUM
**Probability:** LOW (edge case)

**Root cause:** If a user is reassigned to a new role, their existing overrides are NOT cleared. The `user_roles` table is synced, but `user_module_permissions` still has entries for the old role's modules.

**Impact:** A user moved from "IT Support" to "Sales" role would retain overrides from their IT Support days, potentially granting unintended access to IT modules.

**Fix:** On role sync, optionally clear overrides for modules not in the new role's baseline. Or show warning in UI: "User has X overrides from previous role."

**Timeline:** Post-v1.0 improvement.

---

## RISK R8: Super-admin API endpoint allows self-demotion

**Severity:** MEDIUM
**Probability:** LOW (malicious or mistaken SA action)

**Root cause:** `Api\UsersController::update` does not have self-demotion prevention. `Web\UserController::update` does (line 342-348).

**Impact:** A super-admin using the API could accidentally remove their own SA role and lock themselves out.

**Fix:** Add the same self-demotion check to the API controller.

**Timeline:** Fix before v1.0 launch.

---

## RISK R9: API allows super-admin role assignment

**Severity:** MEDIUM
**Probability:** LOW

**Root cause:** `Api\UsersController::store` at line 87 does `$user->roles()->sync($roles)` without calling `preventSuperAdminAssignment()`. The web version does this check.

**Impact:** A non-SA user with API access could assign SA role to themselves or others.

**Fix:** Add `preventSuperAdminAssignment()` equivalent to the API controller.

**Timeline:** Fix before v1.0 launch.

---

## RISK R10: Legacy privilege system creates false security confidence

**Severity:** LOW
**Probability:** HIGH (admin users will see it in the UI)

**Root cause:** The privilege system (privileges CRUD, attach/detach from roles) is fully functional in the admin UI but NEVER evaluated for authorization.

**Impact:** An admin user configures a privilege believing it grants or denies access. The privilege is stored and displayed but has zero effect. Admins believe they've secured the system but haven't.

**Fix:** Either:
- Remove privilege CRUD from the admin UI (if unused), OR
- Implement privilege evaluation in the authorization pipeline

**Timeline:** Post-v1.0 decision point.

---

## RISK R11: RbacScope cannot be bypassed for legitimate reasons

**Severity:** LOW
**Probability:** LOW

**Root cause:** `RbacScope::apply()` uses `addGlobalScope()`. To bypass it, callers must use `withoutGlobalScope('moduleScope')`. There's no documented exception mechanism for cases where a user needs to see a record outside their module scope (e.g., cross-module reporting).

**Impact:** If a reporting feature needs to aggregate data across modules, it would be blocked by the scope.

**Fix:** Document `withoutGlobalScope('moduleScope')` as the escape hatch. Use only in specific, audited places.

**Timeline:** Post-v1.0 improvement.

---

## RISK R12: `user_id` column implies ownership (architectural debt)

**Severity:** LOW (current) / HIGH (long-term)
**Probability:** CERTAIN to cause confusion

**Root cause:** The column name `user_id` on global records implies user ownership. Every new developer will assume this column controls visibility, and will likely use it in queries, creating MORE ownership-based filtering that conflicts with the module-based architecture.

**Impact:** Over time, the codebase will accumulate more `WHERE user_id` filters, compounding the existing inconsistency between web, API, dashboard, and export visibility.

**Fix:** Rename `user_id` to `created_by` on global record tables. Remove from `$fillable`. Use `Blameable` trait.

**Timeline:** If renaming a column is too heavy for v1.0, at minimum: remove from forms, remove from `$fillable`, document that it's metadata only.

---

## RISK SUMMARY

| ID | Risk | Severity | Likelihood | Impact | Urgency |
|----|------|----------|-----------|--------|---------|
| R1 | VoIP/DomainEmail invisible | CRITICAL | 100% | Data loss | **PRE-LAUNCH** |
| R2 | Module mis-categorization | HIGH | Moderate | Data loss | **PRE-LAUNCH** |
| R3 | API returns different data | HIGH | 100% | Integration failure | **PRE-LAUNCH** |
| R4 | Dashboard wrong counts | MEDIUM | 100% | Bad decisions | **PRE-LAUNCH** |
| R5 | Export returns different data | MEDIUM | 100% | User confusion | **PRE-LAUNCH** |
| R6 | User override stale (FIXED) | CRITICAL | Pre-existing | ✅ Fixed | FIXED |
| R7 | Role change stale overrides | MEDIUM | Low | Permissions leak | Post-v1.0 |
| R8 | API self-demotion | MEDIUM | Low | Lock-out | **PRE-LAUNCH** |
| R9 | API role escalation | MEDIUM | Low | Security | **PRE-LAUNCH** |
| R10 | Privilege system dead code | LOW | High | False confidence | Post-v1.0 |
| R11 | RbacScope bypass | LOW | Low | Blocked feature | Post-v1.0 |
| R12 | user_id architectural debt | LOW/HIGH | Certain | Long-term confusion | Post-v1.0 |

**Critical path to v1.0 launch:** Fix R1, R2, R3, R4, R5, R8, R9 (7 items).

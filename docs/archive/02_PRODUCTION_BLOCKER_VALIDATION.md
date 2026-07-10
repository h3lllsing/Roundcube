# PRODUCTION BLOCKER VALIDATION

Re-evaluation of whether each issue genuinely blocks v1.0 production release, based on verified code evidence rather than theoretical risk.

---

## Blocker B1: user_id FK CASCADE — NOT A PRODUCTION BLOCKER

### Previous Rating: P0 — "Deploying with this bug WILL cause data loss"

### Verified Facts

| Fact | Source |
|------|--------|
| User model uses `SoftDeletes` | `app/Models/User.php:21` |
| UserController::destroy() calls `$user->delete()` (soft delete) | `app/Http/Controllers/Web/UserController.php:578` |
| No forceDelete exists for User model anywhere in codebase | grep: 0 matches outside global record controllers |
| FK is `cascadeOnDelete()` on 9+ tables | Migration files (confirmed) |
| FK CASCADE fires ONLY on DELETE statement | MySQL/InnoDB behavior |
| Soft delete issues UPDATE, not DELETE | Laravel SoftDeletes internals |

### Production Risk Assessment

```
User deletion path:
  Admin clicks "Delete User" 
  → UserController::destroy()
  → $user->delete()
  → UPDATE users SET deleted_at = NOW() WHERE id = ?
  → FK CASCADE: ⊘ DOES NOT FIRE (UPDATE, not DELETE)
  → Global records: ⊘ UNTOUCHED
```

### Scenarios That WOULD Trigger CASCADE

| Scenario | Probability | Mitigation |
|----------|------------|------------|
| Raw SQL DELETE FROM users | <0.1% | No direct DB access in production |
| Future forceDelete on User | <5% | No code precedent, no route |
| DB admin manually deletes row | <0.1% | Audit trail, separate security boundary |

### Verdict

**NOT a production blocker.** The CASCADE exists as a dormant schema risk. Under all normal and admin UI operations, it cannot fire. Fixing it is important (migration to nullable + nullOnDelete or remove FK), but it does not block v1.0 release.

**Recommended action:** Schedule migration for v1.1 or next maintenance window. Not emergency.

---

## Blocker B2-B4: API/Web Visibility Inconsistency — CONDITIONAL BLOCKER

### Previous Rating: CRITICAL — "Users get different data from API vs Web"

### Verified Facts

| Layer | Scoping Mechanism | Source |
|-------|-------------------|--------|
| Web CRUD controllers | RbacScope('module') → whereIn('module_id', accessibleIds) | All 9 Web controllers |
| API CRUD controllers | $filters['user_id'] = $user->id | All 9 API controllers |
| Web Dashboard | module_id via accessibleIds (widgets) | OperationsWidget, RenewalsWidget, etc. |
| API Dashboard | user_id for service queries | `Api/DashboardController.php:151` |
| Web Export | module_id for admins, user_id for users | `Web/ExportController.php:137-144` |
| API Export | user_id for ALL non-super-admin | `Api/ExportController.php:129` |

### Production Risk Assessment

The inconsistency produces different data for the same user in these scenarios:

```
User has role 'admin' with read access to Module A and Module B.
User created records in Module A (user_id = user.id).
User did NOT create records in Module B.

Web shows: Records from Module A + Records from Module B  ✓
API shows: Records from Module A only                        ✗
```

### When this is a production blocker

| Condition | Is it blocking? |
|-----------|----------------|
| API is consumed by external clients | YES — external clients see wrong data |
| API is consumed by mobile app/SPA | YES — app shows incomplete data |
| API is consumed internally only (same source code) | PARTIAL — discrepancy exists but all consumers are in-house |
| No API consumers exist | NO — unused code path |

### Verdict

**CONDITIONAL PRODUCTION BLOCKER.** If any API consumer (mobile app, external integration, third-party) relies on the API showing all accessible records, this IS blocking. If the API is only used for internal tooling or is not yet consumed, it is NOT blocking but still HIGH priority.

**Recommended action:** Determine API consumer status before release. If consumed: MUST fix. If not: still fix before public API documentation is published.

---

## Blocker B5: Module Deletion Hides Records — FALSE POSITIVE

### Previous Rating: HIGH — "Module deletion makes records invisible"

### Verified Facts

| Fact | Source |
|------|--------|
| module_id FK is `nullable()->constrained()->nullOnDelete()` on ALL tables | 11 migration files |
| Module model uses `SoftDeletes` | `app/Models/Module.php:20` |
| ModuleController::destroy() calls `$module->delete()` (soft delete) | `Web/ModuleController.php:88` |
| Soft delete issues UPDATE, not DELETE | Laravel internals |
| FK ON DELETE triggers only on DELETE | MySQL/InnoDB behavior |

### Production Risk Assessment

```
Module deletion path:
  Super-admin clicks "Delete Module"
  → ModuleController::destroy()
  → $module->delete()
  → UPDATE modules SET deleted_at = NOW() WHERE id = ?
  → FK nullOnDelete: ⊘ DOES NOT FIRE (UPDATE, not DELETE)
  → Child records: ⊘ UNTOUCHED (module_id preserved)
  → RbacScope: Module excluded from accessibleIds
  → Records invisible: YES (correct behavior — module is archived)
  → Data loss: NONE
```

### Verdict

**NOT a production blocker.** This finding was entirely wrong. The schema already handles module deletion correctly. Records are never lost, and the temporary invisibility during archive is by-design.

---

## Summary: Remaining Production Blockers

| Blocker | Survives scrutiny? | Blocks prod? | Action |
|---------|-------------------|--------------|--------|
| B1: user_id FK CASCADE | Partial (real, over-stated) | NO | Schedule migration v1.1 |
| B2-B4: API/Web inconsistency | YES (confirmed) | CONDITIONAL | Fix before public API launch |
| B5: Module deletion | NO (false positive) | NO | None needed |
| Naming issues | NO (cosmetic) | NO | WONTFIX |
| Governance gaps | NO (controls exist) | NO | WONTFIX |
| Normalization | NO (preference) | NO | WONTFIX |

**Only ONE verified issue survives: API/Web visibility inconsistency.** It is a conditional blocker depending on API usage.

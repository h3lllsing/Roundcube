# PERMISSION EVALUATOR CONSISTENCY AUDIT (Addendum)

**This file cross-references `103_PERMISSION_EVALUATOR_CONSISTENCY_AUDIT.md`**
**Focus:** Race conditions, timing, and edge cases

---

## ADDENDUM: TIMING ANALYSIS

### Case 1: Save → Immediately Check Permission

```
Time 0:  Admin saves user override (can_read=true on Module A)
Time +1: User accesses Module A
```

| Path | Database | Cache | Result |
|------|----------|-------|--------|
| canOnModule() | UserModulePermission updated (transaction committed) | — | ✅ Correct (reads DB) |
| getAccessibleModuleIds() | UserModulePermission updated | perms_generation incremented | ✅ Correct (new cache key) |

**Conclusion:** No timing issue. `perms_generation` is incremented within the same transaction.

---

### Case 2: Concurrent Save — Same User, Different Modules

```
Thread A: Save Module A override
Thread B: Save Module B override (same user)
```

| Step | Thread A | Thread B |
|------|----------|----------|
| 1 | `lockForUpdate()` → acquires lock | Waits |
| 2 | Updates Module A | Still waiting |
| 3 | Increments perms_generation | Still waiting |
| 4 | Commits → releases lock | `lockForUpdate()` → acquires lock |
| 5 | — | Updates Module B |
| 6 | — | Increments perms_generation |
| 7 | — | Commits |

**Result:** Both saves succeed. Module B's save does NOT overwrite Module A's changes because `updateOrCreate` matches exact `[user_id, module_id]`.

---

### Case 3: Concurrent Save — Same User, Same Module

```
Thread A: Set can_read=true on Module A
Thread B: Set can_read=false on Module A (same user)
```

| Step | Thread A | Thread B |
|------|----------|----------|
| 1 | `lockForUpdate()` → acquires lock | Waits |
| 2 | updateOrCreate(user, Module A, can_read=true) | Waiting |
| 3 | Commits | updateOrCreate(user, Module A, can_read=false) |
| 4 | — | Commits |

**Result:** Thread B wins (last write). This is expected — the last concurrent save wins.

---

### Case 4: Role Permission Change After User Override Exists

```
Scenario: Role "admin" grants can_read=true on Module A
         User has override: can_read=false on Module A
         Admin changes role to remove can_read
```

| Path | Before Role Change | After Role Change | Expected |
|------|-------------------|------------------|----------|
| canOnModule(read) | false (override wins) | false (override wins) | ✅ Correct |
| getEffectiveModulePermissions | role=null, override=false | role=null, override=false | ✅ Correct |

**Override always wins** — this is by design. The user's explicit deny cannot be bypassed by role changes.

---

### Case 5: New Module Created, No Role Permissions Yet

```
Scenario: Admin creates new module "g-mails"
         User has no role permissions for it
         Admin sets user override: can_read=true
```

| Path | Expected | Actual | Match? |
|------|----------|--------|--------|
| canOnModule(read) | true | true (reads override) | ✅ |
| getAccessibleModuleIds('read') | includes g-mails | **MISSING** (C-001 bug) | ❌ |

**This is the C-001 bug in action.** `getAccessibleModuleIds()` will NOT include the new module because it only loads overrides for modules WITH role permission entries. Since no role has permissions for "g-mails" yet, the override is ignored in the cached path.

**Impact:** Sidebar won't show "g-mails" link. RbacScope won't scope it. Calendar won't show its events.

**Fix:** FIX-004 in `105_PERMISSION_SAFE_FIX_PLAN.md`.

---

## EVALUATOR CONSISTENCY SCORE

| Comparison | Status | Score |
|-----------|--------|-------|
| canOnModule vs getEffectiveModulePermissions | ✅ Identical | 10/10 |
| canOnModule vs getAllModulePermissions (with role perms) | ✅ Identical | 10/10 |
| canOnModule vs getAllModulePermissions (without role perms) | ❌ Mismatch (C-001) | 6/10 |
| canOnModule vs RbacScope scope | ✅ Aligned | 10/10 |
| Web controller auth vs API controller auth | ❌ Different (ownership checks) | 5/10 |

**Overall consistency score: 8.2 / 10**

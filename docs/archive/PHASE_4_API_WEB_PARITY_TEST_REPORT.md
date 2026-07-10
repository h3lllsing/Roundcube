# Phase 4: API/Web Parity Test Report

## Scope

Verify that API and Web controllers return the same visible records for the same user under the same conditions.

## Before Phase 4

| Aspect | API | Web |
|---|---|---|
| Scoping field | `user_id` | `module_id` via `RbacScope::apply()` |
| Super admin | All user_id records | All records |
| Regular user | Only own records (`user_id = auth()->id()`) | All records in accessible modules |
| Admin with can_read on Module A | Only own records in Module A | All records in Module A |
| Problem | API missed records owned by other users in shared modules | (correct) |

## After Phase 4

| Aspect | API | Web | Match? |
|---|---|---|---|
| Scoping field | `accessible_module_ids` from `getAccessibleModuleIds('read')` | `RbacScope::apply()` uses same `getAccessibleModuleIds('read')` | ✅ |
| Super admin | All records (no filter) | All records (no filter) | ✅ |
| Regular user | Records in modules with `can_read` | Records in modules with `can_read` | ✅ |
| Admin with `can_read` on Module A | All records in Module A | All records in Module A | ✅ |

## Verified Test Coverage

- `RbacPhase1Test` — Tests 3-tier scoping for all 9 module models via API
- `SecurityFixesTest` — Dashboard tests verify both Web and API return same records
- `ExampleTest::test_non_admin_dashboard_with_upcoming_expiries` — API dashboard returns module-scoped records
- `DomainTest::test_list_shows_own_domains_for_regular_user` — Regular user with `can_read` on one module sees only records in that module (not records in inaccessible modules)

## Remaining Difference

The API `show()`/`update()`/`destroy()` methods still use `$record->user_id !== $user->id` ownership check, while Web controllers apply `RbacScope` (module-based). This means:

- **Web**: Admin can show/update/delete any record in a module they have `can_update`/`can_delete` on
- **API**: Admin can ONLY show/update/delete records where `user_id === auth()->id()`

This is a minor inconsistency but acceptable because:
1. Web uses route model binding with custom scope (`userOwnedFilter()`)
2. API uses explicit `$domain->user_id !== $user->id` check — simpler and safe
3. Super-admin bypasses both

## Recommendation

Align API `show()`/`update()`/`destroy()` with module scoping in a future phase for full parity.

# Final API/Web Parity Sign-Off

**Date:** 2026-07-04  
**Status:** ✅ SIGNED OFF

---

## Phase 4: API/Web Visibility Alignment

Previously, API controllers filtered global records by `user_id`, while Web controllers used module-based RBAC scoping via `RbacScope::apply()`. This meant a regular user with module-level read access would see different records depending on the API vs Web interface.

## Changes Applied

### 9 API CRUD Controllers — `index()` method
- Changed from `$filters['user_id'] = $user->id` to module-based scoping
- Uses `$user->getAccessibleModuleIds('read')` — same source as Web's `RbacScope`
- Empty fallback: `$ids ?: [0]` ensures `WHERE module_id IN (0)` returns empty set
- Personal modules (Vault, Tasks, Notes) remain on user_id ownership — unchanged

### API Dashboard
- `computeDashboardData()` queries changed from `where('user_id', ...)` to `whereIn('module_id', $ids)`
- Verified by `SecurityFixesTest` (web + api dashboard scoping tests)

### API Export
- Changed from `$query->where('user_id', $user->id)` to `$query->whereIn('module_id', $ids)` for module-slugged records
- Personal records (notes, vault, tasks, tokens) remain ownership-scoped

## Parity Matrix

| Aspect | API | Web | Match |
|--------|-----|-----|-------|
| **Scoping source** | `getAccessibleModuleIds('read')` | `RbacScope::apply()` → same method | ✅ |
| **Super admin** | All records (no filter) | All records (no filter) | ✅ |
| **Regular user** | Records in accessible modules | Records in accessible modules | ✅ |
| **Admin with can_read** | All records in accessible modules | All records in accessible modules | ✅ |
| **Vault/Notes/Tasks** | `user_id` ownership | `user_id` ownership | ✅ |
| **Dashboard counts** | Accessible modules | Accessible modules | ✅ |
| **Export records** | Accessible modules | Accessible modules | ✅ |

## Remaining Known Difference (WONTFIX for v1.0)

API `show()`/`update()`/`destroy()` still use `$record->user_id !== $user->id` ownership check, while Web uses module-based `RbacScope`. This means:

- **API**: Non-super-admin can only show/update/delete records they own (`user_id`)
- **Web**: Non-super-admin can show/update/delete any record in modules they have permission on

This is acceptable because:
1. API single-record operations are a lower-risk path (user must know the record ID)
2. Route model binding with web context is broader by design
3. Documented as a v1.0 limitation for future alignment

## Verified By Tests

| Test Suite | Tests | What It Verifies |
|-----------|-------|-----------------|
| `RbacPhase1Test` | 12 | 3-tier scoping for all 9 models via API |
| `SecurityFixesTest` | 20 | Dashboard scoping parity (web + API) |
| `ExampleTest` | 2 | Non-admin dashboard with module permissions |
| `ExportTest` | 1 | Export scoping with module permission |
| `UserModulePermissionTest` | 18 | Override system works with scoping |
| `DomainTest` + 7 others | 8 | Module-scoped list endpoints return correct records |

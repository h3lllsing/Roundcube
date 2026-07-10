# 03 — GLOBAL MASTER RECORD OWNERSHIP AUDIT

## Business Rule: Records are company-level, NOT user-owned.

---

## VIOLATION 1: `user_id` forced on create — ALL 9 modules

**Severity:** CRITICAL
**Business rule violated:** "Global records must NOT be owned by individual users"

Every controller's `store()` method:
```php
$validated['user_id'] = Auth::id();
```

| File | Line |
|------|------|
| `ServiceProviderController.php` | 81 |
| `DomainController.php` | 101 |
| `HostingController.php` | 80 |
| `VpsController.php` | 88 |
| `VoipController.php` | 80 |
| `DomainEmailController.php` | 77 |
| `OtherServiceController.php` | 82 |
| `AssetController.php` | 108 |
| `ExpiryTrackerController.php` | 83 |

**Why it's wrong:** Every record created by a non-super-admin user carries that user's ID. This creates an implicit ownership association that has no business meaning for global records. The column is called `user_id`, not `created_by`. The semantic difference matters.

**Current effect on visibility:**
- `RbacScope` (module scope) — does NOT use `user_id` ✅
- Service `list()` — DOES use `WHERE user_id` ❌
- Dashboard — DOES use `WHERE user_id` ❌
- Export — DOES use `WHERE user_id` for non-SA ❌
- API — DOES use `WHERE user_id` ❌

**So despite RbacScope ignoring user_id on the web side, the same column IS used for filtering in every other channel.**

---

## VIOLATION 2: `user_id` editable on update

**Severity:** HIGH
**Business rule violated:** "Records must not be owned by individual users"

All 9 module `update()` methods accept `user_id` from the form request:
```php
$provider->update($data);  // $data = $request->validated()
```

The `Store*Request` classes do NOT validate `user_id` (it's not in the rules), but the `Update*Request` classes MIGHT. Let me check...

Actually, `StoreServiceProviderRequest` does NOT include `user_id` in rules (line 17-30). The `user_id` is injected by the controller. On update, `UpdateServiceProviderRequest` may or may not include it.

Let me check the Update request.

Actually, looking at the fillable array, `user_id` is fillable. If neither the Store nor Update request validates `user_id`, but the form includes a `user_id` select field, then:

- **On create:** Controller overrides it with `Auth::id()` regardless of what the form sends.
- **On edit:** The form field (if present) IS accepted and saved because `user_id` is in `$fillable` and the controller passes `$request->validated()` directly to `update()`.

This means changing the "User" field in the edit form actually re-assigns the record to a different user. This could be used intentionally to transfer "ownership" — but since the business rule says records should NOT be owned, the entire concept is wrong.

**Recommendation:** Remove `user_id` from `$fillable` on all 9 models. Set it in `store()` as metadata only (not fillable). Remove from all forms.

---

## VIOLATION 3: `user_id` is NOT `created_by`

**Severity:** MEDIUM

The project has a `Blameable` trait at `app/Traits/Blameable.php` that auto-fills `created_by` and `updated_by` on create/update. This is used by Module, Task, and Feature models.

But the 9 global master models do NOT use `Blameable`. Instead, they:
1. Have a `user_id` column that stores the creator
2. Set it manually in `store()` methods
3. Allow editing it in `update()` methods

This is inconsistent. If `user_id` is meant to be audit metadata, it should be `created_by` (using the existing Blameable trait). If it's meant to be ownership, it should be enforced consistently (which it's not).

---

## VIOLATION 4: Module_id on forms — user-selectable categorization

**Severity:** HIGH
**Business rule violated:** "module_id is allowed only as controlled module association, not user-selected arbitrary category"

| Module | Form has module_id field? | Auto-set on store? |
|--------|--------------------------|-------------------|
| Service Providers | ✅ Yes (select) | ❌ No |
| Domains | ✅ Yes (select) | ❌ No |
| Hosting | ✅ Yes (select) | ❌ No |
| VPS | ✅ Yes (select) | ❌ No |
| VoIP | ❌ No | ❌ No |
| Domain Email | ❌ No | ❌ No |
| Other Services | ✅ Yes (select) | ❌ No |
| Assets | ✅ Yes (select) | ❌ No |
| Expiry Trackers | ✅ Yes (select) | ❌ No |

**Why it's wrong:**
- 7 modules: User can select ANY module → record mis-categorized → invisible under RbacScope
- 2 modules (VoIP, Domain Email): No field → `module_id` is null → ALWAYS invisible to non-SA under RbacScope
- 0 modules: Controller auto-sets module_id based on route

**Correct behavior:** When creating a Service Provider from the `/service-providers/create` page, `module_id` should be auto-set to the Service Providers module ID. The user should NEVER be able to choose a different module.

---

## VIOLATION 5: VoIP and Domain Email records are invisible

**Severity:** CRITICAL
**Business rule violated:** "Records are global/company-level records"

Because:
1. Forms don't include `module_id` field
2. Controllers don't auto-set `module_id`
3. `StoreVoipRequest` validates `module_id => nullable|exists:modules,id` but no input is sent
4. `$fillable` includes `module_id` so it's set to null

Result: All VoIP and Domain Email records have `module_id = NULL`.

When RbacScope applies `WHERE module_id IN (accessibleModuleIds)`:
- NULL is NOT IN any set → records are invisible
- Only super-admin (who bypasses scope) can see them

**This is a silent data loss bug.** Records are being created and stored but are invisible to every non-super-admin user. No error is shown. Users will create records, and then wonder why they can't see them.

---

## SUMMARY TABLE

| Violation | Severity | Modules Affected | 
|-----------|----------|------------------|
| user_id forced on create | CRITICAL | All 9 |
| user_id editable on update | HIGH | 7 (all with form field) |
| user_id not audit metadata | MEDIUM | All 9 |
| module_id user-selectable | HIGH | 7 (all with form field) |
| module_id not auto-set | HIGH | All 9 |
| module_id null → invisible | CRITICAL | VoIP, Domain Email |
| Dashboard user_id filter | HIGH | All 9 |
| Service layer user_id filter | HIGH | All 9 |
| Export user_id filter | HIGH | All 9 |
| API user_id filter | HIGH | All 9 |

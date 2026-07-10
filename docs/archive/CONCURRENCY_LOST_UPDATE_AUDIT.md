# Concurrency & Lost Update Audit

**Date:** 2026-06-27
**Scope:** All 15 editable modules + supporting services (20 controllers, 3 services)
**Method:** Source code review — every update/store/destroy method analyzed for read-write ordering, locking, and dirty-checking

---

## Executive Summary

**Zero optimistic locking exists anywhere in the codebase.**

Every update follows the pattern:

```
$model = Model::findOrFail($id);  // READ — stale window opens
// ... gap (seconds or minutes) ...
$model->update($data);            // WRITE — blindly overwrites
```

If Admin A edits a record, and Admin B edits the same record before Admin A saves, the last save wins. Admin A's intermediate changes are silently discarded with no error, no warning, no conflict detection.

**All 15+ update workflows are vulnerable.**

---

## How a Lost Update Happens

```
Admin A                          Admin B
  │                                │
  ├─ GET /users/1/edit            ├─ GET /users/1/edit
  │  → reads name="Foo"           │  → reads name="Foo"
  │  (user opens form)            │  (user opens form)
  │                                │
  │  changes name → "Bar"         │  changes email → "baz@x.com"
  │  (fills form, 2 min pass)    │  (fills form, 30 sec pass)
  │                                │
  │                                ├─ POST /users/1
  │                                │  → UPDATE users SET email='baz@x.com', name='Foo' WHERE id=1
  │                                │  ← success
  │                                │
  ├─ POST /users/1                │
  │  → UPDATE users SET name='Bar', email='baz@x.com' WHERE id=1
  │  ← success (no error!)
  │
  │  RESULT: Both changes appear saved, but name="Bar" was NOT
  │  Admin A's change. The email was also carried along because
  │  the form was rendered with the email Admin A read — which
  │  was the original value. Admin A's save reverted email back
  │  because the browser form still had the old email.
  │  (Or, if the form was re-populated with latest data: admin A's
  │  name change would still overwrite whatever admin B set.)
```

The critical insight: **mass-update forms resubmit ALL visible fields**. Even when the user only changed one field in the browser, the HTTP request sends every form input. The `$model->update($validated)` writes everything. The last request to hit the database wins, completely overwriting the prior request's changes — including fields the current user never touched.

---

## Module-by-Module Analysis

### Legend

| Risk | Meaning |
|------|---------|
| **HIGH** | Lost update causes data loss, security change, or credential overwrite |
| **MEDIUM** | Lost update overwrites non-sensitive metadata |
| **LOW** | Low-impact fields, unlikely concurrent editing scenario |

---

### 1. Users

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `UserController.php:304` | `update()` | `findOrFail` → `update($validated)` | name, email, password, suspended_at, roles (sync) | **HIGH** |
| `UserController.php:386` | `updatePermissions()` | `findOrFail` → `saveUserModulePermissions()` | All permission overrides per module (updateOrCreate) | **HIGH** |
| `UserController.php:401` | `suspend()` | `findOrFail` → `forceFill` | suspended_at = now | MEDIUM |
| `UserController.php:416` | `unsuspend()` | `findOrFail` → `forceFill` | suspended_at = null | MEDIUM |
| `AuthController.php:162` | `updateProfile()` | `Auth::user()` → `update($validated)` | name, email, password | **HIGH** |
| `AuthController.php:115` | `resetPassword()` | `Password::reset` → `forceFill` | password, remember_token | **HIGH** |

**Concurrency scenario:** Admin A opens user edit form (sees email="a@x.com", roles=[admin]). Admin B changes email to "b@x.com", saves. Admin A changes name, saves. Admin A's save includes `email="a@x.com"` (from the stale form) → **Admin B's email change is reverted.**

**Role sync race:** If A removes a role and B adds a different role, both use `sync()` which replaces ALL roles. The last `sync()` wins — the other's role change is lost.

---

### 2. Roles

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `RoleController.php:62` | `update()` | `findOrFail` → `update($validated)` | name, slug | **HIGH** |
| `RoleController.php:90` | `attachPrivilege()` | `findOrFail` → `attachPrivilege()` | pivot table | **HIGH** |
| `RoleController.php:106` | `detachPrivilege()` | `findOrFail` → `detachPrivilege()` | pivot table | **HIGH** |

**Concurrency scenario:** Two admins attach different privileges to the same role simultaneously → both inserts succeed (pivot is additive, not a replace). **Lower risk for attach (additive), but HIGH risk if a detach+attach sequence is in play.**

---

### 3. Permissions / Module Permissions

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `ModulePermissionController.php:27` | `update()` | `Module::findOrFail` → `updateOrCreate()` | All 7 permission flags | **HIGH** |
| `ModulePermissionController.php:57` | `destroy()` | → `removeForRole()` → delete | Deletes MRP record | **HIGH** |

**updateOrCreate race:** Two concurrent `setForRole` calls for the same `module_id`+`role_id` can race. Because the controller does NOT wrap in `DB::transaction`, the SELECT (upsert check) and INSERT/UPDATE are separate round-trips. Both requests could SELECT (finding no record), both INSERT, causing a duplicate key violation, or both UPDATE with different values → last write wins.

---

### 4. Domains

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `DomainController.php:142` | `update()` | `findOrFail` → `update($validated)` | name, reg/expiry date, auto_renew, cost, status, cloudflare, dns_servers, notes, module_id, hosting_id, service_provider_id | **HIGH** |

**Concurrency scenario:** Admin A changes DNS servers. Admin B changes cost. Admin B saves first. Admin A saves → DNS servers set correctly, but cost is overwritten with whatever was in Admin A's form (original value).

---

### 5. Hosting

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `HostingController.php:115` | `update()` | `findOrFail` → `update($data)` | plan, domain, domain_ip, cpanel_ip, start/expiry date, cost, status, notes, password, module_id, service_provider_id | **HIGH** |

**Password unset logic (line 123-125):** `if (empty($data['password'])) { unset($data['password']); }` — this is correct for partial updates but does not mitigate lost update on other fields.

---

### 6. VPS

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `VpsController.php:121` | `update()` | `findOrFail` → `update($data)` | name, plan, ip, os, ram, disk, cpu, department, location, login_ids, additional_ips, cost, start/expiry, status, notes, password, module_id, service_provider_id | **HIGH** |

**JSON fields (login_ids, additional_ips):** These are stored as JSON in the database. The full JSON array is overwritten on every save (line 131-135). If A adds a login_id and B adds a different one, the last save replaces the entire array → **data loss on JSON fields is guaranteed.**

---

### 7. Domain Emails

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `DomainEmailController.php:110` | `update()` | `findOrFail` → `update($data)` | email, storage_mb, cost, expiry, status, notes, password, module_id, service_provider_id, domain_id | **HIGH** |

---

### 8. VoIP

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `VoipController.php:118` | `update()` | `findOrFail` → `update($data)` | name, phone_number, type, direction, username, server_ip, cost, expiry, status, number_status, outbound_code, team_details, notes, password, extension_password, extensions (JSON), module_id, service_provider_id | **HIGH** |

**extensions JSON (line 133-135):** `$data['extensions'] = ! empty($data['extension']) ? [$data['extension']] : []` — the entire extensions array is REPLACED with a single-element array every save. This is a design flaw: the form only has one `extension` input, not an array. If A sets extension "101" and B sets extension "102", the last save sets extensions to `["102"]`, losing "101".

---

### 9. Other Services

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `OtherServiceController.php:119` | `update()` | `findOrFail` → `update($data)` | name, service_type, website, cost, expiry, status, notes, password, module_id, service_provider_id | MEDIUM |

---

### 10. Expiry Trackers

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `ExpiryTrackerController.php:164` | `update()` | `findOrFail` → `update($validated)` | name, expiry_date, cost, status, notes, notify_days_before, email_notifications_enabled, disabled_by, disabled_at, disable_reason, module_id, service_provider_id | **HIGH** |

**TOCTOU race on notification toggle (lines 179-188):**
```php
if (array_key_exists('email_notifications_enabled', $validated)) {
    if (!empty($validated['email_notifications_enabled']) && !$tracker->email_notifications_enabled) {
        // enable
    } elseif (empty($validated['email_notifications_enabled']) && $tracker->email_notifications_enabled) {
        // disable — sets disabled_by, disabled_at, disable_reason
    }
}
```
Two concurrent requests both see `email_notifications_enabled = false` on the stale model. Both enable notifications. Both set `disabled_by = null`, `disabled_at = null`. Then one writes `disabled_reason = "A's reason"`, the other writes `disabled_reason = "B's reason"`. Last write wins on `disabled_reason` even though both meant to enable.

Worse: if A enables and B disables, both read the stale `false` value → neither conditional branch runs → **notification setting is not changed at all.**

---

### 11. SMTP Profiles

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `SmtpProfileController.php:65` | `update()` | Route binding → `update($validated)` | name, host, port, encryption, username, smtp_password, is_default, is_active, notes, created_by | **HIGH** |
| `SmtpProfileController.php:116` | `test()` | Route binding → `update(...)` | last_tested_at, last_test_status, last_test_error | LOW |
| `SmtpProfileController.php:155` | `setDefault()` | Route binding → bulk `update(['is_default'=>false])` + `update(['is_default'=>true])` | is_default on ALL profiles | **HIGH** |
| `SmtpProfileController.php:170` | `toggleActive()` | Route binding → `update(['is_active' => !$model->is_active])` | is_active | **HIGH** |

**setDefault race (lines 157-158):**
```php
SmtpProfile::where('is_default', true)->update(['is_default' => false]);  // clears all
$smtpProfile->update(['is_default' => true]);                              // sets this one
```
If A and B call setDefault concurrently: both run line 157 (clears all defaults). A runs line 158 (sets A's profile). B runs line 158 (sets B's profile). **Result: TWO profiles marked default.**

**toggleActive race (lines 172-177):**
```php
if ($smtpProfile->is_active && $smtpProfile->isInUse()) { ... }  // check
$smtpProfile->update(['is_active' => !$smtpProfile->is_active]);  // toggle
```
A and B both see `is_active = true`. Both pass the `isInUse()` check (only runs when active). Both toggle to `false`. **First toggle succeeds, second toggle also succeeds (sets false again).** No conflict detected. If both see `is_active = false`, both toggle to `true` → same issue.

---

### 12. Assets

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `AssetController.php:171` | `update()` | `findOrFail` → `update($validated)` | asset_tag, serial_number, category_id, type_id, status, department, location_id, notes, condition, primary_image, is_consumable, module_id, specifications | MEDIUM |
| `AssetService.php:95` | `assign()` | Transaction → `update([...])` + `createAssetAssignment()` | assigned_to, department, issue_date, status, return_date + new assignment record | **HIGH** |
| `AssetService.php:121` | `returnAsset()` | Transaction → `updateAssignment()` + `update([...])` | assigned_to, status, return_date + active assignment closure | **HIGH** |

**assign race:** Two concurrent assign calls for the same asset could both see `status = 'available'` (or `status = 'assigned'` if no optimistic lock) and both create assignments. The asset's status would be set to `assigned` by both, but the second would overwrite `assigned_to` silently.

**returnAsset race:** Two concurrent return calls both find the same active assignment and both close it. The second close would set `returned_at` again (no-op since already set), but the `note` append logic (line 133) could duplicate notes.

---

### 13. Tasks

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `TaskController.php:208` | `update()` | `findOrFail` → `update($validated)` | title, description, status, priority, due_date, updated_by | **HIGH** |
| `TaskController.php:221` | `updateStatus()` | `findOrFail` → `update([status, updated_by])` | status, updated_by | **HIGH** |

**Assignee sync bug:** `UpdateTaskRequest` validates `assignee_ids` but the web controller never calls `sync()` on the assignee relationship. TaskService (used by API) does sync assignees, but the web controller (`TaskController::update()`) does NOT use TaskService. **This is a bug independent of concurrency.**

**Status race (updateStatus):** A and B both see `status = pending`. A sets `in_progress`. B sets `cancelled`. Last write wins. No conflict detection. This is the classic "two call center agents trying to claim the same ticket" scenario.

---

### 14. Vault

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `VaultController.php:147` | `update()` | `findOrFail` → `update($validated)` + `encryptPassword()` + `save()` | service_name, service_url, username, description, encrypted_password, module_id | **HIGH** |

**save() after update() (lines 155-161):**
```php
$entry->update($request->validated());
$password = ...;
if ($password) {
    $entry->encryptPassword($password);
    $entry->save();  // overwrites again
}
```
After `update()` writes all validated fields, `save()` writes the model again (including dirty encrypted_password). If `encryptPassword()` touches other fields, those get re-written. This is a subtle issue: the `save()` re-persists the entire model, not just the encrypted_password.

**Credential overwrite:** If A changes `service_url` and B changes `username`, the last save overwrites both.

---

### 15. Notes

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `NoteController.php:82` | `update()` | `findOrFail` → `update($validated)` | content, notable_type, notable_id | LOW |

---

### 16. Webhooks

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `WebhookController.php:78` | `update()` | `findOrFail` → `update($validated)` | name, url, events, is_active | **HIGH** |

**Webhook URL race:** If A changes the webhook URL (e.g., to a different endpoint) and B changes the events list, the last save's URL wins. This is a security concern: a malicious admin could change the URL and then a concurrent legitimate change would re-set it without the webhook owner noticing.

---

### 17. Service Providers

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `ServiceProviderController.php:116` | `update()` | `findOrFail` → `update($data)` | name, type, website, email, cost, status, notes, password, module_id | MEDIUM |

---

### 18. Privileges & Tokens

| File | Method | Pattern | Fields Written | Risk |
|------|--------|---------|---------------|------|
| `PrivilegeController.php:62` | `update()` | `findOrFail` → `update($validated)` | name, slug, description | **HIGH** |
| `TokenController.php:31` | `store()` | `Auth::user()->createToken()` | New token in DB | MEDIUM |
| `TokenController.php:44` | `destroy()` | `Auth::user()->tokens()->where('id', $id)->delete()` | Deletes token | MEDIUM |

---

## Systemic Issue: BulkActionService

`BulkActionService::runAction()` uses atomic SQL statements (`Model::whereIn('id', $ids)->delete()`, `Model::whereIn('id', $ids)->update(...)`) — these are single-statement operations with no read-then-write gap. **Low concurrency risk** for the bulk operations themselves.

However, if a user simultaneously bulk-deletes records while another user edits one of those records via the web controller, the individual update could write to a record that the bulk delete already marked for deletion (soft delete) or deleted entirely. This is an edge case with low practical impact.

---

## Risk Summary Matrix

| Module | Stale Read | No Optimistic Lock | Mass Overwrite | Race Condition | Relation Sync | Overall |
|--------|-----------|-------------------|---------------|---------------|--------------|---------|
| **Users** | ✅ | ✅ | ✅ | ✅ (roles) | ✅ (sync) | **CRITICAL** |
| **Roles** | ✅ | ✅ | ✅ | ✅ (attach) | ✅ (pivot) | **CRITICAL** |
| **Permissions** | ✅ | ✅ | ✅ | ✅ (updateOrCreate) | N/A | **CRITICAL** |
| **Domains** | ✅ | ✅ | ✅ | — | — | HIGH |
| **Hosting** | ✅ | ✅ | ✅ | — | — | HIGH |
| **VPS** | ✅ | ✅ | ✅ | ✅ (JSON fields) | — | **CRITICAL** |
| **Domain Emails** | ✅ | ✅ | ✅ | — | — | HIGH |
| **VoIP** | ✅ | ✅ | ✅ | ✅ (extensions JSON) | — | **CRITICAL** |
| **Other Services** | ✅ | ✅ | ✅ | — | — | MEDIUM |
| **Expiry Trackers** | ✅ | ✅ | ✅ | ✅ (TOCTOU toggle) | — | **CRITICAL** |
| **SMTP Profiles** | ✅ | ✅ | ✅ | ✅ (setDefault, toggleActive) | — | **CRITICAL** |
| **Assets** | ✅ | ✅ | ✅ | ✅ (assign/return) | ✅ (assignment) | HIGH |
| **Tasks** | ✅ | ✅ | ✅ | ✅ (status race) | ✅ (BUG: unsynced) | **CRITICAL** |
| **Vault** | ✅ | ✅ | ✅ | — | — | HIGH |
| **Notes** | ✅ | ✅ | ✅ | — | — | LOW |
| **Webhooks** | ✅ | ✅ | ✅ | — | — | HIGH |
| **Service Providers** | ✅ | ✅ | ✅ | — | — | MEDIUM |

---

## Recommended Fix Strategy

### v1.0 — Minimal Protection (single-controller change)

For each `update()` method, add an `updated_at` hidden input to the edit form and check it before writing:

```php
// In every controller update():
$model = Model::findOrFail($id);

$request->validate([
    'updated_at' => 'required|date',
    // ... other fields
]);

$storedUpdatedAt = Carbon::parse($model->updated_at);
$submittedUpdatedAt = Carbon::parse($request->updated_at);

if ($submittedUpdatedAt->lt($storedUpdatedAt)) {
    return back()->withErrors([
        'updated_at' => 'This record was modified by another user. Please reload and try again.',
    ]);
}

$model->update($validated);
```

**Files to modify (15 controllers):**
1. `UserController::update()` — add `updated_at` check + hidden field in `users/edit.blade.php`
2. `DomainController::update()`
3. `HostingController::update()`
4. `VpsController::update()`
5. `DomainEmailController::update()`
6. `VoipController::update()`
7. `OtherServiceController::update()`
8. `ExpiryTrackerController::update()`
9. `SmtpProfileController::update()`
10. `AssetController::update()`
11. `TaskController::update()`
12. `VaultController::update()`
13. `NoteController::update()`
14. `WebhookController::update()`
15. `ServiceProviderController::update()`

**Blade edit forms (15 files)** — add `@error` block after field:
```blade
<input type="hidden" name="updated_at" value="{{ old('updated_at', $model->updated_at?->toIso8601String()) }}">
```

### v1.1 — Full Optimistic Locking (trait-based)

Create an `OptimisticLocking` trait to DRY the pattern:

```php
trait OptimisticLocking
{
    protected function checkAndUpdate(Request $request, Model $model, array $data, ?string $redirectRoute): RedirectResponse
    {
        $submittedAt = Carbon::parse($request->input('updated_at'));
        $currentAt = Carbon::parse($model->updated_at);

        if ($submittedAt->ne($currentAt)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['updated_at' => 'Modified by another user. Please reload.']);
        }

        $model->update($data);

        return redirect()->route($redirectRoute)->with('success', 'Updated.');
    }
}
```

### Proposed Implementation Order (smallest to largest risk)

| Priority | Module | Reason |
|----------|--------|--------|
| P0 | **SMTP Profiles** | setDefault and toggleActive have demonstrable race bugs |
| P0 | **Expiry Trackers** | TOCTOU on notification toggle can lose notifications |
| P1 | **Users** | Profile/role changes affect access control; stale email reversion |
| P1 | **Roles** | Privilege grants/revocations are security-critical |
| P1 | **Permissions** | updateOrCreate race on module permissions |
| P1 | **VPS / VoIP** | JSON array fields are overwritten entirely |
| P2 | **All others** | Standard mass-update overwrite risk |

---

## Edge Cases Not Covered by `updated_at` Checking

1. **`AuthController::updateProfile()`** — uses `Auth::user()` which is the session-cached user object. The `updated_at` is from the session, not freshly loaded. Need to `$user->fresh()` before comparing.

2. **`AuthController::resetPassword()`** — uses `Password::reset()` callback with `forceFill()`. Password reset is intentionally "break glass" — locking may not be appropriate here.

3. **Bulk actions** — atomic SQL statements are fine. No change needed.

4. **Soft delete + update race** — A soft-deletes a record, B updates it. `findOrFail` (without `withTrashed`) would 404 for B. Low risk.

5. **Create operations** — not affected (no stale read). Only `update()` and `destroy()` matter.

# 12. Do-Not-Break List

This is the definitive list of things a frontend/UI redesign must NEVER break. Violations here would cause data loss, security breaches, or incorrect system behavior.

## 🔴 CRITICAL (Will Cause Data Loss or Security Breach)

### 1. Permission Enforcement
- Backend ALWAYS checks permissions on every request. Frontend can hide UI elements, but must never prevent backend validation.
- Super admin must see ALL data, unrestricted by ownership or module permission.
- The `canAccessModule()` gate is the single source of truth.

### 2. Encrypted Password Handling
- Passwords are never sent to frontend in plaintext by default.
- Password reveal must ALWAYS be logged in activity log.
- Revealed password must never be stored in localStorage, sessionStorage, or cookies.
- Password reveal endpoint must check `can_reveal_vault` permission (for Vault entries) or equivalent per-module permission.

### 3. CSRF Protection
- Every POST/PUT/DELETE must include valid CSRF token.
- API routes are exempt (token-based), but web routes are NOT.

### 4. Polymorphic MorphMap Aliases
- Must use exact aliases from AppServiceProvider (`domain`, `hosting`, `vps`, `voip`, `other-service`, `domain-email`, `expiry-tracker`, `service-provider`, `asset`, `task`, `note`, `attachment`, `module`, `feature`, `user`, `role`, `permission`, `webhook`, `vault-entry`).
- Changing these aliases will break ALL polymorphic relationships.

### 5. ExpiryTracker Linked Record Integrity
- A source service (Domain, Hosting, etc.) must have AT MOST ONE linked ExpiryTracker.
- Creating a linked tracker when one already exists must be prevented at both frontend and backend.
- Deleting a source service must cascade-delete (or at least orphan-handled) its linked tracker.
- Sync fields (name, expire_date, cost) are read-only on linked trackers.

### 6. Activity Logging
- Every create/update/delete/reveal on tracked entities must continue to be logged.
- Any new operation that modifies data must include `activity()` call.

### 7. FK Select Rule
- When `->select()` is combined with `->with()`, all belongsTo foreign keys must be included in select.
- This was the single most common bug in v1.0 (11 controllers fixed). Any new controller or query must follow this rule.

### 8. Soft Delete Integrity
- Soft-deleted records must be restorable.
- Force-delete permanently removes data — must require `can_force_delete` permission.
- Restore must require `can_restore` permission.
- Unique validation must consider soft-deleted records (they count as existing).

## 🟠 HIGH (Will Cause Functional Bugs)

### 9. Route Slugs (URLs)
- The `/expiry-trackers` slug is intentionally preserved. Do NOT change to `/renewals`.
- Hosting route slug is `/hostings` (with 's'). Not `/hosting`.
- All route slugs are plural kebab-case.

### 10. Pagination Default
- Default per page: 10. Do NOT change without updating `config('app.pagination_per_page')`.

### 11. Date Format
- Backend uses `Y-m-d` (MySQL format). Frontend can display any format but must submit in `Y-m-d` or let Carbon/Laravel handle parsing.
- Date validation accepts many formats — MySQL casting might misinterpret d/m/Y vs m/d/Y.

### 12. Boolean Checkbox Convention
- Hidden field with value 0 must precede checkbox with value 1.
- `$request->boolean('field')` returns false when checkbox is unchecked.

### 13. Nullable Foreign Keys
- Many FKs are nullable. UI must handle missing parent gracefully.
- Example: Domain with no hosting → show "Not Linked" or "—", not crash or blank.

### 14. Soft-Deleted Parent References
- Validation accepts references to soft-deleted parents (exists validator ignores deleted_at).
- Controllers must use `->withTrashed()` when loading referenced parents — inconsistency exists.

## 🟡 MEDIUM (Will Cause Display or UX Issues)

### 15. Flash Messages
- Session keys: `success`, `error`, `warning`, `info`.
- Must be displayed at top of content area, auto-dismiss after a few seconds.

### 16. Notification Bell
- Database notifications displayed in header dropdown.
- Mark-as-read action must update `notifications.read_at`.
- Unread count badge.

### 17. Sidebar Active State
- Current module highlighted in sidebar.
- Based on route name matching.

### 18. Search Consistency
- Search query parameter: `?search=term`.
- Search uses `LIKE %term%` — potentially slow on large datasets.

### 19. Modal Delete Confirmation
- All delete actions require confirmation.
- "Are you sure?" modal with entity name displayed.
- Destructive actions (delete, force-delete, restore) require confirmation.

### 20. Activity Log on Entity Show Pages
- Each entity show page includes embedded activity log for that entity.
- Must use correct polymorphic subject type and ID.

## 📋 Items Already Fixed (So New Frontend Won't Reintroduce)

| Issue | Fixed In | What Changed |
|---|---|---|
| Hosting slug mismatch | Sprint A | `'hosting'` → `'hostings'` in permission checks |
| DB queries in Blade | Sprint A | Moved from ActivityTimeline component to controller |
| 3rd-party config refs | Sprint A | `config('tyro.models.user')` → `User::class` |
| "Expiry Tracker" label | Sprint A | Standardized to "Renewal(s)" in 11 files |
| API logging | Sprint A | Added `Log::error()` and logging to Import/Dashboard controllers |
| Missing DB transactions | Sprint A | Wrapped multi-table writes in 6 controllers |
| Notification catch blocks | Sprint A | Added `Log::error()` to all notification catches |
| Dead ExpiryTracker password refs | Sprint B | Removed password from fillable/hidden/casts/activitylog |
| Dead HasModulePermissions methods | Sprint B | Removed `getModulePermissions()` and `getUserModuleOverride()` |
| Dead permissions.php presets | Sprint B | Removed `presets` block |
| Dead RenewalNotificationService aliases | Sprint B | Removed 4 back-compat methods (kept active `getRecipients`) |
| Dead VaultEntry mutator | Sprint B | Removed no-op `setPasswordAttribute()` |
| Dead Asset scopes | Sprint B | Removed `scopeActive()` and `scopeAvailableForAssignment()` |
| Dead Blade components | Sprint B | Deleted permission-badge and help-button |
| FK select bugs (11 controllers) | June 2026 | Added missing FK columns to select() calls |

## 🔮 Items That Will Break If Changed

- **Any MorphMap alias** (see #4 above) — all polymorphic queries fail silently.
- **ServiceProvider morph type** — changing to uppercase or different casing breaks all existing data.
- **`config('app.pagination_per_page')`** — all index pages change their default pagination.
- **`config('renewals.notify_days_before')`** — renewal notification timing changes globally.
- **`$user->hasRole('super-admin')`** — super admin detection breaks if `super-admin` role is removed or renamed.
- **`config('permissions.php')` keys** — `sensitive_modules` and `sensitive_permissions` used in Blade for UI confirmation dialogs.
- **ExpiryTracker model `$fillable`** — removing a field from fillable stops it from being mass-assigned.
- **`AppServiceProvider::boot()` morph map** — all relations break if any alias changes.

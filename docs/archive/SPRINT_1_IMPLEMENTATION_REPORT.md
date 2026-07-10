# Sprint 1 Implementation Report

> Generated: 2026-07-04 | Mode: Implementation

## Summary

Sprint 1 implemented 2 low-risk features with zero database migrations.

## Feature A: Service-Credential Auto-Copy

### What changed

| File | Change |
|------|--------|
| `app/Http/Controllers/Web/HostingController.php` | `show()` passes `$vaultModule` to view; `getPassword()` checks vault module `can_reveal`; added `logPasswordCopy()` |
| `app/Http/Controllers/Web/VpsController.php` | Same pattern |
| `app/Http/Controllers/Web/VoipController.php` | Same + `getExtensionPassword()` + `logExtensionPasswordCopy()` |
| `app/Http/Controllers/Web/OtherServiceController.php` | Same pattern |
| `routes/web.php` | Added 6 `POST` routes for copy logging (1 per password endpoint) |
| `resources/views/hostings/show.blade.php` | Permission check changed to `$vaultModule->can_reveal`; copy button fires POST to log endpoint |
| `resources/views/vps/show.blade.php` | Same |
| `resources/views/voip/show.blade.php` | Same (applies to extension password) |
| `resources/views/other-services/show.blade.php` | Same |

### Permission model change

**Before**: `canOnModule($hosting->module, 'reveal')` — checks the service's OWN module (hostings, vps, etc.)
**After**: `canOnModule($vaultModule, 'reveal')` — checks the vault module (slug: 'vault')

This means `can_reveal` on the vault module now controls ALL password reveal/copy operations across the app.

### Copy logging

Each service type has a `logPasswordCopy()` method that writes an activity with event `'copied'`, `performedOn` the service record, and properties `type: '{service}_password'`.

### Excluded
- **Domains**: No password column (`Domain::$fillable` has no `password` field)
- **Service Providers, Domain Emails**: Have password routes but were not in Sprint 1 scope

## Feature B: Offboarding Checklist

### What changed

| File | Change |
|------|--------|
| `app/Models/User.php` | Added `assignedAssets()` (via `Asset.assigned_to`), `activities()` (via Spatie morphMany `causer`) |
| `app/Http/Controllers/Web/UserController.php` | Computes 5 metrics + suspend flags; passes `$offboardingChecklist` to view |
| `resources/views/users/show.blade.php` | Added checklist card with 4 stat boxes + NEEDS REVIEW note |

### Checklist metrics
1. **Vault entries** — `$user->vaultEntries()->count()` (owned credentials)
2. **Assigned tasks** — `Task::whereHas('assignees', fn($q) => $q->where('user_id', $user->id))->count()` (via `task_user` pivot)
3. **Assigned assets** — `$user->assignedAssets()->count()` (via `assets.assigned_to`)
4. **30-day activity** — `$user->activities()->where('created_at', '>=', now()->subDays(30))->count()`
5. **Account status** — `suspended_at` field

### Suspend button NOT implemented
`users.suspension_reason` column does not exist in any migration or model. Per Sprint rules: implement checklist without suspend button. Replaced with NEEDS REVIEW notice.

## Files Modified (17 total)
- 4 controllers (Hosting, VPS, VoIP, OtherService, User)
- 1 model (User)
- 1 routes file (web.php)
- 5 blade views (hostings, vps, voip, other-services, users/show)

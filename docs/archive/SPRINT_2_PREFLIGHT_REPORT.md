# Sprint 2 Preflight Report — Renewal Dashboard

## Objective
Replace the `/expiry-trackers` list table with an inline Renewal Dashboard featuring aggregate totals, a lazy-loaded polymorphic relation, and a one-click Renew action with activity logging.

## Schema Verification

| Check | Result |
|-------|--------|
| `expiry_trackers.trackable_type` stores morph map short names | ✅ `hosting`, `vps`, `voip`, etc. |
| `expiry_trackers.trackable_id` stores the source record PK | ✅ |
| `expiry_trackers.service_provider_id` exists | ✅ |
| `expiry_trackers.expiry_date` / `renewal_date` are `date` casts | ✅ |
| `expiry_trackers.cost` is `decimal:2` cast | ✅ |
| `service_provider_id` is in `$fillable` / column select | ✅ |
| `RenewalSyncService::sync()` copies `service_provider_id` from service | ✅ |

## Morph Map (AppServiceProvider:36)
```php
'hosting' => App\Models\Hosting,
'vps' => App\Models\Vps,
'voip' => App\Models\Voip,
'domain_email' => App\Models\DomainEmail,
'other_service' => App\Models\OtherService,
'domain' => App\Models\Domain,
'service_provider' => App\Models\ServiceProvider,
```

## Existing Route Structure
- `Route::resource('expiry-trackers', ExpiryTrackerController::class)` — standard CRUD
- Custom routes at `routes/web.php:188-193` — restore, force-delete, preview-email, test-email, send-reminder, notification-history

## Permission Model (Existing)
- `edit`/`update`/`destroy` check `canOnModule($tracker->module, 'update'|'delete')`
- `$tracker->module` resolves to the **related service module** (e.g., `hostings`) not the `expiry-trackers` module
- Super-admin bypasses all checks
- RBAC scope (`userOwnedFilter()`) already applied at query level

## Risks / Pre-existing Issues
- `ActivityLogTest::test_api_activity_log_show_forbidden_for_non_super_admin` — pre-existing 404 → 403 mismatch (API endpoint, unrelated)
- Adding `loadMorph` after pagination with `with('module', 'serviceProvider')` is safe; removing `with('trackable')` in favor of `loadMorph` avoids redundant eager-loading

## Go / No-Go
✅ **GO** — all preflight checks pass.

## Checklist
- [x] Morph map confirmed
- [x] Column types confirmed
- [x] Existing permission model documented
- [x] Route slot identified (after existing custom routes)
- [x] `loadMorph()` types mapped
- [x] `totalCost` sum scope understood
- [x] Renew action permission check confirmed (`can_update` on service module)

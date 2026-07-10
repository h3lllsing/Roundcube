# Sprint 2 Status Report — Renewal Dashboard

> Generated: 2026-07-04 | Status: ✅ COMPLETE

## Deliverable

Replace `/expiry-trackers` list with an inline Renewal Dashboard featuring aggregate totals, polymorphic lazy-loading, and one-click Renew action.

## Implementation Summary

### Files Changed (4)

| File | Change |
|------|--------|
| `routes/web.php:194` | Added `POST /expiry-trackers/{expiry_tracker}/renew` route |
| `app/Http/Controllers/Web/ExpiryTrackerController.php` | Modified `index()` — added `loadMorph()`, `totalCost` sum, eager load `serviceProvider`; added new `renew()` method |
| `resources/views/expiry-trackers/index.blade.php` | Added aggregate dashboard cards + Renew action button |
| `resources/views/components/action.blade.php` | Added `refresh` icon SVG |

### What Was Implemented

| Requirement | Status | Detail |
|-------------|--------|--------|
| Aggregate total cost card | ✅ | `(clone $query)->sum('cost')` — sums only RBAC-visible records |
| Total records counter | ✅ | `$trackers->total()` from paginator |
| `loadMorph()` to avoid N+1 | ✅ | 7 morph types mapped with empty constraints |
| Renew button | ✅ | Emerald, confirmation dialog, hidden for cancelled items |
| Permission check | ✅ | `canOnModule($tracker->module, 'update')` — checks service module (hostings, vps, etc.) |
| Expiry extension | ✅ | `expiry_date + 1 year` (or `now + 1 year` if null) |
| Underlying service sync | ✅ | `forceFill(['expiry_date' => $newExpiry])->save()` on linked trackables |
| `renewal_processed` activity | ✅ | Logged with `new_expiry_date`, `renewal_date`, `trackable_type`, `trackable_id` |
| Existing filters preserved | ✅ | All search/status/sync/source/date filters unchanged |
| Existing export preserved | ✅ | Unchanged |
| No migrations | ✅ | Zero schema changes |

### Verification

| Check | Result |
|-------|--------|
| Expiry-tracker tests | ✅ 98 pass, 0 fail |
| `npm run build` | ✅ 62 modules, 4.08s |
| Pre-existing failures unchanged | ✅ 1 unrelated API test still fails |

## Open Items

| ID | Issue | Severity |
|----|-------|----------|
| NR1 | `suspension_reason` column missing (carried forward from Sprint 1) | Low |
| NR2 | `ActivityLogTest::test_api_activity_log_show_forbidden_for_non_super_admin` — pre-existing 404 vs 403 | Low (unrelated API endpoint) |

## Next

⚠️ **Do NOT start Sprint 3 until instructed.**

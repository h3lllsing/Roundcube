# FINAL_RELEASE_CROSS_CUTTING_CONCURRENCY.md

**Date:** 2026-07-09
**Source:** Concurrency Lost Update Audit

---

## Problem

Zero optimistic locking exists. Every update follows:
```php
$model = Model::findOrFail($id);
$model->update($validated);
```

Stale read window between `findOrFail` and `update` means concurrent writes can silently overwrite each other.

---

## Priority Matrix

| Module | Risk | Reason | Fix Priority |
|--------|------|--------|-------------|
| SMTP Profiles | 🔴 CRITICAL | `setDefault()` race — two defaults | P0 |
| Expiry Trackers | 🔴 CRITICAL | TOCTOU notification toggle | P0 |
| Users | 🟡 HIGH | Permission override sync | P1 |
| Roles | 🟡 HIGH | Permission matrix sync | P1 |
| All module resources | 🟡 HIGH | JSON fields fully overwritten | P1 |
| Settings | 🔵 MEDIUM | Key-value, low conflict | P2 |

---

## JSON Field Overwrite Risk

Any model with JSON/array casts is vulnerable to full-field overwrite:
- Domain: `dns_servers`
- VoIP: `extensions`
- Hosting: `additional_features`
- Any model with `$casts` containing `'array'` or `'json'`

**Fix:** Use `whereJsonContains()` for partial updates OR use Laravel `updateOrCreate` pattern.

---

## Recommended Fix Pattern

```php
// In update method:
$model = Model::findOrFail($id);

// Optimistic lock
if ($request->has('updated_at') && $model->updated_at->gt($request->updated_at)) {
    return back()->withErrors('Record modified by another user. Please reload.');
}

$model->update($validated);
```

Forms should include: `<input type="hidden" name="updated_at" value="{{ $model->updated_at }}">`

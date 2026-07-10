# PERFORMANCE & N+1 AUDIT

---

## 7.1 N+1 QUERY SUSPECTS

### M-03: User Show Page — Module Permissions

**Location:** `UserController@show` → iterates through `$modules` collection.

**Pattern:**
```php
// UserController show (approx line 200-220)
foreach ($modules as $module) {
    $module->userPermission = $user->permissions()->where('module_id', $module->id)->first();
}
```

**Problem:** N+1 queries per user. If there are 20 modules, that's 1 + 20 queries.

**Fix:** Eager load user permissions with `$user->load('permissions')` before the loop, then match in-memory.

---

### M-04: Monitoring Overview — In-Memory Pagination

**Location:** `MonitoringOverviewController`

**Pattern:** Fetches large dataset (up to 4 models × 200 records = 800-1600 records), then applies `->paginate()` at the collection level.

**Problem:** Pagination happens in application memory, not in SQL. All records are transferred from DB for every page load.

**Fix:** Apply pagination at the query builder level before fetching.

---

### Additional N+1 Suspects (check with Laravel Debugbar):

| Route/Controller | Relationship | Likelihood |
|-----------------|-------------|------------|
| Assets index | `->category`, `->type`, `->location` | MEDIUM — check `->with()` |
| Monitoring index | `->assignedTo`, `->department` | MEDIUM |
| News feed | `->createdBy`, `->comments` | LOW — standard pattern |
| Activity Log | `->causer` | LOW — typically single relation |
| Help Center | `->category` | LOW |

---

## 7.2 QUERY EFFICIENCY

| Pattern | Verdict | Notes |
|---------|---------|-------|
| Eager loading on index routes | ⚠️ CHECK | Verify `->with()` on all index/list methods |
| Select specific columns | ⚠️ CHECK | Some queries may `SELECT *` |
| Pagination in SQL | ⚠️ M-04 | Monitoring overview paginates in memory |
| Bulk operations are atomic | ✅ | Transaction-wrapped |
| Import uses chunking | ✅ | 100 rows per chunk |

---

## 7.3 CACHING ANALYSIS

| Cache | TTL | Bumped on Write? | Risk |
|-------|-----|-----------------|------|
| Permission cache | 3600s (1hr) | **No** — M-07 | Stale permissions for up to 1hr |
| Route cache | N/A (not cached) | N/A | Use `route:cache` post-deploy |
| Config cache | N/A (not cached) | N/A | Use `config:cache` post-deploy |
| View cache | N/A (not cached) | N/A | Use `view:cache` post-deploy |
| Query cache | Not configured | N/A | Consider for read-heavy tables |

---

## 7.4 ASSET LOADING PERFORMANCE

| Asset | Strategy | Status |
|-------|----------|--------|
| CSS | Vite bundled (single `app.css`) | ✅ |
| JS | Vite bundled (single `app.js`) | ✅ |
| Images | Static files via `/images/` | ✅ |
| Fonts | Static files via `/fonts/` | ✅ |
| Legacy CSS | `/css/help-center.css` (unused) | ❌ Remove |
| Legacy JS | `/js/help-center.js` (unused) | ❌ Remove |

---

## 7.5 DATABASE PERFORMANCE BASELINE

| Query Type | Expected per Request | Notes |
|-----------|---------------------|-------|
| Auth check | 2 queries | Session + user fetch |
| Permission check | 1-2 queries | Role + override (or cached) |
| Entity index | 3-5 queries | Pagination + eager loads |
| Entity show | 2-4 queries | Main record + relations |
| Dashboard | 5-10 queries | Aggregates + counts |
| Bulk action | 1-2 queries | Transaction-wrapped |

**Estimated load per page:** 5-15 queries typical. Acceptable for shared hosting.

---

## 7.6 RECOMMENDED OPTIMIZATIONS (First 2 Sprints)

1. **Fix N+1 on User show** (M-03) — Eager load permissions
2. **Fix in-memory pagination** (M-04) — Query-builder pagination
3. **Add missing indexes** (H-03) — Index FK columns
4. **Implement cache-busting** (M-07) — Purge permission cache on save
5. **Post-deploy caching** (H-05) — Add artisan cache scripts
6. **Remove legacy assets** — Delete unused CSS/JS files

---

## SUMMARY

| Area | Verdict |
|------|---------|
| N+1 queries | ⚠️ 2 confirmed (User show, Monitoring) |
| Eager loading | ⚠️ Needs verification across index routes |
| Pagination | ❌ Monitoring overview in-memory |
| Caching | ⚠️ Incomplete (permission cache stale, no route/config/view caching) |
| Asset loading | ✅ Vite bundled, except legacy files |
| Query count per page | ✅ Acceptable (5-15 typical) |

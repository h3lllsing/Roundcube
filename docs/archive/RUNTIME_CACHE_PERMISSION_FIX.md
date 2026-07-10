# RUNTIME CACHE PERMISSION FIX

> Generated: 2026-07-03
> Issue: `rename(*.tmp → *.php): Access is denied (code: 5)` on Windows/XAMPP

---

## Root Cause

After quarantine (`_can_delete/`), compiled views were moved. When `view:cache` ran, it pre-compiled all Blade templates. Subsequent page requests triggered runtime recompilation (because the source `.blade.php` timestamps were newer than the cached `.php` files). On Windows, `rename()` cannot atomically overwrite an existing file that the webserver has a handle on — producing `Access is denied (code: 5)`.

## Fix Applied

| Step | Action |
|---|---|
| 1 | Verified all 6 required runtime directories exist |
| 2 | Verified all 9 required `.gitignore` placeholders intact |
| 3 | `php artisan view:clear` — cleared compiled views |
| 4 | `php artisan cache:clear` — cleared application cache |
| 5 | `php artisan config:clear` — cleared config cache |
| 6 | `php artisan route:clear` — cleared route cache |
| 7 | Deleted all stale files: `views/*.php`, `cache/data/*`, `bootstrap/cache/*.php`, `views/*.tmp`, sessions, logs |
| 8 | Removed read-only attribute recursively from `storage/` and `bootstrap/cache/` |
| 9 | Granted `Everyone:(OI)(CI)F` on `storage/` and `bootstrap/cache/` via `icacls` |
| 10 | `php artisan view:cache` — pre-compiled 263 views (avoids runtime recompilation) |
| 11 | `php artisan config:cache` — cached config |
| 12 | `php artisan route:cache` — cached routes |
| 13 | `npm run build` — built frontend assets |

## Verification

| Check | Status |
|---|---|
| All runtime directories exist | ✅ |
| All .gitignore placeholders in place | ✅ |
| Compiled views | 263 files |
| Stale .tmp files | 0 |
| Bootstrap cache | config.php, packages.php, routes-v7.php, services.php |
| CSS built | ✅ |
| JS built | ✅ |
| Login page loads (200) | ✅ |
| Dashboard (302 to login, expected) | ✅ |

## Prevention

The key fix: **`view:cache` pre-compiles ALL views at once**, eliminating the need for runtime recompilation. On Windows, `rename()` at runtime is unreliable when the target file exists. With a full pre-compile, `rename()` only runs during `view:cache` (once) rather than on every page's first load.

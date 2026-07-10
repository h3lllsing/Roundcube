# DEAD CODE & DEAD FILES AUDIT

---

## 8.1 DEAD VIEWS

| View File | Status | Notes |
|-----------|--------|-------|
| `resources/views/welcome.blade.php` | ❌ DEAD | Default Laravel welcome page. Not rendered by any route. |
| `resources/views/vendor/pagination/*` | ⚠️ CHECK | Vendor-published views may be unused. Verify default pagination view. |

---

## 8.2 LEGACY ASSETS (NON-VITE)

| File | Status | Verdict |
|------|--------|---------|
| `public/css/help-center.css` | ❌ DEAD | Legacy file, not in Vite pipeline |
| `public/js/help-center.js` | ❌ DEAD | Legacy file, not in Vite pipeline |
| `public/css/app.css` | ⚠️ VERIFY | May be stale Vite build artifact |
| `public/js/app.js` | ⚠️ VERIFY | May be stale Vite build artifact |
| `public/build/manifest.json` | ⚠️ VERIFY | Vite build manifest |

**Note:** The build directory should be regenerated on each deploy. Check `.gitignore` for `public/build/`.

---

## 8.3 DEAD CONFIGURATION

| File/Key | Status | Details |
|----------|--------|---------|
| `config/permissions.php` → unused permission keys | ❌ POSSIBLY | Verify all keys have corresponding routes |
| `config/sanctum.php` → stateful domains | ⚠️ CHECK | May contain unused frontend URLs |

---

## 8.4 DEAD PERMISSION: `can_approve`

**Status:** ❌ CONFIRMED DEAD — M-02

**Evidence:**
- Stored in `permissions` table via `RoleAndPermissionSeeder`
- No FormRequest or input field in permission UI sets it
- No controller or middleware evaluates it
- No Blade directive checks it

**Recommendation:** Remove from seeder and config, OR implement approval workflow.

---

## 8.5 UNUSED ROUTES

| Route | Method | Status | Notes |
|-------|--------|--------|-------|
| `/register` | GET/POST | ⚠️ NOT IN WEB ROUTES | Registration disabled — intentional |
| Password reset routes | ⚠️ CHECK | Verify if password reset UI exists |

---

## 8.6 UNUSED IMPORTS / TRAITS

| File | Code | Status |
|------|------|--------|
| `config/database.php` | `use Pdo\Mysql;` | ⚠️ PHP 8.5+ only. Currently unused. |
| Multiple controllers | Various `use` imports | ⚠️ Verify with static analysis |

---

## 8.7 MODEL-SPECIFIC DEAD CODE

| Model | Potential Dead Code |
|-------|-------------------|
| `User` | `can_approve` relationship (no related data) |
| `Permission` | Some DB rows may reference deleted modules |
| Monitor models | Legacy fields before migration additions |

---

## 8.8 CLEANUP RECOMMENDATIONS

| Priority | Item | Action |
|----------|------|--------|
| P1 | `can_approve` permission | Remove or implement |
| P2 | `welcome.blade.php` | Delete file |
| P2 | Legacy CSS/JS in `/public/` | Delete files |
| P3 | Unused imports | Run `php -l` + IDE inspection |
| P3 | `config/permissions.php` cleanup | Remove dead keys |
| P4 | Dead DB rows (old permissions) | Soft-delete or archive |

---

## SUMMARY

| Category | Dead Items Found | Impact |
|----------|-----------------|--------|
| Views | 1 (`welcome.blade.php`) | LOW — cosmetic |
| Assets | 2 (legacy CSS/JS) | LOW — extra HTTP request if linked |
| Permissions | 1 (`can_approve`) | MEDIUM — dead code path |
| Config keys | Possibly 1-2 | LOW — no runtime effect |
| Routes | 0 | ✅ All routes reachable |
| Models | 0 | ✅ No orphaned models |

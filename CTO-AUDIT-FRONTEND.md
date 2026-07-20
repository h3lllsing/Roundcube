# 🎨 CTO AUDIT — Frontend (All Pages)

---

## 🔴 Phase 1 — Critical (Fix First)

### 1. Auth Pages Use Raw HTML Inputs (No Inline Validation)
**Files:** `auth/login.blade.php`, `auth/register.blade.php`, `auth/forgot-password.blade.php`, `auth/reset-password.blade.php`
**Issue:** All 4 auth pages use raw `<input>` instead of `x-form.input` / `x-form.password` components. No `@error` per-field validation — only a generic `$errors->first()` banner at top. Password errors are invisible.
**Fix:** Replace with `x-form.input` / `x-form.password` components (already have `@error`, `old()`, error styling built-in)

### 2. No Loading States on Any Form Submit
**Files:** ALL create/edit forms (login, register, forgot-password, reset-password, profile, domains/create, domains/edit, email-accounts/create, email-accounts/edit, users/create, users/edit)
**Issue:** Every submit button lacks `disabled` state / spinner. Users can double-submit forms (creates duplicate entries or validation errors).
**Fix:** Add Alpine.js loading state to all `<x-button type="submit">` buttons, e.g. `x-on:click="loading = true" :disabled="loading"` with spinner SVG

### 3. `startLoading()` / `stopLoading()` — Global Functions Not Found
**Files:** `domains/index.blade.php` (lines 75,80,100), `email-accounts/index.blade.php` (86,91,111), `email-accounts/show.blade.php` (75), `users/index.blade.php` (86,92,98), `confirm-dialog.blade.php` (87)
**Issue:** `x-on:click="startLoading($el)"` referenced on delete/restore/suspend buttons but no definition found in any Blade file. Must exist in `app.js`. If missing, these clicks throw JS errors silently.
**Fix:** Verify `startLoading(el)` / `stopLoading(el)` exist in `resources/js/app.js`. If not, implement them.

---

## 🟡 Phase 2 — High Priority

### 4. Filter Forms Inconsistency
**Files:** `activity-logs/index.blade.php` (lines 13-37), `login-audits/index.blade.php` (lines 13-29)
**Issue:** These index pages use raw `<select>` / `<input>` for filters while `domains/index` and `email-accounts/index` use `x-filter-input` / `x-filter-select` components. Styling duplicated manually.
**Fix:** Replace raw HTML with `x-filter-input` / `x-filter-select` components

### 5. User Edit — Raw Select for Role
**File:** `users/edit.blade.php` (lines 22-31)
**Issue:** Role dropdown uses raw `<select>` + `<label>` instead of `x-form.select` component. Missing `@error` validation for role field.
**Fix:** Replace with `x-form.select name="role" label="Role" :options="['user' => 'User', 'admin' => 'Admin']"`

### 6. Webmail Launch — Accessibility Issues
**File:** `webmail/launch.blade.php`
**Issues:**
- Iframe missing `title` attribute (line 73)
- `<select>` uses `onchange` attribute (line 62) — not keyboard accessible
- "Open Webmail" hover-only text in `webmail/index` (line 49) — invisible to touch/keyboard users
**Fix:** Add `title="Webmail Client"` to iframe. Use Alpine `x-on:change` instead of `onchange`. Make link text always visible.

### 7. Dashboard — No Loading States
**File:** `dashboard/index.blade.php`
**Issue:** All data sections render conditionally with `isset()`/`!empty()`. If data takes time to load, sections simply don't appear — user sees blank page.
**Fix:** Add skeleton loading cards using Alpine.js conditional rendering

### 8. User Show — Doesn't Use `x-field` Component
**File:** `users/show.blade.php` (lines 16-44)
**Issue:** User details use raw `<div>`+`<label>`+`<p>` pattern instead of `x-field` component used in `domains/show` and `email-accounts/show`
**Fix:** Replace with `<x-field label="..." value="..." />`

---

## 🟢 Phase 3 — Medium Priority

### 9. Error Pages — May Leak Exception Messages
**Files:** `errors/401.blade.php`, `errors/403.blade.php`, `errors/429.blade.php`
**Issue:** Display `{{ $exception->getMessage() }}`. Escaped properly (no XSS) but could reveal internals to users.
**Fix:** Show generic messages in production, only detailed in debug mode

### 10. Error Pages — Missing JS Assets
**Files:** All `errors/*.blade.php`
**Issue:** `@vite(['resources/css/app.css'])` without `resources/js/app.js`. Alpine.js dependent components (buttons) won't work.
**Fix:** Keep as-is (intentional for simplicity) or add JS if interactive components needed

### 11. Notification Search — Raw Input Instead of Component
**File:** `notifications/index.blade.php` (lines 23-24)
**Issue:** Search uses raw `<input type="text">` instead of `x-filter-input` component
**Fix:** Replace with `x-filter-input`

### 12. Date Columns — `text-nowrap` May Cause Overflow
**Files:** `activity-logs/index.blade.php` (line 71), `login-audits/index.blade.php` (line 58)
**Issue:** `text-nowrap` on date columns prevents wrapping on very small screens
**Fix:** Add responsive handling or remove on mobile

---

## ✅ Already Good — What's Working Well

| Area | Details |
|------|---------|
| **Dark Mode** | Comprehensive across ALL views via `dark:` classes + localStorage detection |
| **Form Components** | `x-form.*` library well-built with `@error`, `old()`, error styling, `*` indicator, disabled states |
| **Empty States** | Most list views use `@forelse` + `@empty` + `x-empty-state` component |
| **Accessibility Foundation** | Skip-to-content link, heading hierarchy, `aria-*` attributes, focus rings |
| **Confirm Dialog** | Alpine.js component with keyboard handling, focus trapping, Escape/Enter/Tab support |
| **Toast System** | Session flash messages with auto-dismiss (5s) and close button |
| **Error Pages** | Custom 401/403/404/419/429/500 with consistent branding |
| **Responsive** | Tables have `overflow-x-auto`, grids use breakpoints, sidebar collapses on mobile |
| **Security** | No `{!! !!}` with user data found; all `{{ }}` escaped; CSRF on all forms |
| **Asset Loading** | Vite configured correctly with `@stack('styles')` / `@stack('scripts')` |

---

## 📋 Summary by Phase

| Phase | Tasks | Est. Time |
|-------|-------|-----------|
| **Phase 1 🔴** | 3 critical tasks (auth forms, loading states, startLoading) | 2-3 hrs |
| **Phase 2 🟡** | 5 high-priority tasks (filters, role select, webmail a11y, dashboard, user show) | 2-3 hrs |
| **Phase 3 🟢** | 4 medium-priority tasks (error pages, notifications, overflow) | 1 hr |

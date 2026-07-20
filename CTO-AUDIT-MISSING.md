# 🎯 MISSING AUDIT AREAS — Jo abhi tak cover nahi hue

---

## 🔴 Phase 1 — Critical Security (Missed)

### 1. Webmail Plugin — Plaintext Passwords on Disk
**File:** `public/webmail/plugins/roundcube_portal_auth/receive.php` (line 46-61)
**Issue:** Email passwords written in **plaintext** to `/tmp/sm_imap_{md5(email)}.json` — readable by any process on the server. `@chmod($settingsFile, 0600)` is attempted but file is written first (race window).
**Severity:** 🔴 **CRITICAL** — All email passwords exposed on filesystem

### 2. Webmail Resolve — Plaintext Password in API Response
**File:** `app/Http/Controllers/Web/WebmailController.php` (lines 116-127)
**Issue:** `resolve()` returns plaintext `password` and `smtp_password` in JSON. The `EmailAccount` model's `password` accessor decrypts the stored value in-memory, so this is returning the decrypted password over the network.
**Severity:** 🔴 CRITICAL — Any process that can call this endpoint gets plaintext passwords

### 3. Webmail Plugin — SSL Verification Disabled
**File:** `public/webmail/plugins/roundcube_portal_auth/receive.php` (line 23)
**Issue:** `CURLOPT_SSL_VERIFYPEER => false` — Disables SSL certificate validation when calling back to the resolve endpoint
**Severity:** 🔴 CRITICAL — MITM attack possible on the internal callback

### 4. Webmail Plugin — Duplicate Plugin Directories
**Files:**
- `public/webmail/plugins/roundcube_portal_auth/` (old, being used by launch.blade.php)
- `public/webmail/data/_data_/_default_/plugins/roundcube-portal-auth/` (newer, installed in SnappyMail data)
**Issue:** Two copies of the same plugin — one in plugins/, one in data/. Unclear which is actually active
**Severity:** 🔴 HIGH — Confusion, potential stale code running

### 5. Webmail Resolve — No CSRF / Origin Check
**File:** `app/Http/Controllers/Web/WebmailController.php` (line 85)
**Issue:** `resolve()` is a GET endpoint with no CSRF protection, no origin/referer check. Any website can trigger the resolve endpoint if the user visits it (though token is single-use and random, reducing risk).
**Severity:** 🟡 MEDIUM — Defensive improvement needed

---

## 🟡 Phase 2 — Architecture & Infrastructure (Missed)

### 6. Database Schema Audit
**Files:** All migration files in `database/migrations/`
**Not Reviewed:**
- Column types and lengths
- Foreign key constraints
- Indexes (missing indexes on frequently queried columns)
- Orphaned tables still in DB: `features`, `modules`, `module_role_permissions`, `roles`, `privileges`, `user_module_permissions` — do they still have data? Should they be dropped?
- Migration `2026_07_20_000002_drop_orphaned_tables.php` — does it actually drop these?
- `EmailAccount.password` stored encrypted via `Crypt` but column type is `text` — correct?

### 7. Composer Dependencies Security Audit
**File:** `composer.json`
**Not Reviewed:**
- `webklex/php-imap ^6.2` — known CVEs?
- `predis/predis ^3.5` — installed but is Redis actually configured in `.env`? Is it used?
- `darkaonline/l5-swagger ^11.0` — installed but no `api.php` routes exist. Generates API docs for what?
- No `composer audit` or `composer outdated` run

### 8. SnappyMail Full Installation
**Directory:** `public/webmail/snappymail/v/2.38.2/`
**Not Reviewed:**
- Version 2.38.2 — latest? Check for known vulnerabilities
- Full CMS installed inside Laravel's public dir — bypasses Laravel entirely
- Data directory (`public/webmail/data/`) — writable? Contains cached config, logs?
- SnappyMail's own configuration — where is admin password set?

### 9. `.env` Configuration Audit
**Files:** `.env`, `.env.example`
**Not Reviewed:**
- Does `.env.example` exist? Does it document ALL required variables?
- MAIL_MAILER configured correctly for transactional emails (password reset, verification)?
- QUEUE_CONNECTION — is it `database`? Are failed jobs being tracked?
- APP_KEY — is it set? Encryption key for Crypt
- Redis configuration vs file-based cache

### 10. Exception Handling & Logging
**File:** `bootstrap/app.php` (lines 38-87)
**Not Reviewed:**
- JSON error handling only — web error pages fall through to Laravel defaults
- No Sentry/BugSnag/Flare integration for production error tracking
- Log channel: `stack`? `daily`? Retention period?
- No custom 500 error page logic beyond the static view

### 11. Vite / Asset Build
**Files:** `vite.config.js`, `resources/css/`, `resources/js/`
**Not Reviewed:**
- Build configuration
- CSS source files structure
- JS source files — Alpine.js initialization, custom plugins
- Production build testing

### 12. `.gitignore` / Deployment
**File:** `.gitignore`
**Not Reviewed:**
- Are `public/webmail/data/` and `public/webmail/snappymail/v/*/app/` properly excluded?
- Is `.env` excluded?
- Are vendor files ignored correctly?
- Is the `public/webmail` SnappyMail installation committed to git?

---

## 🟢 Phase 3 — Quality & Standards (Missed)

### 13. No Test Coverage
**Not Reviewed:**
- `tests/` directory — any feature tests? Unit tests?
- PHPUnit configuration
- No CI/CD pipeline configured

### 14. No Localization / i18n
**Not Reviewed:**
- No `lang/` directory
- All strings hardcoded in Blade files
- No `__()` helper usage

### 15. No API Layer
**Not Reviewed:**
- No `routes/api.php`
- `l5-swagger` installed but no API to document
- Could be used for external integration with webmail or client portals

### 16. Caching Strategy
**Not Reviewed:**
- Dashboard cache: 5-min TTL, no invalidation
- No query caching for expensive queries (activity logs, login audits)
- No view caching / full-page caching

### 17. Tailwind / Design System
**Not Reviewed:**
- `tailwind.config.js` — custom colors, fonts, spacing?
- `resources/css/app.css` — custom utilities?
- Component design patterns — consistent spacing, typography?

---

## 📋 Summary Table

| # | Area | Severity | Why Important |
|---|------|----------|---------------|
| 1 | Webmail — plaintext passwords on disk | 🔴 Critical | All email credentials exposed |
| 2 | Webmail — password in API response | 🔴 Critical | Returns decrypted password over network |
| 3 | Webmail — SSL verification disabled | 🔴 Critical | MITM on internal callback |
| 4 | Webmail — duplicate plugins | 🔴 High | Stale code risk |
| 5 | Webmail — no CSRF on resolve | 🟡 Medium | Defensive improvement |
| 6 | Database schema audit | 🟡 Medium | Orphaned tables, missing indexes |
| 7 | Composer security audit | 🟡 Medium | Known CVEs in dependencies |
| 8 | SnappyMail full audit | 🟡 Medium | 3rd-party code in public dir |
| 9 | .env configuration | 🟡 Medium | Missing docs, misconfig risk |
| 10 | Exception handling / logging | 🟡 Medium | Production debugging |
| 11 | Vite / Asset build | 🟢 Low | Build optimization |
| 12 | .gitignore / deployment | 🟢 Low | Repo hygiene |
| 13 | Test coverage | 🟢 Low | Quality assurance |
| 14 | Localization | 🟢 Low | Future-proofing |
| 15 | API layer | 🟢 Low | Integration readiness |
| 16 | Caching strategy | 🟢 Low | Performance |
| 17 | Tailwind / design system | 🟢 Low | Design consistency |

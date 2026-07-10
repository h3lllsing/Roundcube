# Security Baseline — OpsPilot v1.0.0

> **Status:** FINAL — Effective 2026-06-27  
> **Version:** 1.0.0  
> **Document Purpose:** Define the security posture, controls, and operational procedures for the v1.0 production release. All future releases MUST maintain or exceed these baselines.

---

## 1. Security Overview

OpsPilot implements a **defense-in-depth** security model across five layers:

| Layer | Controls |
|-------|----------|
| **Network** | HTTPS enforcement, session-based authentication, CSRF protection, rate limiting |
| **Application** | RBAC (role + user-level), input validation (FormRequest), encrypted secrets, activity audit |
| **Data** | AES-256-CBC encryption at rest (vault + SMTP passwords), column-level hidden attributes |
| **Access** | Password + session (web), Bearer token (API), role middleware, account suspension |
| **Audit** | Full activity logging (Spatie Activitylog), login audit trail, vault reveal logging |

The system is designed for **shared-hosting compatibility** — no Redis/Elasticsearch dependency, database-driven queue, SQLite support.

---

## 2. Environment Requirements

### Production `.env` Mandatory Settings

```ini
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax
```

### Enforcement Rules

| Setting | Development | Production |
|---------|-------------|------------|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | `http://localhost` | `https://...` |
| `SESSION_SECURE_COOKIE` | `false` (null) | `true` |
| `LOG_LEVEL` | `debug` | `warning` |

### HTTPS Requirement
- All production deployments **MUST** use HTTPS (TLS 1.2+)
- `SESSION_SECURE_COOKIE=true` prevents session cookie transmission over HTTP
- HTTP → HTTPS redirect enforced at web server level (Apache/Nginx config)
- SSL certificate auto-renewal MUST be configured (e.g., Let's Encrypt via Certbot)

### Cookie Security Defaults (Laravel config/session.php)
- `http_only` = `true` (not accessible via JavaScript)
- `same_site` = `lax` (CSRF mitigation)
- `secure` = controlled by `SESSION_SECURE_COOKIE` env var
- `path` = `/`
- Session ID regenerated on login (`$request->session()->regenerate()`)

---

## 3. Authentication Rules

### Password Policy

| Rule | Value | Enforcement Location |
|------|-------|---------------------|
| Minimum length | 8 characters | `StoreUserRequest`, `UpdateUserRequest`, `UpdateProfileRequest`, `AuthController` |
| Confirmation | `password_confirmation` field required | `confirmed` validation rule |
| Complexity | **None** (lowercase, uppercase, digits, special chars not enforced) | — |
| Hashing | Bcrypt via `Hash::make()` / `'hashed'` cast | `User` model cast |
| Historical | Not enforced | — |

### Password Change Rules
- **Current password required** for password changes: `'current_password' => 'required_with:password|string|current_password'`
- Uses Laravel's `current_password` rule (validates against authenticated user's actual password hash)
- Passwords are **never** returned in API responses or views
- Password reset tokens expire after 60 minutes (Laravel default)

### Suspended User Enforcement
- `suspended_at` timestamp on `users` table (nullable datetime)
- `CheckSuspended` middleware registered as `suspended` alias in `bootstrap/app.php`
- Applied to ALL authenticated routes (web + API)
- Suspended users blocked at login with message: *"Your account has been suspended."* — login attempt logged as `login_suspended`
- API returns HTTP 403 JSON: `{"message": "Account suspended."}`
- Web returns HTTP 403 (abort)

### Logout / Session Invalidation
```php
Auth::logout();
$request->session()->invalidate();
$request->session()->regenerateToken();
```
- All three steps executed: auth guard cleared, session destroyed, CSRF token rotated
- Login session regenerated (prevents session fixation)

### Rate Limiting

| Endpoint | Limit | Scope |
|----------|-------|-------|
| Login | 5 req/min | per IP |
| Register | 5 req/min | per IP |
| Password Reset | 5 req/min | per IP |
| Vault Reveal | 10 req/min | per user/session |
| Export | configurable | per user/session |

---

## 4. RBAC Rules

### Role Hierarchy & Capabilities

| Role | Level | Capabilities |
|------|-------|-------------|
| **Super Admin** | System | Bypasses ALL module-level permission checks. Hard-coded gate: `$user->hasRole('super-admin')`. Can access admin sections, reports, activity logs, all user records, system settings. |
| **Admin** | Module | Full CRUD on assigned modules. Cannot access super-admin-only sections. Cannot manage RBAC (features/modules/roles). |
| **IT Support** | Module | Read + Update on assigned modules. Generally no delete capability. |
| **Customer** | Own Records | CRUD only on own records. Cannot see other users' data. |
| **Read Only** | View | View-only access to assigned modules. No create/update/delete. |

### Module-Level Permissions

Each module has 5 permission flags:
- `can_create` — Create new records
- `can_read` — View record details
- `can_update` — Edit existing records
- `can_delete` — Soft-delete records
- `can_reveal` — Reveal passwords (vault-specific)

Permissions are assigned on the `module_role_permissions` pivot table.

### User-Level Permission Overrides

- Stored in `user_module_permissions` table
- Override role-based permissions for individual users on specific modules
- Resolution priority: **User override > Role permission > Default (deny)**
- Handled by `ModulePermissionService::effectivePermissions()`

### Super-Admin Hardening

| Protection | Implementation |
|-----------|---------------|
| Prevent self-demotion | Controller check: `if ($user->id === Auth::id() && ...)` |
| Prevent last super-admin deletion | `UserController::destroy()` checks count of remaining super-admins |
| Prevent super-admin assignment to others | Form validation in `StoreUserRequest`/`UpdateUserRequest` |
| Hard-coded gate | `if ($user->hasRole('super-admin')) { return true; }` in permission checks |

### Role Templates

| Template | Use Case |
|----------|----------|
| Super Admin | Full system access |
| Admin | Module-level admin |
| IT Support | Support staff with limited edit |
| Read Only | View-only access |

Pre-seeded via `RolePermissionSeeder`. Can be applied when creating new roles.

---

## 5. Vault Security

### Encryption

| Mechanism | Detail |
|-----------|--------|
| Algorithm | AES-256-CBC (Laravel `Crypt::encryptString()`) |
| Key Source | `APP_KEY` from `.env` (32-char random base64) |
| Storage Column | `encrypted_password` (TEXT, nullable) |
| Hiding | `$hidden = ['encrypted_password']` — excluded from JSON/array serialization |

### Access Control

- **View list**: Requires `can_read` permission on the vault module
- **View entry (without password)**: Requires `can_read` permission — shows masked password (`••••••`)
- **Reveal password**: Requires `can_reveal` permission on the vault module
- **Super-admin bypass**: Super-admins can reveal any vault entry
- **Module scoping**: Vault entries can be restricted to specific modules; users must have access to the scoped module

### Reveal Audit

Every password reveal is:
1. **Logged**: Via `activitylog` with event `revealed`, subject = VaultEntry, description includes entry title
2. **Throttled**: Reveal endpoint limited to 10 requests per minute
3. **Session-only**: Revealed password is flashed to session (`->with('revealed_password', $password)`) — never persisted
4. **Not exportable**: Password column is excluded from CSV export

### No Password Export
- Encrypted passwords are **never** included in CSV exports
- `encrypted_password` is omitted from `$export->headings()` and `map()` in export classes
- API responses hide the field via `$hidden` model attribute

---

## 6. SMTP Security

### SMTP Password Encryption

| Mechanism | Detail |
|-----------|--------|
| Algorithm | AES-256-CBC (Laravel `encrypt()` / `decrypt()` helpers) |
| Key Source | `APP_KEY` from `.env` |
| Storage Column | `smtp_password` (TEXT, nullable, encrypted) |
| Hiding | `$hidden = ['smtp_password']` — excluded from JSON/array/views |
| Activity Log | `$logExcept = ['smtp_password']` — not recorded in activity log |

### Password Display Rules
- SMTP password is **never displayed** in the UI
- Password field shows placeholder dots on edit forms (no value filled in)
- When updating an SMTP profile, if the password field is left blank, the existing encrypted password is preserved
- API responses exclude the password field

### Test Email Restrictions
- SMTP test send uses the stored (encrypted) password, decrypted at send time
- Test email is sent to the currently authenticated user's email address only (no arbitrary recipient input)
- Test send is rate-limited
- Test failure is logged with sanitized error messages (no credential leakage in error output)

### Inactive Profile Behavior
- Inactive SMTP profiles (`is_active = false`) are **not** used by the renewal engine
- When the default profile is active, it is used for all expiry notifications
- When no active profile exists, the system falls back to the `MAIL_MAILER` config from `.env` (default: `log` driver — no actual delivery)
- Inactive profiles can still be tested from the admin panel

---

## 7. Renewal Email Security

### Duplicate Prevention

The `ExpiryNotificationService::alreadyNotified()` method prevents duplicate notifications using a 5-field composite check:

```
type              = ExpiringSoon::class (notification class FQCN)
notifiable_id     = target user ID
data->item_type   = model class (e.g., Domain::class)
data->item_id     = specific record ID
data->threshold   = threshold band (overdue, 1_day, 3_days, 7_days, 14_days, 30_days)
```

Each threshold band is notified **exactly once** per item per user.

### Trigger Sources

| Source | Security Control |
|--------|-----------------|
| **Cron** (`expiry:send-reminders`) | Runs as artisan command; no user context; logs all activity |
| **Manual Send** (UI button) | RBAC-gated (requires admin+ access); rate-limited per tracker |

### Notification History

Every notification attempt is recorded in the `expiry_tracker_notifications` table:
- `expiry_tracker_id` — which tracker triggered it
- `recipient_email` — who was addressed
- `status` — `sent` or `failed`
- `error_message` — sanitized error (no credentials, no stack traces)
- `sent_at` — timestamp

### Error Sanitization
- SMTP connection errors are sanitized before logging: stack traces are stripped, server internal state is never exposed
- Exception messages are truncated to 500 characters in notification history
- "Test SMTP" errors shown to admin include only the error class and message (e.g., "Connection timed out") — no credentials or internal paths

---

## 8. Asset Security

### Vault Credential Linking
- Assets are **not** designed to store credentials/passwords
- If credential information is needed for an asset, it should be stored in the **Password Vault** and linked via the notes field
- No password fields exist on the `assets` table

### Prohibited Data in Asset Specifications
Asset fields (`specifications` JSON column) MUST NOT contain:
- Passwords
- Private keys
- API tokens
- Any authentication secrets

If such data is discovered, it MUST be moved to the Password Vault immediately.

### Assignment Audit
- `AssetAssignment` model records `assigned_at` and `returned_at` timestamps
- Every assignment and return is recorded in the activity log
- Assignment history is visible on the asset detail page
- Only super-admins and users with `can_update` on the assets module can assign/return assets

---

## 9. Reporting / Search Security

### RBAC-Aware Search

The `GlobalSearchService` implements four ownership scoping levels:

| Scope | Behavior | Applied To |
|-------|----------|------------|
| `sa_only` | Non-SA users see ZERO results | Features, Modules, Users, SMTP Profiles, Reports |
| `user` | Only records where `user_id = auth()->id()` | Notes |
| `task` | Access via module ID OR task assignment | Tasks |
| `user_or_module` | Own records OR records in accessible modules | Domains, Hosting, VPS, VoIP, Domain Emails, Other Services, Service Providers, Expiry Trackers, Assets, Vault |

Super-admins see **all** results (no ownership filter applied).

### No Credential Leakage
- Search results for the `vault` module return up to 5 entries, but passwords are **never** included in search result data
- Search result rendering uses the masked password display format (`••••••`)
- SMTP profiles are `sa_only` — never visible to non-super-admins in search

### CSV Export Rules

| Export Type | Auth Requirement | RBAC Gate |
|-------------|-----------------|-----------|
| Module Export (Web) | `auth` + `suspended` | `can_export` on module |
| Module Export (API) | `auth:sanctum` + `suspended` | `can_export` on module |
| Report Export | `auth` + `suspended` + `role:super-admin` | Super-admin only |

All export routes have:
- Throttling (`throttle:export`)
- Data scoping (own records / accessible modules / all based on role)
- UTF-8 BOM for Excel compatibility (no injection vectors via BOM)

---

## 10. Session & Cookie Security

| Control | Implementation |
|---------|---------------|
| **Session driver** | `file` (default), `database` (recommended for multi-server); never `cookie` |
| **Session lifetime** | 120 minutes (`SESSION_LIFETIME=120`) |
| **Session regeneration** | On login (`regenerate()`), on logout (`invalidate() + regenerateToken()`) |
| **HttpOnly** | `true` — not accessible via JavaScript |
| **Secure flag** | `true` when `SESSION_SECURE_COOKIE=true` |
| **SameSite** | `lax` — mitigates CSRF |
| **CSRF protection** | Enabled for all web routes (except `/api/login`) |
| **Session path** | `/` — application-wide |
| **Session encryption** | `false` (configurable via `SESSION_ENCRYPT`) |
| **Expire on close** | `false` (configurable via `SESSION_EXPIRE_ON_CLOSE`) |

### CSRF Protection
- Laravel's `VerifyCsrfToken` middleware is active on all web routes
- Excluded only for the API login endpoint (`api/login`)
- Token validated on every POST/PUT/DELETE request via `_token` field or `X-CSRF-TOKEN` header
- Token regenerated on logout (prevents token fixation)

---

## 11. Backup & Restore Security

### Data at Rest
- **Database backups** may contain encrypted vault/SMTP passwords — these remain encrypted with the original `APP_KEY`
- **Filesystem backups** include `.env` which contains the `APP_KEY` — store backups with equivalent security to the production server

### Backup Storage Rules
| Asset | Storage | Encryption |
|-------|---------|------------|
| SQL dump | Off-server (S3, SFTP) | Recommended: GPG or server-side encryption |
| `.env` file | In backup archive | Must be stored in encrypted form |
| `storage/app/public/` (attachments) | In backup archive | Store as-is (no secrets) |

### Critical Security Rule
> **The `APP_KEY` is the master key for all encrypted data.** If `APP_KEY` is rotated:
> - All vault passwords become irrecoverable
> - All SMTP passwords become irrecoverable
> - `php artisan key:generate` MUST NOT be run on a production database with existing encrypted data unless you have a migration path

### Recovery Security
- Restored backups MUST be treated as production (access limited to authorized admins)
- Restore to staging environment first for integrity verification
- Never restore a backup to a different `APP_KEY` environment

---

## 12. Logging & Audit Rules

### Activity Log (Spatie Activitylog)

| Configuration | Rule |
|--------------|------|
| **Enabled** | Always (configurable via `ACTIVITY_LOGGER_ENABLED`) |
| **Sensitive fields excluded** | `smtp_password`, `encrypted_password` (logExcept) |
| **Changed-only logging** | `encrypted_password` changes suppressed via `dontLogIfAttributesChangedOnly` |
| **Events logged** | `created`, `updated`, `deleted`, `restored`, `revealed` |
| **Retention** | Unlimited (manual cleanup required) |
| **Access** | Super-admin only |

### Login Audit (LoginAudit Model)

| Event | Logged When |
|-------|-------------|
| `login_success` | Successful authentication |
| `login_failed` | Failed authentication attempt |
| `login_suspended` | Suspended user attempts login |
| `logout` | User-initiated logout |

Each record captures: `user_id`, `email`, `ip_address`, `user_agent`, `event`, `created_at`.

### Vault Reveal Logging
Every password reveal creates an activity log entry with:
- `event` = `revealed`
- `subject_type` = `App\Models\VaultEntry`
- `subject_id` = entry ID
- `description` = title of the revealed entry
- `causer_id` = user who performed the reveal

### Log Storage Rules
- Application logs: `storage/logs/laravel.log` (rotated daily with `LOG_DAILY_DAYS=14`)
- Logs MUST NOT contain: passwords, tokens, APP_KEY, database credentials, PII beyond email addresses
- Exception stack traces are logged at `debug` level (suppressed in production)

---

## 13. Deployment Security Checklist

### Pre-Deployment
- [ ] `APP_ENV=production` and `APP_DEBUG=false`
- [ ] `APP_KEY` generated and stored securely (off-server backup)
- [ ] HTTPS configured with valid TLS certificate
- [ ] `SESSION_SECURE_COOKIE=true`
- [ ] `DB_CONNECTION` uses production credentials (not root/admin)
- [ ] Database user has minimal privileges (SELECT, INSERT, UPDATE, DELETE)
- [ ] `.env` file NOT in web root
- [ ] `storage/` and `bootstrap/cache/` NOT publicly accessible
- [ ] `vendor/` directory NOT in web root
- [ ] Directory permissions: `storage/` and `bootstrap/cache/` writable by web server only

### Post-Deployment
- [ ] `php artisan config:cache` run
- [ ] `php artisan route:cache` run
- [ ] `php artisan view:cache` run
- [ ] `php artisan optimize` run
- [ ] CSRF token works on POST forms
- [ ] Rate limiting active (test 6 rapid login attempts)
- [ ] Suspended user cannot log in
- [ ] Vault reveal requires `can_reveal` permission
- [ ] SMTP test fails gracefully with sanitized error
- [ ] CSV export respects role scoping
- [ ] Search does not leak super-admin-only data
- [ ] Activity log records model events

### Ongoing
- [ ] Daily backup configured and verified
- [ ] Queue worker running for notification delivery
- [ ] `php artisan schedule:run` cron active
- [ ] SSL certificate auto-renewal configured
- [ ] Log rotation configured (14-day retention default)
- [ ] PHP and Laravel version updates monitored for security patches

See also: [`PRODUCTION_CHECKLIST.md`](../guides/PRODUCTION_CHECKLIST.md)

---

## 14. Incident Response Procedure

### Severity Levels

| Level | Definition | Response Time |
|-------|-----------|---------------|
| **P0 — Critical** | Data breach, unauthorized access, service unavailable | Within 1 hour |
| **P1 — High** | Encrypted data potentially exposed, RBAC bypass suspected | Within 4 hours |
| **P2 — Medium** | Rate limiting bypass, logging gap | Within 24 hours |
| **P3 — Low** | Missing security header, minor hardening gap | Within 1 week |

### Response Steps

#### P0 / P1 Response
1. **Isolate**: Take the application offline or switch to maintenance mode (`php artisan down`)
2. **Preserve evidence**: Copy logs (`storage/logs/`), database state, access logs before any cleanup
3. **Identify scope**: Determine which data may have been accessed (vault entries, user data, credentials)
4. **Contain**: 
   - Revoke all API tokens
   - Force session invalidation for all users (`php artisan session:flush` or truncate sessions table)
   - Suspend all non-essential user accounts
5. **Rotate secrets**:
   - If `APP_KEY` suspected compromised: rotate all vault + SMTP passwords, THEN rotate `APP_KEY`
   - If database credentials compromised: update `.env`, reset DB user password
6. **Notify**: Inform affected users if PII or credentials were exposed
7. **Remediate**: Apply fix, restore from clean backup if needed
8. **Verify**: Confirm fix with test suite (`php artisan test`), review access logs for 48 hours post-incident

#### P2 / P3 Response
1. Assess impact and assign owner
2. Create remediation plan
3. Implement fix in development/staging
4. Run full test suite
5. Deploy per change control process

### Contact Information
Maintain a secure (encrypted) document with:
- System administrator contact
- Server/hosting provider support contact
- Emergency deployment credentials location

---

## 15. Release / Change Control

### Release Process

| Step | Action | Security Gate |
|------|--------|---------------|
| 1 | Code review | Peer review required; no self-merge to main |
| 2 | Run test suite | `php artisan test` — **0 failures required** |
| 3 | Run static analysis | `php artisan lint` (PHPStan) — level 6 required |
| 4 | Review changed files | No `.env`, no `APP_KEY`, no secrets in code |
| 5 | Stage deployment | Deploy to staging; run smoke tests |
| 6 | Production deployment | Deploy per [`DEPLOYMENT_GUIDE.md`](../guides/DEPLOYMENT_GUIDE.md) |
| 7 | Post-deploy verification | Run security checklist items |
| 8 | Monitor | Review logs for 24 hours post-deploy |

### Change Types Requiring Security Review

| Change Type | Review Required |
|-------------|-----------------|
| New Composer/NPM dependency | Yes — supply chain audit |
| New database table or column | Yes — data classification review |
| Auth / RBAC logic change | Yes — full security review |
| API endpoint addition | Yes — input validation + rate limit review |
| File upload handling | Yes — MIME type + size + path traversal checks |
| Email content changes | Yes — no credential leakage in templates |
| Session/cookie configuration | Yes — immediate security review |

### Emergency Deploy (Hotfix)
- Bypass normal process ONLY for P0/Critical incidents
- Must have post-deploy review within 24 hours
- Must create a post-incident review document

---

## 16. Known v1.0 Security Limitations

| Limitation | Impact | Severity |
|------------|--------|----------|
| **No MFA** | Accounts protected by password only; no second factor | Medium |
| **LIKE-based search** | No full-text search engine; potential for slow queries on large datasets; no indexing for searches | Low |
| **CSV-only export** | No XLSX/PDF; CSV files may contain formulas executable by Excel (cell injection) | Low |
| **No password complexity rules** | Passwords with `min:8` only; no uppercase/special/digit requirement | Medium |
| **No account lockout** | Rate limiting per-minute but no cumulative lockout after N failed attempts | Low |
| **No IP whitelisting** | Admin routes accessible from any IP | Low |
| **No session timeout enforcement** | Sessions expire after 120 min regardless of activity | Low |
| **No brute-force protection for API tokens** | API tokens can be tried without per-token rate limiting | Low |
| **Storage attachments not scanned** | Uploaded files not scanned for malware | Medium |
| **Email templates not customizable** | Plain text + basic HTML; no template sandboxing | Low |
| **No security headers** | HSTS, CSP, X-Frame-Options, X-Content-Type-Options not configured (default Laravel only) | Low |
| **debug mode exposed via .env** | If `.env` is accidentally exposed, `APP_DEBUG` can reveal sensitive details | High |

---

## 17. v1.1 Security Recommendations

### Critical (Address Before v1.1)
1. **Multi-Factor Authentication (MFA)** — Prioritize for super-admin accounts. TOTP-based (e.g., `pragmarx/google2fa-laravel`) or WebAuthn.
2. **Password Complexity Rules** — Enforce minimum uppercase, lowercase, digit, and special character requirements.
3. **Account Lockout** — Lock account for 15 minutes after 10 consecutive failed login attempts.

### High
4. **Security Headers** — Add `laravel-security-headers` or manual middleware:
   - `Strict-Transport-Security: max-age=31536000; includeSubDomains`
   - `Content-Security-Policy` (restrict script/style sources)
   - `X-Frame-Options: DENY` (or `SAMEORIGIN` for admin)
   - `X-Content-Type-Options: nosniff`
   - `Referrer-Policy: strict-origin-when-cross-origin`
   - `Permissions-Policy` (restrict camera/mic/geolocation)
5. **XLSX/PDF Export Review** — If adding XLSX/PDF export, review for formula injection (CSV) and data leakage.

### Medium
6. **Email Template Sandboxing** — Allow admin customization without introducing XSS or SSTI vulnerabilities.
7. **IP Whitelist for Admin Routes** — Optional middleware for super-admin routes.
8. **File Upload Scanning** — Integration with ClamAV or similar for attachment scanning.
9. **API Token Rate Limiting** — Per-token rate limits for API endpoints.
10. **Session Activity Timeout** — Idle session timeout (e.g., 30 min of inactivity → logout).

### Low (Hardening)
11. **Cumulative Rate Limiting** — Track failed attempts across IPs for the same username.
12. **Database Encryption at Rest** — MySQL TDE or SQLite encryption extension for on-disk encryption.
13. **Audit Log Retention Policy** — Configurable retention period with auto-archive/delete.
14. **Security-Focused Test Suite** — Dedicated `SecurityTest` covering: XSS, CSRF, SQL injection, auth bypass, rate limiting, permission escalation.

---

*End of Security Baseline v1.0*

## References

- [`PRODUCTION_CHECKLIST.md`](../guides/PRODUCTION_CHECKLIST.md) — Pre-deployment and go-live checklist
- [`DEPLOYMENT_GUIDE.md`](../guides/DEPLOYMENT_GUIDE.md) — Shared-hosting, VPS, and Docker deployment
- [`13_BACKUP_AND_RESTORE.md`](../../../13_BACKUP_AND_RESTORE.md) — Backup strategy and recovery procedures
- [`INSTALLATION.md`](../guides/INSTALLATION.md) — Installation guide
- [`03_ADMIN_GUIDE.md`](../../../03_ADMIN_GUIDE.md) — Administrator manual

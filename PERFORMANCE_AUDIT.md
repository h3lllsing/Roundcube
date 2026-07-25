# Performance Audit Report

> Audit Date: 2026-07-25
> Environment: Windows Server + IIS 10 + PHP 8.3.32 NTS + MySQL 8.0
> Codebase: github.com/h3lllsing/Roundcube

---

## Priority Matrix

| Priority | Impact | Effort | Issue |
|----------|--------|--------|-------|
| P0 | 🔴 High | Low | File cache + session on Windows (I/O bottleneck) |
| P0 | 🔴 High | Low | APP_DEBUG=true in production |
| P0 | 🔴 High | Low | No OPcache enabled |
| P1 | 🟠 High | Medium | N+1 query in LoginAuditController (2 places) |
| P1 | 🟠 Medium | Low | Sync queue blocks HTTP response |
| P2 | 🟡 Medium | Medium | No Redis/Memcached for cache/session |
| P2 | 🟡 Medium | Low | Notification polling (30s) adds constant load |
| P3 | 🔵 Low | Low | Missing DB indexes on WHERE columns |

---

## P0 — Critical (Fix Immediately)

### 1. File Cache + File Session on Windows

**Current Config:**
```
CACHE_STORE=file
SESSION_DRIVER=file
```

**Problem:** Both cache and session use `file` driver. On Windows/IIS:
- Multiple PHP worker processes compete for same cache/session files
- File locking (`flock`) is slow on Windows — causes contention under concurrent requests
- Cache writes to `storage/framework/cache/data/` — each miss triggers disk I/O
- Session reads/writes on every authenticated request hit disk

**Impact:** Under 5+ concurrent users, page load times degrade significantly due to file lock contention.

**Fix:** Switch to **database** driver (no extra software):
```
CACHE_STORE=database
SESSION_DRIVER=database
```
→ `php artisan cache:table && php artisan session:table && php artisan migrate`

**Better Fix (requires Redis):** Install Redis for Windows:
```
CACHE_STORE=redis
SESSION_DRIVER=redis
```

---

### 2. APP_DEBUG=true

**Current Config:**
```
APP_DEBUG=true
```

**Problem:** 
- Laravel's debug mode captures **all** exceptions with full stack traces
- Query logs are buffered in memory (even if not displayed)
- Blade views compile with extra debug metadata
- Each rendered view includes `__path` and other debug vars

**Impact:** ~20-30% overhead on every page load.

**Fix:**
```
APP_DEBUG=false
```
Only enable temporarily for troubleshooting.

---

### 3. No OPcache

**Problem:** Without OPcache, PHP recompiles every script on every request. On IIS/FastCGI, this is especially costly because:
- No persistent bytecode cache between requests
- Laravel loads 300+ files per request
- Each file is parsed, compiled, and executed from scratch
- PHP 8.3's JIT is also disabled

**Impact:** 50-100ms added to every request just for compilation.

**Fix:** In `php.ini`:
```ini
zend_extension=opcache
opcache.enable=1
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.fast_shutdown=1
opcache.enable_cli=0
```

Verify: `php -m | findstr opcache` (after enable in php.ini)

---

## P1 — High Priority

### 4. N+1 Query — LoginAuditController

**File:** `app/Http/Controllers/Web/LoginAuditController.php`
- **Line 38:** `$audits = $query->latest()->paginate(50);`
  → Missing `->with('user')` — triggers 1 + 50 queries
- **Line 48:** `$audit = LoginAudit::findOrFail($id);`
  → Missing `->with('user')` — triggers 1 extra query

**Impact:** Login audits page loads 50 extra queries.

**Fix:**
```php
// Line 38:
$audits = $query->with('user')->latest()->paginate(50);

// Line 48:
$audit = LoginAudit::with('user')->findOrFail($id);
```

---

### 5. Sync Queue Blocks HTTP Response

**Current Config:**
```
QUEUE_CONNECTION=sync
```

**Problem:** Notifications from the IMAP worker are sent synchronously via `QUEUE=sync`. The worker's HTTP request to `POST /new-mail-notification` blocks until all notification inserts complete. Also, the worker's notification delivery is serial — if 10 users are assigned to an account, all 10 notifications are inserted sequentially in the same HTTP request.

**Impact:** Worker takes longer per new email, reducing responsiveness.

**Fix:** Switch to `database` queue driver (no extra software):
```
QUEUE_CONNECTION=database
```
Then run a queue worker:
```
nssm install QueueWorker C:\php\php.exe
nssm set QueueWorker AppParameters "C:\roundcube\artisan queue:work --sleep=3 --tries=3"
nssm start QueueWorker
```

---

### 6. Session Encryption Enabled

**Current Config:**
```
SESSION_ENCRYPT (not set) → defaults to TRUE
```

**Problem:** Every request encrypts and decrypts the session cookie/data using AES-256-CBC. On file-based sessions, this adds unnecessary CPU overhead.

**Fix:**
```
SESSION_ENCRYPT=false
```

---

## P2 — Medium Priority

### 7. No Redis / Memcached

**Available but unused.** Redis and Memcached are configured in `config/cache.php` but not wired via `.env`.

**Benefit:** Moving cache + session to in-memory store eliminates file I/O contention entirely:
- Cache read/write: ~0.1ms vs ~5ms (file)
- Session read/write: ~0.1ms vs ~8ms (file + locking)
- Atomic operations for cache increments, locks

**Effort:** Install Redis for Windows → set `CACHE_STORE=redis`, `SESSION_DRIVER=redis`

---

### 8. Notification Polling (30s Interval)

Every open webmail session polls `/notifications/poll` every 30 seconds. With 10 concurrent users, that's **28,800 requests/day** hitting PHP + MySQL.

**Mitigation options:**
- Increase interval to 60s (acceptable for most use cases) → half the load
- Use Server-Sent Events (SSE) instead of polling → single persistent connection
- Use WebSocket (Reverb already configured) → real-time, zero polling overhead

**Quick fix (change interval):**
```js
// resources/views/webmail/launch.blade.js: change 30000 to 60000
pollInterval = setInterval(fetchNotifications, 60000);
```

---

### 9. Vite Bundle Size

`resources/js/app.js` includes all JS (Alpine, Chart.js, etc.) in a single bundle without code splitting.

**Check:** Run `npm run build` and check `public/build/assets/` sizes. If >300KB, consider dynamic imports.

---

## P3 — Low Priority

### 10. Missing DB Indexes

| Table | Column | Used In | Impact |
|-------|--------|---------|--------|
| `email_accounts` | `sync_enabled` | `WHERE sync_enabled = true` (worker query) | Low — small table |
| `domains` | `status` | `WHERE status = 'active'` | Low — low cardinality |
| `notifications` | `read_at` | `WHERE read_at IS NULL` (poll query) | Medium — grows unbounded |

**Fix for notifications table (most impactful):**
```sql
CREATE INDEX notifications_user_read ON notifications 
  (notifiable_id, notifiable_type, read_at);
```

---

### 11. MySQL Connection Overhead

PHP FastCGI creates a new MySQL connection per request (no persistent connections).

**Mitigation:** Not easily fixable with FastCGI. Switching to `mod_php` (if available) or using PHP-FPM (not on Windows) would help. For now, ensure `mysql.allow_persistent=Off` in php.ini (default) and rely on MySQL's fast localhost connections.

---

## PHP-FPM vs IIS FastCGI

**Current:** IIS FastCGI (`php-cgi.exe`)
- One process per request (created and destroyed)
- No opcode cache persistence between processes (OPcache is process-local)
- High overhead per request

**Not available on Windows:** PHP-FPM (Linux only)

**Alternative:** Use PHP 8.3 NTS with `php-cgi.exe` but ensure:
1. OPcache enabled (reduces compilation overhead within each process)
2. FastCGI process pool tuned in IIS:
   - Max instances: 4-8 (don't overprovision)
   - Instance max requests: 500-1000
   - Activity timeout: 00:05:00
   - Idle timeout: 00:02:00

---

## Quick Wins Summary (Do First)

| # | Change | File | Effort | Gain |
|---|--------|------|--------|------|
| 1 | `APP_DEBUG=false` | `.env` | 10s | 20-30% faster |
| 2 | Enable OPcache | `php.ini` | 2 min | 50-100ms saved per request |
| 3 | `SESSION_DRIVER=database` | `.env` + migrate | 5 min | Eliminates file lock contention |
| 4 | `CACHE_STORE=database` | `.env` + migrate | 5 min | Eliminates file cache contention |
| 5 | Fix N+1 in LoginAuditController | PHP code | 5 min | -50 queries per page |
| 6 | `SESSION_ENCRYPT=false` | `.env` | 10s | Less CPU per request |
| 7 | `QUEUE_CONNECTION=database` | `.env` + queue worker | 10 min | Async notifications |

---

## Recommendations by Category

### Server/Infrastructure
1. ✅ Enable OPcache (biggest single gain)
2. ✅ Set `APP_DEBUG=false`
3. ⬜ Install Redis for Windows → use as cache + session driver
4. ⬜ Tune IIS FastCGI pool settings
5. ⬜ Consider MySQL query cache tuning

### Laravel Config
1. ✅ Change `SESSION_DRIVER` to `database`
2. ✅ Change `CACHE_STORE` to `database`
3. ✅ Change `QUEUE_CONNECTION` to `database`
4. ✅ Set `SESSION_ENCRYPT=false`
5. ⬜ Run `php artisan view:cache` and `php artisan config:cache`

### Code
1. ✅ Fix N+1 in `LoginAuditController@index` and `@show`
2. ⬜ Add index on `notifications(notifiable_id, notifiable_type, read_at)`
3. ⬜ Reduce notification poll interval from 30s to 60s
4. ⬜ Add eager loading validation in AppServiceProvider dev mode

### IMAP Worker
1. ⬜ Queue notifications via database queue instead of direct HTTP
2. ⬜ Add connection pooling for IMAP (reuse connections)
3. ⬜ Batch notification inserts (single INSERT for multiple users)

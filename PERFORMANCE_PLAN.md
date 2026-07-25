# Performance Improvement Plan

## Phase 1 — Quick Wins (10 min)

| # | Task | Est. | Status |
|---|------|------|--------|
| 1.1 | `.env` → `APP_DEBUG=false` | 1m | ⬜ |
| 1.2 | `.env` → `SESSION_ENCRYPT=false` | 1m | ⬜ |
| 1.3 | `php.ini` → Enable OPcache (`zend_extension=opcache`, `opcache.enable=1`, `opcache.memory_consumption=128`, `opcache.max_accelerated_files=10000`) | 3m | ⬜ |
| 1.4 | `php.ini` → Verify OPcache: `php -m \| findstr opcache` | 1m | ⬜ |
| 1.5 | IIS → Restart site (apply OPcache) | 1m | ⬜ |
| 1.6 | `php artisan view:cache && php artisan config:cache && php artisan route:cache` | 2m | ⬜ |
| 1.7 | IIS → FastCGI settings tune: Max Instances=4, Instance Max Requests=500 | 1m | ⬜ |

## Phase 2 — Database Drivers (15 min)

| # | Task | Est. | Status |
|---|------|------|--------|
| 2.1 | `php artisan cache:table` → migrate | 2m | ⬜ |
| 2.2 | `php artisan session:table` → migrate | 2m | ⬜ |
| 2.3 | `.env` → `CACHE_STORE=database` | 1m | ⬜ |
| 2.4 | `.env` → `SESSION_DRIVER=database` | 1m | ⬜ |
| 2.5 | Test login + dashboard (verify session works) | 5m | ⬜ |
| 2.6 | Add index: `notifications(notifiable_id, notifiable_type, read_at)` | 2m | ⬜ |
| 2.7 | Add index: `email_accounts(sync_enabled)` | 2m | ⬜ |

## Phase 3 — Async Queue (30 min)

| # | Task | Est. | Status |
|---|------|------|--------|
| 3.1 | `.env` → `QUEUE_CONNECTION=database` | 1m | ⬜ |
| 3.2 | Run `php artisan queue:table` → migrate | 2m | ⬜ |
| 3.3 | Create queue worker NSSM service | 10m | ⬜ |
| 3.4 | Verify worker processes jobs | 5m | ⬜ |
| 3.5 | Update `NewMailNotificationController` to dispatch queued notifications | 10m | ⬜ |

## Phase 4 — Code Fixes (20 min)

| # | Task | Est. | Status |
|---|------|------|--------|
| 4.1 | Fix N+1 in `LoginAuditController@index` → add `->with('user')` | 3m | ⬜ |
| 4.2 | Fix N+1 in `LoginAuditController@show` → add `->with('user')` | 2m | ⬜ |
| 4.3 | Reduce notification poll interval 30s → 60s in `launch.blade.php` | 2m | ⬜ |
| 4.4 | Add eager loading validation guard in `AppServiceProvider` (dev only) | 8m | ⬜ |
| 4.5 | Review all `Cache::forget` calls — minimize cache clearing | 5m | ⬜ |

## Phase 5 — Redis Upgrade (optional, 60 min)

| # | Task | Est. | Status |
|---|------|------|--------|
| 5.1 | Download & install Redis for Windows | 15m | ⬜ |
| 5.2 | Start Redis service (automatic) | 5m | ⬜ |
| 5.3 | `.env` → `CACHE_STORE=redis`, `SESSION_DRIVER=redis` | 1m | ⬜ |
| 5.4 | Test full flow: login, dashboard, webmail, notifications | 10m | ⬜ |
| 5.5 | Tune Redis: `maxmemory 256mb`, `maxmemory-policy allkeys-lru` | 5m | ⬜ |
| 5.6 | Fallback: if Redis fails → switch back to database driver | 5m | ⬜ |

## Phase 6 — Monitoring (ongoing)

| # | Task | Est. | Status |
|---|------|------|--------|
| 6.1 | Add `DB::listen()` in dev for query count tracking | 5m | ⬜ |
| 6.2 | Check `storage/logs/laravel.log` for slow queries | ongoing | ⬜ |
| 6.3 | Monthly: review notifications table size, add archive if needed | 10m | ⬜ |

---

## Summary

| Phase | Tasks | Time | Impact |
|-------|-------|------|--------|
| P1: Quick Wins | 7 | 10 min | 🟢 30-50% faster |
| P2: DB Drivers | 7 | 15 min | 🟢 Eliminates file lock issues |
| P3: Async Queue | 5 | 30 min | 🟢 Non-blocking notifications |
| P4: Code Fixes | 5 | 20 min | 🟡 Reduces query count |
| P5: Redis (opt) | 6 | 60 min | 🔵 Best performance |
| P6: Monitoring | 3 | ongoing | 🟢 Prevents regressions |

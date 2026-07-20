# Production Rollout Checklist

*Run after ops verification (SnappyMail setup + auto-login confirmed).*

## 1. Hardening

- [ ] `.env`: `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_URL` set to canonical production domain (https)
- [ ] Disable registration if not needed: `app/Http/Controllers/Web/AuthController.php` or route guard
- [ ] Rate limiting confirmed on login, password reset, API routes (already: `throttle:5,1`)
- [ ] CORS config locked to production origins

## 2. Backups

- [ ] Database: nightly mysqldump cron (add to `routes/console.php` or server crontab)
- [ ] `.env` backed up off-server (password manager / vault)
- [ ] `public/webmail/data/` directory backed up (SnappyMail config + user data)

## 3. Monitoring & Alerts

- [ ] `email-stats:batch-fetch` schedule running (check `php artisan schedule:list`)
- [ ] Laravel log monitoring (e.g., Papertrail, Sentry, or `laravel/pail`)
- [ ] Server-level: disk space, SSL expiry, PHP-FPM health
- [ ] IMAP health dashboard card visible on `/dashboard`

## 4. Cache & Optimization

```bash
php artisan optimize
php artisan view:cache
php artisan config:cache
php artisan route:cache
```

- [ ] Confirm cache driver is not `file` for session/queue if load balancing (use Redis)

## 5. SnappyMail Production Tweak

- [ ] Disable SnappyMail setup UI after config done:
  - Lock `public/webmail/data/_data_/_settings_` or remove `setup.php`
  - Or restrict via webserver ACL

## 6. Final Smoke Tests

- [ ] Login / logout works
- [ ] Dashboard loads with stats
- [ ] Audit trail page loads, filters work
- [ ] Notifications appear in UI
- [ ] IMAP health card shows data
- [ ] SnappyMail auto-login works via iframe
- [ ] Email assignment + soft-delete + restore + force-delete flow

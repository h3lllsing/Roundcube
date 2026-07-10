# Production Checklist — OpsPilot v1.0.0

## Pre-Deployment

### Environment
- [ ] `APP_ENV=production` (not `local`)
- [ ] `APP_DEBUG=false`
- [ ] `APP_KEY` generated via `php artisan key:generate`
- [ ] `APP_URL` set to production domain (with HTTPS)
- [ ] `LOG_LEVEL=warning` (not `debug`)

### Database
- [ ] Production database created and migrated
- [ ] `DB_DATABASE` uses production credentials (not local)
- [ ] Database user has minimal required privileges (SELECT, INSERT, UPDATE, DELETE, ALTER for migrations)
- [ ] `QUEUE_CONNECTION=database` (not `sync`)
- [ ] Migrations verified: `php artisan migrate:status` — all 54 present

### Security
- [ ] `.env` is NOT in public web root
- [ ] `storage/` and `bootstrap/cache/` are NOT publicly accessible
- [ ] Directory permissions: `storage/` and `bootstrap/cache/` writable by web server only
- [ ] Default test/admin users removed or passwords changed
- [ ] `config/sanctum.php` stateful domains configured for SPA if applicable
- [ ] CORS configured in `config/cors.php` if SPA frontend exists
- [ ] Session cookie set to `Secure`, `HttpOnly`, `SameSite=Lax`
- [ ] HTTPS enforced (redirect HTTP → HTTPS)

### Asset Pipeline
- [ ] `npm install && npm run build` completed
- [ ] `public/build/` contains production manifest
- [ ] Vite dev server NOT running
- [ ] All asset URLs resolve correctly

### Caching
- [ ] `php artisan config:cache` run
- [ ] `php artisan route:cache` run
- [ ] `php artisan view:cache` run
- [ ] `php artisan optimize` run
- [ ] `php artisan storage:link` created

### Scheduled Tasks
- [ ] Cron job configured: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`
- [ ] `expiry:send-reminders` command runs on schedule (every 15 minutes)
- [ ] `monitor:check` command runs on schedule (hourly)

### Queue Worker
- [ ] Queue worker running: `php artisan queue:work --queue=default --tries=3`
- [ ] Queue worker restarts after deploy (via `queue:restart`)

---

## Post-Deployment Verification

### Functional Tests
- [ ] Login page loads at `/login`
- [ ] Registration works (if enabled)
- [ ] Password reset flow functional
- [ ] Dashboard loads without errors
- [ ] At least one CRUD module fully functional (create, read, update, delete)
- [ ] Search returns results
- [ ] Reports render and export CSV
- [ ] API endpoints respond (test with a token)
- [ ] File uploads work (attachments)
- [ ] Email sending works (SMTP test from admin panel)

### Performance
- [ ] Page load time < 2 seconds (cached)
- [ ] Memory usage within PHP limits (default 128 MB)
- [ ] Database query time < 500ms for index pages
- [ ] No slow queries in MySQL slow query log

### Monitoring
- [ ] Error logging configured (stack driver)
- [ ] Application logs writable
- [ ] Queue failed jobs table monitored
- [ ] Disk space monitoring for attachment storage

### Recovery
- [ ] Database backup configured (daily)
- [ ] Filesystem backup configured (daily)
- [ ] APP_KEY stored securely off-server
- [ ] Restore procedure tested on staging

---

## Go-Live Sequence

1. DNS records updated (A record / CNAME)
2. SSL certificate installed and auto-renewal configured
3. `.env` settings finalized
4. Database migration run
5. Caches built
6. Queue worker started
7. Cron job registered
8. First admin login verified
9. SMTP profile created and tested
10. Backup schedule enabled
11. Monitoring alerts configured (if available)
12. Documentation shared with team

---

## Post-Launch (First 72 Hours)

- [ ] Monitor error logs for unexpected exceptions
- [ ] Verify all email notifications deliver
- [ ] Check queue worker processes are stable
- [ ] Confirm database connection pool is sufficient
- [ ] Review disk usage growth rate
- [ ] Collect user feedback on missing features

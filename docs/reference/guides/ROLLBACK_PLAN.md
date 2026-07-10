# ROLLBACK PLAN

> Generated: 2026-07-03
> App: OpsPilot | Target: cPanel shared hosting

---

## When to Roll Back

Execute the rollback if any of these conditions occur:

| Condition | Severity | Action |
|---|---|---|
| Site returns 500 on login page | CRITICAL | Roll back immediately |
| All pages return 500 | CRITICAL | Roll back immediately |
| Database migration causes data loss | CRITICAL | Restore DB + roll back code |
| Authentication broken for all users | CRITICAL | Roll back immediately |
| Queue worker fails to process jobs | HIGH | Roll back if SMTP/notifications required |
| Email sending completely broken | HIGH | Roll back if email is in use |
| Specific module broken (domains, etc.) | MEDIUM | Roll back that module or fix forward |
| Performance degradation > 5x | MEDIUM | Roll back if unacceptable |
| UI/UX regression but admin can work | LOW | Fix forward instead of rollback |

**Rule:** If more than 2 CRITICAL or 3 HIGH conditions are met → **ROLL BACK**.

---

## Rollback Decision Flow

```
Deployment fails?
├── Yes → Are users affected?
│   ├── Yes → ROLL BACK
│   └── No → Fix forward
├── No → Continue monitoring (24h window)
│   ├── Issues found?
│   │   ├── Yes → ROLL BACK
│   │   └── No → DEPLOYMENT SUCCESSFUL
```

---

## Pre-Deployment Preparation

### Backup Everything Before Deploying

Before starting deployment, create these backups:

```bash
# 1. Database backup (CRITICAL)
mysqldump -u username -p database_name > /home/username/backups/opspilot_db_$(date +%Y%m%d_%H%M%S).sql

# Via phpMyAdmin:
# Export → "Quick" method → SQL format

# 2. Current code backup (if replacing existing deployment)
cp -r /home/username/opspilot /home/username/opspilot_backup_$(date +%Y%m%d)

# 3. .env backup
cp /home/username/opspilot/.env /home/username/backups/.env_$(date +%Y%m%d)

# 4. Storage directory backup (if has uploaded files)
cp -r /home/username/opspilot/storage/app/public /home/username/backups/uploads_$(date +%Y%m%d)
```

### Backup Checklist

| Item | Command / Method | Done |
|---|---|---|
| Database | `mysqldump` or phpMyAdmin Export | ☐ |
| Current code | `cp -r` or cPanel Backup | ☐ |
| `.env` file | Save copy in secure location | ☐ |
| Uploaded files | `storage/app/public/` backup | ☐ |
| Storage symlink path | Note the target: `readlink -f public/storage` | ☐ |

---

## Rollback Procedure

### Phase 1: Stop the Bleeding (Immediate)

```bash
# 1. Put site in maintenance mode
cd /home/username/opspilot
php artisan down --retry=60 --render="errors.503"
# Visitors will see a maintenance page, refreshing every 60s

# 2. Log the incident
echo "[$(date)] ROLLBACK initiated: <reason>" >> /home/username/logs/rollback.log
```

### Phase 2: Restore Database

```bash
# 1. Access MySQL
mysql -u username -p

# 2. Drop and re-create the database
DROP DATABASE opspilot_prod;
CREATE DATABASE opspilot_prod;
EXIT;

# 3. Restore from backup
mysql -u username -p opspilot_prod < /home/username/backups/opspilot_db_20260703.sql

# 4. Verify restore
mysql -u username -p -e "USE opspilot_prod; SHOW TABLES;"
```

**Alternative via phpMyAdmin:**
1. Login to cPanel → phpMyAdmin
2. Select the database
3. **Operations** → **Drop database** (check "Drop tables that exist")
4. **Import** → Select the SQL backup file → **Go**

### Phase 3: Restore Code

```bash
# Option A: Restore from backup directory
cd /home/username
rm -rf opspilot
cp -r opspilot_backup_20260703 opspilot
cd opspilot

# Option B: Restore from git (if using git deploy)
git checkout <previous-working-tag-or-commit>

# Option C: Re-upload from ZIP (local backup)
# Upload previous production ZIP via cPanel File Manager → Extract
```

### Phase 4: Restore .env

```bash
cp /home/username/backups/.env_20260703 /home/username/opspilot/.env
```

### Phase 5: Restore Storage

```bash
# 1. Restore uploaded files (if any were modified during deploy)
cp -r /home/username/backups/uploads_20260703/* /home/username/opspilot/storage/app/public/

# 2. Recreate storage symlink
cd /home/username/opspilot
php artisan storage:link

# 3. Reset permissions
chmod -R 775 storage bootstrap/cache
```

### Phase 6: Rebuild Cache

```bash
cd /home/username/opspilot

# Clear any new cache
php artisan optimize:clear

# Rebuild cache for previous version
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
php artisan optimize

# Reset permissions again
chmod -R 775 storage bootstrap/cache
```

### Phase 7: Bring Site Online

```bash
php artisan up
```

### Phase 8: Verify Rollback

```bash
# 1. Test login
curl -I https://yourdomain.com/login
# Expected: 200 OK

# 2. Test dashboard
# Visit https://yourdomain.com/dashboard in browser

# 3. Check logs
tail -n 50 /home/username/opspilot/storage/logs/laravel.log

# 4. Confirm previous behavior restored
# Run the first 10 items from POST_DEPLOYMENT_SMOKE_TEST.md Phase 1
```

---

## Database Rollback (Migration Reversal)

If the deployment included new migrations that need to be reversed:

```bash
# Roll back the last batch of migrations
php artisan migrate:rollback --step=1

# Roll back ALL migrations (DESTRUCTIVE)
php artisan migrate:reset

# If using specific migration files, reverse by timestamp:
php artisan migrate:rollback --step=5
# Rolls back the last 5 migration batches
```

**IMPORTANT**: `migrate:rollback` only works if the migration has a `down()` method. Check each migration file before relying on this. Some migrations may not have rollback logic.

### If `down()` missing — Manual SQL Rollback

If a migration added a column and has no `down()`:

```sql
ALTER TABLE table_name DROP COLUMN column_name;
```

If a migration created a table with no `down()`:

```sql
DROP TABLE IF EXISTS table_name;
```

Run these manually via phpMyAdmin or MySQL CLI **before** restoring the database backup.

---

## Rollback Scenarios

### Scenario 1: Login Broken After Deploy

**Symptoms:** 500 error on `/login`, infinite redirect, or "Invalid credentials" for all users

**Cause:** Auth configuration error, APP_KEY changed, DB credentials wrong

**Rollback:**
```bash
# Quick fix first (try before full rollback):
php artisan key:generate
php artisan config:cache
```

If quick fix fails:
```bash
# Full rollback
php artisan down
# Restore .env from backup
# Restore DB if migrations ran
# Restore code if changed
php artisan up
```

### Scenario 2: Database Migration Data Loss

**Symptoms:** Missing columns, truncated data, broken queries on existing pages

**Cause:** Migration dropped a column or table that had production data

**Rollback:**
```bash
# IMMEDIATE: Take site down
php artisan down

# Restore database from backup
mysql -u username -p opspilot_prod < backup.sql

# Roll back code to previous version
# Restore .env
# Rebuild cache
php artisan up
```

### Scenario 3: Email Notifications Broken

**Symptoms:** Emails not sending, queue jobs failing, SMTP errors

**Cause:** SMTP misconfiguration, queue worker not running

**Partial rollback (no code revert needed):**
```bash
# Fix SMTP settings in .env (no code changes needed)
# Verify queue worker cron entry
# Test with: php artisan queue:work --stop-when-empty --tries=1
```

If SMTP was changed in code (not just .env):
```bash
# Revert config/mail.php to previous version
git checkout HEAD~1 -- config/mail.php
php artisan config:cache
```

### Scenario 4: Queue Worker Fails

**Symptoms:** Jobs stuck in `jobs` table, notifications not sending

**Cause:** Missing dependency, class not found, wrong queue config

**Rollback:**
```bash
# Check failed jobs
php artisan queue:failed

# Inspect a failed job
php artisan queue:failed-table  # if table exists
# OR check database directly: SELECT * FROM failed_jobs;

# If caused by code changes, revert code and re-cache
# If caused by dependency, re-run: composer install --no-dev
```

### Scenario 5: Blank White Page (WSOD)

**Symptoms:** All pages return blank white HTML without content

**Cause:** PHP fatal error, syntax error, missing class, memory limit

**Emergency fix:**
```bash
# 1. Enable debug to see the error
# Edit .env: APP_DEBUG=true
# Clear config cache: php artisan config:clear

# 2. Visit the page again — you should see the error now
# 3. Fix the specific issue
# 4. Set APP_DEBUG=false, re-cache
```

If cannot access server:
```bash
# Restore entire codebase from backup
# Restore .env
# Restore DB
```

---

## Rollback Communication

When rollback is triggered, notify:

| Who | What | How |
|---|---|---|
| Team lead | Rollback initiated + reason | Slack / Email |
| Support team | Users may experience downtime | Internal ticket |
| All users (if prolonged) | "Scheduled maintenance" message | Via maintenance page |

### Rollback Log Template

```
---
ROLLBACK LOG
Date: 2026-07-03
Time: 14:30 UTC
Triggered by: [Name]
Reason: [Brief description of the failure]
Duration: [Minutes site was down]
Previous version: [Git commit / deploy date]
Rolled back to: [Git commit / backup date]
Root cause: [To be determined / Known]
Follow-up actions: [List]
---
```

---

## Post-Rollback Tasks

| # | Task | Who | Done |
|---|---|---|---|
| 1 | Investigate root cause of failure | Developer | ☐ |
| 2 | Fix the issue in development | Developer | ☐ |
| 3 | Test the fix locally | Developer | ☐ |
| 4 | Run full smoke test locally | QA/Tester | ☐ |
| 5 | Schedule re-deployment | DevOps | ☐ |
| 6 | Update deployment checklist with lessons learned | All | ☐ |
| 7 | Monitor logs for 24h after re-deployment | DevOps | ☐ |

---

## Rollback Kit

Keep these items accessible at all times:

| Item | Location |
|---|---|
| Database backup (last working) | `/home/username/backups/` |
| Code backup (last working) | `/home/username/opspilot_backup_*/` |
| `.env` backup | `/home/username/backups/.env_*` |
| Production credentials | Password manager / secure note |
| cPanel login credentials | Password manager |
| SSH access details | Password manager |
| Hosting support contact | Support ticket system |
| Deployment documentation | `CPANEL_DEPLOYMENT_GUIDE.md` |
| Smoke test plan | `POST_DEPLOYMENT_SMOKE_TEST.md` |
| This rollback plan | `ROLLBACK_PLAN.md` |

---

## Rollback Time Estimates

| Operation | Estimated Time |
|---|---|
| Put site in maintenance mode | 30 seconds |
| Restore database (500 MB) | 5–15 minutes |
| Restore code from backup | 10–30 minutes (ZIP upload) |
| Restore code from git | 2–5 minutes |
| Rebuild cache | 1 minute |
| Verify site is working | 5–10 minutes |
| **Total estimated downtime** | **15–45 minutes** |

---

## Prevention Checklist (for next deployment)

| Practice | Current Status |
|---|---|
| Test on staging before production | ☐ |
| Run migrations in dry-run mode first | ☐ |
| Take DB backup before any migration | ☐ |
| Take code backup before deploy | ☐ |
| Deploy during low-usage window | ☐ |
| Have rollback backup ready before going live | ☐ |
| Monitor error logs during first hour | ☐ |
| Test emails after deploy (not before) | ☐ |
| Verify queue processes after deploy | ☐ |
| Keep previous version accessible | ☐ |
| Document each deploy in changelog | ☐ |

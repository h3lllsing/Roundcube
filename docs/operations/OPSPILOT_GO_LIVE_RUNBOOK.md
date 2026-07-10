# OpsPilot Go-Live Runbook

## Purpose

This runbook covers everything needed to take OpsPilot from deployment to production use. Follow each section in order.

---

## Pre-Flight Checklist (Before Deployment)

### Environment Check

- [ ] **PHP Version:** Verify PHP 8.1 or higher is installed
- [ ] **Database:** MySQL 8.0+ or MariaDB 10.3+ is available
- [ ] **Web Server:** Apache with mod_rewrite or Nginx configured
- [ ] **Disk Space:** At least 1GB free for application + uploads
- [ ] **Memory:** At least 512MB RAM allocated to PHP
- [ ] **Extensions:** Verify required PHP extensions: BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, cURL, GD
- [ ] **URL:** Domain or subdomain configured to point to the server
- [ ] **HTTPS:** SSL certificate installed and working

### Server Configuration

- [ ] **Document Root:** Point web server to `public/` directory
- [ ] **URL Rewriting:** Configured for Laravel (Nginx `try_files` or Apache `.htaccess`)
- [ ] **File Permissions:** `storage/` and `bootstrap/cache/` are writable by web server
- [ ] **PHP CLI:** Available and matches web PHP version
- [ ] **Composer:** Installed (if needed for updates)

### SMTP Check

- [ ] **Email Server:** SMTP credentials ready (host, port, username, password)
- [ ] **Test Email:** Can send test email from command line or SMTP profile
- [ ] **App Password:** For Gmail, ensure App Password is generated (not regular password)
- [ ] **Outbound Port:** Port 587 or 465 is open on the firewall

### Cron / Scheduler Check

- [ ] **Crontab Entry:** Added to server crontab:
  ```
  * * * * * cd /path-to-app && php artisan schedule:run >> /dev/null 2>&1
  ```
- [ ] **Verify:** After adding, wait 1 minute and check if `storage/logs/laravel.log` has scheduler entries
- [ ] **Check Expiries:** Run manually to test: `php artisan check-expiries`

### Environment File (.env)

- [ ] **APP_KEY:** Generated (`php artisan key:generate`)
- [ ] **APP_URL:** Set to the production URL (important for URL generation)
- [ ] **APP_ENV:** Set to `production`
- [ ] **APP_DEBUG:** Set to `false`
- [ ] **DB_*:** Database connection details correct
- [ ] **MAIL_*:** (If using direct mail config as fallback)
- [ ] **SESSION_DRIVER:** Set to `file` or `redis` (not `array`)
- [ ] **CACHE_STORE:** Set to `file` or `redis` (not `array`)
- [ ] **QUEUE_CONNECTION:** Set to `sync` or `database` (not `array`)

---

## Deployment Steps

### Step 1: Deploy Code

- [ ] Upload application files to server
- [ ] Or pull from Git repository
- [ ] Run `composer install --no-dev --optimize-autoloader`
- [ ] Run `npm install && npm run build` (if using Vite/Node assets)

### Step 2: Configure Environment

- [ ] Copy `.env.example` to `.env`
- [ ] Update all settings for production
- [ ] Generate app key: `php artisan key:generate`

### Step 3: Database

- [ ] Create database and user
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Run seeders: `php artisan db:seed --class=FeatureModuleSeeder --force`
- [ ] Verify tables exist

### Step 4: Storage Links

- [ ] Run `php artisan storage:link`
- [ ] Verify `public/storage` points to `storage/app/public`

### Step 5: Optimization

- [ ] `php artisan config:cache`
- [ ] `php artisan route:cache`
- [ ] `php artisan view:cache`
- [ ] `php artisan event:cache`

### Step 6: Permissions

- [ ] `chmod -R 775 storage bootstrap/cache`
- [ ] `chmod -R 775 public/storage` (if it exists)
- [ ] Ensure web server user owns these directories or has write access

### Step 7: Test Deployment

- [ ] Visit the URL — login page should appear
- [ ] Try logging in with default Super Admin credentials (if seeded)
- [ ] OR register a new account (if registration is enabled)

---

## First Login Setup

### Step 1: Login as Super Admin

- [ ] Login with the Super Admin account
- [ ] Verify Dashboard loads with all widgets
- [ ] Verify no error messages appear

### Step 2: Create SMTP Profile

- [ ] Go to **Administration → SMTP Profiles → Create**
- [ ] Enter your email server details
- [ ] Click **Save**
- [ ] Click **Test** to verify
- [ ] Click **Set as Default**

### Step 3: Create Service Providers

- [ ] Add your main providers (GoDaddy, DigitalOcean, AWS, etc.)
- [ ] Verify they appear in dropdowns

### Step 4: Verify Module Permissions

- [ ] Go to **Module Permissions**
- [ ] Default permissions may be set from seeders
- [ ] Adjust as needed for your organization

### Step 5: Create Team Users

- [ ] Go to **Users → Create**
- [ ] Add each team member with appropriate role
- [ ] Verify they can login

### Step 6: Set Up Expiry Tracking

- [ ] Create at least one Expiry Tracker
- [ ] Select the SMTP Profile
- [ ] Enable notifications
- [ ] Click **Test Email**
- [ ] Verify email is received

### Step 7: Verify All Modules

- [ ] Create a test Domain
- [ ] Create a test Hosting record
- [ ] Create a test VPS
- [ ] Create a test VoIP
- [ ] Create a test Asset
- [ ] Create a test Task
- [ ] Create a test Vault entry
- [ ] Verify Search finds records
- [ ] Verify Calendar shows dates
- [ ] Verify Export works

---

## User Role Verification

- [ ] Create a test user with **User** role
- [ ] Login as that user — verify they see only their own records
- [ ] Create a test user with **Admin** role
- [ ] Set module permissions for Admin
- [ ] Login as Admin — verify they see records based on module permissions
- [ ] Test **Super Admin** — verify full access
- [ ] Test **Forgot Password** flow

---

## Security Verification

- [ ] `APP_DEBUG=false` — verify no debug output on errors
- [ ] **Login attempts** — verify throttling works (5 attempts per minute)
- [ ] **Password reveal** — verify it is throttled (10 per minute)
- [ ] **Export** — verify throttled
- [ ] **Search** — verify throttled
- [ ] **Suspension** — suspend a test user, verify they cannot login
- [ ] **403 pages** — try accessing admin pages as regular user, verify 403 page appears
- [ ] **404 pages** — try a non-existent URL, verify 404 page appears
- [ ] **HTTPS** — verify all traffic is over HTTPS (redirect HTTP to HTTPS)

---

## Data Migration (If Importing Existing Data)

- [ ] Prepare CSV files for each module
- [ ] Go to **Import**
- [ ] Import each type one at a time
- [ ] Verify imported records
- [ ] Check for missing data
- [ ] Backfill any missing fields

---

## Backup Verification

- [ ] **Database backup:** Create a manual backup: `mysqldump -u user -p database > backup.sql`
- [ ] **Storage backup:** Verify `storage/app/` contents are included in backup strategy
- [ ] **Backup schedule:** Configure automated daily backups
- [ ] **Test restore:** Verify backup can be restored (test on a non-production environment)

---

## Go-Live Signoff

### Final Checks

- [ ] All pre-flight items completed
- [ ] Deployment steps completed
- [ ] First login successful
- [ ] SMTP working
- [ ] Cron running
- [ ] Backup configured
- [ ] All team users created
- [ ] Roles and permissions verified
- [ ] Test records created and working

### Signoff

| Role | Name | Signature | Date |
|------|------|-----------|------|
| Super Admin | _____________ | _____________ | ______ |
| IT Manager | _____________ | _____________ | ______ |
| Stakeholder | _____________ | _____________ | ______ |

---

## Post-Go-Live Checklist (First Week)

### Day 1
- [ ] Monitor Dashboard for any issues
- [ ] Check server health (disk, memory, PHP errors)
- [ ] Verify all users can login
- [ ] Answer user questions

### Day 2
- [ ] Check Activity Logs — any unexpected errors?
- [ ] Test SMTP profiles again
- [ ] Verify expiry notifications working

### Day 3
- [ ] Check Login Audits — any unusual failed attempts?
- [ ] Review any support tickets

### Day 7
- [ ] Full review — users, permissions, data quality
- [ ] Export first weekly backup
- [ ] Check `storage/logs/laravel.log` for errors
- [ ] Verify cron has been running all week

---

## Rollback Plan (If Something Goes Wrong)

### Minor Issue (e.g., one feature not working)
- Continue using the system
- Note the issue
- Fix in next maintenance window

### Major Issue (e.g., cannot login, data loss)
1. **Stop the cron** to prevent automated operations
2. **Restore database** from latest backup
3. **Restore files** from backup
4. **Run `php artisan optimize`** after restore
5. **Verify** system is working
6. **Investigate** root cause

### Critical Issue (e.g., security breach)
1. **Take site offline** — replace `public/index.php` with maintenance page
2. **Revoke all API tokens**
3. **Reset all user passwords**
4. **Restore from backup**
5. **Investigate** thoroughly before going live again

---

## Emergency Contacts

| Contact | Role | Phone | Email |
|---------|------|-------|-------|
| ____________ | Super Admin | ____________ | ____________ |
| ____________ | System Admin | ____________ | ____________ |
| ____________ | Database Admin | ____________ | ____________ |
| ____________ | Hosting Support | ____________ | ____________ |

---

## Quick Commands Reference

```bash
# Application
php artisan key:generate              # Generate app key
php artisan migrate --force            # Run migrations
php artisan db:seed --force            # Run seeders
php artisan storage:link              # Create storage symlink
php artisan optimize                  # Cache config, routes, views
php artisan config:clear              # Clear config cache
php artisan route:clear               # Clear route cache
php artisan view:clear                # Clear compiled views

# Maintenance
php artisan down                      # Maintenance mode
php artisan up                        # Bring site back up

# Testing
php artisan test                      # Run all tests
php artisan check-expiries            # Manually trigger expiry checks

# Permissions (Linux)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

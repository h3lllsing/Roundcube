# Shared Hosting Deployment Guide

## Requirements
- PHP 8.2+
- MySQL 5.7+
- Redis (check with your hosting provider)
- mod_rewrite (Apache)
- Cron job access (cPanel or similar)

## Step 1: PHP Extensions Required
- pdo_mysql, mbstring, openssl, curl, fileinfo, zip, ctype, bcmath, tokenizer, xml
- **No** need for IMAP, phpredis, or intl extensions (predis polyfills)

## Step 2: Upload Files
Upload entire project to your hosting root (e.g., `public_html/`).

**IMPORTANT:** Set these permissions after upload:
```bash
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
chmod -R 775 public/webmail/data/
```

## Step 3: Configure .env
Update these values for production:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_HOST=localhost
DB_DATABASE=your_database
DB_USERNAME=your_user
DB_PASSWORD=your_password

SESSION_DRIVER=redis
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true

QUEUE_CONNECTION=redis
CACHE_STORE=redis

REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

## Step 4: Setup Cron Jobs (cPanel)
Add this cron job (runs every minute):
```bash
* * * * * php /home/username/public_html/artisan schedule:run >> /dev/null 2>&1
```

**How it works:**
- Every minute, cron calls `schedule:run`
- Laravel checks if `queue:work` needs to run → processes queue jobs for up to 240 seconds
- Every 10 minutes, dispatches IMAP sync jobs to the Redis queue
- Each email account is synced as a separate queued job

## Step 5: Setup Laravel
```bash
# Generate app key (if not set)
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache everything
php artisan optimize
php artisan route:cache
php artisan config:cache
php artisan view:cache
php artisan event:cache
php artisan storage:link
```

## Step 6: Verify
1. Visit `https://yourdomain.com` → Login
2. Click Webmail → should show accounts
3. Click any account → SnappyMail opens in iframe
4. Check Redis is used: login persists across page reloads

## Architecture Notes

### Queue Flow (Cron-based)
```
cron (every min)
  └─ artisan schedule:run
       └─ queue:work --stop-when-empty (processes until done)
       └─ email-sync:dispatch (every 10 min)
            └─ dispatches EmailSyncJob per account
                 └─ connects to IMAP, updates last_sync_at
```

### Security
- SnappyMail data directory protected with `.htaccess` (Require all denied)
- Email passwords stored encrypted (Laravel Crypt)
- Session data in Redis (encrypted with SESSION_ENCRYPT=true)
- IMAP credentials never exposed to frontend
- Temp files use random names + restricted permissions

### Performance for 200+ Domains / 1000+ Accounts
- Each account sync = 1 queue job (30s timeout, 2 retries)
- Queue worker runs up to 4 min per cron cycle
- 1000 jobs ÷ (240s ÷ 30s per job) = ~12.5 cycles = ~12.5 minutes
- Redis handles queue much faster than database

### Troubleshooting
```bash
# Test Redis connectivity
php artisan tinker
> Redis::connection()->ping()

# Check queue status
php artisan queue:monitor

# Test sync manually
php artisan email-sync:dispatch

# View logs
tail -f storage/logs/laravel-*.log
```

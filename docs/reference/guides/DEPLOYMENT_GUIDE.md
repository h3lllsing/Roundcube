# Deployment Guide — OpsPilot v1.0.0

## Shared-Hosting Deployment

### Prerequisites
- PHP 8.2+ (check with `php -v`)
- Composer 2.x
- MySQL 8.0+ / MariaDB 10.6+ database (provided by host)
- SSH or cPanel File Manager access
- Domains: one for the application, one for email config

### Step 1 — Upload Files
Upload the entire project to your hosting document root (e.g., `public_html`) via:
- **cPanel File Manager**: Extract a ZIP archive of the project
- **SFTP**: Use FileZilla/WinSCP to transfer files
- **Git**: `git clone` if shell access is available

### Step 2 — Set Document Root
Change the web root to point to the `public/` subdirectory:
- **cPanel**: Use "Document Root" setting for your domain, set to `/path/to/project/public`
- **DirectAdmin**: Under Domain Setup → Document Root
- **Manual**: If symlinks are supported, `ln -s /path/to/project/public /home/user/public_html`

### Step 3 — Configure Environment
```bash
cp .env.example .env
php artisan key:generate
```
Edit `.env`:
- Set `APP_ENV=production`, `APP_DEBUG=false`
- Configure `DB_*` with your hosting MySQL credentials
- Set `QUEUE_CONNECTION=database` (database-driven queue, no Redis needed)
- Set `MAIL_*` with your SMTP credentials (or configure later via admin panel)

### Step 4 — Directory Permissions (Critical)
```bash
chmod -R 775 storage bootstrap/cache
chmod -R 775 public
chown -R user:www-data storage bootstrap/cache  # adjust group to web server user
```

### Step 5 — Database Setup
```bash
php artisan migrate --force
```
If using cPanel, create a MySQL database and user via the MySQL Databases wizard, then update `.env`.

### Step 6 — Optimize
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### Step 7 — Cron Job for Scheduled Tasks
Set up a cron job via cPanel or the server's crontab:
```cron
* * * * * /usr/bin/php /home/user/tyro-rbac/artisan schedule:run >> /dev/null 2>&1
```

### Step 8 — Queue Worker
The database queue worker processes notifications and reminders. Start it:
```bash
php artisan queue:work --queue=default --tries=3 --timeout=90
```
For shared hosting without persistent processes, use an external cron-based monitor or a web-based queue dashboard.

---

## VPS / Dedicated Server Deployment

### Using the Deploy Script
```bash
chmod +x deploy.sh
./deploy.sh
```
The script handles: `git pull`, `composer install`, `migrations`, `config:cache`, `route:cache`, `view:cache`, `storage:link`, `queue:restart`.

### Manual Deploy
```bash
git pull origin main
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
php artisan queue:restart
```

---

## Docker Deployment

```bash
docker-compose up -d
```

The included `docker-compose.yml` provides:
- PHP 8.2 FPM container
- Nginx container
- MySQL 8.0 container
- Node.js build container (one-shot)

After startup:
```bash
docker-compose exec app php artisan migrate --seed
docker-compose exec app php artisan storage:link
```

---

## Deployment Checklist

- [ ] `.env` `APP_ENV=production`, `APP_DEBUG=false`
- [ ] `APP_KEY` generated (never reuse across instances)
- [ ] Database migrated and seeded
- [ ] `storage/` and `bootstrap/cache/` writable by web server
- [ ] `config:cache`, `route:cache`, `view:cache` run
- [ ] `storage:link` created
- [ ] Cron job configured for `schedule:run`
- [ ] Queue worker running for default queue
- [ ] SMTP profiles created in admin UI (or `.env` MAIL_* set)
- [ ] SSL/TLS certificate installed
- [ ] First admin user created, default test users removed
- [ ] Backup schedule configured (see BACKUP_AND_RESTORE.md)

---

## Environment-Specific Notes

### Using SQLite on Shared Hosting
Set `DB_CONNECTION=sqlite` and point `DB_DATABASE` to a writable path outside the web root:
```ini
DB_CONNECTION=sqlite
DB_DATABASE=/home/user/tyro-rbac/database/database.sqlite
```
SQLite is fully supported. All 54 migrations run without modification.

### Using MySQL
Default driver. Create the database with `utf8mb4` charset. The migrations use `utf8mb4_unicode_ci` collation.

### Asset Building
Production assets are pre-built and committed. If you modify CSS/JS:
```bash
npm install
npm run build
```
This generates versioned files in `public/build/` with a Vite manifest.

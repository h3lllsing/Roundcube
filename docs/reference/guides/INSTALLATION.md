# Installation Guide — OpsPilot v1.0.0

## System Requirements

| Requirement | Minimum |
|-------------|---------|
| PHP | 8.2+ |
| Database | MySQL 8.0 / MariaDB 10.6 / SQLite 3.x |
| Web Server | Apache 2.4+ (mod_rewrite) / Nginx 1.20+ |
| Composer | 2.x |
| Node.js | 20+ (for asset build only) |
| NPM | 10+ |
| Storage | 100 MB application + 50 MB per 10k records |
| PHP Extensions | BCMath, Ctype, Fileinfo, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, GD, intl |

---

## Quick Install (Development)

```bash
# 1. Clone the repository
git clone <repository-url> tyro-rbac
cd tyro-rbac

# 2. Install PHP dependencies
composer install

# 3. Create environment file
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure database in .env
#    Default: DB_CONNECTION=mysql, DB_HOST=127.0.0.1, DB_PORT=3306
#    For SQLite: DB_CONNECTION=sqlite, DB_DATABASE=/absolute/path/database.sqlite

# 6. Install NPM dependencies and build assets
npm install
npm run build

# 7. Run migrations and seeders
php artisan migrate --seed

# 8. Start development server
php artisan serve
```

Visit `http://localhost:8000` and log in with the seeded admin credentials.

---

## Step-by-Step Installation

### 1. Web Server Setup

#### Apache
- Ensure `mod_rewrite` is enabled
- Point document root to `/path/to/project/public`
- The included `.htaccess` handles URL rewriting

#### Nginx
```nginx
server {
    listen 80;
    server_name tyro.example.com;
    root /var/www/tyro-rbac/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### 2. Database Setup

#### MySQL
```sql
CREATE DATABASE tyro_rbac CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'tyro'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON tyro_rbac.* TO 'tyro'@'localhost';
FLUSH PRIVILEGES;
```

#### SQLite (shared-hosting compatible)
```bash
touch database/database.sqlite
```
Set `DB_CONNECTION=sqlite` and `DB_DATABASE` to the absolute path in `.env`.

### 3. Environment Configuration

Edit `.env` with your settings:

```ini
APP_NAME="OpsPilot"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://tyro.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=tyro_rbac
DB_USERNAME=tyro
DB_PASSWORD=secure_password

QUEUE_CONNECTION=database

SESSION_DRIVER=file
SESSION_LIFETIME=120

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@example.com
MAIL_PASSWORD=mail_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Tyro RBAC"
```

### 4. Finalize Installation

```bash
php artisan migrate --seed
php artisan storage:link
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### 5. Set Up Cron (Required for Renewal Reminders)

Add to crontab:

```cron
* * * * * cd /path/to/tyro-rbac && php artisan schedule:run >> /dev/null 2>&1
```

This runs `expiry:send-reminders` and `monitor:check` on schedule.

---

## Credential Security

- SMTP profile passwords are encrypted at rest (AES-256-CBC via Laravel `encrypted` cast)
- Vault passwords are encrypted at rest
- `.env` contains the `APP_KEY` — keep it secret; rotating it will invalidate all encrypted data
- API tokens are hashed via Laravel Sanctum

---

## Troubleshooting

| Symptom | Cause | Solution |
|---------|-------|----------|
| Blank white screen | APP_KEY missing | Run `php artisan key:generate` |
| SQLite PDOException | Missing extension | Install `php-sqlite3` |
| 403 Forbidden on all routes | .htaccess not loaded | Enable `mod_rewrite`, check `AllowOverride All` |
| Asset 404s | Vite manifest missing | Run `npm install && npm run build` |
| Queue jobs not running | Queue worker not started | Run `php artisan queue:work --queue=default` or set up cron |
| Email not sending | SMTP misconfigured | Check MAIL_* vars; use "Test SMTP" from admin panel |
| Route/page 404 | Route cache stale | Run `php artisan route:clear` |

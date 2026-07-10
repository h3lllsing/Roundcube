# Backup & Restore Guide — OpsPilot v1.0.0

## Backup Strategy

### What to Back Up
1. **Database** — All application data (domains, users, roles, assets, etc.)
2. **Storage Directory** — `storage/app/public/` (uploaded attachments)
3. **Environment File** — `.env` (contains APP_KEY, DB credentials)
4. **Generated Assets** — `public/build/` (Vite manifest; optional if rebuildable)

### What NOT to Back Up
- `vendor/` — reinstalled via `composer install`
- `node_modules/` — reinstalled via `npm install`
- `bootstrap/cache/` — regenerated via artisan commands
- `storage/framework/` — session/cache/view temp files

---

## Database Backup

### MySQL
```bash
# Backup
mysqldump -u username -p tyro_rbac > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore
mysql -u username -p tyro_rbac < backup_file.sql
```

### SQLite
```bash
# Backup (simple copy)
cp database/database.sqlite backup_$(date +%Y%m%d_%H%M%S).sqlite

# Restore
cp backup_file.sqlite database/database.sqlite
```

### Using Artisan
```bash
# Backup via db-dump (if package installed)
php artisan db:dump --database=mysql --destination=storage/backups/

# Or manually run the built-in snapshot
php artisan backup:run
```

---

## Filesystem Backup

```bash
# Backup storage and .env
tar -czf tyro-backup-$(date +%Y%m%d).tar.gz \
    storage/app/public \
    .env \
    database/database.sqlite \
    public/build

# Restore
tar -xzf tyro-backup-20260627.tar.gz
cp .env .env
php artisan storage:link
```

---

## Automated Backup Script

Create `scripts/backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/path/to/backups"
DB_NAME="tyro_rbac"
DB_USER="root"
DB_PASS="password"
DATE=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

# Database dump
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/db_$DATE.sql

# Files
tar -czf $BACKUP_DIR/files_$DATE.tar.gz \
    storage/app/public \
    .env

# Rotate: keep last 30 days
find $BACKUP_DIR -name "*.sql" -mtime +30 -delete
find $BACKUP_DIR -name "*.tar.gz" -mtime +30 -delete
```

Schedule via cron:
```cron
0 3 * * * /path/to/scripts/backup.sh
```

---

## Recovery Procedure

### Full Restore
1. Set up a fresh Laravel installation with same version
2. Copy `.env` from backup
3. Run `php artisan key:generate` if APP_KEY was lost (note: this invalidates encrypted data)
4. Restore database from SQL dump
5. Run `php artisan migrate` (should detect already-run migrations)
6. Restore `storage/app/public/` from backup
7. Run `php artisan storage:link`
8. Clear caches: `php artisan optimize:clear`
9. Regenerate assets: `npm install && npm run build`

### Encrypted Data Recovery
If the `APP_KEY` is lost:
- **Vault passwords**: Irrecoverable — encrypted with AES-256-CBC using APP_KEY
- **SMTP passwords**: Irrecoverable — also encrypted
- **All other data**: Recoverable (plaintext columns)

**Always keep a secure copy of APP_KEY separately from backups.**

---

## Backup Verification

Periodically verify backups by:
1. Restoring to a staging environment
2. Running `php artisan migrate:status` to confirm migration integrity
3. Spot-checking sample records from each module
4. Confirming attachment files are accessible

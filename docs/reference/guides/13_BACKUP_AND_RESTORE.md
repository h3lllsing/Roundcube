# Backup and Restore Guide

> **Audience:** Super Administrators

## Table of Contents

- [Overview](#overview)
- [What Needs Backup](#what-needs-backup)
- [Database Backup](#database-backup)
- [File Storage Backup](#file-storage-backup)
- [Configuration Backup](#configuration-backup)
- [Restore Procedures](#restore-procedures)
- [Backup Schedule](#backup-schedule)
- [Testing Backups](#testing-backups)

---

## Overview

This guide covers backup and restore procedures for OpsPilot. Regular backups protect against data loss from system failures, security incidents, or human error.

> **Warning:** These procedures require direct server or database access. They are intended for system administrators managing the OpsPilot infrastructure.

## What Needs Backup

| Component | Location | Frequency |
|-----------|----------|-----------|
| **Database** | Database server (MySQL/PostgreSQL) | Daily |
| **Uploaded files** | `storage/app/public/` and `storage/app/uploads/` | Daily |
| **Environment configuration** | `.env` file | After each change |
| **Application code** | Application directory | With each deployment |

## Database Backup

### Automated Backup (Recommended)

Set up a daily cron job:

```bash
# Example cron: runs daily at 2 AM
0 2 * * * /usr/bin/mysqldump -u [user] -p[password] [database] > /backups/daily/opsPilot_$(date +\%Y\%m\%d).sql
```

### Manual Backup

```bash
# Export the database
mysqldump -u [user] -p [database] > opsPilot_backup_$(date +%Y%m%d).sql
```

### Backup with Compression

```bash
mysqldump -u [user] -p [database] | gzip > opsPilot_backup_$(date +%Y%m%d).sql.gz
```

## File Storage Backup

### Uploaded Files

```bash
# Backup uploaded files
tar -czf uploads_backup_$(date +%Y%m%d).tar.gz -C /path/to/app/storage app/public app/uploads
```

### Application Logs (Optional)

```bash
# Backup logs
tar -czf logs_backup_$(date +%Y%m%d).tar.gz -C /path/to/app/storage logs
```

## Configuration Backup

### Environment File

Always keep a secure copy of your `.env` file. It contains database credentials, app key, and service configurations.

```bash
cp .env .env.backup.$(date +%Y%m%d)
```

Store configuration backups in a secure, encrypted location.

## Restore Procedures

### Restore Database

```bash
# Restore from SQL file
mysql -u [user] -p [database] < opsPilot_backup_20240101.sql

# Restore from compressed backup
gunzip -c opsPilot_backup_20240101.sql.gz | mysql -u [user] -p [database]
```

### Restore File Storage

```bash
# Extract files to the application storage directory
tar -xzf uploads_backup_20240101.tar.gz -C /path/to/app/storage
```

### Full Restore Process

1. Restore the database from the latest backup
2. Restore file storage
3. Restore the `.env` file
4. Clear application caches (will be done by the system)
5. Verify the application is functioning:
   - Log in as Super Admin
   - Check Dashboard loads
   - Verify a few module records exist
   - Test search functionality

## Backup Schedule

| Frequency | What | Retention |
|-----------|------|-----------|
| Daily | Database dump | 7 days |
| Weekly | Database + files | 4 weeks |
| Monthly | Full backup (DB + files + config) | 12 months |
| Per deployment | Application code | Permanent (via version control) |

## Testing Backups

Test your restore procedure monthly:

1. Restore the latest backup to a test environment
2. Verify data integrity by checking record counts
3. Test login and basic functionality
4. Document any issues found

> A backup that has never been tested is not a backup.

---

## Related Modules

- [Disaster Recovery Guide](16_DISASTER_RECOVERY_GUIDE.md)
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md)

# Disaster Recovery Guide

> **Audience:** Super Administrators

## Table of Contents

- [Overview](#overview)
- [Recovery Scenarios](#recovery-scenarios)
- [Recovery Procedures](#recovery-procedures)
- [Communication Plan](#communication-plan)
- [Post-Recovery Checklist](#post-recovery-checklist)

---

## Overview

This guide outlines procedures for recovering OpsPilot from various disaster scenarios. It assumes you have valid backups available. See the [Backup and Restore Guide](13_BACKUP_AND_RESTORE.md) for backup procedures.

> **Warning:** These procedures require direct server or database access. They are intended for system administrators managing the OpsPilot infrastructure.

## Recovery Scenarios

### Scenario 1: Database Corruption

**Symptoms:** Error messages when loading pages, missing or garbled data, database connection errors.

**Recovery Steps:**
1. Stop the application to prevent further writes
2. Restore the database from the most recent clean backup
3. Clear application caches
4. Verify data integrity
5. Resume normal operation

**Estimated downtime:** 30-60 minutes

### Scenario 2: Complete Server Failure

**Symptoms:** Server unreachable, no response on any service.

**Recovery Steps:**
1. Provision a new server with matching specifications
2. Install required software stack (PHP, database server, web server)
3. Deploy the application code from version control
4. Restore the `.env` file from secure storage
5. Restore the database from the latest backup
6. Restore uploaded files from backup
7. Update DNS records if server IP changed
8. Verify all functionality
9. Resume normal operation

**Estimated downtime:** 2-4 hours

### Scenario 3: Data Breach / Security Incident

**Symptoms:** Unauthorized access detected, suspicious activity in logs, compromised user accounts.

**Recovery Steps:**
1. Isolate the affected server from the network immediately
2. Review Activity Logs and Login Audits to assess the scope
3. Notify affected users
4. Force password reset for all users (database update)
5. Rotate all service credentials stored in OpsPilot
6. Rotate SMTP profile passwords
7. Regenerate application key
8. Restore from a known-clean backup if data was tampered with
9. Conduct security audit before resuming operation

**Estimated downtime:** 4-8 hours (plus investigation time)

### Scenario 4: Accidental Mass Deletion

**Symptoms:** Critical records missing, soft-deleted and need restoration.

**Recovery Steps:**
1. Do NOT panic — records are soft-deleted, not permanently removed
2. For small numbers of records: use the Trashed filter and Restore individually
3. For mass restore: use Bulk Actions (Super Admin only)
4. If records were force-deleted, restore from the most recent backup

**Estimated downtime:** 15-60 minutes

## Recovery Procedures

### Pre-Recovery Checklist

Before beginning recovery:
- [ ] Confirm the scope of the disaster
- [ ] Verify backup availability and date
- [ ] Notify stakeholders of expected downtime
- [ ] Document the current state for post-incident review

### Database Restore Procedure

```bash
# Step 1: Stop web traffic (or put in maintenance mode)
# Step 2: Drop and recreate the database
mysql -u root -p -e "DROP DATABASE IF EXISTS opspilot; CREATE DATABASE opspilot;"
# Step 3: Restore from backup
mysql -u root -p opspilot < /backups/opspilot_latest.sql
# Step 4: Verify restoration
mysql -u root -p opspilot -e "SELECT COUNT(*) FROM users;"
```

### Full Server Build Procedure

1. **Server provisioning**: Set up OS, web server, PHP, database
2. **Code deployment**: Clone from repository, install dependencies
3. **Configuration**: Restore `.env`, set file permissions
4. **Database restore**: Import the latest database backup
5. **File restore**: Restore uploaded files
6. **Verification**: Run the application and verify core functionality

## Communication Plan

| Phase | Who to Notify | Message |
|-------|---------------|---------|
| During outage | All users (via alternative channel) | "OpsPilot is currently unavailable due to [reason]. We are working on restoration." |
| Estimated resolution | All users | "Estimated restoration time: [time]" |
| Post-recovery | All users | "OpsPilot is back online. [Summary of what happened and what data was affected]" |
| Post-incident | Super Admin team | Full incident report with root cause analysis |

## Post-Recovery Checklist

- [ ] All core modules are accessible and show correct data
- [ ] User authentication works (login, password reset)
- [ ] Search returns expected results
- [ ] Dashboard widgets display accurate counts
- [ ] Export functionality works
- [ ] Email notifications are sending (test with an Expiry Tracker)
- [ ] Backup system is verified and running
- [ ] Incident report is documented
- [ ] Review and update disaster recovery plan if gaps were found

---

## Related Modules

- [Backup and Restore Guide](13_BACKUP_AND_RESTORE.md)
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md)

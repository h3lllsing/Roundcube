# OpsPilot Daily Operations Checklist

## Daily (Every Working Day)

### Morning Check — All Users

- [ ] Login to OpsPilot
- [ ] Check Dashboard for any alerts or issues
- [ ] Review Operations Widget — total active services and monthly cost
- [ ] Review Tasks Widget — pending and overdue tasks
- [ ] Review Renewals Widget — upcoming renewals and failed notifications

### IT Support / Admin

- [ ] **Check My Tasks** — update status on any completed work
- [ ] **Check Calendar** — see what is expiring today or this week
- [ ] **Review Expiry Trackers** — verify upcoming renewals have SMTP configured
- [ ] **Update Records** — add any new services, update changed details
- [ ] **Process Renewals** — if any services expired today, handle them

### Super Admin — Morning

- [ ] Check Dashboard thoroughly — all 9 widgets
- [ ] **Server Health Widget** — verify:
  - Database status is OK
  - Disk usage is below 80%
  - Scheduler (cron) is running
  - Cache driver is working
- [ ] **SMTP Widget** — check for profile failures
- [ ] **Renewals Widget** — review failed notifications

### Super Admin — Throughout Day

- [ ] **Activity Logs** — skim for unusual activity
- [ ] **Login Audits** — check for failed login attempts
- [ ] Investigate any error reports from users

---

## Weekly (Every Monday)

### All Users

- [ ] Review all your records for accuracy
- [ ] Update any missing data (costs, expiry dates, notes)
- [ ] Clean up incomplete or duplicate records

### Admin / IT Manager

- [ ] Review team's records for completeness
- [ ] Check for records without Service Providers linked
- [ ] Check for records without Cost entered
- [ ] Verify Expiry Trackers are set for all expiring services
- [ ] Export key data for backup (if permitted)

### Super Admin — Weekly

- [ ] **Review Users** — check for inactive accounts
- [ ] **Review Module Permissions** — verify roles still have correct access
- [ ] **Check All SMTP Profiles** — test each one
- [ ] **Review Reports** — financial summaries, task completion, login stats
- [ ] **Review Vault** — remove outdated entries
- [ ] **Backup Database** (or verify automated backup)
- [ ] **Activity Logs** — review deletions and permission changes

---

## Monthly

### Super Admin — Monthly

- [ ] **Full Audit Review:**
  - Review all activity logs from the past month
  - Review all login audits from the past month
  - Investigate any suspicious patterns
- [ ] **Cleanup:**
  - Delete old login audit records
  - Soft-delete obsolete records
  - Force-delete only if absolutely necessary
- [ ] **Performance Check:**
  - Check database size
  - Check storage usage (attachments)
  - Verify scheduled tasks are completing
- [ ] **Security Review:**
  - Verify all users still need access
  - Suspend accounts of departed employees
  - Review API tokens — revoke unused ones
  - Check webhook endpoints are active

---

## Per-Role Checklists

### Super Admin Checklist

**Daily:**
- [ ] Dashboard review (all widgets)
- [ ] Server health check
- [ ] SMTP health check
- [ ] Quick activity log scan
- [ ] Quick login audit scan

**Weekly:**
- [ ] User review
- [ ] Permission review
- [ ] SMTP test
- [ ] Report review
- [ ] Database backup verification

**Monthly:**
- [ ] Full audit review
- [ ] Cleanup old records
- [ ] Security review
- [ ] Performance check

### Admin / IT Manager Checklist

**Daily:**
- [ ] Dashboard review
- [ ] Tasks check
- [ ] Calendar check
- [ ] Expiry tracker review

**Weekly:**
- [ ] Team records review
- [ ] Data completeness check
- [ ] Data export (if needed)

**Monthly:**
- [ ] Full data review
- [ ] Cleanup duplicates
- [ ] Update documentation

### IT Support Checklist

**Daily:**
- [ ] My Tasks — update progress
- [ ] My Records — keep current
- [ ] Check new assignments

**Weekly:**
- [ ] Review all your records
- [ ] Add missing details
- [ ] Plan upcoming work

---

## Quick Reference: Dashboard Widget Health

| Widget | What to Watch For | Action if Problem |
|--------|------------------|-------------------|
| Operations | Services expiring soon | Create/review Expiry Trackers |
| Renewals | Failed notifications | Test SMTP, verify trackers |
| Tasks | Overdue tasks | Reassign or update due dates |
| Assets | Unreturned assets | Contact assigned user |
| Vault | High reveal count | Investigate reveals |
| SMTP | Inactive/failed profiles | Test and fix SMTP |
| Server Health | Disk > 80%, DB down | Free space, check DB connection |

---

## Quick Reference: When Things Go Wrong

| Issue | Check First | Escalate To |
|-------|-------------|-------------|
| Cannot login | Forgot Password link | Super Admin |
| Cannot see page | Check My Access for permissions | Super Admin |
| Cannot save record | Check required fields | Admin |
| Delete blocked | Check error message for dependencies | Admin |
| Email not sent | SMTP Profile → Test | Super Admin |
| Dashboard shows no data | Check you have records | Admin |
| Error 500 page | Refresh, try again | Super Admin (check logs) |

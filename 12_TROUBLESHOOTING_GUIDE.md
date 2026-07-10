# Troubleshooting Guide

> **Audience:** All Users

## Table of Contents

- [Login Issues](#login-issues)
- [Permission Issues](#permission-issues)
- [Module Access Issues](#module-access-issues)
- [Data Issues](#data-issues)
- [Performance Issues](#performance-issues)
- [Browser Issues](#browser-issues)

---

## Login Issues

### Cannot log in at all

Possible causes:

| Symptom | Likely Cause | Solution |
|---------|-------------|----------|
| "The provided credentials do not match our records." | Incorrect email or password | Use "Forgot your password?" to reset, or check for typos |
| "Your account has been suspended." | Account suspended by Super Admin | Contact your Super Admin to reactivate |
| No error, just returns to login page | Session or browser issue | Clear browser cache and cookies, try a different browser |
| Page does not load | Server or network issue | Check your internet connection, try again later |

### Login rate limiting

After multiple failed login attempts, you may be temporarily blocked. Wait 1 minute before trying again.

### Forgot password link not received

1. Check your spam/junk folder
2. Verify you entered the correct email address
3. Contact your Super Admin if the issue persists

## Permission Issues

### I can see a module but cannot create records

You have **View** permission but not **Create** permission. Contact your Super Admin to request Create access.

### I can see a record but cannot edit it

You have **View** permission but not **Edit** permission. Contact your Super Admin to request Edit access.

### I can see a record but the password is masked

You have **View** permission but not **Reveal** permission. Password reveal must be granted separately.

### I cannot delete a record

You lack **Delete** permission on that module. Contact your Super Admin.

### I cannot export data

You lack **Export** permission. This must be granted separately from View.

### I used to have access but now I do not

Your role or permissions may have been changed by a Super Admin. Check your **My Permissions** page to see your current effective permissions. Contact your Super Admin if you believe this is an error.

## Module Access Issues

### Module is not in my sidebar

You do not have **View** permission on that module. Contact your Super Admin to request access.

### I see the module but no records are shown

Possible causes:
- You have View permission but there are no records yet
- You have View permission but the records are all soft-deleted (check the Trashed filter)
- There is a temporary system issue — try refreshing

### I can see the Edit or Delete button but get a permission error

The buttons are shown based on your permissions, but the server may deny the action if:
- Your permissions changed after the page loaded (refresh the page)
- The record belongs to a module you no longer have access to
- There is a permission caching issue

### Bulk actions are not available

Bulk action buttons appear only if you have the required permission (Delete for bulk delete, Edit for bulk status update). If you cannot see them, you lack the permission.

## Data Issues

### A record I created is missing

Possible causes:
1. You do not have **View** permission on the module — without it, you cannot see any records, even your own
2. The record was deleted by another user
3. The record was moved to a different module

Check with your Super Admin to investigate.

### Incorrect data in a record

If you have **Edit** permission, you can correct the data yourself. If not, contact an Administrator or IT Staff member to make the correction.

### Duplicate records found

1. Verify both records are not the same entry entered twice
2. Delete the duplicate if you have Delete permission
3. If you cannot delete, contact your Super Admin

### Expiry dates are wrong

1. Edit the record if you have Edit permission
2. Update the expiry date
3. The Expiry Tracker will automatically recalculate notifications

## Performance Issues

### Pages load slowly

Possible causes:
- Large number of records in the module
- Network connectivity issues
- Server load

Try:
- Using the search bar instead of browsing
- Applying filters to reduce results
- Refreshing the page after a few seconds

### Dashboard widgets are empty

Possible causes:
- You may not have access to the underlying modules
- The widget may have a loading error — try refreshing
- Some widgets (Server Health, SMTP Status) are Super Admin only

### Search returns no results

- Ensure your search term is at least 2 characters
- Check that you have View permission on the modules you are searching
- Try different search terms

## Browser Issues

### Page layout is broken

- Try refreshing the page (Ctrl+F5 for hard refresh)
- Clear your browser cache
- Try a different browser
- Ensure your browser is up to date

### Buttons or links do not work

- Try refreshing the page
- Check for JavaScript errors in your browser console
- Try a different browser

### Session expired unexpectedly

- You may have been logged out due to inactivity
- Simply log in again
- Check "Remember me" on login to stay logged in longer

---

## If None of These Help

Contact your **Super Admin** with:
1. Your account email
2. The exact error message or behavior
3. What you were trying to do
4. When the issue started

---

## Related Modules

- [FAQ](07_FAQ.md)
- [Quick Start Guide](01_QUICK_START_GUIDE.md)
- [Super Admin Guide](02_SUPER_ADMIN_GUIDE.md)

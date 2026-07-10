# OpsPilot SMTP & Expiry Notification Guide

## Overview

This guide explains how email notifications work in OpsPilot. There are two parts:

1. **SMTP Profile** — the email sender (who sends the email)
2. **Expiry Tracker** — the reminder system (what to remind about, who to notify)

---

## SMTP Profile vs Expiry Tracker — The Difference

| | SMTP Profile | Expiry Tracker |
|---|---|---|
| **Analogy** | The post office | The reminder you set on your calendar |
| **Purpose** | Defines an email server that can send emails | Tracks when something expires and sends reminders |
| **What it stores** | SMTP server address, port, username, password | What expires, when, who to notify |
| **How many you need** | One or more (can have multiple) | One for every service that needs renewal reminders |
| **Who creates** | Super Admin | Any user with permission |
| **Can it send alone?** | No — needs an Expiry Tracker to trigger it | No — needs an SMTP Profile to send through |

### Simple explanation:

**SMTP Profile = Your email sender.**
Like having a Gmail account or Office 365 account that can send emails.

**Expiry Tracker = What you want to be reminded about.**
Like: "Domain example.com expires on Jan 1. Remind me 30 days before."

You need BOTH for reminders to work.

---

## Part 1: SMTP Profiles

### What You Need Before Creating

Get these from your email provider:

- **SMTP Host** — e.g., smtp.gmail.com, smtp.office365.com
- **SMTP Port** — usually 587 (TLS) or 465 (SSL)
- **SMTP Username** — usually your full email address
- **SMTP Password** — your email password or app password

**Important for Gmail:** Use an App Password, not your regular Gmail password. Generate one from your Google Account → Security → App Passwords.

**Important for Office 365:** You may need to enable SMTP Authentication or use an app password.

### Creating an SMTP Profile

1. Go to **Administration → SMTP Profiles**
2. Click **Create SMTP Profile**
3. Fill in:
   - **Name:** Anything descriptive (e.g., "Company Gmail")
   - **Sender Name:** What recipients see (e.g., "OpsPilot Notifications")
   - **Sender Email:** The email address that appears in From field
   - **Reply-To Email:** (optional) Where replies go
   - **SMTP Host:** Your SMTP server
   - **SMTP Port:** Usually 587
   - **Encryption:** tls or ssl
   - **Username:** Usually the email address
   - **Password:** SMTP password
4. Click **Save**

### Testing an SMTP Profile

1. Find the profile in the list
2. Click **Test**
3. The system sends a test email to the Sender Email address
4. Check your inbox — if you receive the email, the profile works

### Setting a Default Profile

- Click **Set as Default** on the profile
- New Expiry Trackers will automatically use this profile
- You can change it per tracker later

### Troubleshooting SMTP

| Problem | Likely Cause | Fix |
|---------|-------------|-----|
| Test email not received | Wrong password | Check SMTP password. For Gmail, use App Password. |
| Connection refused | Wrong port or host | Check with your email provider. |
| "Authentication failed" | Wrong username/password | Double-check credentials. |
| "Connection timed out" | Firewall blocking port | Allow port 587 or 465 outbound. |
| Email goes to spam | Missing SPF/DKIM | Configure SPF and DKIM for your domain. |

---

## Part 2: Expiry Trackers

### What is an Expiry Tracker?

It is a reminder record. You create one for anything that has an expiry date:

- Domain renewal
- Hosting plan renewal
- VPS subscription
- SSL certificate
- SaaS subscription
- Any service with a fixed expiry

### How Notifications Work

```
Every night at midnight (or configured time):
  1. System checks all Active Expiry Trackers
  2. Compares today's date with Expiry Date and Notify Days
  3. If today is exactly (Expiry Date - Notify Days), it sends an email
  4. Email is sent via the selected SMTP Profile
  5. Notification history is recorded
```

### Example:

```
Expiry Tracker: "example.com Domain"
Expiry Date: 2028-01-01
Notify Days: 30, 7, 1

The system sends:
  - 2027-12-02 → "30 days until expiry"
  - 2027-12-25 → "7 days until expiry"
  - 2027-12-31 → "1 day until expiry"
  - 2028-01-01 → Email on expiry day (if "Notify on Expiry Day" is on)
```

### Creating an Expiry Tracker

1. Go to **Infrastructure → Renewals**
2. Click **Create**
3. Fill in required fields:
   - **Name:** What you are tracking (e.g., "example.com Renewal")
   - **Expiry Date:** When it expires
4. Optional but important fields:
   - **Cost:** Monthly cost for reports
   - **Status:** Active, Expired, Pending Renewal, Cancelled
   - **SMTP Profile:** Which email server to use (only active profiles show)
   - **Email Notifications:** Must be ON for reminders to work
   - **Notify Days:** Check the days you want reminders (1, 7, 15, 30 days before)
   - **Notify on Expiry Day:** Send email on the exact expiry date
   - **Notify Assigned User:** Send to the user who created the tracker
   - **Notify Admins:** Send to all admins
   - **Notify Custom Emails:** Enter additional email addresses
5. Click **Save**

### Testing an Expiry Tracker

1. Open the tracker
2. Click **Test Email**
3. The system sends a preview email to the configured recipients
4. Check your inbox

### Sending a Manual Reminder

1. Open the tracker
2. Click **Send Reminder Now**
3. The system sends the reminder immediately, regardless of schedule

### Viewing Notification History

1. Open the tracker
2. Click **Notification History**
3. See all past notifications, their status, and timestamps

---

## Automatic Scheduling

The system runs a command nightly:

```
php artisan check-expiries
```

This command:
1. Finds all Expiry Trackers that need notifications today
2. Sends emails via their configured SMTP Profile
3. Updates the notification history
4. Logs any failures

### Setting up the scheduler (cron):

Add this to your server's crontab:

```
* * * * * cd /path-to-your-app && php artisan schedule:run >> /dev/null 2>&1
```

The `check-expiries` command runs as part of the Laravel schedule (usually once per minute, but the expiry check itself runs once daily).

---

## Dashboard Monitoring

The **Dashboard → Renewals Widget** shows:

- Total trackers
- Manual sends today
- Automatic sends today
- Failed sends today
- Upcoming renewals (next 30 days)
- Notifications sent in last 30 days
- Notifications failed in last 30 days
- Total SMTP profiles
- Renewal expiry chart

Check this widget daily to catch any failed notifications.

---

## Common Mistakes

| Mistake | Result | Fix |
|---------|--------|-----|
| No SMTP Profile selected | Notifications never send | Edit tracker → select SMTP Profile |
| SMTP Profile inactive | Notifications fail | Go to SMTP Profiles → toggle Active |
| Email Notifications disabled | Notifications never send | Edit tracker → enable Email Notifications |
| Expiry Date in the past | Immediate notification | Set date in the future |
| Wrong Notify Days | Reminder comes too early or too late | Edit tracker → adjust days |
| Not checking dashboard | Miss failed notifications | Check Renewals widget daily |
| Forgot to set cron | Nothing runs automatically | Set up the cron job |

---

## Full Workflow: Setting Up Email Notifications

```
Step 1: Create SMTP Profile
  → Go to SMTP Profiles → Create
  → Enter your email server details
  → Click Test to verify
  → Set as Default

Step 2: Create Expiry Tracker
  → Go to Renewals → Create
  → Enter what expires and when
  → Select the SMTP Profile
  → Enable Email Notifications
  → Set Notify Days
  → Choose recipients

Step 3: Test
  → Click Test Email on the tracker
  → Verify email is received

Step 4: Verify Schedule
  → Ensure cron is running
  → Check Dashboard next day for sent notifications
```

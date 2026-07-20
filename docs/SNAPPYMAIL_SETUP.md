# SnappyMail Setup Checklist

## Step 1 — Open SnappyMail

```
https://yourdomain/webmail/
```

First-run wizard will appear. Create the SnappyMail admin account (this is separate from Laravel users).

## Step 2 — Configure storage

Ensure `data/` is writable:

```bash
chown -R www-data:www-data public/webmail/data
chmod -R 775 public/webmail/data
```

SnappyMail will create config JSON files under `public/webmail/data/_data_/`.

## Step 3 — Enable custom plugin

In SnappyMail admin UI → **Plugins** → enable **roundcube_portal_auth**.

This plugin handles Laravel token auto-login (POSTs credentials into iframe).

## Step 4 — Set cron password

SnappyMail admin UI → **General** → **Cron password**.

Generate a random string:

```bash
openssl rand -hex 16
```

Used for background tasks (IMAP sync, cleanup, etc.).

## Step 5 — Verify auto-login

From Laravel dashboard, click **Open Webmail**.
- Laravel generates an opaque single-use token
- Token is POSTed into the iframe
- Plugin calls `/webmail/resolve?t=<token>` to resolve credentials
- User is logged in without manual password entry

## Step 6 — Verify config files

After setup, confirm:

```bash
ls -la public/webmail/data/_data_/
```

Should contain JSON files (`config.json`, `application.json`, etc.).

## Evidence to capture

| Evidence | Command / Location |
|---|---|
| `data/_data_/` files | `ls -la public/webmail/data/_data_/` |
| Plugin enabled | SnappyMail admin → Plugins → screenshot |
| Auto-login works | Screenshot of webmail loaded via iframe from Laravel |
| File permissions | `ls -ld public/webmail/data` |

## Success criteria

- [ ] Admin can log into SnappyMail config UI
- [ ] Plugin `roundcube_portal_auth` shows as enabled
- [ ] Laravel auto-login works end-to-end
- [ ] `data/` contains config JSON files and is writable

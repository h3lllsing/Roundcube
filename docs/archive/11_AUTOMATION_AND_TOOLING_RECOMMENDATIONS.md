# AUTOMATION & TOOLING RECOMMENDATIONS

---

## 11.1 IMMEDIATE (BEFORE DEPLOY)

### R-01: `.env` Hygiene Script

Create a pre-deploy script that validates `.env` has production-safe values:
```bash
# scripts/validate-env.sh
if grep -q "APP_ENV=local" .env; then echo "FAIL: APP_ENV=local"; exit 1; fi
if grep -q "APP_DEBUG=true" .env; then echo "FAIL: APP_DEBUG=true"; exit 1; fi
```

### R-02: Post-Deploy Cache Script

Add to `composer.json`:
```json
"scripts": {
    "post-deploy": [
        "@php artisan optimize",
        "@php artisan route:cache",
        "@php artisan config:cache",
        "@php artisan view:cache",
        "@php artisan event:cache",
        "@php artisan storage:link"
    ]
}
```

### R-03: Platform Requirements

Add to `composer.json:require`:
```json
"ext-ctype": "*",
"ext-curl": "*",
"ext-fileinfo": "*",
"ext-mbstring": "*",
"ext-filter": "*",
"ext-pdo_mysql": "*",
"ext-tokenizer": "*",
"ext-xml": "*"
```

---

## 11.2 SHORT-TERM (FIRST SPRINT)

### R-04: PHPStan Configuration

Create `phpstan.neon`:
```yaml
parameters:
    level: 1
    paths:
        - app/
        - config/
    excludePaths:
        - app/helpers.php  # if needed
    ignoreErrors:
        - '#Unsafe usage of new static\(\)#'
    checkMissingIterableValueType: false
```

### R-05: Pint Configuration

Create `pint.json`:
```json
{
    "preset": "laravel",
    "rules": {
        "simplified_null_return": true,
        "no_unused_imports": true
    }
}
```

### R-06: CI Pipeline Enhancement

Extend GitHub Actions with:
```yaml
- name: Run static analysis
  run: vendor/bin/phpstan analyse --level 1

- name: Check code style
  run: vendor/bin/pint --test

- name: Check coverage threshold
  run: vendor/bin/phpunit --coverage-text --min-coverage=90
```

---

## 11.3 MEDIUM-TERM (WITHIN 2 SPRINTS)

### R-07: Deployment Runbook

Create `DEPLOY.md` with step-by-step:
1. Pre-deploy validation (env check, backup DB)
2. File transfer (rsync/git pull on cPanel)
3. Composer install (no-dev)
4. Vite build
5. Database migration
6. Post-deploy caching
7. Permission setup
8. Cron job setup
9. Smoke tests (curl endpoints)
10. Rollback procedure

### R-08: Monitoring & Observability

- Set up UptimeRobot (free tier) for HTTPS monitoring
- Configure `LOG_CHANNEL=daily` with 30-day retention
- Add Slack notification for failed queues (when worker is implemented)

### R-09: Automated Smoke Tests

```bash
# scripts/smoke-test.sh
URL="https://opspilot.whizzweb.net"
curl -f "$URL/login" || exit 1
curl -f "$URL/api/health" || exit 1  # add health endpoint
echo "Smoke tests PASSED"
```

---

## 11.4 TOOLING SUMMARY

| Tool | Status | Action |
|------|--------|--------|
| PHPStan | Missing config | Create `phpstan.neon` |
| Pint | Missing config | Create `pint.json` |
| Pre-deploy validator | Missing | Create `scripts/validate-env.sh` |
| Post-deploy script | Missing | Add to `composer.json` |
| Smoke tests | Missing | Create `scripts/smoke-test.sh` |
| DEPLOY.md | Missing | Create runbook |
| Uptime monitoring | Not configured | Set up UptimeRobot |
| Error tracking | Not configured | Consider Sentry (free tier) |
| Extended CI | Partial | Add PHPStan + Pint + coverage |

---

## 11.5 EFFORT ESTIMATES

| Task | Effort | Priority |
|------|--------|----------|
| R-01: `.env` validator | 30 min | 🔴 Before deploy |
| R-02: Post-deploy script | 15 min | 🔴 Before deploy |
| R-03: Platform requirements | 10 min | 🔴 Before deploy |
| R-04: PHPStan config | 1 hr | 🟡 Sprint 1 |
| R-05: Pint config | 30 min | 🟡 Sprint 1 |
| R-06: CI enhancement | 2 hr | 🟡 Sprint 1 |
| R-07: DEPLOY.md | 2 hr | 🟡 Sprint 1 |
| R-08: Monitoring | 1 hr | 🟢 Sprint 2 |
| R-09: Smoke tests | 1 hr | 🟢 Sprint 2 |

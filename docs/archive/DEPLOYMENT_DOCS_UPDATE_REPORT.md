# DEPLOYMENT DOCS UPDATE REPORT

## Objective

Remove all `php artisan migrate --seed` and `php artisan db:seed` instructions from production deployment documentation. Production command is now exclusively `php artisan migrate --force`.

---

## Files Updated

### 1. `CPANEL_DEPLOYMENT_GUIDE.md` (line 267)

**Before:**
```
# 4. (Optional) Seed initial data if first deployment
php artisan db:seed --class=DatabaseSeeder --force
```

**After:**
```
# 4. (Optional) Seed initial data if first deployment
# WARNING: This will create demo admin accounts and test data. Do NOT run on a production database that already has real data.
# php artisan db:seed --class=DatabaseSeeder --force
```

---

### 2. `PRODUCTION_CONFIGURATION_GUIDE.md` (lines 262-266)

**Before:**
```
If first deployment with seed data:
```bash
php artisan db:seed --class=DatabaseSeeder --force
php artisan db:seed --class=DemoDataSeeder --force    # optional demo data
```
```

**After:**
```
# WARNING: Seeding is for local development only.
# DemoDataSeeder is BLOCKED in production environments.
# If you require initial data, write a custom production-safe seeder.
```bash
php artisan migrate --force
```
```

---

### 3. `DEPLOYMENT_GUIDE.md` (line 44)

**Before:**
```
php artisan migrate --seed
```

**After:**
```
php artisan migrate --force
```

---

### 4. `OPS_PILOT_V1_RELEASE_CANDIDATE_SIGNOFF.md` (line 60)

**Before:**
```
4. Run `php artisan migrate --seed`
```

**After:**
```
4. Run `php artisan migrate --force` (do NOT use --seed on production)
```

---

### 5. `FINAL_DEPLOYMENT_GATE_REPORT.md` (line 46)

**Before:**
```
php artisan migrate --seed
```

**After:**
```
php artisan migrate --force   # WARNING: --seed creates demo data. Never seed production.
```

---

## Not Changed (Intentionally)

The following files retain `--seed` because they are LOCAL DEVELOPMENT guides:

| File | Reason |
|------|--------|
| `README.md` | Local Quick Start — seeding is correct for development |
| `INSTALLATION.md` | Installation guide — local setup only |
| `CONTRIBUTING.md` | Contributor guide — local development only |

These development docs use `--seed` in the context of creating demo data for a fresh local environment. This is the intended and correct behavior.

---

## Production Deploy Command

**The ONLY production database command:**

```bash
php artisan migrate --force
```

No `--seed`. No `db:seed`. No demo data.

# SEEDER GUARD FIX REPORT

## Change Applied

**File:** `database/seeders/DatabaseSeeder.php`
**Line:** 33

### Before
```php
if (! app()->environment('testing')) {
    $this->call(DemoDataSeeder::class);
}
```

### After
```php
if (! app()->environment('testing', 'production')) {
    $this->call(DemoDataSeeder::class);
}
```

## What This Changes

| Environment | Before | After |
|-------------|--------|-------|
| `local` (dev) | ✅ Runs DemoDataSeeder | ✅ Runs DemoDataSeeder |
| `testing` | ❌ Skipped | ❌ Skipped |
| `staging` | ✅ Runs DemoDataSeeder | ✅ Runs DemoDataSeeder |
| `production` | ✅ **Ran DemoDataSeeder** | ❌ **Skipped** |

## Why

The original guard used a blocklist pattern (deny only `testing`). This allowed `DemoDataSeeder` to execute in `production` if `php artisan migrate --seed` or `php artisan db:seed` was ever run on a production server — whether intentionally or by accident.

## What DemoDataSeeder Creates

From `database/seeders/DemoDataSeeder.php`:
- Admin user `admin@tyro.project` with known password
- 3 service providers (DigitalOcean, Namecheap, Google Workspace)
- 2 hosting entries with passwords
- 2 VPS entries with IPs and passwords
- 2 domain entries with DNS servers
- 2 VoIP entries with phone numbers and extension passwords
- 2 domain emails with passwords
- 2 other services (Slack, GitHub Enterprise) with passwords
- 2 expiry trackers
- 2 vault entries (AWS root credentials, GitHub PAT)

## Risk Mitigated

**HIGH severity** — Production data contamination, demo admin account security exposure, known passwords in production database.

## Test Impact

**Zero.** Tests run in `testing` environment which was already excluded. The fix only affects `production`.

$e2eDb = "database\e2e.sqlite"
$envFile = ".env.e2e"

# Create .env.e2e
@"
APP_ENV=testing
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_KEY=base64:EDHUMgHIRxgyyrLpa+kQ1Y6joN++TeEKOo5R0M3fJog=
DB_CONNECTION=sqlite
DB_DATABASE=$e2eDb
SESSION_DRIVER=file
"@ | Set-Content -Path $envFile -Encoding UTF8

# Remove old database
if (Test-Path $e2eDb) { Remove-Item $e2eDb }

# Run migrations and seed
php artisan migrate --seed --env=testing --force
if ($LASTEXITCODE -ne 0) {
    Write-Error "Migration failed"
    exit 1
}

Write-Host "E2E database seeded successfully"

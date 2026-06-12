$e2eDb = get-content -path ".env.e2e" | Select-String "DB_DATABASE" | ForEach-Object { $_ -split "=" | Select-Object -Last 1 }

if (Test-Path $e2eDb) { Remove-Item $e2eDb }

php artisan migrate --seed --env=e2e --force 2>&1
if ($LASTEXITCODE -ne 0) { Write-Error "Migration failed"; exit 1 }

php artisan serve --port=8000 --env=e2e 2>&1

@echo off
setlocal enabledelayedexpansion

REM ============================================================
REM deploy.bat — OpsPilot Production Build Script (Windows/XAMPP)
REM Prepares a deployment-ready ZIP archive. Run from project root.
REM ============================================================

echo ==^> Step 1: Install dependencies (no dev)
call composer install --no-dev --optimize-autoloader
if %ERRORLEVEL% neq 0 exit /b %ERRORLEVEL%

echo ==^> Step 2: Build frontend assets
call npm ci && call npm run build
if %ERRORLEVEL% neq 0 exit /b %ERRORLEVEL%

echo ==^> Step 3: Clear caches
call php artisan optimize:clear
if %ERRORLEVEL% neq 0 exit /b %ERRORLEVEL%

echo ==^> Step 4: Cache for production
call php artisan config:cache
call php artisan route:cache
call php artisan view:cache
call php artisan event:cache

echo ==^> Step 5: Create storage link
call php artisan storage:link --force

echo ==^> Step 6: Create deploy archive
set TIMESTAMP=%date:~10,4%%date:~4,2%%date:~7,2%_%time:~0,2%%time:~3,2%%time:~6,2%
set TIMESTAMP=!TIMESTAMP: =0!
set DEPLOY_DIR=deploy_%TIMESTAMP%

REM Use PowerShell to zip excluding dev files
powershell -NoProfile -Command ^
    $exclude = @('.env', '_can_delete', 'coverage', 'node_modules', '.git', '.github', ^
        'tests', 'docs', 'scripts', 'e2e', 'deploy_*', 'Dockerfile*', 'docker-compose*', ^
        '.phpunit*', 'phpunit*', 'phpstan*', '.env.*', '.editorconfig', ^
        'resources\js', 'resources\css'); ^
    Get-ChildItem -Path '.' -Exclude $exclude ^
    | Compress-Archive -DestinationPath "%DEPLOY_DIR%.zip" -CompressionLevel Optimal

if %ERRORLEVEL% neq 0 exit /b %ERRORLEVEL%

echo.
echo ============================================
echo   BUILD COMPLETE
echo ============================================
echo   Archive: %DEPLOY_DIR%.zip
echo.
echo   Next steps:
echo   1. Upload %DEPLOY_DIR%.zip to your server via FTP/cPanel
echo   2. Extract on server
echo   3. Copy public/ contents to public_html/
echo   4. Copy .env.example to .env and edit:
echo      - Set APP_ENV=production, APP_DEBUG=false
echo      - Set CACHE_STORE=redis (or keep file for single-server)
echo   5. Run: php artisan key:generate
echo   6. Run: php artisan migrate --force
echo   7. Set permissions: chmod -R 775 storage bootstrap/cache
echo   8. Set up cron: * * * * * php artisan schedule:run
echo   9. Set up queue worker (Supervisord/Forge): php artisan queue:work
echo ============================================

endlocal

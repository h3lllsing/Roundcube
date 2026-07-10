@echo off
setlocal enabledelayedexpansion

set BASE_URL=%1
if "%BASE_URL%"=="" set BASE_URL=https://opspilot.whizzweb.net

echo ========================================
echo   Smoke Test - OpsPilot Portal
echo   Target: %BASE_URL%
echo ========================================
echo.

set FAILED=0

call :check "%BASE_URL%/api/health" "Health check"
call :check "%BASE_URL%/login" "Login page"
call :check "%BASE_URL%/api/documentation" "Swagger docs"

echo.
if %FAILED% neq 0 (
    echo [FAIL] %FAILED% check(s) failed.
    exit /b 1
) else (
    echo [PASS] All smoke tests passed.
    exit /b 0
)

:check
set URL=%~1
set LABEL=%~2
curl -sf -o nul -w "%%{http_code}" "%URL%" > "%TEMP%\smoke_status.txt" 2>nul
set /p STATUS=<"%TEMP%\smoke_status.txt"
if "%STATUS%"=="200" (
    echo [PASS] %LABEL% (%STATUS%)
) else if "%STATUS%"=="401" (
    echo [PASS] %LABEL% (%STATUS% - unauthenticated, expected)
) else if "%STATUS%"=="302" (
    echo [PASS] %LABEL% (%STATUS% - redirect, expected)
) else (
    echo [FAIL] %LABEL% (%STATUS%)
    set /a FAILED=FAILED+1
)
exit /b 0

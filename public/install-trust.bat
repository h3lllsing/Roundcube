@echo off
title Install SSL Trust - 10.10.10.24
echo ===========================================
echo  Installing Trust Certificate for 10.10.10.24
echo ===========================================
echo.
echo This will install the certificate to Trusted Root store.
echo Run as Administrator!
echo.

certutil -f -addstore Root "https://10.10.10.24/mkcert-ca.cer" >nul 2>&1
if %ERRORLEVEL% equ 0 (
    echo Success! Certificate installed.
    echo Green lock will work now. Restart your browser.
) else (
    echo Trying alternate method...
    
    setlocal enabledelayedexpansion
    set "tmpFile=%TEMP%\mkcert-ca.cer"
    
    certutil -urlcache -f "https://10.10.10.24/mkcert-ca.cer" "%tmpFile%" >nul 2>&1
    if exist "%tmpFile%" (
        certutil -addstore Root "%tmpFile%" >nul 2>&1
        if !ERRORLEVEL! equ 0 (
            echo Success! Certificate installed.
            echo Green lock will work now. Restart your browser.
        ) else (
            echo Failed. Run as Administrator and try again.
        )
        del "%tmpFile%" >nul 2>&1
    ) else (
        echo Could not download certificate.
        echo Open https://10.10.10.24/mkcert-ca.cer in browser and install manually.
    )
)

echo.
pause

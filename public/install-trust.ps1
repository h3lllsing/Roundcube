Write-Host "Installing SSL Trust Certificate for 10.10.10.24..." -ForegroundColor Cyan
Write-Host "Run as Administrator!" -ForegroundColor Yellow
Write-Host ""

$tmpFile = "$env:TEMP\mkcert-ca.cer"

try {
    [Net.ServicePointManager]::ServerCertificateValidationCallback = { $true }
    $wc = New-Object System.Net.WebClient
    $wc.DownloadFile("https://10.10.10.24/mkcert-ca.cer", $tmpFile)
    $wc.Dispose()

    if (Test-Path $tmpFile) {
        $cert = Import-Certificate -FilePath $tmpFile -CertStoreLocation Cert:\LocalMachine\Root
        if ($cert) {
            Write-Host "Success! Certificate installed." -ForegroundColor Green
            Write-Host "Restart your browser. HTTPS://10.10.10.24 will be trusted." -ForegroundColor Green
        }
        Remove-Item $tmpFile -Force
    }
} catch {
    Write-Host "Failed: $($_.Exception.Message)" -ForegroundColor Red
    Write-Host "Alternative: Open https://10.10.10.24/mkcert-ca.cer in browser, download and install manually." -ForegroundColor Yellow
}

Read-Host "`nPress Enter to exit"

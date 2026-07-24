# Install IMAP IDLE Worker as NSSM Windows Service
# Run as Administrator on the server
# Usage: .\scripts\install-worker-service.ps1

$projectRoot = "C:\roundcube"
$nssm = "$projectRoot\scripts\nssm.exe"
$php = "C:\php\php.exe"
$script = "$projectRoot\scripts\imap-idle-worker.php"
$serviceName = "ImapIdleWorker"
$logDir = "$projectRoot\storage\app\webmail\logs"

if (-not (Test-Path $nssm)) {
    Write-Error "NSSM not found at $nssm"
    exit 1
}

if (-not (Test-Path $php)) {
    Write-Error "PHP not found at $php"
    exit 1
}

# Stop and remove existing service if present
& $nssm stop $serviceName 2>$null
& $nssm remove $serviceName confirm 2>$null
Start-Sleep -Seconds 2

# Create log directory
New-Item -ItemType Directory -Force -Path $logDir | Out-Null

# Install service
& $nssm install $serviceName $php $script
& $nssm set $serviceName AppDirectory $projectRoot
& $nssm set $serviceName DisplayName "IMAP IDLE Worker — RoundCube Portal"
& $nssm set $serviceName Description "Monitors all IMAP accounts for new mail in real-time via IDLE. Auto-restarts on failure."
& $nssm set $serviceName Start SERVICE_AUTO_START
& $nssm set $serviceName AppStdout "$logDir\nssm-stdout.log"
& $nssm set $serviceName AppStderr "$logDir\nssm-stderr.log"
& $nssm set $serviceName AppRotateFiles 1
& $nssm set $serviceName AppRotateOnline 1
& $nssm set $serviceName AppRotateSeconds 86400
& $nssm set $serviceName AppThrottle 1000
& $nssm set $serviceName AppRestartDelay 5000

# Give it time to register
Start-Sleep -Seconds 2

# Start service
& $nssm start $serviceName

Write-Host "=== Service '$serviceName' installed and started ==="
Write-Host "Manual commands:"
Write-Host "  Stop:    & `"$nssm`" stop $serviceName"
Write-Host "  Start:   & `"$nssm`" start $serviceName"
Write-Host "  Restart: & `"$nssm`" restart $serviceName"
Write-Host "  Remove:  & `"$nssm`" remove $serviceName confirm"

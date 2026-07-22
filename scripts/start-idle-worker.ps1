# PowerShell script to start IMAP IDLE Worker
# This script starts the worker hidden (no console window)
# For Task Scheduler: use this as the action

$phpExe = "C:\xampp\php\php.exe"
$workerScript = "C:\xampp\htdocs\roundcube\scripts\imap-idle-worker.php"

# Check if worker is already running
$worker = Get-Process php -ErrorAction SilentlyContinue | Where-Object {
    $_.CommandLine -match "imap-idle-worker"
}

if ($worker) {
    Write-Output "Worker is already running (PID: $($worker.Id))"
    exit 0
}

# Start worker hidden
Start-Process -FilePath $phpExe -ArgumentList $workerScript -WindowStyle Hidden

Write-Output "Worker started"

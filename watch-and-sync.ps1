param([switch]$Log)

$localRoot = "C:\xampp\htdocs\roundcube"
$ftpHost   = "ftp://10.10.10.24"
$user      = "ftpuser"
$pass      = "ftp@1234"

$ignore = @(
    '/node_modules/', '/vendor/', '/.git/', '/storage/',
    '/bootstrap/cache/', '/.env', '/sync-to-server.ps1',
    '/watch-and-sync.ps1'
)

$changed = @{}
$timer = $null

function Upload-File($path) {
    $rel = $path.Substring($localRoot.Length).TrimStart('\').Replace('\', '/')
    foreach ($pat in $ignore) { if ($rel -like "*$pat*") { return } }
    if ($Log) { Write-Host "  PUT $rel" -ForegroundColor Gray }
    curl.exe -s -T $path -u "$user`:$pass" "$ftpHost/$rel" --ftp-create-dirs > $null
}

function Flush-Changes {
    $timer = $null
    $batch = @($changed.Keys)
    $changed.Clear()
    foreach ($p in $batch) { Upload-File $p }
    if ($Log -and $batch.Count -gt 0) { Write-Host "  ($($batch.Count) files)" -ForegroundColor Cyan }
}

function OnChange {
    $changed[(Get-Date -Format 'HH:mm:ss')] = $Event.SourceEventArgs.FullPath
    if ($timer) { $timer.Dispose() }
    $timer = [System.Timers.Timer]::new(2000)
    $timer.AutoReset = $false
    Register-ObjectEvent $timer Elapsed -Action { Flush-Changes } > $null
    $timer.Start()
}

$watcher = New-Object System.IO.FileSystemWatcher
$watcher.Path = $localRoot
$watcher.IncludeSubdirectories = $true
$watcher.NotifyFilter = [System.IO.NotifyFilters]::FileName -bor [System.IO.NotifyFilters]::LastWrite
$watcher.Filter = '*.*'

Register-ObjectEvent $watcher 'Changed' -Action { OnChange } > $null
Register-ObjectEvent $watcher 'Created' -Action { OnChange } > $null
Register-ObjectEvent $watcher 'Renamed' -Action { OnChange } > $null

Write-Host "Watching $localRoot ..." -ForegroundColor Green
Write-Host "Save a file = FTP upload in 2 seconds" -ForegroundColor Yellow

while ($true) { Start-Sleep -Seconds 1 }

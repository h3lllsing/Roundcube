$localRoot = "C:\xampp\htdocs\roundcube"
$ftpHost   = "ftp://10.10.10.24"
$user      = "ftpuser"
$pass      = "ftp@1234"

$ignore = @(
    '\node_modules\',
    '\vendor\',
    '\.git\',
    '\storage\',
    '\bootstrap\cache\',
    '\.env',
    '\sync-to-server.ps1',
    '\watch-and-sync.ps1'
)

Write-Host "Syncing $localRoot -> $ftpHost ..." -ForegroundColor Cyan

Get-ChildItem $localRoot -Recurse -File | ForEach-Object {
    $rel = $_.FullName.Substring($localRoot.Length).TrimStart('\').Replace('\', '/')
    $skip = $false
    foreach ($pat in $ignore) { if ($rel -like "*$pat*") { $skip = $true; break } }
    if ($skip) { return }

    $remoteUrl = "$ftpHost/$rel"
    Write-Host "  PUT $rel" -ForegroundColor Gray
    curl.exe -s -T $_.FullName -u "$user`:$pass" "$remoteUrl" --ftp-create-dirs
    if ($LASTEXITCODE -ne 0) { Write-Host "    FAILED $rel" -ForegroundColor Red }
}

Write-Host "Done!" -ForegroundColor Green

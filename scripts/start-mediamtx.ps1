$root = Split-Path -Parent $PSScriptRoot
$toolDir = Join-Path $root 'storage\tools\mediamtx'
$configPath = Join-Path $toolDir 'sexylua-mediamtx.yml'
$logDir = Join-Path $toolDir 'logs'
$stdoutPath = Join-Path $logDir 'mediamtx.out.log'
$stderrPath = Join-Path $logDir 'mediamtx.err.log'

New-Item -ItemType Directory -Force -Path $logDir | Out-Null
New-Item -ItemType Directory -Force -Path (Join-Path $root 'public\uploads\live\recordings') | Out-Null

$existing = Get-CimInstance Win32_Process -Filter "Name = 'mediamtx.exe'" | Where-Object {
    $_.CommandLine -like "*$configPath*"
}

if ($existing) {
    Write-Output 'MediaMTX local ja esta em execucao.'
    return
}

Start-Process -FilePath (Join-Path $toolDir 'mediamtx.exe') `
    -ArgumentList @('"' + $configPath + '"') `
    -WorkingDirectory $toolDir `
    -WindowStyle Hidden `
    -RedirectStandardOutput $stdoutPath `
    -RedirectStandardError $stderrPath

Write-Output 'MediaMTX local iniciado.'

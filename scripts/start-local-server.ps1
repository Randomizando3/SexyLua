$root = Split-Path -Parent $PSScriptRoot
$publicRoot = Join-Path $root 'public'
$php = 'C:\php\php.exe'
$port = 8089
$logDir = Join-Path $root 'storage\logs'
$stdoutPath = Join-Path $logDir 'php-server.out.log'
$stderrPath = Join-Path $logDir 'php-server.err.log'

New-Item -ItemType Directory -Force -Path $logDir | Out-Null

$existing = Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" | Where-Object {
    $_.CommandLine -like '*127.0.0.1:8089*index.php*'
}

if ($existing) {
    Write-Output 'Servidor local ja esta em execucao.'
    return
}

Get-CimInstance Win32_Process -Filter "Name = 'php.exe'" | Where-Object {
    $_.CommandLine -like '*127.0.0.1:8089*'
} | ForEach-Object {
    Stop-Process -Id $_.ProcessId -Force -ErrorAction SilentlyContinue
}

Start-Process -FilePath $php `
    -ArgumentList @('-S', "127.0.0.1:$port", 'index.php') `
    -WorkingDirectory $publicRoot `
    -WindowStyle Hidden `
    -RedirectStandardOutput $stdoutPath `
    -RedirectStandardError $stderrPath

Write-Output 'Servidor local iniciado em 127.0.0.1:8089.'

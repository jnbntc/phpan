Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

$projectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $projectRoot

Write-Host "Iniciando servidor en http://127.0.0.1:8000"
& php -S 127.0.0.1:8000 -t .

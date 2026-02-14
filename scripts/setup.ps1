Set-StrictMode -Version Latest
$ErrorActionPreference = "Stop"

Write-Host "Verificando PHP..."
$phpCmd = Get-Command php -ErrorAction SilentlyContinue
if (-not $phpCmd) {
    throw "PHP no esta instalado o no esta en PATH."
}

$modules = & php -m
if (-not ($modules -match "^PDO$") -or -not ($modules -match "^pdo_sqlite$")) {
    throw "Faltan extensiones requeridas: PDO y/o pdo_sqlite."
}

$projectRoot = Split-Path -Parent $PSScriptRoot
$dataDir = Join-Path $projectRoot "data"
$newDb = Join-Path $dataDir "recipes.sqlite"
$legacyDb = Join-Path $projectRoot "recipes.sqlite"

if (-not (Test-Path $dataDir)) {
    New-Item -ItemType Directory -Path $dataDir | Out-Null
}

if (-not (Test-Path $newDb)) {
    if (Test-Path $legacyDb) {
        Copy-Item $legacyDb $newDb
        Write-Host "Base de datos migrada desde recipes.sqlite a data/recipes.sqlite."
    } else {
        & php (Join-Path $PSScriptRoot "init_db.php")
        Write-Host "Base de datos inicializada en data/recipes.sqlite."
    }
} else {
    Write-Host "Base de datos ya existe en data/recipes.sqlite."
}

Write-Host ""
Write-Host "Instalacion finalizada."
Write-Host "Para iniciar el proyecto:"
Write-Host "  powershell -ExecutionPolicy Bypass -File scripts/dev.ps1"

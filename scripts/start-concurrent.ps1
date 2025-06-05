# Script de lancement alternatif utilisant concurrently
# Ce script utilise le package concurrently pour gérer les serveurs

Write-Host "[START] Demarrage des serveurs avec concurrently..." -ForegroundColor Green

# Vérifier si nous sommes dans le bon répertoire
$projectRoot = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path "$projectRoot\backend\artisan") -or -not (Test-Path "$projectRoot\frontend\package.json")) {
    Write-Host "[ERROR] Ce script doit etre execute depuis la racine du projet" -ForegroundColor Red
    exit 1
}

Write-Host "[INFO] Repertoire du projet: $projectRoot" -ForegroundColor Cyan

# Vérifier si concurrently est installé dans le backend
Set-Location "$projectRoot\backend"
if (-not (Test-Path "node_modules\concurrently")) {
    Write-Host "[ERROR] concurrently n'est pas installe" -ForegroundColor Red
    Write-Host "[INFO] Executez .\scripts\init.ps1 pour initialiser le projet" -ForegroundColor Yellow
    exit 1
}

Write-Host "[START] Lancement des serveurs en parallele..." -ForegroundColor Green
Write-Host "[BACKEND] Laravel: http://localhost:8000" -ForegroundColor Cyan
Write-Host "[FRONTEND] Vue.js: http://localhost:5173" -ForegroundColor Cyan
Write-Host "`n[INFO] Appuyez sur Ctrl+C pour arreter les serveurs`n" -ForegroundColor Yellow

# Utiliser concurrently pour lancer les deux serveurs
npx concurrently `
    --names "Laravel,Vue.js" `
    --prefix-colors "blue,green" `
    --kill-others-on-fail `
    "php artisan serve --host=127.0.0.1 --port=8000" `
    "cd `"$projectRoot\frontend`" && npm run dev"

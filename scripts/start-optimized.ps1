# Script de lancement optimisé des serveurs Laravel et Vue.js
# Ce script démarre les serveurs et affiche uniquement les informations importantes

Write-Host "[START] Demarrage des serveurs de developpement..." -ForegroundColor Green

# Vérifier si nous sommes dans le bon répertoire
$projectRoot = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path "$projectRoot\backend\artisan") -or -not (Test-Path "$projectRoot\frontend\package.json")) {
    Write-Host "[ERROR] Ce script doit etre execute depuis la racine du projet" -ForegroundColor Red
    Write-Host "[INFO] Conseil: Executez d'abord .\scripts\init.ps1 pour initialiser le projet" -ForegroundColor Yellow
    exit 1
}

Write-Host "[INFO] Repertoire du projet: $projectRoot" -ForegroundColor Cyan

# Vérifier si les dépendances sont installées
if (-not (Test-Path "$projectRoot\backend\vendor")) {
    Write-Host "[ERROR] Les dependances PHP ne sont pas installees" -ForegroundColor Red
    Write-Host "[INFO] Executez .\scripts\init.ps1 pour initialiser le projet" -ForegroundColor Yellow
    exit 1
}

if (-not (Test-Path "$projectRoot\frontend\node_modules")) {
    Write-Host "[ERROR] Les dependances Node.js du frontend ne sont pas installees" -ForegroundColor Red
    Write-Host "[INFO] Executez .\scripts\init.ps1 pour initialiser le projet" -ForegroundColor Yellow
    exit 1
}

Write-Host "[START] Lancement des serveurs en parallele..." -ForegroundColor Green
Write-Host "[BACKEND] Laravel: http://localhost:8000" -ForegroundColor Cyan
Write-Host "[FRONTEND] Vue.js: http://localhost:5173" -ForegroundColor Cyan

# Démarrer Laravel en arrière-plan
Write-Host "[LARAVEL] Demarrage du serveur Laravel..." -ForegroundColor Yellow
$laravelJob = Start-Job -ScriptBlock {
    param($ProjectPath)
    Set-Location "$ProjectPath\backend"
    php artisan serve --host=127.0.0.1 --port=8000
} -ArgumentList $projectRoot

# Attendre un peu pour que Laravel démarre
Start-Sleep -Seconds 3

# Démarrer Vue.js en arrière-plan
Write-Host "[VUE] Demarrage du serveur Vue.js..." -ForegroundColor Yellow
$vueJob = Start-Job -ScriptBlock {
    param($ProjectPath)
    Set-Location "$ProjectPath\frontend"
    npm run dev
} -ArgumentList $projectRoot

# Attendre un peu pour que Vue.js démarre
Start-Sleep -Seconds 3

Write-Host "`n[SUCCESS] Les serveurs sont demarres!" -ForegroundColor Green
Write-Host "[ACCESS] URLs d'acces:" -ForegroundColor Cyan
Write-Host "   • Backend Laravel: http://localhost:8000" -ForegroundColor White
Write-Host "   • Frontend Vue.js: http://localhost:5173" -ForegroundColor White

Write-Host "`n[INFO] Statut des serveurs:" -ForegroundColor Cyan
Write-Host "   • Laravel: $($laravelJob.State)" -ForegroundColor White
Write-Host "   • Vue.js: $($vueJob.State)" -ForegroundColor White

Write-Host "`n[INFO] Appuyez sur Ctrl+C pour arreter ce script" -ForegroundColor Yellow
Write-Host "[INFO] Les serveurs continueront de fonctionner en arriere-plan" -ForegroundColor Yellow

# Boucle simple pour vérifier le statut sans spam
try {
    $checkCount = 0
    while ($true) {
        Start-Sleep -Seconds 10
        $checkCount++
        
        # Vérifier le statut toutes les 10 secondes
        if ($laravelJob.State -eq "Failed" -or $vueJob.State -eq "Failed") {
            Write-Host "`n[ERROR] Un des serveurs a echoue!" -ForegroundColor Red
            Write-Host "   • Laravel: $($laravelJob.State)" -ForegroundColor White
            Write-Host "   • Vue.js: $($vueJob.State)" -ForegroundColor White
            break
        }
        
        # Afficher un message de statut toutes les minutes (6 * 10 secondes)
        if ($checkCount % 6 -eq 0) {
            Write-Host "[STATUS] Serveurs actifs - Laravel: $($laravelJob.State), Vue.js: $($vueJob.State)" -ForegroundColor Green
        }
    }
}
catch {
    Write-Host "`n[STOP] Arret demande par l'utilisateur..." -ForegroundColor Yellow
}
finally {
    # Nettoyer les jobs
    Write-Host "[CLEANUP] Arret des serveurs..." -ForegroundColor Yellow
    Stop-Job -Job $laravelJob, $vueJob -ErrorAction SilentlyContinue
    Remove-Job -Job $laravelJob, $vueJob -ErrorAction SilentlyContinue
    Write-Host "[OK] Serveurs arretes" -ForegroundColor Green
}

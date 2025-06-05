# Script de lancement simultané des serveurs Laravel et Vue.js
# Ce script démarre le backend Laravel et le frontend Vue.js en parallèle

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

# Fonction pour démarrer le serveur Laravel
function Start-LaravelServer {
    param($ProjectPath)
    Write-Host "[LARAVEL] Demarrage du serveur Laravel..." -ForegroundColor Yellow
    Set-Location "$ProjectPath\backend"
    php artisan serve --host=127.0.0.1 --port=8000
}

# Fonction pour démarrer le serveur Vue.js
function Start-VueServer {
    param($ProjectPath)
    Write-Host "[VUE] Demarrage du serveur Vue.js..." -ForegroundColor Yellow
    Set-Location "$ProjectPath\frontend"
    npm run dev
}

# Créer des jobs en arrière-plan pour les deux serveurs
Write-Host "[START] Lancement des serveurs en parallele..." -ForegroundColor Green
Write-Host "[BACKEND] Laravel: http://localhost:8000" -ForegroundColor Cyan
Write-Host "[FRONTEND] Vue.js: http://localhost:5173" -ForegroundColor Cyan
Write-Host "`n[INFO] Appuyez sur Ctrl+C pour arreter les serveurs`n" -ForegroundColor Yellow

# Démarrer Laravel en arrière-plan
$laravelJob = Start-Job -ScriptBlock ${function:Start-LaravelServer} -ArgumentList $projectRoot

# Démarrer Vue.js en arrière-plan  
$vueJob = Start-Job -ScriptBlock ${function:Start-VueServer} -ArgumentList $projectRoot

# Afficher les logs en temps réel
try {
    Write-Host "[INFO] Monitoring des serveurs en cours... (Ctrl+C pour arreter)" -ForegroundColor Yellow
    
    while ($true) {
        # Afficher les nouvelles sorties de Laravel (sans -Keep pour éviter la répétition)
        $laravelOutput = Receive-Job -Job $laravelJob
        if ($laravelOutput) {
            $laravelOutput | ForEach-Object { Write-Host "[Laravel] $_" -ForegroundColor Blue }
        }
        
        # Afficher les nouvelles sorties de Vue.js (sans -Keep pour éviter la répétition)
        $vueOutput = Receive-Job -Job $vueJob
        if ($vueOutput) {
            $vueOutput | ForEach-Object { Write-Host "[Vue.js] $_" -ForegroundColor Green }
        }
        
        # Vérifier si les jobs sont toujours en cours d'exécution
        if ($laravelJob.State -eq "Failed" -or $vueJob.State -eq "Failed") {
            Write-Host "[ERROR] Un des serveurs a echoue" -ForegroundColor Red
            break
        }
        
        # Vérifier si les jobs sont terminés
        if ($laravelJob.State -eq "Completed" -and $vueJob.State -eq "Completed") {
            Write-Host "[INFO] Tous les serveurs se sont arretes" -ForegroundColor Yellow
            break
        }
        
        Start-Sleep -Seconds 2
    }
}
catch {
    Write-Host "`n[STOP] Arret des serveurs..." -ForegroundColor Yellow
}
finally {
    # Nettoyer les jobs
    Stop-Job -Job $laravelJob, $vueJob -ErrorAction SilentlyContinue
    Remove-Job -Job $laravelJob, $vueJob -ErrorAction SilentlyContinue
    Write-Host "[OK] Serveurs arretes" -ForegroundColor Green
}

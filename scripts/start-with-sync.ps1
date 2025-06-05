# Script de lancement complet avec système de synchronisation
# Ce script démarre le backend Laravel, le frontend Vue.js et le worker de synchronisation

Write-Host "[START] Demarrage des serveurs avec systeme de synchronisation..." -ForegroundColor Green

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

# Fonction pour démarrer le worker de synchronisation
function Start-QueueWorker {
    param($ProjectPath)
    Write-Host "[WORKER] Demarrage du worker de synchronisation..." -ForegroundColor Yellow
    Set-Location "$ProjectPath\backend"
    php artisan queue:work --sleep=3 --tries=3 --max-time=3600 --memory=512
}

# Fonction pour démarrer le planificateur Laravel
function Start-Scheduler {
    param($ProjectPath)
    Write-Host "[SCHEDULER] Demarrage du planificateur Laravel..." -ForegroundColor Yellow
    Set-Location "$ProjectPath\backend"
    while ($true) {
        php artisan schedule:run
        Start-Sleep -Seconds 60
    }
}

# Créer des jobs en arrière-plan pour tous les composants
Write-Host "[START] Lancement de tous les services..." -ForegroundColor Green
Write-Host "[BACKEND] Laravel: http://localhost:8000" -ForegroundColor Cyan
Write-Host "[FRONTEND] Vue.js: http://localhost:5173" -ForegroundColor Cyan
Write-Host "[SYNC] Worker de synchronisation" -ForegroundColor Cyan
Write-Host "[SCHEDULE] Planificateur de taches" -ForegroundColor Cyan
Write-Host "`n[INFO] Appuyez sur Ctrl+C pour arreter tous les services`n" -ForegroundColor Yellow

# Démarrer tous les services en arrière-plan
$laravelJob = Start-Job -ScriptBlock ${function:Start-LaravelServer} -ArgumentList $projectRoot
$vueJob = Start-Job -ScriptBlock ${function:Start-VueServer} -ArgumentList $projectRoot
$workerJob = Start-Job -ScriptBlock ${function:Start-QueueWorker} -ArgumentList $projectRoot
$schedulerJob = Start-Job -ScriptBlock ${function:Start-Scheduler} -ArgumentList $projectRoot

# Afficher les logs en temps réel
try {
    Write-Host "[INFO] Monitoring de tous les services... (Ctrl+C pour arreter)" -ForegroundColor Yellow
    
    while ($true) {
        # Afficher les nouvelles sorties de Laravel
        $laravelOutput = Receive-Job -Job $laravelJob
        if ($laravelOutput) {
            $laravelOutput | ForEach-Object { Write-Host "[Laravel] $_" -ForegroundColor Blue }
        }
        
        # Afficher les nouvelles sorties de Vue.js
        $vueOutput = Receive-Job -Job $vueJob
        if ($vueOutput) {
            $vueOutput | ForEach-Object { Write-Host "[Vue.js] $_" -ForegroundColor Green }
        }
        
        # Afficher les nouvelles sorties du Worker
        $workerOutput = Receive-Job -Job $workerJob
        if ($workerOutput) {
            $workerOutput | ForEach-Object { Write-Host "[Worker] $_" -ForegroundColor Magenta }
        }
        
        # Afficher les nouvelles sorties du Scheduler
        $schedulerOutput = Receive-Job -Job $schedulerJob
        if ($schedulerOutput) {
            $schedulerOutput | ForEach-Object { Write-Host "[Scheduler] $_" -ForegroundColor Cyan }
        }
        
        # Vérifier si des jobs ont échoué
        $allJobs = @($laravelJob, $vueJob, $workerJob, $schedulerJob)
        $failedJobs = $allJobs | Where-Object { $_.State -eq "Failed" }
        
        if ($failedJobs.Count -gt 0) {
            Write-Host "[ERROR] Un ou plusieurs services ont echoue" -ForegroundColor Red
            $failedJobs | ForEach-Object {
                $error = Receive-Job -Job $_ -ErrorAction SilentlyContinue
                if ($error) {
                    Write-Host "[ERROR] $($_.Name): $error" -ForegroundColor Red
                }
            }
            break
        }
        
        # Vérifier si tous les jobs sont terminés
        $completedJobs = $allJobs | Where-Object { $_.State -eq "Completed" }
        if ($completedJobs.Count -eq $allJobs.Count) {
            Write-Host "[INFO] Tous les services se sont arretes" -ForegroundColor Yellow
            break
        }
        
        Start-Sleep -Seconds 2
    }
}
catch {
    Write-Host "`n[STOP] Arret de tous les services..." -ForegroundColor Yellow
}
finally {
    # Nettoyer tous les jobs
    $allJobs = @($laravelJob, $vueJob, $workerJob, $schedulerJob)
    Stop-Job -Job $allJobs -ErrorAction SilentlyContinue
    Remove-Job -Job $allJobs -ErrorAction SilentlyContinue
    Write-Host "[OK] Tous les services arretes" -ForegroundColor Green
}

Write-Host "`n[INFO] Services disponibles:" -ForegroundColor Cyan
Write-Host "   • Application web: http://localhost:5173" -ForegroundColor White
Write-Host "   • API backend: http://localhost:8000/api" -ForegroundColor White
Write-Host "   • Systeme de synchronisation: Actif" -ForegroundColor White

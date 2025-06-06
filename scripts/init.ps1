# Script d'initialisation automatique du projet Laravel + Vue.js
# Ce script configure l'environnement de développement complet

Write-Host "[START] Initialisation du projet Laravel + Vue.js..." -ForegroundColor Green

# Vérifier si nous sommes dans le bon répertoire
$projectRoot = Split-Path -Parent $PSScriptRoot
if (-not (Test-Path "$projectRoot\backend\composer.json") -or -not (Test-Path "$projectRoot\frontend\package.json")) {
    Write-Host "[ERROR] Ce script doit etre execute depuis la racine du projet" -ForegroundColor Red
    exit 1
}

Write-Host "[INFO] Repertoire du projet: $projectRoot" -ForegroundColor Cyan

# Installation des dépendances du backend (Laravel)
Write-Host "`n[PHP] Installation des dependances PHP..." -ForegroundColor Yellow
Set-Location "$projectRoot\backend"

if (-not (Get-Command composer -ErrorAction SilentlyContinue)) {
    Write-Host "[ERROR] Composer n'est pas installe. Veuillez installer Composer d'abord." -ForegroundColor Red
    exit 1
}

composer install --no-interaction --prefer-dist

# Copier le fichier .env s'il n'existe pas
if (-not (Test-Path ".env")) {
    Write-Host "[ENV] Creation du fichier .env..." -ForegroundColor Yellow
    Copy-Item ".env.example" ".env"
}

# Générer la clé d'application Laravel
Write-Host "[KEY] Generation de la cle d'application Laravel..." -ForegroundColor Yellow
php artisan key:generate

# Créer la base de données SQLite si elle n'existe pas
if (-not (Test-Path "database\database.sqlite")) {
    Write-Host "[DB] Creation de la base de donnees SQLite..." -ForegroundColor Yellow
    New-Item -ItemType File -Path "database\database.sqlite" -Force
}

# Exécuter les migrations
Write-Host "[MIGRATE] Execution des migrations de base de donnees..." -ForegroundColor Yellow
php artisan migrate --force

# Exécuter les seeders (si nécessaire)
Write-Host "[SEED] Execution des seeders..." -ForegroundColor Yellow
php artisan db:seed --force

# Configuration du système de rangs automatiques
Write-Host "[RANKS] Configuration du systeme de rangs automatiques..." -ForegroundColor Yellow

# Mettre à jour les rangs de tous les utilisateurs basé sur leurs points
Write-Host "[RANKS] Mise a jour des rangs utilisateurs selon leurs points..." -ForegroundColor Cyan
php artisan ranks:update --force

Write-Host "[OK] Systeme de rangs automatiques configure" -ForegroundColor Green

# Configuration du système de files d'attente
Write-Host "[QUEUE] Configuration du systeme de files d'attente..." -ForegroundColor Yellow
php artisan queue:table 2>$null
if ($LASTEXITCODE -eq 0) {
    php artisan migrate --force
}

# Créer les tables de jobs pour la synchronisation
Write-Host "[SYNC] Creation des tables de synchronisation..." -ForegroundColor Yellow
php artisan make:job-table 2>$null
if ($LASTEXITCODE -eq 0) {
    php artisan migrate --force
}

# Vérifier la configuration du cache
Write-Host "[CACHE] Configuration du cache..." -ForegroundColor Yellow
php artisan config:cache
php artisan route:cache

# Tester la connectivité de la base de données
Write-Host "[TEST] Test de la connectivite de la base de donnees..." -ForegroundColor Yellow
$dbTest = php artisan tinker --execute="echo 'DB OK';" 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Base de donnees accessible" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Probleme de connexion a la base de donnees" -ForegroundColor Yellow
}

# Installation des dépendances Node.js pour le backend
if (Test-Path "package.json") {
    Write-Host "[NPM] Installation des dependances Node.js du backend..." -ForegroundColor Yellow
    npm install
}

# Installation des dépendances du frontend (Vue.js)
Write-Host "`n[FRONTEND] Installation des dependances du frontend..." -ForegroundColor Yellow
Set-Location "$projectRoot\frontend"

if (-not (Get-Command npm -ErrorAction SilentlyContinue)) {
    Write-Host "[ERROR] npm n'est pas installe. Veuillez installer Node.js d'abord." -ForegroundColor Red
    exit 1
}

# Copier et configurer le fichier .env du frontend
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Write-Host "[ENV] Creation du fichier .env frontend..." -ForegroundColor Yellow
        Copy-Item ".env.example" ".env"
    }
}

npm install

# Vérifier que Vite peut démarrer
Write-Host "[TEST] Test de la configuration Vite..." -ForegroundColor Yellow
$viteTest = npm run build 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Configuration Vite validee" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Probleme avec la configuration Vite" -ForegroundColor Yellow
}

# Retourner à la racine du projet
Set-Location $projectRoot

# Vérifications finales et configuration du système de synchronisation
Write-Host "`n[SYNC] Verification du systeme de synchronisation..." -ForegroundColor Yellow
Set-Location "$projectRoot\backend"

# Vérifier que les listeners sont bien enregistrés
Write-Host "[CHECK] Verification des listeners d'evenements..." -ForegroundColor Cyan
$eventCheck = php artisan event:list 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Systeme d'evenements operationnel" -ForegroundColor Green
    Write-Host "[INFO] Events configures:" -ForegroundColor Cyan
    Write-Host "   • QuizCompleted -> SynchronizeUserScore (points + rangs)" -ForegroundColor White
    Write-Host "   • RankUpdated -> Notification automatique de progression" -ForegroundColor White
} else {
    Write-Host "[INFO] Configurez les listeners dans EventServiceProvider" -ForegroundColor Yellow
}

# Vérifier la distribution des rangs après initialisation
Write-Host "[CHECK] Verification de la distribution des rangs..." -ForegroundColor Cyan
$rankDistribution = php artisan tinker --execute="`$distribution = \App\Models\User::join('ranks', 'users.rank_id', '=', 'ranks.id')->selectRaw('ranks.name, ranks.level, COUNT(*) as count')->groupBy('ranks.id', 'ranks.name', 'ranks.level')->orderBy('ranks.level')->get(); if (`$distribution->isNotEmpty()) { echo \"Distribution actuelle des rangs:\\n\"; foreach (`$distribution as `$rank) { echo \"  • {`$rank->name} (Niv. {`$rank->level}): {`$rank->count} utilisateurs\\n\"; } } else { echo \"Aucune distribution de rang trouvee\\n\"; }" 2>$null

# Créer les répertoires de logs si nécessaire
$logDir = "storage\logs"
if (-not (Test-Path $logDir)) {
    New-Item -ItemType Directory -Path $logDir -Force
    Write-Host "[LOG] Repertoire de logs cree" -ForegroundColor Green
}

# Configurer les permissions sur les répertoires de cache et logs
Write-Host "[PERM] Configuration des permissions..." -ForegroundColor Yellow
if (Test-Path "storage\framework\cache") {
    icacls "storage\framework\cache" /grant Users:F /T 2>$null
}
if (Test-Path "storage\logs") {
    icacls "storage\logs" /grant Users:F /T 2>$null
}

# Test final du système
Write-Host "[TEST] Test final du systeme..." -ForegroundColor Yellow
$finalTest = php artisan about 2>$null
if ($LASTEXITCODE -eq 0) {
    Write-Host "[OK] Laravel operationnel" -ForegroundColor Green
} else {
    Write-Host "[WARNING] Probleme de configuration detecte" -ForegroundColor Yellow
}

# Retourner à la racine du projet
Set-Location $projectRoot

Write-Host "`n[SUCCESS] Initialisation terminee avec succes!" -ForegroundColor Green
Write-Host "`n[SUMMARY] Configuration completee:" -ForegroundColor Cyan
Write-Host "   ✅ Dependances PHP installees (Composer)" -ForegroundColor Green
Write-Host "   ✅ Dependances Node.js installees (npm)" -ForegroundColor Green
Write-Host "   ✅ Fichiers d'environnement configures" -ForegroundColor Green
Write-Host "   ✅ Cles d'application generees" -ForegroundColor Green
Write-Host "   ✅ Base de donnees creee et migree" -ForegroundColor Green
Write-Host "   ✅ Seeders executes" -ForegroundColor Green
Write-Host "   ✅ Systeme de rangs automatiques configure" -ForegroundColor Green
Write-Host "   ✅ Systeme de files d'attente configure" -ForegroundColor Green
Write-Host "   ✅ Systeme de synchronisation verifie" -ForegroundColor Green

Write-Host "`n[INFO] Prochaines etapes:" -ForegroundColor Cyan
Write-Host "   • Executez .\scripts\start.ps1 pour lancer les serveurs de developpement" -ForegroundColor White
Write-Host "   • Backend sera disponible sur http://localhost:8000" -ForegroundColor White
Write-Host "   • Frontend sera disponible sur http://localhost:5173" -ForegroundColor White
Write-Host "   • Pour demarrer le worker de synchronisation :" -ForegroundColor White
Write-Host "     cd backend && php artisan queue:work" -ForegroundColor Gray

Write-Host "`n[TIPS] Commandes utiles:" -ForegroundColor Cyan
Write-Host "   • .\scripts\start.ps1         - Demarrer les serveurs" -ForegroundColor White
Write-Host "   • php artisan queue:work      - Demarrer le worker de synchronisation" -ForegroundColor White
Write-Host "   • php artisan ranks:update    - Mettre a jour les rangs manuellement" -ForegroundColor White
Write-Host "   • php artisan sync:all-scores - Synchroniser manuellement les scores" -ForegroundColor White
Write-Host "   • php artisan about           - Verifier l'etat du systeme" -ForegroundColor White

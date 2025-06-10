# Script de déploiement Breitling League vers Heroku avec Git Subtree
# Usage: .\deploy-heroku-subtree.ps1 <nom-app-heroku>

param(
    [Parameter(Mandatory=$true)]
    [string]$AppName
)

$ErrorActionPreference = "Stop"

Write-Host "🚀 Déploiement automatique vers Heroku avec Git Subtree..." -ForegroundColor Green
Write-Host "📱 App: $AppName" -ForegroundColor Cyan

# Vérifier que Heroku CLI est installé
try {
    heroku --version | Out-Null
} catch {
    Write-Host "❌ Heroku CLI non trouvé. Installez-le depuis https://devcenter.heroku.com/articles/heroku-cli" -ForegroundColor Red
    exit 1
}

# Vérifier que nous sommes dans le répertoire racine du projet (pas dans backend/)
if ((Get-Location).Path.EndsWith("backend")) {
    Write-Host "⚠️  Vous êtes dans le dossier backend. Remontez à la racine du projet." -ForegroundColor Yellow
    Write-Host "Exécutez: cd .. && .\backend\bin\deploy-heroku-subtree.ps1 $AppName" -ForegroundColor Yellow
    exit 1
}

# Vérifier que Git est configuré et que nous sommes à la racine
try {
    git status | Out-Null
} catch {
    Write-Host "❌ Ce n'est pas un repository Git ou Git n'est pas configuré" -ForegroundColor Red
    exit 1
}

# Vérifier que le dossier backend existe
if (-not (Test-Path "backend")) {
    Write-Host "❌ Dossier backend non trouvé. Assurez-vous d'être à la racine du projet." -ForegroundColor Red
    exit 1
}

# Vérifier la connexion Heroku
Write-Host "🔐 Vérification de la connexion Heroku..." -ForegroundColor Yellow
try {
    $user = heroku auth:whoami
    Write-Host "✅ Connecté à Heroku comme: $user" -ForegroundColor Green
} catch {
    Write-Host "❌ Non connecté à Heroku. Connectez-vous avec: heroku login" -ForegroundColor Red
    exit 1
}

# Créer l'app Heroku si elle n'existe pas
Write-Host "🏗️  Vérification/création de l'app Heroku..." -ForegroundColor Yellow
try {
    heroku apps:info $AppName | Out-Null
    Write-Host "✅ App '$AppName' trouvée" -ForegroundColor Green
} catch {
    Write-Host "🆕 Création de l'app '$AppName'..." -ForegroundColor Cyan
    heroku create $AppName --region eu
    Write-Host "✅ App '$AppName' créée" -ForegroundColor Green
}

# Ajouter PostgreSQL addon si pas encore fait
Write-Host "🐘 Configuration PostgreSQL..." -ForegroundColor Yellow
$addons = heroku addons --app $AppName
if ($addons -match "heroku-postgresql") {
    Write-Host "✅ PostgreSQL déjà configuré" -ForegroundColor Green
} else {
    Write-Host "🆕 Ajout de PostgreSQL..." -ForegroundColor Cyan
    heroku addons:create heroku-postgresql:mini --app $AppName
    Write-Host "✅ PostgreSQL ajouté" -ForegroundColor Green
}

# Configurer les variables d'environnement
Write-Host "⚙️  Configuration des variables d'environnement..." -ForegroundColor Yellow
heroku config:set `
    APP_NAME="Breitling League" `
    APP_ENV=production `
    APP_DEBUG=false `
    DB_CONNECTION=pgsql `
    CACHE_STORE=database `
    QUEUE_CONNECTION=database `
    SESSION_DRIVER=database `
    --app $AppName

Write-Host "✅ Variables d'environnement configurées" -ForegroundColor Green

# Ajouter le remote Heroku pour le subtree backend
Write-Host "🔗 Configuration du remote Git..." -ForegroundColor Yellow
$remotes = git remote
if ($remotes -contains "backend") {
    Write-Host "ℹ️  Remote 'backend' déjà configuré" -ForegroundColor Blue
    git remote set-url backend "https://git.heroku.com/$AppName.git"
} else {
    Write-Host "🆕 Ajout du remote 'backend'..." -ForegroundColor Cyan
    git remote add backend "https://git.heroku.com/$AppName.git"
}

Write-Host "✅ Remote configuré: backend -> https://git.heroku.com/$AppName.git" -ForegroundColor Green

# Vérifier que le backend est prêt
Write-Host "🔍 Vérification du backend..." -ForegroundColor Yellow
if (-not (Test-Path "backend\composer.json")) {
    Write-Host "❌ composer.json non trouvé dans backend\" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path "backend\Procfile")) {
    Write-Host "❌ Procfile non trouvé dans backend\" -ForegroundColor Red
    exit 1
}

Write-Host "✅ Backend validé" -ForegroundColor Green

# Vérifier les changements Git
Write-Host "📝 Vérification des changements Git..." -ForegroundColor Yellow
$gitStatus = git status --porcelain
if ($gitStatus) {
    Write-Host "⚠️  Il y a des changements non commités." -ForegroundColor Yellow
    $response = Read-Host "Voulez-vous les commiter automatiquement? (y/N)"
    if ($response -eq 'y' -or $response -eq 'Y') {
        git add .
        git commit -m "Deploy: Préparation pour déploiement Heroku"
        Write-Host "✅ Changements commités" -ForegroundColor Green
    } else {
        Write-Host "❌ Déploiement annulé. Committez vos changements d'abord." -ForegroundColor Red
        exit 1
    }
}

# Déployer avec Git subtree
Write-Host "🚀 Déploiement avec Git subtree..." -ForegroundColor Green
Write-Host "📤 Commande: git subtree push --prefix=backend backend main" -ForegroundColor Cyan

try {
    git subtree push --prefix=backend backend main
    Write-Host "✅ Déploiement réussi!" -ForegroundColor Green
} catch {
    Write-Host "❌ Erreur lors du déploiement subtree" -ForegroundColor Red
    Write-Host "🔧 Solutions possibles:" -ForegroundColor Yellow
    Write-Host "   1. Vérifiez que le remote 'backend' pointe vers Heroku" -ForegroundColor Gray
    Write-Host "   2. Essayez: git subtree push --prefix=backend backend main --force" -ForegroundColor Gray
    Write-Host "   3. Vérifiez vos permissions Heroku" -ForegroundColor Gray
    exit 1
}

# Attendre que le déploiement soit terminé
Write-Host "⏳ Attente de la fin du déploiement..." -ForegroundColor Yellow
Start-Sleep -Seconds 10

# Vérifier le statut de l'app
Write-Host "🔍 Vérification du statut de l'application..." -ForegroundColor Yellow
$psStatus = heroku ps --app $AppName
if ($psStatus -match "web.*up") {
    $appInfo = heroku info --app $AppName
    $appUrl = ($appInfo | Where-Object { $_ -match "Web URL" }) -replace ".*Web URL:\s+", ""
    Write-Host "✅ Application déployée avec succès!" -ForegroundColor Green
    Write-Host "🌐 URL: $appUrl" -ForegroundColor Cyan
    
    # Proposer d'ouvrir l'app dans le navigateur
    $response = Read-Host "Voulez-vous ouvrir l'application dans le navigateur? (y/N)"
    if ($response -eq 'y' -or $response -eq 'Y') {
        heroku open --app $AppName
    }
} else {
    Write-Host "⚠️  L'application semble avoir des problèmes" -ForegroundColor Yellow
    Write-Host "📊 Vérifiez les logs: heroku logs --tail --app $AppName" -ForegroundColor Gray
}

Write-Host ""
Write-Host "🎉 Déploiement terminé!" -ForegroundColor Green
Write-Host "📋 Commandes utiles:" -ForegroundColor Blue
Write-Host "   heroku logs --tail --app $AppName    # Voir les logs" -ForegroundColor Gray
Write-Host "   heroku ps --app $AppName             # Statut des dynos" -ForegroundColor Gray
Write-Host "   heroku config --app $AppName         # Variables d'environnement" -ForegroundColor Gray
Write-Host "   heroku pg:info --app $AppName        # Info PostgreSQL" -ForegroundColor Gray

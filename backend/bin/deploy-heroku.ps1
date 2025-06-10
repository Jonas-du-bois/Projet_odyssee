# Script de déploiement Heroku pour Breitling League
# Utilisation: .\deploy-heroku.ps1 [nom-de-votre-app]

param(
    [string]$AppName = "breitling-league-app"
)

Write-Host "🚀 Déploiement de Breitling League sur Heroku..." -ForegroundColor Green

# Vérifier si Heroku CLI est installé
if (-not (Get-Command heroku -ErrorAction SilentlyContinue)) {
    Write-Host "❌ Heroku CLI n'est pas installé. Veuillez l'installer depuis: https://devcenter.heroku.com/articles/heroku-cli" -ForegroundColor Red
    exit 1
}

# Se déplacer vers le dossier backend
Set-Location "c:\Users\jonas.dubois1\Desktop\breilting-league - Copie\laravel-vue-project\backend"

# Vérifier si l'app Heroku existe
$appExists = heroku apps:info $AppName 2>&1
if ($LASTEXITCODE -ne 0) {
    Write-Host "📱 Création de l'application Heroku: $AppName" -ForegroundColor Yellow
    heroku create $AppName --region eu
    
    # Ajouter l'addon PostgreSQL
    Write-Host "🐘 Ajout de PostgreSQL..." -ForegroundColor Yellow
    heroku addons:create heroku-postgresql:mini --app $AppName
} else {
    Write-Host "📱 Application $AppName existe déjà" -ForegroundColor Green
}

# Configurer les variables d'environnement
Write-Host "⚙️ Configuration des variables d'environnement..." -ForegroundColor Yellow
heroku config:set APP_ENV=production --app $AppName
heroku config:set APP_DEBUG=false --app $AppName
heroku config:set LOG_CHANNEL=errorlog --app $AppName
heroku config:set DB_CONNECTION=pgsql --app $AppName
heroku config:set SESSION_DRIVER=database --app $AppName
heroku config:set CACHE_STORE=database --app $AppName

# Générer une nouvelle clé d'application
Write-Host "🔑 Génération de la clé d'application..." -ForegroundColor Yellow
$appKey = php artisan key:generate --show
heroku config:set APP_KEY=$appKey --app $AppName

# Déployer l'application
Write-Host "📤 Déploiement sur Heroku..." -ForegroundColor Yellow
git add .
git commit -m "Deploy to Heroku with PostgreSQL support"
git push heroku main

# Vérifier le statut
Write-Host "✅ Déploiement terminé!" -ForegroundColor Green
heroku logs --tail --app $AppName

Write-Host "🌐 Votre application est disponible à: https://$AppName.herokuapp.com" -ForegroundColor Cyan

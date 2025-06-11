# Script PowerShell de correction et redéploiement Heroku pour le problème Sanctum

Write-Host "🔧 CORRECTION DU PROBLÈME SANCTUM SUR HEROKU" -ForegroundColor Cyan
Write-Host "============================================" -ForegroundColor Cyan

# Variables
$HEROKU_APP = "backend-breitling-league"

Write-Host "📱 Application: $HEROKU_APP" -ForegroundColor Yellow
Write-Host ""

# 1. Vérifier que nous sommes dans le bon répertoire
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Erreur: Ce script doit être exécuté depuis le répertoire backend" -ForegroundColor Red
    exit 1
}

Write-Host "1️⃣ Nettoyage du cache de configuration..." -ForegroundColor Green
php artisan config:clear

Write-Host ""
Write-Host "2️⃣ Ajout des providers manquants à bootstrap/providers.php..." -ForegroundColor Green
Write-Host "   (Le provider Sanctum a déjà été ajouté)" -ForegroundColor Gray

Write-Host ""
Write-Host "3️⃣ Génération du cache de configuration..." -ForegroundColor Green
php artisan config:cache

Write-Host ""
Write-Host "4️⃣ Commit et push des modifications..." -ForegroundColor Green
git add .
git commit -m "Fix: Ajouter SanctumServiceProvider pour corriger l'auth sur Heroku

- Ajout explicite de Laravel\Sanctum\SanctumServiceProvider
- Correction de l'erreur 'Auth driver [sanctum] for guard [sanctum] is not defined'
- Regénération du cache de configuration"

Write-Host ""
Write-Host "5️⃣ Déploiement sur Heroku..." -ForegroundColor Green
git push heroku main

Write-Host ""
Write-Host "6️⃣ Clear du cache Heroku..." -ForegroundColor Green
heroku run "php artisan config:clear && php artisan config:cache" --app $HEROKU_APP

Write-Host ""
Write-Host "7️⃣ Vérification du déploiement..." -ForegroundColor Green
Start-Sleep -Seconds 10

Write-Host ""
Write-Host "8️⃣ Test rapide de l'API..." -ForegroundColor Green
$API_URL = "https://$HEROKU_APP-e1d83468309e.herokuapp.com/api"

Write-Host "Testing login..." -ForegroundColor Gray
try {
    $loginData = @{
        email = "lucas@example.com"
        password = "password"
    } | ConvertTo-Json

    $response = Invoke-RestMethod -Uri "$API_URL/login" -Method POST -Body $loginData -ContentType "application/json"
    Write-Host "✅ Login réussi!" -ForegroundColor Green
    $response | ConvertTo-Json -Depth 3
} catch {
    Write-Host "❌ Erreur login: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "✅ DÉPLOIEMENT TERMINÉ" -ForegroundColor Green
Write-Host ""
Write-Host "🧪 Pour tester complètement l'API:" -ForegroundColor Cyan
Write-Host "  cd ../frontend && node test-api-heroku.js" -ForegroundColor White
Write-Host ""
Write-Host "📋 Pour voir les logs:" -ForegroundColor Cyan
Write-Host "  heroku logs --tail --app $HEROKU_APP" -ForegroundColor White

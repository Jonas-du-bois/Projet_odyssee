# Script PowerShell de diagnostic pour déploiement Heroku
# Usage: .\bin\heroku-diagnostic.ps1

Write-Host "🔍 DIAGNOSTIC HEROKU BREITLING LEAGUE" -ForegroundColor Cyan
Write-Host "==================================" -ForegroundColor Cyan

# Variables
$HEROKU_APP = "backend-breitling-league-e1d83468309e"

Write-Host "📱 App Heroku: $HEROKU_APP" -ForegroundColor Yellow
Write-Host ""

# 1. Vérifier le statut de l'application
Write-Host "1️⃣ STATUT DE L'APPLICATION" -ForegroundColor Green
Write-Host "-------------------------" -ForegroundColor Green
heroku ps --app $HEROKU_APP

Write-Host ""

# 2. Vérifier les logs récents
Write-Host "2️⃣ LOGS RÉCENTS (50 dernières lignes)" -ForegroundColor Green
Write-Host "------------------------------------" -ForegroundColor Green
heroku logs --tail --num=50 --app $HEROKU_APP

Write-Host ""

# 3. Vérifier les variables d'environnement
Write-Host "3️⃣ VARIABLES D'ENVIRONNEMENT" -ForegroundColor Green
Write-Host "---------------------------" -ForegroundColor Green
heroku config --app $HEROKU_APP

Write-Host ""

# 4. Vérifier la base de données
Write-Host "4️⃣ INFORMATIONS BASE DE DONNÉES" -ForegroundColor Green
Write-Host "------------------------------" -ForegroundColor Green
heroku pg:info --app $HEROKU_APP

Write-Host ""

# 5. Vérifier les migrations
Write-Host "5️⃣ STATUT DES MIGRATIONS" -ForegroundColor Green
Write-Host "-----------------------" -ForegroundColor Green
heroku run "php artisan migrate:status" --app $HEROKU_APP

Write-Host ""

# 6. Vérifier la configuration Laravel
Write-Host "6️⃣ CONFIGURATION LARAVEL" -ForegroundColor Green
Write-Host "-----------------------" -ForegroundColor Green
heroku run "php artisan config:show database" --app $HEROKU_APP

Write-Host ""

# 7. Test de connexion à la base de données
Write-Host "7️⃣ TEST CONNEXION BDD" -ForegroundColor Green
Write-Host "--------------------" -ForegroundColor Green

$tinkerScript = @"
try {
    `$pdo = DB::connection()->getPdo();
    echo "✅ Connexion BDD OK\n";
    
    // Vérifier quelques tables essentielles
    `$users = DB::table('users')->count();
    echo "👥 Utilisateurs: `$users\n";
    
    `$chapters = DB::table('chapters')->count();
    echo "📚 Chapitres: `$chapters\n";
    
    `$ranks = DB::table('ranks')->count();
    echo "🏆 Rangs: `$ranks\n";
    
    // Vérifier les tokens Sanctum
    `$tokens = DB::table('personal_access_tokens')->count();
    echo "🔑 Tokens actifs: `$tokens\n";
    
} catch (Exception `$e) {
    echo "❌ Erreur BDD: " . `$e->getMessage() . "\n";
}
exit;
"@

$tinkerScript | heroku run "php artisan tinker" --app $HEROKU_APP

Write-Host ""

# 8. Vérifier les routes API
Write-Host "8️⃣ TEST ROUTES API PRINCIPALES" -ForegroundColor Green
Write-Host "-----------------------------" -ForegroundColor Green
$API_URL = "https://$HEROKU_APP.herokuapp.com/api"

Write-Host "Testing $API_URL/login (GET - should return 405)" -ForegroundColor Gray
try {
    $response = Invoke-WebRequest -Uri "$API_URL/login" -Method GET -ErrorAction SilentlyContinue
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Yellow
} catch {
    Write-Host "Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Yellow
}

Write-Host "Testing $API_URL/chapters (GET - should return 401 without auth)" -ForegroundColor Gray
try {
    $response = Invoke-WebRequest -Uri "$API_URL/chapters" -Method GET -ErrorAction SilentlyContinue
    Write-Host "Status: $($response.StatusCode)" -ForegroundColor Yellow
} catch {
    Write-Host "Status: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Yellow
}

Write-Host ""

Write-Host "✅ DIAGNOSTIC TERMINÉ" -ForegroundColor Green
Write-Host "===================" -ForegroundColor Green
Write-Host ""
Write-Host "🚀 Pour redéployer si nécessaire:" -ForegroundColor Cyan
Write-Host "  git push heroku main" -ForegroundColor White
Write-Host ""
Write-Host "🔄 Pour relancer les migrations:" -ForegroundColor Cyan
Write-Host "  heroku run 'php artisan migrate:fresh --seed --force' --app $HEROKU_APP" -ForegroundColor White
Write-Host ""
Write-Host "📋 Pour voir les logs en temps réel:" -ForegroundColor Cyan
Write-Host "  heroku logs --tail --app $HEROKU_APP" -ForegroundColor White

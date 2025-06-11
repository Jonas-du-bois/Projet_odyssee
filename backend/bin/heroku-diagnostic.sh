#!/bin/bash
# Script de diagnostic pour déploiement Heroku
# Usage: ./bin/heroku-diagnostic.sh

echo "🔍 DIAGNOSTIC HEROKU BREITLING LEAGUE"
echo "=================================="

# Variables
HEROKU_APP="backend-breitling-league-e1d83468309e"

echo "📱 App Heroku: $HEROKU_APP"
echo ""

# 1. Vérifier le statut de l'application
echo "1️⃣ STATUT DE L'APPLICATION"
echo "-------------------------"
heroku ps --app $HEROKU_APP

echo ""

# 2. Vérifier les logs récents
echo "2️⃣ LOGS RÉCENTS (50 dernières lignes)"
echo "------------------------------------"
heroku logs --tail --num=50 --app $HEROKU_APP

echo ""

# 3. Vérifier les variables d'environnement
echo "3️⃣ VARIABLES D'ENVIRONNEMENT"
echo "---------------------------"
heroku config --app $HEROKU_APP

echo ""

# 4. Vérifier la base de données
echo "4️⃣ INFORMATIONS BASE DE DONNÉES"
echo "------------------------------"
heroku pg:info --app $HEROKU_APP

echo ""

# 5. Vérifier les migrations
echo "5️⃣ STATUT DES MIGRATIONS"
echo "-----------------------"
heroku run php artisan migrate:status --app $HEROKU_APP

echo ""

# 6. Vérifier la configuration Laravel
echo "6️⃣ CONFIGURATION LARAVEL"
echo "-----------------------"
heroku run php artisan config:show database --app $HEROKU_APP

echo ""

# 7. Test de connexion à la base de données
echo "7️⃣ TEST CONNEXION BDD"
echo "--------------------"
heroku run php artisan tinker --app $HEROKU_APP << 'EOF'
try {
    DB::connection()->getPdo();
    echo "✅ Connexion BDD OK\n";
    
    // Vérifier quelques tables essentielles
    $users = DB::table('users')->count();
    echo "👥 Utilisateurs: $users\n";
    
    $chapters = DB::table('chapters')->count();
    echo "📚 Chapitres: $chapters\n";
    
    $ranks = DB::table('ranks')->count();
    echo "🏆 Rangs: $ranks\n";
    
} catch (Exception $e) {
    echo "❌ Erreur BDD: " . $e->getMessage() . "\n";
}
EOF

echo ""

# 8. Vérifier les routes API
echo "8️⃣ TEST ROUTES API PRINCIPALES"
echo "-----------------------------"
API_URL="https://$HEROKU_APP.herokuapp.com/api"

echo "Testing $API_URL/login (GET - should return 405)"
curl -s -o /dev/null -w "Status: %{http_code}\n" -X GET "$API_URL/login"

echo "Testing $API_URL/chapters (GET - should return 401 without auth)"
curl -s -o /dev/null -w "Status: %{http_code}\n" -X GET "$API_URL/chapters"

echo ""

# 9. Vérifier l'état de Sanctum
echo "9️⃣ VÉRIFICATION SANCTUM"
echo "----------------------"
heroku run php artisan tinker --app $HEROKU_APP << 'EOF'
try {
    // Vérifier la table personal_access_tokens
    $tokens = DB::table('personal_access_tokens')->count();
    echo "🔑 Tokens actifs: $tokens\n";
    
    // Vérifier la configuration Sanctum
    echo "📝 Config Sanctum:\n";
    echo "  - Stateful domains: " . json_encode(config('sanctum.stateful')) . "\n";
    echo "  - Guard: " . config('sanctum.guard') . "\n";
    
} catch (Exception $e) {
    echo "❌ Erreur Sanctum: " . $e->getMessage() . "\n";
}
EOF

echo ""

echo "✅ DIAGNOSTIC TERMINÉ"
echo "==================="
echo ""
echo "🚀 Pour redéployer si nécessaire:"
echo "  git push heroku main"
echo ""
echo "🔄 Pour relancer les migrations:"
echo "  heroku run php artisan migrate:fresh --seed --force --app $HEROKU_APP"
echo ""
echo "📋 Pour voir les logs en temps réel:"
echo "  heroku logs --tail --app $HEROKU_APP"

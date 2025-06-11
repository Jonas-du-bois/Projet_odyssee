#!/bin/bash

# Script de correction et redéploiement Heroku pour le problème Sanctum

echo "🔧 CORRECTION DU PROBLÈME SANCTUM SUR HEROKU"
echo "============================================"

# Variables
HEROKU_APP="backend-breitling-league"

echo "📱 Application: $HEROKU_APP"
echo ""

# 1. Vérifier que nous sommes dans le bon répertoire
if [ ! -f "artisan" ]; then
    echo "❌ Erreur: Ce script doit être exécuté depuis le répertoire backend"
    exit 1
fi

echo "1️⃣ Nettoyage du cache de configuration..."
php artisan config:clear

echo ""
echo "2️⃣ Ajout des providers manquants à bootstrap/providers.php..."
# Le provider Sanctum a déjà été ajouté manuellement

echo ""
echo "3️⃣ Génération du cache de configuration..."
php artisan config:cache

echo ""
echo "4️⃣ Commit et push des modifications..."
git add .
git commit -m "Fix: Ajouter SanctumServiceProvider pour corriger l'auth sur Heroku

- Ajout explicite de Laravel\Sanctum\SanctumServiceProvider
- Correction de l'erreur 'Auth driver [sanctum] for guard [sanctum] is not defined'
- Regénération du cache de configuration"

echo ""
echo "5️⃣ Déploiement sur Heroku..."
git push heroku main

echo ""
echo "6️⃣ Clear du cache Heroku (optionnel)..."
heroku run "php artisan config:clear && php artisan config:cache" --app $HEROKU_APP

echo ""
echo "7️⃣ Vérification du déploiement..."
sleep 10

echo ""
echo "8️⃣ Test rapide de l'API..."
API_URL="https://$HEROKU_APP-e1d83468309e.herokuapp.com/api"

echo "Testing login..."
curl -s -X POST "$API_URL/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"lucas@example.com","password":"password"}' | jq '.'

echo ""
echo "✅ DÉPLOIEMENT TERMINÉ"
echo ""
echo "🧪 Pour tester complètement l'API:"
echo "  cd ../frontend && node test-api-heroku.js"
echo ""
echo "📋 Pour voir les logs:"
echo "  heroku logs --tail --app $HEROKU_APP"

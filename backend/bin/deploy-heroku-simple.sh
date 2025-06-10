#!/bin/bash

# Script de déploiement Heroku Git Subtree simplifié pour Windows
# Usage: ./deploy-heroku-simple.sh <nom-app>

set -e

APP_NAME=$1

if [[ -z "$APP_NAME" ]]; then
    echo "❌ Usage: $0 <nom-app-heroku>"
    echo "   Exemple: $0 backend-breitling-league"
    exit 1
fi

echo "🚀 Déploiement Breitling League avec Git Subtree..."
echo "📱 App: $APP_NAME"

# Vérifier qu'on est dans le bon répertoire
if [[ $(basename "$PWD") == "backend" ]]; then
    echo "⚠️  Vous êtes dans le dossier backend. Remontez à la racine."
    echo "Exécutez: cd .. && ./backend/bin/deploy-heroku-simple.sh $APP_NAME"
    exit 1
fi

if [[ ! -d "backend" ]]; then
    echo "❌ Dossier backend non trouvé. Placez-vous à la racine du projet."
    exit 1
fi

# Test simple de la connexion Heroku
echo "🔐 Test de connexion Heroku..."
heroku auth:whoami
if [[ $? -eq 0 ]]; then
    echo "✅ Connexion Heroku OK"
else
    echo "❌ Problème de connexion Heroku. Essayez: heroku login"
    exit 1
fi

# Créer l'app si elle n'existe pas
echo "🏗️  Vérification de l'app '$APP_NAME'..."
if heroku apps:info "$APP_NAME" >/dev/null 2>&1; then
    echo "✅ App '$APP_NAME' trouvée"
else
    echo "🆕 Création de l'app '$APP_NAME'..."
    heroku create "$APP_NAME" --region eu
fi

# Ajouter PostgreSQL
echo "🐘 Configuration PostgreSQL..."
if heroku addons --app "$APP_NAME" | grep -q "heroku-postgresql"; then
    echo "✅ PostgreSQL déjà configuré"
else
    echo "🆕 Ajout de PostgreSQL..."
    heroku addons:create heroku-postgresql:mini --app "$APP_NAME"
fi

# Variables d'environnement
echo "⚙️  Configuration des variables..."
heroku config:set \
    APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=pgsql \
    DB_SSLMODE=require \
    CACHE_DRIVER=database \
    SESSION_DRIVER=database \
    --app "$APP_NAME"

# Générer APP_KEY si nécessaire
if [[ -z "$(heroku config:get APP_KEY --app "$APP_NAME")" ]]; then
    echo "🔑 Génération de la clé..."
    cd backend
    APP_KEY=$(php -r "require 'vendor/autoload.php'; echo 'base64:'.base64_encode(random_bytes(32));")
    cd ..
    heroku config:set APP_KEY="$APP_KEY" --app "$APP_NAME"
fi

# Configuration Git remote
echo "🔗 Configuration Git remote..."
if git remote | grep -q "^backend$"; then
    git remote set-url backend "https://git.heroku.com/$APP_NAME.git"
else
    git remote add backend "https://git.heroku.com/$APP_NAME.git"
fi

# Commit si nécessaire
if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "📝 Commit des changements..."
    git add .
    git commit -m "Deploy: Préparation déploiement $APP_NAME"
fi

# Déploiement
echo "🚀 Déploiement avec Git subtree..."
echo "📤 git subtree push --prefix=backend backend main"

if git subtree push --prefix=backend backend main; then
    echo "✅ Déploiement réussi!"
    
    # Attendre un peu et vérifier
    sleep 10
    echo "🔍 Vérification..."
    
    if heroku ps --app "$APP_NAME" | grep -q "web.*up"; then
        echo "✅ Application en ligne!"
        echo "🌐 URL: https://$APP_NAME.herokuapp.com"
        echo ""
        echo "📋 Commandes utiles:"
        echo "   heroku logs --tail --app $APP_NAME"
        echo "   heroku open --app $APP_NAME"
        echo "   heroku ps --app $APP_NAME"
    else
        echo "⚠️  Application en cours de démarrage..."
        echo "📊 Vérifiez les logs: heroku logs --tail --app $APP_NAME"
    fi
    
else
    echo "❌ Erreur de déploiement"
    echo "🔧 Essayez un force push:"
    echo "   git push backend \`git subtree split --prefix=backend HEAD\`:main --force"
    exit 1
fi

echo ""
echo "🎉 Déploiement terminé!"

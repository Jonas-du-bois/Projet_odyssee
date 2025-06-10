#!/bin/bash

# Script de déploiement ultra-simple pour contourner les problèmes Windows
# Usage: ./deploy-quick.sh backend-breitling-league

APP_NAME=$1

if [[ -z "$APP_NAME" ]]; then
    echo "❌ Usage: $0 <nom-app>"
    exit 1
fi

echo "🚀 Déploiement rapide de $APP_NAME..."

# Vérifications de base
if [[ ! -d "backend" ]]; then
    echo "❌ Dossier backend non trouvé"
    exit 1
fi

echo "📱 App: $APP_NAME"
echo "👤 Utilisateur Heroku: $(heroku auth:whoami)"

# Étapes manuelles avec confirmation
echo ""
echo "🔧 ÉTAPES À SUIVRE:"
echo ""

echo "1️⃣  Créer l'app (si pas fait):"
echo "   heroku create $APP_NAME --region eu"
echo ""

echo "2️⃣  Ajouter PostgreSQL:"
echo "   heroku addons:create heroku-postgresql:mini --app $APP_NAME"
echo ""

echo "3️⃣  Configurer les variables:"
echo "   heroku config:set APP_ENV=production APP_DEBUG=false DB_CONNECTION=pgsql --app $APP_NAME"
echo ""

echo "4️⃣  Configurer Git remote:"
echo "   git remote add backend https://git.heroku.com/$APP_NAME.git"
echo ""

echo "5️⃣  Déployer:"
echo "   git subtree push --prefix=backend backend main"
echo ""

read -p "🚀 Voulez-vous que je lance automatiquement ces étapes? (y/N): " -n 1 -r
echo

if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo "🏗️  Création/vérification de l'app..."
    heroku create "$APP_NAME" --region eu 2>/dev/null || echo "   (App existe déjà)"
    
    echo "🐘 Ajout PostgreSQL..."
    heroku addons:create heroku-postgresql:mini --app "$APP_NAME" 2>/dev/null || echo "   (PostgreSQL existe déjà)"
    
    echo "⚙️  Configuration variables..."
    heroku config:set \
        APP_ENV=production \
        APP_DEBUG=false \
        DB_CONNECTION=pgsql \
        DB_SSLMODE=require \
        CACHE_DRIVER=database \
        SESSION_DRIVER=database \
        --app "$APP_NAME"
    
    echo "🔗 Configuration Git..."
    git remote remove backend 2>/dev/null || true
    git remote add backend "https://git.heroku.com/$APP_NAME.git"
    
    echo "📝 Commit si nécessaire..."
    git add . && git commit -m "Deploy $APP_NAME" 2>/dev/null || echo "   (Rien à commiter)"
    
    echo "🚀 Déploiement..."
    git subtree push --prefix=backend backend main
    
    if [[ $? -eq 0 ]]; then
        echo "✅ Déploiement réussi!"
        echo "🌐 https://$APP_NAME.herokuapp.com"
        echo "📊 heroku logs --tail --app $APP_NAME"
    else
        echo "❌ Erreur de déploiement"
        echo "🔧 Essayez manuellement:"
        echo "   git push backend \`git subtree split --prefix=backend HEAD\`:main --force"
    fi
else
    echo "👉 Lancez les commandes manuellement une par une"
fi

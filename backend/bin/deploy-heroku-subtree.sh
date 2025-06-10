#!/bin/bash

# Script de déploiement Breitling League vers Heroku avec Git Subtree
# Usage: ./deploy-heroku-subtree.sh <nom-app-heroku>

set -e

APP_NAME=$1

# Vérifier les paramètres
if [[ -z "$APP_NAME" ]]; then
    echo "❌ Usage: $0 <nom-app-heroku>"
    echo "   Exemple: $0 breitling-league-prod"
    exit 1
fi

echo "🚀 Déploiement automatique vers Heroku avec Git Subtree..."
echo "📱 App: $APP_NAME"

# Vérifier que Heroku CLI est installé
if ! command -v heroku &> /dev/null; then
    echo "❌ Heroku CLI non trouvé. Installez-le depuis https://devcenter.heroku.com/articles/heroku-cli"
    exit 1
fi

# Vérifier que nous sommes dans le répertoire racine du projet (pas dans backend/)
if [[ $(basename "$PWD") == "backend" ]]; then
    echo "⚠️  Vous êtes dans le dossier backend. Remontez à la racine du projet."
    echo "Exécutez: cd .. && ./backend/bin/deploy-heroku-subtree.sh $APP_NAME"
    exit 1
fi

# Vérifier que Git est configuré et que nous sommes à la racine
if ! git status &> /dev/null; then
    echo "❌ Ce n'est pas un repository Git ou Git n'est pas configuré"
    exit 1
fi

# Vérifier que le dossier backend existe
if [[ ! -d "backend" ]]; then
    echo "❌ Dossier backend non trouvé. Assurez-vous d'être à la racine du projet."
    exit 1
fi

# Vérifier la connexion Heroku
echo "🔐 Vérification de la connexion Heroku..."
HEROKU_USER=$(heroku auth:whoami 2>/dev/null)
if [[ $? -ne 0 ]] || [[ -z "$HEROKU_USER" ]]; then
    echo "❌ Non connecté à Heroku. Connectez-vous avec: heroku login"
    exit 1
fi

echo "✅ Connecté à Heroku comme: $HEROKU_USER"

# Créer l'app Heroku si elle n'existe pas
echo "🏗️  Vérification/création de l'app Heroku..."
if heroku apps:info "$APP_NAME" &> /dev/null; then
    echo "✅ App '$APP_NAME' trouvée"
else
    echo "🆕 Création de l'app '$APP_NAME'..."
    heroku create "$APP_NAME" --region eu
    echo "✅ App '$APP_NAME' créée"
fi

# Ajouter PostgreSQL addon si pas encore fait
echo "🐘 Configuration PostgreSQL..."
if heroku addons --app "$APP_NAME" | grep -q "heroku-postgresql"; then
    echo "✅ PostgreSQL déjà configuré"
else
    echo "🆕 Ajout de PostgreSQL..."
    heroku addons:create heroku-postgresql:mini --app "$APP_NAME"
    echo "✅ PostgreSQL ajouté"
fi

# Configurer les variables d'environnement
echo "⚙️  Configuration des variables d'environnement..."
heroku config:set \
    APP_NAME="Breitling League" \
    APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=pgsql \
    CACHE_STORE=database \
    QUEUE_CONNECTION=database \
    SESSION_DRIVER=database \
    --app "$APP_NAME"

echo "✅ Variables d'environnement configurées"

# Ajouter le remote Heroku pour le subtree backend
echo "🔗 Configuration du remote Git..."
if git remote | grep -q "^backend$"; then
    echo "ℹ️  Remote 'backend' déjà configuré"
    git remote set-url backend "https://git.heroku.com/$APP_NAME.git"
else
    echo "🆕 Ajout du remote 'backend'..."
    git remote add backend "https://git.heroku.com/$APP_NAME.git"
fi

echo "✅ Remote configuré: backend -> https://git.heroku.com/$APP_NAME.git"

# Vérifier que le backend est prêt
echo "🔍 Vérification du backend..."
if [[ ! -f "backend/composer.json" ]]; then
    echo "❌ composer.json non trouvé dans backend/"
    exit 1
fi

if [[ ! -f "backend/Procfile" ]]; then
    echo "❌ Procfile non trouvé dans backend/"
    exit 1
fi

echo "✅ Backend validé"

# Commit les derniers changements si nécessaire
echo "📝 Vérification des changements Git..."
if ! git diff --quiet || ! git diff --cached --quiet; then
    echo "⚠️  Il y a des changements non commités."
    read -p "Voulez-vous les commiter automatiquement? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        git add .
        git commit -m "Deploy: Préparation pour déploiement Heroku"
        echo "✅ Changements commités"
    else
        echo "❌ Déploiement annulé. Committez vos changements d'abord."
        exit 1
    fi
fi

# Déployer avec Git subtree
echo "🚀 Déploiement avec Git subtree..."
echo "📤 Commande: git subtree push --prefix=backend backend main"

if git subtree push --prefix=backend backend main; then
    echo "✅ Déploiement réussi!"
else
    echo "❌ Erreur lors du déploiement subtree"
    echo "🔧 Solutions possibles:"
    echo "   1. Vérifiez que le remote 'backend' pointe vers Heroku"
    echo "   2. Essayez: git subtree push --prefix=backend backend main --force"
    echo "   3. Vérifiez vos permissions Heroku"
    exit 1
fi

# Attendre que le déploiement soit terminé
echo "⏳ Attente de la fin du déploiement..."
sleep 10

# Vérifier le statut de l'app
echo "🔍 Vérification du statut de l'application..."
if heroku ps --app "$APP_NAME" | grep -q "web.*up"; then
    APP_URL=$(heroku info --app "$APP_NAME" | grep "Web URL" | awk '{print $3}')
    echo "✅ Application déployée avec succès!"
    echo "🌐 URL: $APP_URL"
    
    # Ouvrir l'app dans le navigateur
    read -p "Voulez-vous ouvrir l'application dans le navigateur? (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        heroku open --app "$APP_NAME"
    fi
else
    echo "⚠️  L'application semble avoir des problèmes"
    echo "📊 Vérifiez les logs: heroku logs --tail --app $APP_NAME"
fi

echo ""
echo "🎉 Déploiement terminé!"
echo "📋 Commandes utiles:"
echo "   heroku logs --tail --app $APP_NAME    # Voir les logs"
echo "   heroku ps --app $APP_NAME             # Statut des dynos"
echo "   heroku config --app $APP_NAME         # Variables d'environnement"
echo "   heroku pg:info --app $APP_NAME        # Info PostgreSQL"

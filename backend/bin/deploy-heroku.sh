#!/bin/bash

# Script de déploiement pour Heroku PostgreSQL
# Ce script sera exécuté lors du déploiement

echo "🚀 Déploiement Breitling League sur Heroku..."

# Générer la clé d'application si elle n'existe pas
php artisan key:generate --force

# Exécuter les migrations
echo "📊 Exécution des migrations PostgreSQL..."
php artisan migrate --force

# Exécuter les seeders en production (données de base uniquement)
echo "🌱 Seeding des données de base..."
php artisan db:seed --class=HerokuProductionSeeder --force

# Optimiser l'application pour la production
echo "⚡ Optimisation de l'application..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Optimiser l'autoloader
composer dump-autoload --optimize

echo "✅ Déploiement terminé avec succès!"

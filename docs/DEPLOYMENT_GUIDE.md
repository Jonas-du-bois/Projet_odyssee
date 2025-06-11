# 🚀 Guide de Déploiement Complet - Breitling League

## 📋 Vue d'ensemble

Ce guide consolidé couvre tous les aspects du déploiement de Breitling League sur Heroku avec PostgreSQL et Git Subtree.

---

## ⚡ Déploiement Express (5 minutes)

### 🎯 Option 1 : Déploiement ultra-rapide

```bash
# 1. Vérifier les prérequis (30 secondes)
cd "c:/Users/jonas.dubois1/Desktop/breilting-league - Copie/laravel-vue-project"
heroku --version
heroku login

# 2. Commit les changements si nécessaire
git add .
git commit -m "Déploiement production"

# 3. Lancer le déploiement automatique
./backend/bin/deploy-heroku-subtree.sh mon-app-breitling

# 4. Ouvrir l'application
heroku open -a mon-app-breitling
```

**✅ C'est fini !** Votre app est disponible sur : `https://mon-app-breitling.herokuapp.com`

---

## 📖 Déploiement Step-by-Step

### 📌 ÉTAPE 1 : Préparation initiale

#### 1.1 Vérifier l'état du projet
```bash
# Ouvrir un terminal dans la racine du projet
cd "c:/Users/jonas.dubois1/Desktop/breilting-league - Copie/laravel-vue-project"

# Vérifier que vous êtes dans le bon répertoire
ls -la
# Vous devez voir : backend/ frontend/ docs/ scripts/
```

#### 1.2 Vérifier Git
```bash
# Vérifier l'état Git
git status

# Si des changements non committés :
git add .
git commit -m "Préparation déploiement Heroku"
```

#### 1.3 Vérifier la structure subtree
```bash
# Vérifier que le backend existe
ls backend/
# Vous devez voir : app/ config/ database/ composer.json Procfile etc.
```

### 📌 ÉTAPE 2 : Installation et connexion Heroku

#### 2.1 Installer Heroku CLI (si pas fait)
```bash
# Vérifier si Heroku CLI est installé
heroku --version

# Si non installé, télécharger depuis :
# https://devcenter.heroku.com/articles/heroku-cli
```

#### 2.2 Se connecter à Heroku
```bash
# Se connecter à votre compte Heroku
heroku login
# Suivre les instructions dans le navigateur
```

### 📌 ÉTAPE 3 : Créer l'application Heroku

#### 3.1 Créer l'app avec PostgreSQL
```bash
# Créer l'application Heroku
heroku create mon-app-breitling

# Ajouter PostgreSQL
heroku addons:create heroku-postgresql:essential-0 -a mon-app-breitling
```

#### 3.2 Configurer les variables d'environnement
```bash
# Configurer Laravel
heroku config:set APP_ENV=production -a mon-app-breitling
heroku config:set APP_DEBUG=false -a mon-app-breitling
heroku config:set APP_KEY=$(php artisan key:generate --show) -a mon-app-breitling

# L'URL de la base de données PostgreSQL est automatiquement configurée
```

### 📌 ÉTAPE 4 : Déploiement avec Git Subtree

#### 4.1 Déployer le backend
```bash
# Déployer uniquement le dossier backend vers Heroku
git subtree push --prefix=backend heroku main

# Ou utiliser le script automatisé
./backend/bin/deploy-heroku-subtree.sh mon-app-breitling
```

#### 4.2 Exécuter les migrations
```bash
# Exécuter les migrations sur Heroku
heroku run php artisan migrate --force -a mon-app-breitling

# Exécuter les seeders (optionnel)
heroku run php artisan db:seed --force -a mon-app-breitling
```

### 📌 ÉTAPE 5 : Vérification et tests

#### 5.1 Ouvrir l'application
```bash
# Ouvrir dans le navigateur
heroku open -a mon-app-breitling
```

#### 5.2 Vérifier les logs
```bash
# Suivre les logs en temps réel
heroku logs --tail -a mon-app-breitling

# Voir les logs récents
heroku logs -a mon-app-breitling
```

---

## 🔧 État du Projet : PRÊT POUR PRODUCTION

### ✅ Checklist de préparation complétée

- ✅ Migration SQLite → PostgreSQL complétée
- ✅ Event Listeners multi-DB fonctionnels  
- ✅ Scripts de déploiement Git Subtree créés
- ✅ Configuration Heroku PostgreSQL préparée
- ✅ Modèles Eloquent synchronisés
- ✅ Système polymorphique préservé
- ✅ Production Seeder optimisé
- ✅ Tests de validation passés
- ✅ Documentation complète

### 🎯 Commande de déploiement unique

```bash
# Depuis la racine du projet
./backend/bin/deploy-heroku-subtree.sh breitling-league-prod
```

**Le script s'occupe automatiquement de :**
- Créer l'app Heroku si nécessaire
- Configurer PostgreSQL automatiquement
- Déployer via Git subtree
- Configurer les variables d'environnement
- Exécuter les migrations et seeders

---

## 💰 Coûts Heroku Estimés

### 🏠 Hobby Plan (recommandé pour démarrer)
- **App Dyno**: $7/mois
- **PostgreSQL**: $9/mois  
- **Total**: ~$16/mois

### 🏢 Professional (pour production)
- **Professional Dyno**: $25/mois
- **PostgreSQL Standard**: $50/mois
- **Total**: ~$75/mois

### 💡 Alternative : PostgreSQL externe

Pour réduire les coûts, utilisez un PostgreSQL externe (DigitalOcean, AWS RDS, etc.) :

```bash
# Configurer une base de données externe
heroku config:set DATABASE_URL="postgresql://user:pass@host:port/dbname" -a mon-app-breitling
```

---

## 🔄 Maintenance et Mises à Jour

### Redéployer des changements
```bash
# Commit les changements
git add .
git commit -m "Mise à jour application"

# Redéployer
./backend/bin/deploy-heroku-subtree.sh mon-app-breitling
```

### Suivre les performances
```bash
# Métriques de l'app
heroku ps -a mon-app-breitling

# Utilisation de la base de données
heroku pg:info -a mon-app-breitling
```

### Sauvegardes
```bash
# Créer une sauvegarde manuelle
heroku pg:backups:capture -a mon-app-breitling

# Lister les sauvegardes
heroku pg:backups -a mon-app-breitling
```

---

## 🚨 Dépannage

### Problèmes courants

#### App ne démarre pas
```bash
# Vérifier les logs
heroku logs --tail -a mon-app-breitling

# Vérifier la configuration
heroku config -a mon-app-breitling
```

#### Erreurs de base de données
```bash
# Réinitialiser la base de données
heroku pg:reset DATABASE -a mon-app-breitling
heroku run php artisan migrate --force -a mon-app-breitling
```

#### Problèmes de déploiement
```bash
# Forcer le redéploiement
git subtree push --prefix=backend heroku main --force
```

---

## 📞 Support et Resources

- **Documentation Heroku :** https://devcenter.heroku.com/
- **Laravel sur Heroku :** https://devcenter.heroku.com/articles/getting-started-with-laravel
- **Support PostgreSQL :** https://devcenter.heroku.com/articles/heroku-postgresql

---

*Guide de déploiement consolidé - Breitling League - Juin 2025*

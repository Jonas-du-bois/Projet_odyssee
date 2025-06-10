# 🚀 Step-by-Step : Déploiement Git Subtree sur Heroku

## 📋 Guide complet de déploiement pour Breitling League

### 🎯 Vue d'ensemble
Ce guide vous accompagne pas à pas pour déployer votre application Laravel avec Git Subtree sur Heroku PostgreSQL.

---

## 📌 ÉTAPE 1 : Préparation initiale

### 1.1 Vérifier l'état du projet
```bash
# Ouvrir un terminal dans la racine du projet
cd "c:\Users\jonas.dubois1\Desktop\breilting-league - Copie\laravel-vue-project"

# Vérifier que vous êtes dans le bon répertoire
ls -la
# Vous devez voir : backend/ frontend/ docs/ scripts/
```

### 1.2 Vérifier Git
```bash
# Vérifier l'état Git
git status

# Si des changements non committés :
git add .
git commit -m "Préparation déploiement Heroku"
```

### 1.3 Vérifier la structure subtree
```bash
# Vérifier que le backend existe
ls backend/
# Vous devez voir : app/ config/ database/ composer.json Procfile etc.
```

---

## 📌 ÉTAPE 2 : Installation et connexion Heroku

### 2.1 Installer Heroku CLI (si pas fait)
```bash
# Vérifier si Heroku CLI est installé
heroku --version

# Si non installé, télécharger depuis :
# https://devcenter.heroku.com/articles/heroku-cli
```

### 2.2 Se connecter à Heroku
```bash
# Connexion (ouvre le navigateur)
heroku login

# Vérifier la connexion
heroku auth:whoami
```

---

## 📌 ÉTAPE 3 : Validation pré-déploiement

### 3.1 Exécuter le script de validation
```bash
# Lancer la validation automatique
./backend/bin/validate-heroku-subtree.sh

# Si erreurs, les corriger avant de continuer
```

### 3.2 Tests manuels (optionnel)
```bash
# Tester la compatibilité PostgreSQL
cd backend
php artisan breitling:test-postgresql
cd ..
```

---

## 📌 ÉTAPE 4 : Création de l'application Heroku

### 4.1 Choisir un nom d'application
```bash
# Remplacez "votre-app-name" par le nom souhaité
# Exemples : breitling-league-prod, bl-production, etc.
APP_NAME="breitling-league-prod"
```

### 4.2 Créer l'application
```bash
# Créer l'app Heroku (région Europe)
heroku create $APP_NAME --region eu

# Ou si l'app existe déjà, passer à l'étape suivante
```

---

## 📌 ÉTAPE 5 : Configuration PostgreSQL

### 5.1 Ajouter PostgreSQL
```bash
# Ajouter l'addon PostgreSQL (coût : ~$9/mois)
heroku addons:create heroku-postgresql:mini -a $APP_NAME

# Vérifier l'ajout
heroku addons -a $APP_NAME
```

### 5.2 Configurer les variables d'environnement
```bash
# Configuration automatique via script
heroku config:set \
    APP_ENV=production \
    APP_DEBUG=false \
    DB_CONNECTION=pgsql \
    DB_SSLMODE=require \
    CACHE_DRIVER=database \
    SESSION_DRIVER=database \
    -a $APP_NAME
```

### 5.3 Générer la clé d'application
```bash
# Générer une clé Laravel sécurisée
heroku config:set APP_KEY=$(php -r "require 'backend/vendor/autoload.php'; echo 'base64:'.base64_encode(random_bytes(32));") -a $APP_NAME
```

---

## 📌 ÉTAPE 6 : Configuration Git Subtree

### 6.1 Ajouter le remote Heroku
```bash
# Ajouter le remote pour le subtree backend
git remote add backend https://git.heroku.com/$APP_NAME.git

# Vérifier l'ajout
git remote -v
```

### 6.2 Vérifier la configuration
```bash
# Le remote "backend" doit pointer vers votre app Heroku
git remote get-url backend
# Doit afficher : https://git.heroku.com/votre-app-name.git
```

---

## 📌 ÉTAPE 7 : Déploiement automatisé

### 7.1 Méthode recommandée : Script automatique
```bash
# Déploiement en une commande (remplacez le nom)
./backend/bin/deploy-heroku-subtree.sh breitling-league-prod
```

### 7.2 Méthode manuelle (si le script échoue)
```bash
# Déploiement manuel du subtree
git subtree push --prefix=backend backend main

# Si erreur de force push nécessaire :
git push backend `git subtree split --prefix=backend HEAD`:main --force
```

---

## 📌 ÉTAPE 8 : Vérification du déploiement

### 8.1 Suivre les logs de déploiement
```bash
# Voir les logs en temps réel
heroku logs --tail -a $APP_NAME
```

### 8.2 Vérifier le statut
```bash
# Statut des dynos
heroku ps -a $APP_NAME

# Informations de l'app
heroku info -a $APP_NAME
```

### 8.3 Tester l'application
```bash
# Ouvrir l'app dans le navigateur
heroku open -a $APP_NAME

# Ou visiter manuellement
echo "URL: https://$APP_NAME.herokuapp.com"
```

---

## 📌 ÉTAPE 9 : Vérification base de données

### 9.1 Vérifier les migrations
```bash
# Statut des migrations
heroku run php artisan migrate:status -a $APP_NAME
```

### 9.2 Vérifier les données
```bash
# Console Laravel
heroku run php artisan tinker -a $APP_NAME
# Puis dans tinker :
# User::count()
# QuizInstance::count()
# exit
```

### 9.3 Console PostgreSQL (optionnel)
```bash
# Accès direct à PostgreSQL
heroku pg:psql -a $APP_NAME
# Puis dans psql :
# \dt (lister les tables)
# SELECT COUNT(*) FROM users;
# \q (quitter)
```

---

## 📌 ÉTAPE 10 : Configuration finale

### 10.1 Variables d'environnement supplémentaires
```bash
# Ajouter des configs spécifiques à votre app
heroku config:set \
    APP_NAME="Breitling League" \
    MAIL_MAILER=log \
    -a $APP_NAME
```

### 10.2 Optimisations (optionnel)
```bash
# Redémarrer l'application
heroku restart -a $APP_NAME

# Mettre à l'échelle (si besoin)
heroku ps:scale web=1 -a $APP_NAME
```

---

## 🎯 Résumé des commandes essentielles

### Déploiement rapide (après première configuration)
```bash
# 1. Commit les changements
git add . && git commit -m "Mise à jour"

# 2. Déployer
./backend/bin/deploy-heroku-subtree.sh votre-app-name
```

### Commandes de monitoring
```bash
# Logs
heroku logs --tail -a votre-app-name

# Statut
heroku ps -a votre-app-name

# Variables
heroku config -a votre-app-name

# Base de données
heroku pg:info -a votre-app-name
```

---

## ❗ Dépannage courant

### Erreur de push subtree
```bash
# Force push si nécessaire
git push backend `git subtree split --prefix=backend HEAD`:main --force
```

### Erreur de migration
```bash
# Reset et re-migrer
heroku pg:reset -a votre-app-name --confirm votre-app-name
heroku run php artisan migrate:fresh --seed -a votre-app-name
```

### Application lente à démarrer
```bash
# Redémarrer
heroku restart -a votre-app-name

# Vérifier les ressources
heroku ps -a votre-app-name
```

---

## ✅ Checklist finale

- [ ] Application accessible sur https://votre-app-name.herokuapp.com
- [ ] Logs sans erreurs : `heroku logs --tail -a votre-app-name`
- [ ] Base de données peuplée : `heroku run php artisan tinker -a votre-app-name`
- [ ] Variables d'environnement configurées : `heroku config -a votre-app-name`
- [ ] SSL activé automatiquement par Heroku
- [ ] Migrations exécutées : `heroku run php artisan migrate:status -a votre-app-name`

---

## 🎉 Félicitations !

Votre application Breitling League est maintenant déployée sur Heroku avec :
- ✅ PostgreSQL configuré
- ✅ Git Subtree fonctionnel
- ✅ SSL sécurisé
- ✅ Variables d'environnement optimisées
- ✅ Migrations et seeders automatiques

**URL de production :** `https://votre-app-name.herokuapp.com`

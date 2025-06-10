# ⚡ Déploiement Express - 5 Minutes

## 🎯 Guide ultra-rapide pour déployer en 5 minutes

### 📋 Prérequis (vérification 30 secondes)
```bash
# 1. Être dans le bon répertoire
cd "c:\Users\jonas.dubois1\Desktop\breilting-league - Copie\laravel-vue-project"

# 2. Vérifier Heroku CLI
heroku --version

# 3. Se connecter (si pas fait)
heroku login
```

### 🚀 Déploiement en 4 étapes

#### Étape 1 : Commit les changements (si nécessaire)
```bash
git add .
git commit -m "Déploiement production"
```

#### Étape 2 : Lancer le déploiement automatique
```bash
# Remplacez "mon-app" par votre nom d'app
./backend/bin/deploy-heroku-subtree.sh mon-app-breitling
```

#### Étape 3 : Suivre les logs
```bash
# Dans un autre terminal
heroku logs --tail -a mon-app-breitling
```

#### Étape 4 : Vérifier l'app
```bash
# Ouvrir dans le navigateur
heroku open -a mon-app-breitling
```

### ✅ C'est fini !

Votre app est disponible sur : `https://mon-app-breitling.herokuapp.com`

---

## 🔧 Commandes de maintenance

### Redéployer des changements
```bash
git add . && git commit -m "Mise à jour"
./backend/bin/deploy-heroku-subtree.sh mon-app-breitling
```

### Voir les logs
```bash
heroku logs --tail -a mon-app-breitling
```

### Console Laravel
```bash
heroku run php artisan tinker -a mon-app-breitling
```

### Reset base de données (si problème)
```bash
heroku pg:reset -a mon-app-breitling --confirm mon-app-breitling
./backend/bin/deploy-heroku-subtree.sh mon-app-breitling
```

---

## 💰 Coût : ~$16/mois (Hobby plan)

Le script s'occupe de tout automatiquement !

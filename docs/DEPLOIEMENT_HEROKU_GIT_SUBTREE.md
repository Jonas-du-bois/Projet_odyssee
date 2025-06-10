# 🌳 Guide de déploiement Heroku avec Git Subtree

## Vue d'ensemble

Ce projet utilise **Git subtree** pour gérer le backend séparément. Cette configuration nécessite une approche spécifique pour le déploiement Heroku.

## 📁 Structure du projet

```
breitling-league/
├── backend/              # Git subtree - Laravel backend
│   ├── app/
│   ├── config/
│   ├── database/
│   ├── bin/
│   │   ├── deploy-heroku-subtree.sh    # Script Bash
│   │   └── deploy-heroku-subtree.ps1   # Script PowerShell
│   ├── composer.json
│   └── Procfile
├── frontend/             # Vue.js frontend  
├── docs/
└── scripts/
```

## 🚀 Déploiement automatisé

### Option 1: Script Bash (Linux/Mac/WSL)

```bash
# Depuis la racine du projet
./backend/bin/deploy-heroku-subtree.sh votre-app-name
```

### Option 2: Script PowerShell (Windows)

```powershell
# Depuis la racine du projet
.\backend\bin\deploy-heroku-subtree.ps1 votre-app-name
```

## 🔧 Configuration Git subtree

### Votre commande actuelle
```bash
git subtree push --prefix=backend backend main
```

### Ce que font nos scripts

1. **Vérification de l'environnement**
   - Heroku CLI installé
   - Position dans le répertoire racine (pas backend/)
   - Git configuré et repository valide

2. **Configuration Heroku**
   - Création automatique de l'app si inexistante
   - Ajout de PostgreSQL addon
   - Configuration des variables d'environnement

3. **Configuration Git**
   - Ajout du remote `backend` pointant vers Heroku
   - Vérification des changements non commités

4. **Déploiement subtree**
   - Exécution de `git subtree push --prefix=backend backend main`
   - Gestion des erreurs et solutions

## ⚙️ Variables d'environnement Heroku

Les scripts configurent automatiquement :

```env
APP_NAME="Breitling League"
APP_ENV=production
APP_DEBUG=false
DB_CONNECTION=pgsql
CACHE_STORE=database
QUEUE_CONNECTION=database
SESSION_DRIVER=database
```

## 🔍 Dépannage Git subtree

### Erreur: "Updates were rejected"

```bash
# Solution 1: Force push (attention aux données)
git subtree push --prefix=backend backend main --force

# Solution 2: Vérifier les remotes
git remote -v
git remote set-url backend https://git.heroku.com/votre-app.git
```

### Erreur: "fatal: ambiguous argument"

```bash
# Assurer que la branche main existe
git checkout main
git push origin main

# Puis réessayer le subtree
git subtree push --prefix=backend backend main
```

### Erreur: "Working tree has modifications"

```bash
# Commiter tous les changements d'abord
git add .
git commit -m "Préparation déploiement Heroku"

# Puis déployer
git subtree push --prefix=backend backend main
```

## 📋 Checklist de déploiement

### Pré-déploiement
- [ ] Position dans la racine du projet (pas backend/)
- [ ] Heroku CLI installé et connecté (`heroku login`)
- [ ] Changements Git commités
- [ ] Backend validé (composer.json, Procfile présents)

### Déploiement
- [ ] Exécution du script approprié (.sh ou .ps1)
- [ ] App Heroku créée/configurée
- [ ] PostgreSQL addon ajouté
- [ ] Variables d'environnement définies
- [ ] Remote Git 'backend' configuré
- [ ] Subtree push réussi

### Post-déploiement
- [ ] Application accessible via URL Heroku
- [ ] Migrations exécutées (`heroku logs --app votre-app`)
- [ ] Seeders exécutés avec succès
- [ ] Tests fonctionnels validés

## 🔗 Commandes utiles

### Gestion de l'app Heroku
```bash
# Voir les logs
heroku logs --tail --app votre-app

# Statut des dynos
heroku ps --app votre-app

# Variables d'environnement
heroku config --app votre-app

# Info PostgreSQL
heroku pg:info --app votre-app

# Ouvrir l'app
heroku open --app votre-app
```

### Gestion Git subtree
```bash
# Voir les remotes
git remote -v

# Changer l'URL du remote backend
git remote set-url backend https://git.heroku.com/nouvelle-app.git

# Déployer avec force (attention!)
git subtree push --prefix=backend backend main --force

# Historique subtree
git log --grep="git-subtree-dir: backend/"
```

### Debug et maintenance
```bash
# Exécuter une commande sur Heroku
heroku run php artisan migrate:status --app votre-app

# Accéder au terminal Heroku
heroku run bash --app votre-app

# Redémarrer l'app
heroku restart --app votre-app

# Voir les builds
heroku builds --app votre-app
```

## 🎯 Avantages de Git subtree

1. **Séparation claire** - Backend isolé du frontend
2. **Déploiement ciblé** - Seul le backend va sur Heroku
3. **Historique propre** - Commits backend séparés
4. **Flexibilité** - Backend peut avoir son propre cycle de release

## ⚠️ Points d'attention

### Subtree vs Submodule
- **Subtree**: Code intégré, plus simple pour CI/CD
- **Submodule**: Référence externe, plus complexe

### Gestion des branches
- Toujours déployer depuis `main`
- Éviter les conflits entre repos parent/subtree
- Synchroniser régulièrement les changements

### Performance
- Subtree push peut être long sur gros repos
- Considérer `--squash` pour l'historique
- Cache Heroku améliore les déploiements suivants

## 🎉 Résumé

Avec votre configuration Git subtree, le déploiement Heroku nécessite :

1. **Position**: À la racine du projet (pas dans backend/)
2. **Commande**: `./backend/bin/deploy-heroku-subtree.sh app-name`
3. **Remote**: `backend` pointant vers Heroku
4. **Push**: `git subtree push --prefix=backend backend main`

Les scripts automatisent tout le processus pour un déploiement en un clic ! 🚀

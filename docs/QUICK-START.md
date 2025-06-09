# Guide de Démarrage Rapide - Breitling League

Ce guide vous permettra de lancer le projet Breitling League avec son système de synchronisation automatique en quelques étapes simples.

## 🚀 Installation en une commande

### Windows
```powershell
git clone [URL_DU_REPO]
cd breilting-league\laravel-vue-project
.\scripts\init.ps1
```

### Linux/macOS
```bash
git clone [URL_DU_REPO]
cd breilting-league/laravel-vue-project
chmod +x scripts/*.sh
./scripts/init.sh
```

## 🎯 Démarrage des services

### Option 1 - Système complet avec synchronisation (Recommandé)

**Windows :**
```powershell
.\scripts\start-with-sync.ps1
```

**Linux/macOS :**
```bash
./scripts/start-with-sync.sh
```

**Services démarrés :**
- ✅ Backend Laravel (http://localhost:8000)
- ✅ Frontend Vue.js (http://localhost:5173)
- ✅ Worker de synchronisation (Queue Worker)
- ✅ Planificateur de tâches automatiques

### Option 2 - Développement basique

**Windows :**
```powershell
.\scripts\start.ps1
```

**Services démarrés :**
- ✅ Backend Laravel (http://localhost:8000)
- ✅ Frontend Vue.js (http://localhost:5173)

## 🔧 Vérification du système

Une fois les services démarrés, vous pouvez vérifier que tout fonctionne :

```bash
# Vérifier l'état de Laravel
cd backend
php artisan about

# Tester la synchronisation
php artisan sync:all-scores

# Voir la file d'attente
php artisan queue:monitor
```

## 🌐 Accès à l'application

- **Application web** : http://localhost:5173
- **API Backend** : http://localhost:8000/api
- **Documentation API** : http://localhost:8000/docs (si disponible)

## 🛠️ Commandes utiles

### Synchronisation manuelle
```bash
cd backend
php artisan sync:all-scores      # Synchroniser tous les scores
php artisan calculate:ranks      # Recalculer les rangs
php artisan queue:clear          # Nettoyer la file d'attente
```

### Maintenance
```bash
php artisan migrate:fresh --seed # Réinitialiser la base de données
php artisan optimize:clear       # Vider tous les caches
php artisan queue:work           # Démarrer manuellement le worker
```

### Logs et debugging
```bash
php artisan tail                 # Voir les logs en temps réel
php artisan tinker               # Console interactive Laravel
npm run dev                      # Mode développement Vite avec hot reload
```

## 🚦 Arrêt des services

Pour arrêter tous les services, appuyez simplement sur **Ctrl+C** dans le terminal où les scripts sont en cours d'exécution.

## 🐛 Dépannage rapide

### Problème de permissions (Windows)
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Ports occupés
```bash
# Changer le port Laravel
php artisan serve --port=8001

# Changer le port Vite (dans vite.config.js)
server: { port: 5174 }
```

### Base de données corrompue
```bash
cd backend
rm database/database.sqlite
php artisan migrate:fresh --seed
```

## 📊 Système de Synchronisation

Le système de synchronisation fonctionne automatiquement :

1. **Quiz terminé** → Événement `QuizCompleted` 
2. **Listener** → Ajoute un job à la file d'attente
3. **Worker** → Traite le job et met à jour les scores
4. **Calcul automatique** → Nouveau rang calculé
5. **Notification** → Utilisateur informé du changement

### Monitoring en temps réel

Lorsque vous utilisez `start-with-sync.ps1`, vous verrez les logs en couleur :
- 🔵 **[Laravel]** : Messages du backend
- 🟢 **[Vue.js]** : Messages du frontend  
- 🟣 **[Worker]** : Jobs de synchronisation traités
- 🟦 **[Scheduler]** : Tâches automatiques exécutées

## 📚 Documentation complète

Pour plus de détails, consultez :
- `README.md` : Documentation complète du projet
- `scripts/README.md` : Guide détaillé des scripts
- `backend/README.md` : Documentation spécifique au backend Laravel

## 🎉 Vous êtes prêt !

Votre environnement Breitling League est maintenant configuré avec :
- ✅ Système de quiz fonctionnel
- ✅ Synchronisation automatique des scores
- ✅ Interface utilisateur moderne
- ✅ API backend robuste
- ✅ Système de rangs automatique

Commencez par créer des quiz et voir la synchronisation en action ! 🚀

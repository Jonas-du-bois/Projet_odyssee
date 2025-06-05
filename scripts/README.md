# Scripts d'automatisation

Ce dossier contient des scripts pour automatiser l'initialisation et le lancement du projet Laravel + Vue.js avec système de synchronisation automatique.

## Scripts disponibles

### 🚀 Initialisation

#### `init.ps1` / `init.sh` - Initialisation complète du projet

Ces scripts configurent automatiquement l'environnement de développement complet :

- ✅ Installation des dépendances PHP avec Composer
- ✅ Installation des dépendances Node.js pour le backend et frontend
- ✅ Configuration des fichiers `.env` (Laravel et Vue.js)
- ✅ Génération de la clé d'application Laravel
- ✅ Création de la base de données SQLite
- ✅ Exécution des migrations et seeders
- ✅ Configuration du système de files d'attente
- ✅ Vérification du système de synchronisation

**Utilisation :**
```powershell
# Windows
.\scripts\init.ps1

# Linux/macOS
chmod +x scripts/init.sh
./scripts/init.sh
```

### 🎯 Démarrage des serveurs

#### `start.ps1` - Lancement standard

Démarre simultanément le serveur Laravel et le serveur de développement Vue.js :

- Backend Laravel : http://localhost:8000
- Frontend Vue.js : http://localhost:5173

**Utilisation :**
```powershell
.\scripts\start.ps1
```

#### `start-with-sync.ps1` / `start-with-sync.sh` - Lancement avec synchronisation (Recommandé)

Version complète qui démarre tous les services nécessaires :

- ✅ Serveur Laravel (Backend API)
- ✅ Serveur Vue.js (Frontend)
- ✅ Worker de synchronisation (Queue Worker)
- ✅ Planificateur de tâches (Task Scheduler)

**Utilisation :**
```powershell
# Windows
.\scripts\start-with-sync.ps1

# Linux/macOS
chmod +x scripts/start-with-sync.sh
./scripts/start-with-sync.sh
```

#### `start-optimized.ps1` - Lancement optimisé

Version optimisée qui évite l'affichage répétitif des logs et ne montre que les informations importantes.

**Utilisation :**
```powershell
.\scripts\start-optimized.ps1
```

### 🔄 `start-concurrent.ps1` - Lancement alternatif

Version alternative utilisant le package `concurrently` pour une meilleure gestion des processus parallèles.

**Utilisation :**
```powershell
.\scripts\start-concurrent.ps1
```

## 🎯 Choix du script de démarrage

### Pour le développement standard :
- `start.ps1` : Affichage des logs en temps réel, bon pour le debugging
- `start-optimized.ps1` : Affichage minimal, plus propre

### Pour le développement avec synchronisation (Recommandé) :
- `start-with-sync.ps1` : Démarre tous les services nécessaires au système de synchronisation
- `start-with-sync.sh` : Version Linux/macOS équivalente

### Pour des cas particuliers :
- `start-concurrent.ps1` : Utilise des outils externes pour la gestion des processus

## Prérequis

Assurez-vous d'avoir installé :

- **PHP 8.3** ou supérieure
- **Composer** (gestionnaire de dépendances PHP)
- **Node.js 22** et **npm** (pour les dépendances frontend)
- **PowerShell** (Windows) ou **Bash** (Linux/macOS)

### Extensions PHP requises :
- BCMath, Ctype, JSON, Mbstring, OpenSSL, PDO, Tokenizer, XML, SQLite3

## Workflow recommandé

### 🚀 Installation initiale

```powershell
# Windows
git clone [URL_DU_REPO]
cd breilting-league\laravel-vue-project
.\scripts\init.ps1

# Linux/macOS
git clone [URL_DU_REPO]
cd breilting-league/laravel-vue-project
chmod +x scripts/*.sh
./scripts/init.sh
```

### 🎯 Développement quotidien

**Option 1 - Développement complet avec synchronisation (Recommandé) :**
```powershell
# Windows
.\scripts\start-with-sync.ps1

# Linux/macOS
./scripts/start-with-sync.sh
```

**Option 2 - Développement basique :**
```powershell
# Windows
.\scripts\start-optimized.ps1

# Linux/macOS
./scripts/start.sh  # (à créer si nécessaire)
```

### 🛠️ Commandes manuelles

Si vous préférez démarrer les services manuellement :

```bash
# Terminal 1 - Backend Laravel
cd backend
php artisan serve

# Terminal 2 - Frontend Vue.js
cd frontend
npm run dev

# Terminal 3 - Worker de synchronisation (optionnel)
cd backend
php artisan queue:work

# Terminal 4 - Planificateur (optionnel)
cd backend
php artisan schedule:work
```

### 🔄 Gestion des services

```bash
# Vérifier l'état du système
cd backend
php artisan about

# Synchroniser manuellement les scores
php artisan sync:all-scores

# Voir les jobs en file d'attente
php artisan queue:monitor

# Nettoyer les jobs échoués
php artisan queue:clear
```

## 🐛 Dépannage

### Erreur d'exécution de script PowerShell

Si vous obtenez une erreur de politique d'exécution :
```powershell
Set-ExecutionPolicy -ExecutionPolicy RemoteSigned -Scope CurrentUser
```

### Ports déjà utilisés

Si les ports 8000 ou 5173 sont déjà utilisés :
```bash
# Voir quels processus utilisent les ports
netstat -ano | findstr :8000
netstat -ano | findstr :5173

# Modifier les ports si nécessaire dans vite.config.js ou Laravel
```

### Base de données verrouillée

Si la base de données SQLite est verrouillée :
```bash
cd backend
php artisan migrate:fresh --seed
```

### Worker de synchronisation qui ne démarre pas

Vérifiez la configuration de la file d'attente :
```bash
cd backend
php artisan config:clear
php artisan queue:table
php artisan migrate
```

## 📊 Monitoring

### Logs disponibles

- Laravel : `backend/storage/logs/laravel.log`
- Worker : Affiché dans la console avec `[Worker]`
- Frontend : Affiché dans la console avec `[Vue.js]`
- Scheduler : Affiché dans la console avec `[Scheduler]`

### Surveillance en temps réel

Les scripts avec synchronisation affichent automatiquement les logs de tous les services avec des codes couleur pour faciliter le debugging.

## 🚀 Performance et Production

Pour la production, utilisez des solutions plus robustes :

- **Worker** : Supervisor (Linux) ou Task Scheduler (Windows)
- **Monitoring** : Laravel Horizon avec Redis
- **Logs** : Centralisés avec ELK Stack ou similaire

### Problèmes de dépendances

Si vous rencontrez des problèmes avec les dépendances :
1. Supprimez les dossiers `vendor` et `node_modules`
2. Relancez `.\scripts\init.ps1`

## Structure du projet

```
laravel-vue-project/
├── scripts/              # Scripts d'automatisation
│   ├── init.ps1         # Initialisation du projet
│   ├── start.ps1        # Lancement des serveurs
│   ├── start-concurrent.ps1  # Lancement alternatif
│   └── README.md        # Ce fichier
├── backend/             # Application Laravel
└── frontend/            # Application Vue.js
```

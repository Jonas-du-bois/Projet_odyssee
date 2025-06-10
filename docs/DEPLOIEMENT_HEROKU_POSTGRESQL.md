# Guide de Déploiement Heroku PostgreSQL
## Breitling League

### 📋 Prérequis

1. **Heroku CLI installé** : https://devcenter.heroku.com/articles/heroku-cli
2. **Git initialisé** dans le projet
3. **Compte Heroku** avec carte de crédit (pour PostgreSQL, même gratuit)

### 🚀 Déploiement Automatique

#### Option 1: Script PowerShell (Windows)
```powershell
cd backend
.\bin\deploy-heroku.ps1 votre-nom-app
```

#### Option 2: Déploiement Manuel

1. **Créer l'application Heroku**
```bash
cd backend
heroku create votre-nom-app --region eu
```

2. **Ajouter PostgreSQL**
```bash
heroku addons:create heroku-postgresql:mini
```

3. **Configurer les variables d'environnement**
```bash
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
heroku config:set DB_CONNECTION=pgsql
heroku config:set LOG_CHANNEL=errorlog
heroku config:set SESSION_DRIVER=database
heroku config:set CACHE_STORE=database
```

4. **Générer et configurer la clé d'application**
```bash
php artisan key:generate --show
heroku config:set APP_KEY=base64:votre-cle-generee
```

5. **Déployer**
```bash
git add .
git commit -m "Deploy to Heroku"
git push heroku main
```

### 🗄️ Migration des Données

#### Depuis SQLite vers PostgreSQL

1. **Exporter les données de SQLite**
```bash
php artisan db:seed --class=DataExportSeeder
```

2. **Les migrations s'exécutent automatiquement** grâce au Procfile

3. **Importer les données (si nécessaire)**
```bash
heroku run php artisan db:seed --app votre-nom-app
```

### 🔧 Configuration PostgreSQL

#### Variables d'environnement automatiques
Heroku configure automatiquement :
- `DATABASE_URL` : URL complète de connexion PostgreSQL
- Toutes les variables DB_* sont extraites automatiquement

#### SSL obligatoire
PostgreSQL sur Heroku nécessite SSL :
```php
// Dans config/database.php
'sslmode' => env('DB_SSLMODE', 'require'),
```

#### Event Listeners PostgreSQL
Les Event Listeners ont été adaptés pour PostgreSQL :
- **SynchronizeUserScore** : Gère les différences de format de date entre SQLite et PostgreSQL
- **Optimisations automatiques** : Cache des événements et optimisations de performance
- **Tests de compatibilité** : Command artisan pour valider la configuration

##### Tester la compatibilité
```bash
heroku run php artisan breitling:test-postgresql --verbose --app votre-nom-app
```

### 📊 Monitoring

#### Logs en temps réel
```bash
heroku logs --tail --app votre-nom-app
```

#### Statut de l'application
```bash
heroku ps --app votre-nom-app
```

#### Base de données
```bash
heroku pg:info --app votre-nom-app
heroku pg:psql --app votre-nom-app
```

### 🛠️ Commandes Utiles

#### Exécuter des commandes Artisan
```bash
heroku run php artisan migrate --app votre-nom-app
heroku run php artisan tinker --app votre-nom-app
```

#### Redémarrer l'application
```bash
heroku restart --app votre-nom-app
```

#### Mettre à l'échelle
```bash
heroku ps:scale web=1 --app votre-nom-app
```

### 🔒 Sécurité

#### Variables d'environnement sensibles
```bash
heroku config:set MAIL_PASSWORD=your-mail-password --app votre-nom-app
heroku config:set JWT_SECRET=your-jwt-secret --app votre-nom-app
```

#### SSL/TLS forcé
L'application force automatiquement HTTPS en production.

### 📈 Performance

#### Cache optimisé
```bash
heroku run php artisan config:cache --app votre-nom-app
heroku run php artisan route:cache --app votre-nom-app
heroku run php artisan view:cache --app votre-nom-app
```

#### Index PostgreSQL
Les index sont automatiquement créés via le schéma SQL optimisé.

### 🚨 Dépannage

#### Erreur de migration
```bash
heroku run php artisan migrate:reset --app votre-nom-app
heroku run php artisan migrate --app votre-nom-app
```

#### Problème de clé d'application
```bash
heroku config:set APP_KEY=$(php artisan key:generate --show) --app votre-nom-app
```

#### Logs d'erreur
```bash
heroku logs --tail --app votre-nom-app
```

### 📚 Ressources

- [Heroku PostgreSQL](https://devcenter.heroku.com/articles/heroku-postgresql)
- [Laravel sur Heroku](https://devcenter.heroku.com/articles/getting-started-with-laravel)
- [Variables d'environnement Heroku](https://devcenter.heroku.com/articles/config-vars)

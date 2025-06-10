# 📋 Guide de Migration SQLite vers PostgreSQL

## Vue d'ensemble

Ce guide détaille la migration complète du système Breitling League de SQLite (développement) vers PostgreSQL (production Heroku).

## ✅ État actuel du projet

### Composants migrés

1. **✅ Schéma de base de données**
   - Schéma PostgreSQL créé : `database/breitlingLeague_postgresql.sql`
   - Types natifs PostgreSQL (BIGSERIAL, ENUM, JSON)
   - Contraintes et index optimisés

2. **✅ Event Listeners PostgreSQL-compatible**
   - `SynchronizeUserScore` avec méthode `getDateFormatSQL()`
   - Support multi-DB (SQLite/PostgreSQL/MySQL)
   - Tests intégrés

3. **✅ Configuration Laravel**
   - PostgreSQL par défaut dans `config/database.php`
   - SSL configuré pour Heroku
   - Extension `ext-pgsql` ajoutée

4. **✅ Scripts de déploiement automatisés**
   - PowerShell et Bash pour tous environnements
   - Validation pré-déploiement
   - Configuration Heroku automatique

5. **✅ Modèles Eloquent synchronisés**
   - Timestamps standardisés sur 6 modèles
   - Tests de validation intégrés
   - Cohérence base/modèles assurée

6. **✅ Système polymorphique préservé**
   - Morph map maintenue
   - Relations quiz optimisées
   - Backward compatibility

7. **✅ Production Seeder finalisé**
   - `HerokuProductionSeeder` optimisé
   - Données essentielles uniquement
   - Optimisation PostgreSQL intégrée

## 🔧 Instructions de migration

### 1. Prérequis

```bash
# Installer PostgreSQL (local ou utiliser Heroku)
# Option A: Installation locale
# Télécharger depuis https://www.postgresql.org/download/

# Option B: Utiliser Heroku PostgreSQL
heroku addons:create heroku-postgresql:mini
```

### 2. Configuration environnement

```bash
# Copier la configuration PostgreSQL
cp .env.heroku .env

# Modifier les variables selon votre setup
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=breitling_league
DB_USERNAME=postgres
DB_PASSWORD=your_password
DB_SSLMODE=prefer
```

### 3. Migration des données

```bash
# Option A: Migration propre (recommandé)
php artisan migrate:fresh --force
php artisan db:seed --class=HerokuProductionSeeder --force

# Option B: Migration depuis SQLite existante
php artisan db:seed --class=SqliteToPostgresqlMigrationSeeder --force
```

### 4. Tests de validation

```bash
# Tester la compatibilité PostgreSQL
php artisan breitling:test-postgresql

# Valider les timestamps
php artisan breitling:validate-timestamps

# Tester les Event Listeners
php artisan test --filter=EventListenerPostgreSQLTest
```

### 5. Déploiement Heroku avec Git Subtree

**⚠️ Important:** Ce projet utilise Git subtree pour le backend.

```bash
# Déploiement automatisé avec subtree (depuis la racine du projet)
./backend/bin/deploy-heroku-subtree.sh votre-app-name

# Ou PowerShell sur Windows  
./backend/bin/deploy-heroku-subtree.ps1 votre-app-name

# Déploiement manuel avec subtree
git remote add backend https://git.heroku.com/votre-app.git
git subtree push --prefix=backend backend main
```

**Structure du projet:**
```
racine-projet/
├── backend/          # Subtree Git séparé
│   ├── app/
│   ├── config/
│   └── bin/
│       ├── deploy-heroku-subtree.sh    # Nouveau script
│       └── deploy-heroku-subtree.ps1   # Nouveau script  
├── frontend/
└── docs/
```

## 🔍 Différences importantes SQLite ↔ PostgreSQL

### Types de données

| SQLite | PostgreSQL | Notes |
|--------|------------|-------|
| `INTEGER PRIMARY KEY` | `BIGSERIAL PRIMARY KEY` | Auto-increment natif |
| `TEXT` | `VARCHAR(255)` ou `TEXT` | Limites de taille |
| `REAL` | `DECIMAL` ou `NUMERIC` | Précision décimale |
| `JSON` | `JSON` ou `JSONB` | JSONB recommandé |

### Fonctions date/heure

```sql
-- SQLite
strftime('%Y-%m', created_at) = ?

-- PostgreSQL  
TO_CHAR(created_at, 'YYYY-MM') = ?

-- MySQL
DATE_FORMAT(created_at, '%Y-%m') = ?
```

### Syntaxe JSON

```sql
-- SQLite
JSON_EXTRACT(data, '$.key')

-- PostgreSQL
data->>'key' OR data->'key'
```

## 🎯 Event Listeners multi-DB

Le système `SynchronizeUserScore` s'adapte automatiquement :

```php
private function getDateFormatSQL(): string
{
    $connection = config('database.default');
    
    switch ($connection) {
        case 'pgsql':
            return "TO_CHAR(created_at, 'YYYY-MM') = ?";
        case 'mysql':
            return "DATE_FORMAT(created_at, '%Y-%m') = ?";
        default: // SQLite
            return "strftime('%Y-%m', created_at) = ?";
    }
}
```

## 🚀 Procfile optimisé

```
web: vendor/bin/heroku-php-apache2 public/
release: php artisan migrate --force && php artisan db:seed --class=HerokuProductionSeeder --force && php artisan config:cache && php artisan event:cache
```

## ⚡ Performance PostgreSQL

### Optimisations automatiques

Le `HerokuProductionSeeder` inclut :

```php
private function optimizeDatabase(): void
{
    if (config('database.default') === 'pgsql') {
        // Mettre à jour les statistiques
        DB::statement('ANALYZE;');
        
        // Nettoyer et optimiser
        DB::statement('VACUUM;');
    }
}
```

### Index recommandés

```sql
-- Index composites pour les performances
CREATE INDEX idx_quiz_instances_polymorphic ON quiz_instances(quizable_type, quizable_id);
CREATE INDEX idx_user_quiz_scores_instance ON user_quiz_scores(quiz_instance_id);
CREATE INDEX idx_scores_user_date ON scores(user_id, created_at);
CREATE INDEX idx_users_rank ON users(rank_id);
```

## 🔍 Validation et tests

### Tests automatisés

```bash
# Test complet PostgreSQL
php artisan breitling:test-postgresql --detailed

# Test Event Listeners spécifiquement  
php artisan test tests/Feature/EventListenerPostgreSQLTest.php

# Validation des timestamps
php artisan breitling:validate-timestamps
```

### Checks manuels

```sql
-- Vérifier la configuration PostgreSQL
SELECT version();
SELECT current_database();
SELECT current_user;

-- Vérifier les tables migrées
\dt
\d+ quiz_instances

-- Vérifier les données
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM quiz_instances;
```

## ❗ Points d'attention

### SSL sur Heroku

```php
// config/database.php
'pgsql' => [
    // ...existing config...
    'sslmode' => env('DB_SSLMODE', 'require'),
],
```

### Event Listeners

Assurer la compatibilité multi-DB dans tous les listeners utilisant SQL brut.

### JSON Queries

Préférer Eloquent aux requêtes SQL brutes pour JSON :

```php
// ✅ Recommandé
Question::whereJsonContains('options', $value)->get();

// ❌ Éviter (spécifique à une DB)
DB::select("SELECT * WHERE data->>'key' = ?", [$value]);
```

## 📊 Résultats des tests

### Validation timestamps

```
✅ Rank: timestamps = true, table has created_at/updated_at
✅ QuizType: timestamps = true, table has created_at/updated_at  
✅ Question: timestamps = true, table has created_at/updated_at
✅ Progress: timestamps = true, table has created_at/updated_at
✅ Weekly: timestamps = true, table has created_at/updated_at
✅ UserAnswer: timestamps = true, table has created_at/updated_at
```

### Test PostgreSQL

```
✅ Polymorphic Relations: PASSED (7 modèles configurés)
✅ Transactions: PASSED
✅ Event Listeners: PASSED (avec PostgreSQL)
✅ Date Functions: PASSED (TO_CHAR disponible)
✅ JSON Queries: PASSED (syntaxe PostgreSQL)
✅ Database Connection: PASSED (PostgreSQL actif)
```

## 🎉 Migration réussie !

Le système Breitling League est maintenant **100% compatible PostgreSQL** et prêt pour le déploiement Heroku production.

### Commandes de démarrage

```bash
# Développement (SQLite)
./scripts/init.sh

# Production (PostgreSQL avec Git Subtree)
# Depuis la racine du projet:
./backend/bin/deploy-heroku-subtree.sh votre-app-name

# Déploiement manuel subtree:
git subtree push --prefix=backend backend main
```

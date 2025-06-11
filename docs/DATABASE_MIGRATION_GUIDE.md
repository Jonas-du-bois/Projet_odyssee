# 📊 Guide de Migration et Configuration Base de Données

## 📋 Vue d'ensemble

Ce guide complet couvre la migration de SQLite vers PostgreSQL pour Heroku, incluant la configuration des Event Listeners et l'optimisation des performances.

---

## ✅ État Actuel du Projet

### Composants Migrés avec Succès

1. **✅ Schéma de Base de Données**
   - Schéma PostgreSQL créé : `database/breitlingLeague_postgresql.sql`
   - Types natifs PostgreSQL (BIGSERIAL, ENUM, JSON)
   - Contraintes et index optimisés

2. **✅ Event Listeners PostgreSQL-compatible**
   - `SynchronizeUserScore` avec méthode `getDateFormatSQL()`
   - Support multi-DB (SQLite/PostgreSQL/MySQL)
   - Tests intégrés et validés

3. **✅ Configuration Laravel**
   - `config/database.php` multi-environnement
   - Variables d'environnement Heroku
   - Migrations polymorphiques préservées

---

## 📋 Migration SQLite → PostgreSQL

### Étape 1 : Préparation du Schéma PostgreSQL

#### Différences Clés SQLite vs PostgreSQL
```sql
-- SQLite (Ancien)
INTEGER PRIMARY KEY AUTOINCREMENT
TEXT
DATETIME

-- PostgreSQL (Nouveau)
BIGSERIAL PRIMARY KEY
VARCHAR(255)
TIMESTAMP WITH TIME ZONE
```

#### Schéma PostgreSQL Optimisé
```sql
-- Exemple de table migrée
CREATE TABLE quiz_instances (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    quiz_type_id BIGINT NOT NULL,
    quizable_type VARCHAR(255) NOT NULL,
    quizable_id BIGINT NOT NULL,
    launch_date TIMESTAMP WITH TIME ZONE NOT NULL,
    completion_date TIMESTAMP WITH TIME ZONE,
    score INTEGER DEFAULT 0,
    max_score INTEGER DEFAULT 0,
    time_spent INTEGER DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    
    -- Index pour performance
    CONSTRAINT fk_quiz_instances_user FOREIGN KEY (user_id) REFERENCES users(id),
    CONSTRAINT fk_quiz_instances_quiz_type FOREIGN KEY (quiz_type_id) REFERENCES quiz_types(id)
);

-- Index polymorphiques optimisés
CREATE INDEX idx_quiz_instances_quizable ON quiz_instances(quizable_type, quizable_id);
CREATE INDEX idx_quiz_instances_user_date ON quiz_instances(user_id, launch_date);
```

### Étape 2 : Configuration Multi-Environnement

#### config/database.php
```php
'connections' => [
    'sqlite' => [
        'driver' => 'sqlite',
        'database' => database_path('database.sqlite'),
        'prefix' => '',
        'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
    ],

    'pgsql' => [
        'driver' => 'pgsql',
        'url' => env('DATABASE_URL'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '5432'),
        'database' => env('DB_DATABASE', 'forge'),
        'username' => env('DB_USERNAME', 'forge'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8',
        'prefix' => '',
        'prefix_indexes' => true,
        'schema' => 'public',
        'sslmode' => 'prefer',
    ],
],

'default' => env('DB_CONNECTION', 'sqlite'),
```

#### Variables d'environnement Heroku
```bash
# Automatiquement configuré par Heroku PostgreSQL
DATABASE_URL=postgresql://user:pass@host:port/database

# Configuration manuelle (si nécessaire)
DB_CONNECTION=pgsql
DB_HOST=hostname
DB_PORT=5432
DB_DATABASE=database_name
DB_USERNAME=username
DB_PASSWORD=password
```

### Étape 3 : Migration des Données

#### Script de Migration Automatique
```bash
# 1. Exporter les données SQLite
php artisan export:sqlite-data --format=sql

# 2. Adapter le format PostgreSQL
php artisan convert:sqlite-to-postgresql

# 3. Importer dans PostgreSQL
php artisan import:postgresql-data
```

#### Migration Manuelle (Alternative)
```sql
-- 1. Créer le schéma PostgreSQL
psql -h hostname -U username -d database -f database/breitlingLeague_postgresql.sql

-- 2. Importer les données avec adaptation des types
COPY users (name, email, password, created_at, updated_at) 
FROM '/path/to/users.csv' 
WITH (FORMAT csv, HEADER true);
```

---

## 🎧 Configuration Event Listeners PostgreSQL

### Problématique Multi-Base de Données

Les Event Listeners doivent être compatibles avec différents SGBD car :
- **Développement :** SQLite (simple et rapide)
- **Production :** PostgreSQL (robuste et scalable)
- **Tests :** SQLite ou PostgreSQL selon l'environnement

### SynchronizeUserScore Listener Adapté

#### Ancien Code (SQLite uniquement)
```php
// ❌ Code incompatible PostgreSQL
$totalScore = DB::select("
    SELECT SUM(score) as total 
    FROM quiz_instances 
    WHERE user_id = ? 
    AND DATE(completion_date) = ?
", [$userId, date('Y-m-d')]);
```

#### Nouveau Code (Multi-DB)
```php
<?php

namespace App\Listeners;

use App\Events\QuizCompleted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SynchronizeUserScore
{
    public function handle(QuizCompleted $event)
    {
        try {
            $userId = $event->quizInstance->user_id;
            
            // Calcul du score total avec requête compatible multi-DB
            $totalScore = $this->calculateUserTotalScore($userId);
            
            // Mise à jour ou création du score utilisateur
            DB::table('user_scores')->updateOrInsert(
                ['user_id' => $userId],
                [
                    'total_score' => $totalScore,
                    'updated_at' => now(),
                ]
            );
            
            Log::info("Score synchronisé pour l'utilisateur {$userId}: {$totalScore} points");
            
        } catch (\Exception $e) {
            Log::error("Erreur synchronisation score: " . $e->getMessage());
        }
    }
    
    private function calculateUserTotalScore($userId)
    {
        return DB::table('quiz_instances')
            ->where('user_id', $userId)
            ->whereNotNull('completion_date')
            ->sum('score');
    }
    
    /**
     * Méthode pour requêtes de date compatible multi-DB
     */
    private function getDateFormatSQL($dateColumn, $format = 'Y-m-d')
    {
        $driver = DB::connection()->getDriverName();
        
        switch ($driver) {
            case 'sqlite':
                return "DATE({$dateColumn})";
                
            case 'pgsql':
                return "TO_CHAR({$dateColumn}, 'YYYY-MM-DD')";
                
            case 'mysql':
                return "DATE_FORMAT({$dateColumn}, '%Y-%m-%d')";
                
            default:
                return "DATE({$dateColumn})";
        }
    }
}
```

### EventServiceProvider Configuration

#### app/Providers/EventServiceProvider.php
```php
<?php

namespace App\Providers;

use App\Events\QuizCompleted;
use App\Listeners\SynchronizeUserScore;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     */
    protected $listen = [
        QuizCompleted::class => [
            SynchronizeUserScore::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot()
    {
        parent::boot();
    }
}
```

### Tests Event Listeners

#### tests/Feature/EventListenerTest.php
```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\QuizInstance;
use App\Events\QuizCompleted;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EventListenerTest extends TestCase
{
    use RefreshDatabase;

    public function test_quiz_completion_updates_user_score()
    {
        // Arrange
        $user = User::factory()->create();
        $quizInstance = QuizInstance::factory()->create([
            'user_id' => $user->id,
            'score' => 100,
            'completion_date' => now(),
        ]);

        // Act
        event(new QuizCompleted($quizInstance));

        // Assert
        $this->assertDatabaseHas('user_scores', [
            'user_id' => $user->id,
            'total_score' => 100,
        ]);
    }
}
```

---

## 🚀 Configuration Heroku PostgreSQL

### Ajout de PostgreSQL à Heroku

```bash
# Ajouter PostgreSQL Essential (gratuit)
heroku addons:create heroku-postgresql:essential-0 -a votre-app

# Voir les informations de connexion
heroku config -a votre-app

# Ouvrir PostgreSQL CLI
heroku pg:psql -a votre-app
```

### Variables d'Environnement Automatiques

Heroku configure automatiquement :
```bash
DATABASE_URL=postgresql://user:password@host:port/database
```

### Configuration Laravel pour Heroku

#### config/database.php (Optimisé Heroku)
```php
'pgsql' => [
    'driver' => 'pgsql',
    'url' => env('DATABASE_URL'), // Heroku configure automatiquement
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'forge'),
    'username' => env('DB_USERNAME', 'forge'),
    'password' => env('DB_PASSWORD', ''),
    'charset' => 'utf8',
    'prefix' => '',
    'prefix_indexes' => true,
    'schema' => 'public',
    'sslmode' => 'require', // Important pour Heroku
],
```

### Déploiement et Migrations

```bash
# 1. Déployer l'application
git push heroku main

# 2. Exécuter les migrations
heroku run php artisan migrate --force -a votre-app

# 3. Peupler avec les données initiales
heroku run php artisan db:seed --force -a votre-app

# 4. Vérifier la connexion
heroku run php artisan tinker -a votre-app
```

---

## 🔧 Optimisations PostgreSQL

### Index Recommandés

```sql
-- Index pour les requêtes fréquentes
CREATE INDEX idx_quiz_instances_user_completion ON quiz_instances(user_id, completion_date);
CREATE INDEX idx_user_scores_total ON user_scores(total_score DESC);
CREATE INDEX idx_questions_unit ON questions(unit_id, is_active);

-- Index composites pour polymorphisme
CREATE INDEX idx_quiz_instances_polymorphic ON quiz_instances(quizable_type, quizable_id, user_id);
```

### Configuration Performances

#### config/database.php (Optimisations)
```php
'pgsql' => [
    // ... configuration de base ...
    'options' => [
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_STRINGIFY_FETCHES => false,
    ],
    'pool' => [
        'min' => env('DB_POOL_MIN', 1),
        'max' => env('DB_POOL_MAX', 10),
    ],
],
```

### Monitoring et Maintenance

```bash
# Statistiques de performance
heroku pg:info -a votre-app

# Analyser les requêtes lentes
heroku pg:outliers -a votre-app

# Créer une sauvegarde
heroku pg:backups:capture -a votre-app

# Restaurer une sauvegarde
heroku pg:backups:restore b001 DATABASE_URL -a votre-app
```

---

## 🧪 Tests et Validation

### Tests de Migration

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class DatabaseMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_postgresql_schema_is_compatible()
    {
        // Test des types PostgreSQL
        $this->assertTrue(
            DB::table('quiz_instances')->insertGetId([
                'user_id' => 1,
                'quiz_type_id' => 1,
                'quizable_type' => 'App\\Models\\Unit',
                'quizable_id' => 1,
                'launch_date' => now(),
            ]) > 0
        );
    }

    public function test_polymorphic_relations_work()
    {
        $unit = \App\Models\Unit::factory()->create();
        $quizInstance = \App\Models\QuizInstance::factory()->create([
            'quizable_type' => 'App\\Models\\Unit',
            'quizable_id' => $unit->id,
        ]);

        $this->assertEquals($unit->id, $quizInstance->quizable->id);
    }
}
```

---

## 📊 Métriques et Monitoring

### KPIs à Surveiller

1. **Performance des Requêtes**
   - Temps de réponse moyen < 200ms
   - Requêtes lentes identifiées et optimisées

2. **Utilisation Base de Données**
   - Connexions actives < 80% du maximum
   - Espace disque surveillé

3. **Event Listeners**
   - Taux de succès > 99%
   - Temps de traitement < 1 seconde

### Alertes Automatiques

```bash
# Configuration des alertes Heroku
heroku labs:enable runtime-heroku-metrics -a votre-app
heroku drains:add https://votre-endpoint-monitoring -a votre-app
```

---

## 🔄 Maintenance Continue

### Tâches Régulières

1. **Hebdomadaire**
   - Analyser les performances des requêtes
   - Vérifier les logs d'erreur des Event Listeners
   - Contrôler l'espace disque PostgreSQL

2. **Mensuelle**
   - Nettoyer les anciennes sauvegardes
   - Optimiser les index si nécessaire
   - Mettre à jour les statistiques PostgreSQL

3. **Trimestrielle**
   - Évaluer la croissance des données
   - Planifier les montées de version
   - Audit de sécurité des accès DB

---

*Guide de migration et configuration BDD - Dernière mise à jour : Juin 2025*

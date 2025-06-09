# 🏗️ ARCHITECTURE MODULAIRE BREITLING LEAGUE - ANALYSE ET SOLUTION

## 📋 FONCTIONNEMENT DÉTAILLÉ PAR TYPE DE QUIZ

### 🔍 **Discovery & Novelty**
- **Base** : Chapitres complets
- **Mode 1** : Apprentissage (théorie + questions intercalées)
- **Mode 2** : Quiz pur (après avoir fait la théorie une première fois)
- **Particularité** : Rejouable à volonté pour améliorer le score

### 🎪 **Event**
- **Base** : Collection d'unités de différents chapitres
- **Thématique** : Liés aux temps forts (Fête des Mères, etc.)
- **Contenu** : Questions variées selon l'événement

### 📅 **Weekly**
- **Base** : Questions aléatoires d'un chapitre
- **Objectif** : Tickets de loterie
- **Fréquence** : Hebdomadaire

### ⏰ **Reminder**
- **Base** : Questions aléatoires d'un chapitre déjà vu
- **Objectif** : Révision rapide
- **Contenu** : Discoveries déjà complétées

## 🎯 SOLUTION ARCHITECTURALE OPTIMISÉE

### Structure Polymorphe Adaptée

```php
// Chaque type de quiz a sa propre logique métier
interface QuizableModule 
{
    public function getQuestions(array $options = []): Collection;
    public function getTitle(): string;
    public function getDescription(): string;
    public function isAvailable(User $user): bool;
    public function getQuizMode(): string; // 'learning', 'quiz', 'random'
}
```

### Modèles Spécialisés

#### 1. Discovery (Mode Apprentissage + Quiz)
```php
class Discovery implements QuizableModule
{
    public function getQuestions(array $options = []): Collection
    {
        $mode = $options['mode'] ?? 'learning';
        
        return match($mode) {
            'learning' => $this->getLearningQuestions(),
            'quiz' => $this->getQuizOnlyQuestions(),
            default => collect()
        };
    }
    
    private function getLearningQuestions(): Collection
    {
        // Questions intercalées avec la théorie
        return $this->chapter->units()
            ->with(['questions.choices'])
            ->get()
            ->flatMap(function($unit) {
                return $unit->questions->map(function($question) use ($unit) {
                    $question->theory_content = $unit->theory_html;
                    return $question;
                });
            });
    }
    
    private function getQuizOnlyQuestions(): Collection
    {
        // Quiz pur sans théorie
        return $this->chapter->units()
            ->with(['questions.choices'])
            ->get()
            ->pluck('questions')
            ->flatten();
    }
    
    public function isAvailable(User $user): bool
    {
        return $this->available_date <= now();
    }
    
    public function getQuizMode(): string
    {
        return 'learning_or_quiz';
    }
}
```

#### 2. Event (Multi-Unités Thématiques)
```php
class Event implements QuizableModule
{
    // Relation many-to-many avec les unités
    public function units()
    {
        return $this->belongsToMany(Unit::class, 'event_units');
    }
    
    public function getQuestions(array $options = []): Collection
    {
        // Questions de toutes les unités de l'événement
        return $this->units()
            ->with(['questions.choices'])
            ->get()
            ->pluck('questions')
            ->flatten()
            ->shuffle(); // Mélange pour varier
    }
    
    public function isAvailable(User $user): bool
    {
        return $this->start_date <= now() && 
               $this->end_date >= now();
    }
    
    public function getQuizMode(): string
    {
        return 'event_quiz';
    }
}
```

#### 3. Weekly (Questions Aléatoires d'un Chapitre)
```php
class Weekly implements QuizableModule
{
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
    
    public function getQuestions(array $options = []): Collection
    {
        $limit = $options['limit'] ?? 5;
        
        // Questions aléatoires du chapitre
        return $this->chapter->units()
            ->with(['questions.choices'])
            ->get()
            ->pluck('questions')
            ->flatten()
            ->shuffle()
            ->take($limit);
    }
    
    public function isAvailable(User $user): bool
    {
        // Disponible chaque semaine
        return $this->week_start <= now() && 
               $this->week_end >= now();
    }
    
    public function getQuizMode(): string
    {
        return 'random_questions';
    }
}
```

#### 4. Reminder (Révision des Discoveries)
```php
class Reminder implements QuizableModule
{
    public function getQuestions(array $options = []): Collection
    {
        $user = $options['user'] ?? null;
        $limit = $options['limit'] ?? 3;
        
        if (!$user) return collect();
        
        // Questions des discoveries déjà complétées par l'utilisateur
        $completedDiscoveries = $user->completedDiscoveries();
        
        return $completedDiscoveries
            ->flatMap(function($discovery) {
                return $discovery->getQuestions(['mode' => 'quiz']);
            })
            ->shuffle()
            ->take($limit);
    }
    
    public function isAvailable(User $user): bool
    {
        // Disponible si l'utilisateur a complété au moins une discovery
        return $user->completedDiscoveries()->count() > 0;
    }
    
    public function getQuizMode(): string
    {
        return 'revision';
    }
}
```

### QuizInstance Amélioré

```php
class QuizInstance extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_type_id',
        'quizable_type',
        'quizable_id',
        'quiz_mode', // Nouveau champ pour distinguer les modes
        'launch_date',
    ];
    
    public function quizable()
    {
        return $this->morphTo();
    }
    
    public function getQuestionsForQuiz(array $options = []): Collection
    {
        $options['mode'] = $this->quiz_mode;
        $options['user'] = $this->user;
        
        return $this->quizable->getQuestions($options);
    }
    
    public function canReplay(): bool
    {
        return in_array($this->quizable_type, [
            Discovery::class,
            Novelty::class
        ]);
    }
}
```

## 🗄️ STRUCTURE DE BASE DE DONNÉES ADAPTÉE

### Migration Améliorée
```sql
-- Table centrale quiz_instances
CREATE TABLE quiz_instances (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    quiz_type_id BIGINT NOT NULL,
    quizable_type VARCHAR(255) NOT NULL,
    quizable_id BIGINT NOT NULL,
    quiz_mode VARCHAR(50), -- 'learning', 'quiz', 'random', 'revision'
    launch_date DATETIME NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Table de liaison pour les événements multi-unités
CREATE TABLE event_units (
    id BIGINT PRIMARY KEY,
    event_id BIGINT NOT NULL,
    unit_id BIGINT NOT NULL,
    created_at TIMESTAMP
);

-- Progress tracking pour les modes d'apprentissage
CREATE TABLE user_learning_progress (
    id BIGINT PRIMARY KEY,
    user_id BIGINT NOT NULL,
    discovery_id BIGINT NOT NULL,
    has_completed_theory BOOLEAN DEFAULT FALSE,
    theory_completion_date DATETIME,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## 🎮 FLUX UTILISATEUR ADAPTÉ

### 1. Discovery/Novelty avec Double Mode
```php
class DiscoveryController extends Controller
{
    public function start(Request $request, Discovery $discovery)
    {
        $user = $request->user();
        $hasCompletedTheory = $user->hasCompletedTheory($discovery);
        
        // Mode automatique selon l'historique
        $mode = $hasCompletedTheory ? 'quiz' : 'learning';
        
        // Ou mode forcé par l'utilisateur
        if ($request->has('force_mode')) {
            $mode = $request->input('force_mode');
        }
        
        $quizInstance = QuizInstance::create([
            'user_id' => $user->id,
            'quiz_type_id' => QuizType::DISCOVERY,
            'quizable_type' => Discovery::class,
            'quizable_id' => $discovery->id,
            'quiz_mode' => $mode,
            'launch_date' => now()
        ]);
        
        return response()->json([
            'quiz_instance_id' => $quizInstance->id,
            'mode' => $mode,
            'questions' => $quizInstance->getQuestionsForQuiz()
        ]);
    }
}
```

### 2. Event Multi-Unités
```php
class EventController extends Controller
{
    public function start(Request $request, Event $event)
    {
        $quizInstance = QuizInstance::create([
            'user_id' => $request->user()->id,
            'quiz_type_id' => QuizType::EVENT,
            'quizable_type' => Event::class,
            'quizable_id' => $event->id,
            'quiz_mode' => 'event_quiz',
            'launch_date' => now()
        ]);
        
        return response()->json([
            'quiz_instance_id' => $quizInstance->id,
            'event_theme' => $event->theme,
            'units_covered' => $event->units->pluck('title'),
            'questions' => $quizInstance->getQuestionsForQuiz()
        ]);
    }
}
```

### 3. Weekly avec Questions Aléatoires
```php
class WeeklyController extends Controller
{
    public function start(Request $request, Weekly $weekly)
    {
        $quizInstance = QuizInstance::create([
            'user_id' => $request->user()->id,
            'quiz_type_id' => QuizType::WEEKLY,
            'quizable_type' => Weekly::class,
            'quizable_id' => $weekly->id,
            'quiz_mode' => 'random_questions',
            'launch_date' => now()
        ]);
        
        return response()->json([
            'quiz_instance_id' => $quizInstance->id,
            'chapter' => $weekly->chapter->title,
            'questions' => $quizInstance->getQuestionsForQuiz(['limit' => 5]),
            'reward' => 'lottery_ticket'
        ]);
    }
}
```

## 🎯 AVANTAGES DE CETTE APPROCHE

### ✅ **Modularité Préservée**
- Chaque type de quiz a sa logique propre
- Interface commune mais implémentations spécialisées
- Facile d'ajouter de nouveaux types

### ✅ **Flexibilité Maximale**
- Discoveries rejouables en mode quiz
- Events configurables avec n'importe quelles unités
- Weekly et Reminder avec logique aléatoire

### ✅ **Performance Optimisée**
- Eager loading adapté à chaque type
- Pas de switch/case dans les modèles
- Cache possible par type de quiz

### ✅ **Maintenance Facilitée**
- Code organisé par responsabilité
- Tests unitaires par type
- Évolution indépendante des modules

Cette architecture respecte parfaitement la philosophie de la Breitling League tout en gardant un code maintenable et performant ! 🎯

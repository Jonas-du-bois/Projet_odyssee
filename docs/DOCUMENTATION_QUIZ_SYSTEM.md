# 🎯 Documentation du Système de Quiz - Breitling League

## Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Structure de la Base de Données](#structure-de-la-base-de-données)
3. [Relations entre les Tables](#relations-entre-les-tables)
4. [Parcours Utilisateur Type](#parcours-utilisateur-type)
5. [Endpoints API](#endpoints-api)
6. [Règles Métier](#règles-métier)
7. [Correction du Raisonnement Initial](#correction-du-raisonnement-initial)

---

## Vue d'ensemble

Le système de quiz de Breitling League est structuré autour d'un modèle flexible qui permet de créer des quiz basés sur différents types de modules (Units, Discoveries, Events, etc.). Contrairement à la compréhension initiale, **les quiz ne sont pas exclusivement liés aux Discoveries** mais peuvent être associés à différents types de modules.

---

## Structure de la Base de Données

### Tables Principales

#### 1. **chapters** - Chapitres
```sql
- id: bigint (PK)
- title: varchar(255) - Titre du chapitre
- description: text - Description détaillée
- theory_content: longtext - Contenu théorique complet
- order_index: int - Ordre d'affichage
- is_active: boolean - Statut actif/inactif
- created_at, updated_at: timestamps
```

#### 2. **units** - Unités
```sql
- id: bigint (PK)
- chapter_id: bigint (FK → chapters.id)
- title: varchar(255) - Titre de l'unité
- description: text - Description
- content: longtext - Contenu pédagogique
- order_index: int - Ordre dans le chapitre
- is_active: boolean
- points_reward: int - Points accordés à la completion
- created_at, updated_at: timestamps
```

#### 3. **discoveries** - Découvertes
```sql
- id: bigint (PK)
- chapter_id: bigint (FK → chapters.id)
- title: varchar(255)
- description: text
- content: longtext - Contenu de la découverte
- discovery_date: date - Date de publication
- points_reward: int - Points accordés
- is_active: boolean
- created_at, updated_at: timestamps
```

#### 4. **quiz_types** - Types de Quiz
```sql
- id: bigint (PK)
- nom: varchar(255) - Nom du type (Unit Quiz, Discovery Quiz, etc.)
- base_points: int - Points de base accordés
- speed_bonus: int - Bonus de vitesse maximum
- gives_ticket: boolean - Donne-t-il un ticket de loterie
- created_at, updated_at: timestamps
```

#### 5. **quiz_instances** - Instances de Quiz
```sql- id: bigint (PK)
- user_id: bigint (FK → users.id)
- quiz_type_id: bigint (FK → quiz_types.id)
- module_type: enum('Unit', 'Discovery', 'Event', 'Weekly', 'Novelty', 'Reminder')
- module_id: bigint - ID du module associé
- chapter_id: bigint (FK → chapters.id, nullable)
- status: enum('started', 'completed', 'abandoned')
- score: int - Score final
- max_score: int - Score maximum possible
- time_limit: int - Temps limite en secondes
- time_taken: int - Temps effectivement pris
- bonus_points: int - Points bonus accordés
- created_at, updated_at: timestamps
- completed_at: timestamp
```

#### 6. **questions** - Questions
```sql
- id: bigint (PK)
- quiz_instance_id: bigint (FK → quiz_instances.id)
- question_text: text - Texte de la question
- question_type: enum('multiple_choice', 'true_false', 'text')
- points: int - Points de la question
- order_index: int - Ordre dans le quiz
- created_at, updated_at: timestamps
```

#### 7. **choices** - Choix de Réponses
```sql
- id: bigint (PK)
- question_id: bigint (FK → questions.id)
- choice_text: text - Texte du choix
- is_correct: boolean - Si c'est la bonne réponse
- order_index: int - Ordre d'affichage
- created_at, updated_at: timestamps
```

#### 8. **user_answers** - Réponses Utilisateur
```sql
- id: bigint (PK)
- quiz_instance_id: bigint (FK → quiz_instances.id)
- question_id: bigint (FK → questions.id)
- choice_id: bigint (FK → choices.id, nullable)
- answer_text: text (nullable) - Pour les questions texte
- is_correct: boolean - Si la réponse est correcte
- points_earned: int - Points gagnés pour cette question
- created_at, updated_at: timestamps
```

#### 9. **user_quiz_scores** - Scores Utilisateur
```sql
- id: bigint (PK)
- user_id: bigint (FK → users.id)
- quiz_type_id: bigint (FK → quiz_types.id)
- module_type: enum(...)
- module_id: bigint
- total_score: int - Score total accumulé
- best_score: int - Meilleur score
- total_attempts: int - Nombre de tentatives
- last_completed_at: timestamp
- created_at, updated_at: timestamps
```

#### 10. **progress** - Progression Utilisateur
```sql
- id: bigint (PK)
- user_id: bigint (FK → users.id)
- progressable_type: varchar(255) - Type de module (Unit, Discovery, etc.)
- progressable_id: bigint - ID du module
- status: enum('not_started', 'in_progress', 'completed')
- completion_percentage: decimal(5,2)
- completed_at: timestamp (nullable)
- created_at, updated_at: timestamps
```

---

## Relations entre les Tables

### Hiérarchie du Contenu
```
Chapters (1) ──→ (N) Units
    ↓
    └── (N) Discoveries
```

### Flux des Quiz
```
Users ──→ QuizInstances ──→ Questions ──→ Choices
  ↓           ↓               ↓
Progress   UserAnswers   UserQuizScores
```

### Relations Eloquent

#### Chapter Model
```php
// Un chapitre a plusieurs unités
public function units() {
    return $this->hasMany(Unit::class)->orderBy('order_index');
}

// Un chapitre a plusieurs découvertes
public function discoveries() {
    return $this->hasMany(Discovery::class);
}

// Quiz instances liées au chapitre
public function quizInstances() {
    return $this->hasMany(QuizInstance::class);
}
```

#### QuizInstance Model
```php
// Appartient à un utilisateur
public function user() {
    return $this->belongsTo(User::class);
}

// Appartient à un type de quiz
public function quizType() {
    return $this->belongsTo(QuizType::class);
}

// Appartient à un chapitre (optionnel)
public function chapter() {
    return $this->belongsTo(Chapter::class);
}

// A plusieurs questions
public function questions() {
    return $this->hasMany(Question::class)->orderBy('order_index');
}

// A plusieurs réponses utilisateur
public function userAnswers() {
    return $this->hasMany(UserAnswer::class);
}

// Relation polymorphe vers le module
public function module() {
    return $this->morphTo('module', 'module_type', 'module_id');
}
```

---

## Parcours Utilisateur Type

### 1. **Sélection du Quiz**
```
Utilisateur → Choisit un type de module (Unit/Discovery/Event)
           → Sélectionne un module spécifique
           → Choisit le type de quiz (si plusieurs disponibles)
```

### 2. **Démarrage du Quiz**
```php
POST /api/quiz/start
{
    "quiz_type_id": 1,
    "module_type": "Unit",
    "module_id": 5,
    "chapter_id": 3  // optionnel
}
```

**Processus backend :**
1. Création d'une `QuizInstance` avec status 'started'
2. Génération dynamique des questions basées sur le contenu théorique
3. Création des objets `Question` et `Choice`
4. Retour de la structure complète du quiz

### 3. **Réponse aux Questions**
```php
POST /api/quiz/{quizInstanceId}/answer
{
    "question_id": 1,
    "choice_id": 3  // ou "answer_text" pour les questions ouvertes
}
```

**Processus :**
1. Validation de la réponse
2. Calcul des points (correct/incorrect)
3. Sauvegarde dans `user_answers`
4. Mise à jour du statut de progression

### 4. **Finalisation du Quiz**
```php
POST /api/quiz/{quizInstanceId}/complete
```

**Processus :**
1. Calcul du score final
2. Application des bonus (vitesse, etc.)
3. Mise à jour de `quiz_instances.status` → 'completed'
4. Mise à jour de `user_quiz_scores`
5. Mise à jour de `progress`
6. Attribution éventuelle de tickets de loterie
7. Déclenchement d'événements (`QuizCompleted`)

---

## Endpoints API

### Quiz Management

#### `GET /api/quiz-types`
**Description :** Récupère tous les types de quiz disponibles
```json
{
    "data": [
        {
            "id": 1,
            "nom": "Unit Quiz",
            "base_points": 100,
            "speed_bonus": 50,
            "gives_ticket": true,
            "instances_count": 25
        }
    ]
}
```

#### `POST /api/quiz/start`
**Description :** Démarre une nouvelle instance de quiz
```json
// Request
{
    "quiz_type_id": 1,
    "module_type": "Unit",
    "module_id": 5,
    "chapter_id": 3
}

// Response
{
    "quiz_instance_id": 123,
    "total_questions": 10,
    "time_limit": 600,
    "questions": [
        {
            "id": 1,
            "question_text": "Quelle est la caractéristique principale de...",
            "question_type": "multiple_choice",
            "points": 10,
            "choices": [
                {
                    "id": 1,
                    "choice_text": "Option A",
                    "order_index": 1
                }
            ]
        }
    ]
}
```

#### `POST /api/quiz/{quizInstanceId}/answer`
**Description :** Enregistre une réponse utilisateur
```json
// Request
{
    "question_id": 1,
    "choice_id": 3
}

// Response
{
    "success": true,
    "is_correct": true,
    "points_earned": 10,
    "remaining_questions": 9
}
```

#### `POST /api/quiz/{quizInstanceId}/complete`
**Description :** Finalise le quiz et calcule les résultats
```json
// Response
{
    "quiz_instance_id": 123,
    "final_score": 85,
    "max_score": 100,
    "percentage": 85,
    "time_taken": 345,
    "bonus_points": 25,
    "total_points": 110,
    "ticket_earned": true,
    "detailed_results": [
        {
            "question": "...",
            "user_answer": "...",
            "correct_answer": "...",
            "is_correct": true,
            "points_earned": 10
        }
    ]
}
```

### Progress & Statistics

#### `GET /api/user/progress`
**Description :** Récupère la progression utilisateur
```json
{
    "chapters": [
        {
            "id": 1,
            "title": "Chapitre 1",
            "completion_percentage": 75,
            "units_progress": [...],
            "discoveries_progress": [...]
        }
    ]
}
```

#### `GET /api/user/quiz-stats`
**Description :** Statistiques détaillées des quiz
```json
{
    "total_quizzes": 45,
    "total_points": 4250,
    "average_score": 87.5,
    "by_type": [
        {
            "quiz_type": "Unit Quiz",
            "attempts": 20,
            "best_score": 95,
            "average_score": 85
        }
    ]
}
```

---

## Règles Métier

### 1. **Génération des Questions**
- Les questions sont générées dynamiquement à partir du `theory_content` des chapitres
- Utilisation d'IA (OpenAI) pour créer des questions pertinentes
- Mix de types : choix multiples, vrai/faux, questions ouvertes
- Difficulté adaptée au niveau du module

### 2. **Système de Points**
```php
Score final = Score de base + Bonus vitesse + Bonus précision

Bonus vitesse = (temps_limite - temps_pris) / temps_limite * speed_bonus_max
Bonus précision = (réponses_correctes / total_questions) * precision_bonus
```

### 3. **Attribution des Tickets**
- Certains types de quiz donnent des tickets de loterie (`gives_ticket = true`)
- Un ticket maximum par type de quiz par jour
- Les tickets sont attribués seulement si le score ≥ 70%

### 4. **Progression**
- Une `Progress` est créée/mise à jour pour chaque module tenté
- Status : 'not_started' → 'in_progress' → 'completed'
- Completion à 100% si score ≥ 80%

### 5. **Restrictions**
- Un utilisateur peut refaire un quiz, mais seul le meilleur score compte
- Limite de temps stricte (pas d'extension possible)
- Questions en ordre aléatoire pour éviter la mémorisation

### 6. **Validation des Données**
```php
// Validation au démarrage
- quiz_type_id: required|exists:quiz_types,id
- module_type: required|in:Unit,Discovery,Event,Weekly,Novelty,Reminder
- module_id: required|integer|min:1
- chapter_id: sometimes|exists:chapters,id

// Validation des réponses
- question_id: required|exists:questions,id
- choice_id: required_without:answer_text|exists:choices,id
- answer_text: required_without:choice_id|string|max:1000
```

---

## Correction du Raisonnement Initial

### ❌ **Incompréhensions initiales :**

1. **"Chaque quiz est associé à un Discovery"**
   - **Réalité :** Les quiz peuvent être associés à différents types de modules (Unit, Discovery, Event, Weekly, Novelty, Reminder)

2. **"Quiz généré à partir du contenu théorique du chapitre lié"**
   - **Réalité :** La génération se base sur le `theory_content` du chapitre, mais aussi sur le contenu spécifique du module (Unit, Discovery, etc.)

3. **"Une instance contient les questions générées"**
   - **Réalité :** Correct, mais les questions sont liées à l'instance via une relation `hasMany`

### ✅ **Points corrects confirmés :**

1. **Structure hiérarchique Chapitres → Unités** ✓
2. **Création d'instances à chaque quiz** ✓
3. **Sauvegarde des réponses utilisateur** ✓
4. **Calcul de scores et bonus** ✓
5. **Mise à jour des points utilisateur** ✓

### 🔄 **Architecture réelle :**

```
User démarre un quiz
    ↓
Sélectionne : QuizType + ModuleType + ModuleId + (ChapterId optionnel)
    ↓
Système crée QuizInstance
    ↓
Génère Questions basées sur theory_content + module_content
    ↓
User répond → UserAnswers sauvegardées
    ↓
Finalisation → Calcul scores + mise à jour Progress + attribution tickets
```

---

## Conclusion

Le système de quiz est conçu pour être flexible et extensible, permettant de créer des quiz sur différents types de contenu tout en maintenant une progression cohérente de l'utilisateur. La structure de base de données supporte une gamification complète avec points, bonus, tickets et suivi de progression détaillé.

Les règles métier assurent l'équité et l'engagement des utilisateurs, while la génération dynamique des questions garantit une expérience unique à chaque tentative.

### DRY (Don't Repeat Yourself)
- Composable `useQuiz` centralisé
- Service `quiz.js` réutilisable
- Méthodes du contrôleur modulaires

### KISS (Keep It Simple, Stupid)
- API REST claire et intuitive
- Relations de base de données simples
- Flow utilisateur linéaire

### Clean Code
- Noms de variables explicites (`quizInstanceId`, `userAnswers`)
- Functions courtes et spécialisées
- Documentation complète des endpoints
- Gestion d'erreurs centralisée

---

*Documentation générée le 6 juin 2025*  
*Version du système : Laravel 11 + Vue 3*

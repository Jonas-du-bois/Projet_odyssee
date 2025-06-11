# 🎯 Système de Quiz Complet - Breitling League

## 📚 Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Architecture et Base de Données](#architecture-et-base-de-données)
3. [Guide d'Utilisation](#guide-dutilisation)
4. [Types de Quiz](#types-de-quiz)
5. [Endpoints API](#endpoints-api)
6. [Règles Métier](#règles-métier)
7. [Système de Points](#système-de-points)

---

## Vue d'ensemble

Le système de quiz de Breitling League utilise une **architecture polymorphique moderne** permettant la création de quiz basés sur différents types de modules (Units, Discoveries, Events, etc.). Cette flexibilité permet de gérer tous les types de contenu pédagogique de manière unifiée.

### ✨ Caractéristiques Principales
- **Architecture polymorphique** : Un système unifié pour tous types de quiz
- **Flexibilité** : Support de contenus variés (Discovery, Novelty, Weekly, Event)
- **Performance** : Relations optimisées avec eager loading
- **Extensibilité** : Ajout facile de nouveaux types de modules
- **Backward Compatibility** : Support des anciennes données

---

## Architecture et Base de Données

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

#### 3. **quiz_instances** - Instances de Quiz (Polymorphique)
```sql
- id: bigint (PK)
- user_id: bigint (FK → users.id)
- quiz_type_id: bigint (FK → quiz_types.id)
- quizable_type: varchar(255) - Type polymorphique (Unit, Discovery, etc.)
- quizable_id: bigint - ID polymorphique
- launch_date: datetime
- completion_date: datetime (nullable)
- score: int - Score final
- max_score: int - Score maximum possible
- time_spent: int - Temps passé en secondes
- created_at, updated_at: timestamps
```

#### 4. **quiz_answers** - Réponses aux Quiz
```sql
- id: bigint (PK)
- quiz_instance_id: bigint (FK → quiz_instances.id)
- question_id: bigint (FK → questions.id)
- selected_answer_ids: json - IDs des réponses sélectionnées
- is_correct: boolean - Réponse correcte ou non
- time_spent: int - Temps de réponse en secondes
- points_earned: int - Points obtenus pour cette question
- created_at, updated_at: timestamps
```

### Relations Polymorphiques

```php
// Dans QuizInstance.php
public function quizable()
{
    return $this->morphTo();
}

// Dans Unit.php, Discovery.php, etc.
public function quizInstances()
{
    return $this->morphMany(QuizInstance::class, 'quizable');
}
```

---

## Guide d'Utilisation

### 📱 Flux Utilisateur Complet

#### 1. 🔍 **Sélection du Quiz**
```
L'utilisateur ouvre l'application et voit :
• Discoveries (avec dates de disponibilité)
• Weekly Quiz (hebdomadaire)
• Events (épisodiques)
• Novelties (nouveautés)
• Reminders (rappels)
```

#### 2. 📖 **Étude Théorique (Optionnel)**
```
Avant le quiz, l'utilisateur peut :
• Lire le contenu théorique du chapitre
• Consulter les unités associées
• Se préparer au quiz
```

#### 3. 🎮 **Lancement du Quiz**
```
L'utilisateur lance le quiz :
• Une QuizInstance est créée avec relation polymorphique
• Les questions sont récupérées selon le type
• Le timer démarre automatiquement
```

#### 4. ❓ **Réponse aux Questions**
```
Pour chaque question :
⏱️ Timer affiché (15-45 secondes selon difficulté)
📝 Question avec choix multiples ou réponses multiples
✅ Validation de la réponse en temps réel
📊 Feedback immédiat (correct/incorrect)
🎯 Points attribués selon rapidité et précision
```

#### 5. 🏆 **Calcul et Affichage du Score**
```
Calcul automatique basé sur :
• Score de base (selon le type de quiz)
• Bonus de précision (% de bonnes réponses)
• Bonus de vitesse (temps de réponse)
• Multiplicateur selon le type :
  - Discovery: x2
  - Event: x3
  - Weekly: x1.5
  - Novelty: x2.5
• Attribution éventuelle de tickets de loterie
```

#### 6. 📈 **Sauvegarde et Progression**
```
Mise à jour automatique :
• Sauvegarde du score dans user_scores
• Mise à jour du rang utilisateur
• Synchronisation des achievements
• Notification des nouveaux rangs/badges
```

---

## Types de Quiz

### 🔍 **Discovery & Novelty**
- **Base :** Chapitres complets avec théorie
- **Mode 1 :** Apprentissage (théorie + questions intercalées)
- **Mode 2 :** Quiz pur (après avoir fait la théorie)
- **Particularité :** Rejouable à volonté pour améliorer le score
- **Points :** Score élevé, multiplicateur x2-2.5

### 🎪 **Event**
- **Base :** Collection d'unités de différents chapitres
- **Thématique :** Liés aux temps forts (Fête des Mères, etc.)
- **Contenu :** Questions variées selon l'événement
- **Particularité :** Disponibilité limitée dans le temps
- **Points :** Multiplicateur x3, bonus tickets loterie

### 📅 **Weekly**
- **Base :** Questions aléatoires d'un chapitre
- **Objectif :** Tickets de loterie hebdomadaires
- **Fréquence :** Renouvelé chaque semaine
- **Particularité :** Une tentative par semaine
- **Points :** Multiplicateur x1.5, focus sur tickets

### ⏰ **Reminder**
- **Base :** Révision de contenus précédents
- **Objectif :** Maintenir les connaissances
- **Fréquence :** Déclenchés automatiquement
- **Points :** Score standard, pas de multiplicateur

---

## Endpoints API

### Quiz Management
```http
GET    /api/quiz-types                    # Liste des types de quiz
GET    /api/quiz-instances/{userId}       # Historique des quiz d'un utilisateur
POST   /api/quiz-instances                # Créer une nouvelle instance
PUT    /api/quiz-instances/{id}/complete  # Terminer un quiz
DELETE /api/quiz-instances/{id}           # Supprimer une instance
```

### Questions & Answers
```http
GET    /api/quiz-instances/{id}/questions # Questions du quiz
POST   /api/quiz-instances/{id}/answers   # Soumettre une réponse
GET    /api/quiz-instances/{id}/results   # Résultats du quiz
```

### Content Access
```http
GET    /api/chapters                      # Liste des chapitres
GET    /api/chapters/{id}/units          # Unités d'un chapitre
GET    /api/discoveries                   # Liste des discoveries
GET    /api/events/active                # Events actifs
```

---

## Règles Métier

### Limitations et Contrôles
- **Discovery/Novelty :** Rejouables sans limite
- **Weekly :** Une tentative par semaine et par chapitre
- **Event :** Disponibles uniquement pendant la période définie
- **Reminder :** Déclenchés automatiquement selon l'algorithme

### Calcul des Points
```php
$baseScore = $correctAnswers * $basePointsPerQuestion;
$precisionBonus = ($correctAnswers / $totalQuestions) * 100;
$speedBonus = max(0, (300 - $averageTimePerQuestion) * 0.5);
$finalScore = ($baseScore + $precisionBonus + $speedBonus) * $typeMultiplier;
```

### Attribution des Tickets Loterie
- **Weekly :** 1 ticket pour 80%+ de réussite
- **Event :** 2-5 tickets selon performance
- **Discovery :** 1 ticket pour premier passage réussi

---

## Système de Points

### Hiérarchie des Rangs
1. **Novice** (0-99 points)
2. **Apprenti** (100-499 points)
3. **Compagnon** (500-1499 points)
4. **Expert** (1500-3999 points)
5. **Maître** (4000-9999 points)
6. **Grand Maître** (10000+ points)

### Achievements Spéciaux
- **Perfectionniste :** 100% de réussite sur 10 quiz consécutifs
- **Rapide comme l'éclair :** Temps moyen < 10 secondes par question
- **Assidu :** 30 jours consécutifs avec au moins 1 quiz
- **Polyvalent :** Réussite dans tous les types de quiz

---

## 🔧 Maintenance et Évolution

### Ajout d'un Nouveau Type de Quiz
1. Créer le modèle Eloquent avec interface `Quizable`
2. Ajouter les migrations nécessaires
3. Implémenter les méthodes `getQuestions()` et `calculateScore()`
4. Ajouter le type dans `quiz_types`
5. Configurer les endpoints API

### Optimisations Performances
- **Eager Loading :** Relations automatiques avec `with()`
- **Cache :** Questions mises en cache pour 1 heure
- **Index DB :** Sur `quizable_type`, `quizable_id`, `user_id`
- **Queue Jobs :** Calculs de score en arrière-plan

---

*Documentation complète du système de quiz - Dernière mise à jour : Juin 2025*

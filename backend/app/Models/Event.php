<?php

namespace App\Models;

use App\Contracts\Quizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class Event extends Model implements Quizable
{
    
    protected $table = 'events'; // Match your database table name
    
    protected $fillable = [
        'theme',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relations
     */
    
    /**
     * Relation many-to-many avec les unités via event_units
     */
    public function units()
    {
        return $this->belongsToMany(Unit::class, 'event_units', 'event_id', 'unit_id');
    }

    /**
     * Méthodes utilitaires
     */
    
    /**
     * Vérifie si l'événement est actif (entre start_date et end_date)
     * 
     * @param string|null $date Date à vérifier (format Y-m-d), par défaut aujourd'hui
     * @return bool
     */
    public function isActive($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $dateDebut = Carbon::parse($this->start_date)->startOfDay();
        $dateFin = Carbon::parse($this->end_date)->endOfDay();
        
        return $checkDate->between($dateDebut, $dateFin);
    }

    /**
     * Vérifie si l'événement est à venir (start_date dans le futur)
     * 
     * @param string|null $date Date de référence, par défaut aujourd'hui
     * @return bool
     */
    public function isUpcoming($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return Carbon::parse($this->start_date)->startOfDay()->gt($checkDate->endOfDay());
    }

    /**
     * Vérifie si l'événement est terminé (end_date dans le passé)
     * 
     * @param string|null $date Date de référence, par défaut aujourd'hui
     * @return bool
     */
    public function isFinished($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return Carbon::parse($this->end_date)->endOfDay()->lt($checkDate->startOfDay());
    }

    /**
     * Calcule le nombre de jours restants avant la fin
     * 
     * @param string|null $date Date de référence, par défaut aujourd'hui
     * @return int Nombre de jours restants (0 si terminé)
     */
    public function getRemainingDays($date = null): int
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $dateFin = Carbon::parse($this->end_date);
        
        if ($dateFin->lt($checkDate->startOfDay())) {
            return 0; // Terminé
        }
        
        return $checkDate->startOfDay()->diffInDays($dateFin->startOfDay()) + 1;
    }

    /**
     * Récupère les unités avec leur théorie HTML et leurs questions
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUnitsWithContent()
    {
        return $this->units()
            ->with(['questions.choices', 'chapter'])
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'titre' => $unit->title,
                    'description' => $unit->description,
                    'theorie_html' => $unit->theory_html,
                    'chapter' => $unit->chapter ? [
                        'id' => $unit->chapter->id,
                        'titre' => $unit->chapter->title,
                        'description' => $unit->chapter->description
                    ] : null,
                    'questions_count' => $unit->questions->count(),
                    'questions' => $unit->questions->map(function ($question) {
                        return [
                            'id' => $question->id,
                            'enonce' => $question->statement,
                            'type' => $question->type,
                            'timer_secondes' => $question->timer_seconds,
                            'choices' => $question->choices->map(function ($choice) {
                                return [
                                    'id' => $choice->id,
                                    'texte' => $choice->text,
                                    'est_correct' => $choice->is_correct
                                ];
                            })
                        ];
                    })
                ];
            });
    }

    /**
     * Compte le nombre total de questions dans l'événement
     * 
     * @return int
     */
    public function getTotalQuestionsCount(): int
    {
        return $this->units()
            ->withCount('questions')
            ->get()
            ->sum('questions_count');
    }

    /**
     * Scopes
     */

    /**
     * Scope pour les événements actifs
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('start_date', '<=', $checkDate->format('Y-m-d'))
                    ->where('end_date', '>=', $checkDate->format('Y-m-d'));
    }

    /**
     * Scope pour les événements à venir
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUpcoming($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('start_date', '>', $checkDate->format('Y-m-d'));
    }

    /**
     * Scope pour les événements terminés
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinished($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('end_date', '<', $checkDate->format('Y-m-d'));
    }

    /**
     * Scope pour les événements se terminant bientôt
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days Nombre de jours
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEndingSoon($query, $days = 3)
    {
        $today = Carbon::now();
        $limitDate = $today->copy()->addDays($days);
        
        return $query->where('end_date', '>=', $today->format('Y-m-d'))
                    ->where('end_date', '<=', $limitDate->format('Y-m-d'));
    }

    /**
     * Relation polymorphique avec les instances de quiz
     */
    public function quizInstances()
    {
        return $this->morphMany(QuizInstance::class, 'quizable');
    }

    // Implémentation de l'interface Quizable

    /**
     * Obtenir les questions pour cet événement
     */
    public function getQuestions(array $options = []): Collection
    {
        // Commencer avec une collection Eloquent vide
        $questions = new Collection([]);
        
        // Récupérer toutes les questions de toutes les unités liées à cet événement
        foreach ($this->units as $unit) {
            $questions = $questions->merge($unit->questions);
        }
        
        // Mélanger et limiter selon les options
        $limit = $options['limit'] ?? 10; // Event par défaut : 10 questions
        return $questions->shuffle()->take($limit);
    }

    /**
     * Obtenir le titre du quiz Event
     */
    public function getQuizTitle(): string
    {
        return "Événement : {$this->theme}";
    }

    /**
     * Obtenir la description du quiz Event
     */
    public function getQuizDescription(): string
    {
        return "Quiz événementiel sur le thème : {$this->theme}";
    }

    /**
     * Vérifier si cet événement est disponible pour un utilisateur
     */
    public function isAvailable(User $user): bool
    {
        return $this->isActive();
    }

    /**
     * Obtenir le mode de quiz par défaut
     */
    public function getDefaultQuizMode(): string
    {
        return 'event';
    }

    /**
     * Vérifier si ce quiz peut être rejoué
     */
    public function isReplayable(): bool
    {
        return $this->isActive(); // Peut être rejoué tant que l'événement est actif
    }
}

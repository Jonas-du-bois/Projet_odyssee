<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    public $timestamps = false;
    
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
}

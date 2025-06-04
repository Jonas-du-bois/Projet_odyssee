<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reminder extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'chapter_id',
        'nb_questions',
        'date_limite',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'nb_questions' => 'integer',
        'date_limite' => 'date',
    ];

    /**
     * Relations
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    /**
     * Méthodes utilitaires
     */
    
    /**
     * Vérifie si le reminder est encore actif (date limite non dépassée)
     * 
     * @param string|null $date Date à vérifier (format Y-m-d), par défaut aujourd'hui
     * @return bool
     */
    public function isActive($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return Carbon::parse($this->date_limite)->gte($checkDate->startOfDay());
    }

    /**
     * Vérifie si le reminder est expiré
     * 
     * @param string|null $date Date à vérifier (format Y-m-d), par défaut aujourd'hui
     * @return bool
     */
    public function isExpired($date = null): bool
    {
        return !$this->isActive($date);
    }

    /**
     * Calcule le nombre de jours restants avant expiration
     * 
     * @param string|null $date Date de référence, par défaut aujourd'hui
     * @return int Nombre de jours restants (0 si expiré)
     */
    public function getRemainingDays($date = null): int
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $limitDate = Carbon::parse($this->date_limite);
        
        if ($limitDate->lt($checkDate->startOfDay())) {
            return 0; // Expiré
        }
        
        return $checkDate->startOfDay()->diffInDays($limitDate->startOfDay()) + 1;
    }

    /**
     * Récupère les questions du chapitre pour générer le quiz de révision
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChapterQuestions()
    {
        if (!$this->chapter) {
            return collect();
        }

        return $this->chapter->units()
            ->with('questions')
            ->get()
            ->pluck('questions')
            ->flatten()
            ->take($this->nb_questions);
    }

    /**
     * Scopes
     */

    /**
     * Scope pour les reminders actifs (non expirés)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date)->startOfDay() : Carbon::now()->startOfDay();
        return $query->where('date_limite', '>=', $checkDate->format('Y-m-d'));
    }

    /**
     * Scope pour les reminders expirés
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExpired($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date)->startOfDay() : Carbon::now()->startOfDay();
        return $query->where('date_limite', '<', $checkDate->format('Y-m-d'));
    }

    /**
     * Scope pour les reminders se terminant bientôt (dans X jours)
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $days Nombre de jours
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeEndingSoon($query, $days = 3)
    {
        $today = Carbon::now()->startOfDay();
        $limitDate = $today->copy()->addDays($days);
        
        return $query->where('date_limite', '>=', $today->format('Y-m-d'))
                    ->where('date_limite', '<=', $limitDate->format('Y-m-d'));
    }
}

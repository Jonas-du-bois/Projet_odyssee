<?php

namespace App\Models;

use App\Contracts\Quizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class Novelty extends Model implements Quizable
{
    use HasFactory;
    
    protected $table = 'novelties'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'chapter_id',
        'publication_date',
        'initial_bonus',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'publication_date' => 'date',
        'initial_bonus' => 'integer',
    ];

    /**
     * Relationship with chapter
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    /**
     * Vérifie si la nouveauté est publiée
     * 
     * @param string|null $date Date de référence, par défaut aujourd'hui
     * @return bool
     */
    public function isPublished($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return Carbon::parse($this->publication_date)->startOfDay()->lte($checkDate->endOfDay());
    }

    /**
     * Vérifie si la nouveauté est accessible (publiée)
     * 
     * @param string|null $date Date de référence, par défaut aujourd'hui
     * @return bool
     */
    public function isAccessible($date = null): bool
    {
        return $this->isPublished($date);
    }

    /**
     * Vérifie si la nouveauté est éligible pour le bonus (dans les 7 jours suivant la publication)
     * 
     * @param string|null $date Date de référence, par défaut aujourd'hui
     * @return bool
     */
    public function isEligibleForBonus($date = null): bool
    {
        if (!$this->initial_bonus) {
            return false;
        }

        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $publicationDate = Carbon::parse($this->publication_date);
        $bonusDeadline = $publicationDate->copy()->addDays(7);

        return $checkDate->between($publicationDate->startOfDay(), $bonusDeadline->endOfDay());
    }

    /**
     * Calcule le nombre de jours restants pour bénéficier du bonus
     * 
     * @param string|null $date Date de référence, par défaut aujourd'hui
     * @return int Nombre de jours restants (0 si plus éligible)
     */
    public function getRemainingBonusDays($date = null): int
    {
        if (!$this->initial_bonus) {
            return 0;
        }

        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $publicationDate = Carbon::parse($this->publication_date);
        $bonusDeadline = $publicationDate->copy()->addDays(7);

        if ($checkDate->gt($bonusDeadline->endOfDay())) {
            return 0; // Période bonus expirée
        }

        if ($checkDate->lt($publicationDate->startOfDay())) {
            return 7; // Pas encore publié, 7 jours complets disponibles
        }

        return $checkDate->startOfDay()->diffInDays($bonusDeadline->startOfDay()) + 1;
    }

    /**
     * Récupère les unités du chapitre avec leur contenu théorique
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChapterUnitsWithTheory()
    {
        if (!$this->chapter) {
            return collect([]);
        }

        return $this->chapter->units()
            ->get()
            ->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'chapter_id' => $unit->chapter_id,
                    'titre' => $unit->title,
                    'description' => $unit->description,
                    'theorie_html' => $unit->theory_html
                ];
            });
    }

    /**
     * Scope pour les nouveautés publiées
     */
    public function scopePublished($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('publication_date', '<=', $checkDate->format('Y-m-d'));
    }

    /**
     * Scope pour les nouveautés à venir
     */
    public function scopeUpcoming($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('publication_date', '>', $checkDate->format('Y-m-d'));
    }

    /**
     * Scope pour les nouveautés accessibles (publiées)
     */
    public function scopeAccessible($query, $date = null)
    {
        return $this->scopePublished($query, $date);
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
     * Obtenir les questions pour cette nouveauté
     */
    public function getQuestions(array $options = []): Collection
    {
        if (!$this->chapter) {
            return new Collection([]);
        }

        // Commencer avec une collection Eloquent vide
        $questions = new Collection([]);
        
        // Récupérer toutes les questions de toutes les unités du chapitre
        foreach ($this->chapter->units as $unit) {
            $questions = $questions->merge($unit->questions);
        }
        
        // Mélanger et limiter selon les options
        $limit = $options['limit'] ?? 8; // Novelty par défaut : 8 questions
        return $questions->shuffle()->take($limit);
    }

    /**
     * Obtenir le titre du quiz Novelty
     */
    public function getQuizTitle(): string
    {
        return $this->chapter 
            ? "Nouveauté : {$this->chapter->title}" 
            : 'Quiz Nouveauté';
    }

    /**
     * Obtenir la description du quiz Novelty
     */
    public function getQuizDescription(): string
    {
        return $this->chapter
            ? "Découvrez les dernières nouveautés du chapitre : {$this->chapter->title}"
            : 'Quiz sur les dernières nouveautés';
    }

    /**
     * Vérifier si cette nouveauté est disponible pour un utilisateur
     */
    public function isAvailable(User $user): bool
    {
        return $this->isPublished();
    }

    /**
     * Obtenir le mode de quiz par défaut
     */
    public function getDefaultQuizMode(): string
    {
        return 'novelty';
    }

    /**
     * Vérifier si ce quiz peut être rejoué
     */
    public function isReplayable(): bool
    {
        return true; // Les nouveautés peuvent être rejouées
    }
}

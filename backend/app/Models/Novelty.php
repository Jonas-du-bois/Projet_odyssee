<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Novelty extends Model
{
    use HasFactory;

    public $timestamps = false;
    
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
}

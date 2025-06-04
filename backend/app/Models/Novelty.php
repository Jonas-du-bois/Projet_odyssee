<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Novelty extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'chapter_id',
        'date_publication',
        'bonus_initial',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'date_publication' => 'date',
        'bonus_initial' => 'boolean',
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
     * Vérifie si la nouveauté est accessible (publiée)
     * 
     * @param string|null $date Date à vérifier (format Y-m-d), par défaut aujourd'hui
     * @return bool
     */
    public function isAccessible($date = null): bool
    {
        $checkDate = $date ? Carbon::parse($date) : now();
        return $this->date_publication <= $checkDate->format('Y-m-d');
    }

    /**
     * Vérifie si la nouveauté est éligible au bonus (dans les 7 jours)
     * 
     * @param string|null $date Date à vérifier (format Y-m-d), par défaut aujourd'hui
     * @return bool
     */
    public function isEligibleForBonus($date = null): bool
    {
        if (!$this->bonus_initial) {
            return false;
        }
        
        $checkDate = $date ? Carbon::parse($date) : now();
        $publicationDate = Carbon::parse($this->date_publication);
        
        // Bonus disponible dans les 7 jours suivant la publication
        return $checkDate->diffInDays($publicationDate, false) <= 7 && $checkDate >= $publicationDate;
    }

    /**
     * Récupère les unités du chapitre avec leur contenu théorique
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getChapterUnitsWithTheory()
    {
        return $this->chapter->units()
            ->select(['id', 'chapter_id', 'titre', 'description', 'theorie_html'])
            ->get();
    }

    /**
     * Calcule les jours restants pour le bonus
     * 
     * @param string|null $date Date de référence (format Y-m-d), par défaut aujourd'hui
     * @return int Nombre de jours restants (0 si expiré ou pas de bonus)
     */
    public function getRemainingBonusDays($date = null): int
    {
        if (!$this->bonus_initial) {
            return 0;
        }
        
        $checkDate = $date ? Carbon::parse($date) : now();
        $publicationDate = Carbon::parse($this->date_publication);
        $bonusEndDate = $publicationDate->addDays(7);
        
        if ($checkDate > $bonusEndDate || $checkDate < $publicationDate) {
            return 0;
        }
        
        return max(0, $bonusEndDate->diffInDays($checkDate, false));
    }

    /**
     * Scope pour les nouveautés accessibles
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAccessible($query, $date = null)
    {
        $checkDate = $date ? $date : now()->format('Y-m-d');
        return $query->where('date_publication', '<=', $checkDate);
    }

    /**
     * Scope pour les nouveautés non encore accessibles
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotAccessible($query, $date = null)
    {
        $checkDate = $date ? $date : now()->format('Y-m-d');
        return $query->where('date_publication', '>', $checkDate);
    }

    /**
     * Scope pour les nouveautés éligibles au bonus
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBonusEligible($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : now();
        $sevenDaysAgo = $checkDate->copy()->subDays(7)->format('Y-m-d');
        
        return $query->where('bonus_initial', true)
                    ->where('date_publication', '<=', $checkDate->format('Y-m-d'))
                    ->where('date_publication', '>=', $sevenDaysAgo);
    }
}

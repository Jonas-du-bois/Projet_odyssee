<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Discovery extends Model
{
    public $timestamps = false;
    
    protected $fillable = [
        'chapter_id',
        'date_disponible',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'date_disponible' => 'date',
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
     * Vérifie si la découverte est disponible à une date donnée
     * 
     * @param string|null $date Date à vérifier (format Y-m-d), par défaut aujourd'hui
     * @return bool
     */
    public function isAvailable($date = null): bool
    {
        $checkDate = $date ? $date : now()->format('Y-m-d');
        return $this->date_disponible <= $checkDate;
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
     * Scope pour les découvertes disponibles
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeAvailable($query, $date = null)
    {
        $checkDate = $date ? $date : now()->format('Y-m-d');
        return $query->where('date_disponible', '<=', $checkDate);
    }

    /**
     * Scope pour les découvertes non encore disponibles
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $date Date de référence (par défaut aujourd'hui)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnavailable($query, $date = null)
    {
        $checkDate = $date ? $date : now()->format('Y-m-d');
        return $query->where('date_disponible', '>', $checkDate);
    }
}

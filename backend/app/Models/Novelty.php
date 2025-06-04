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
}

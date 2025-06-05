<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class LastChance extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'last_chances';
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Vérifie si la dernière chance est active
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
     * Scope pour les dernières chances actives
     */
    public function scopeActive($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('start_date', '<=', $checkDate->format('Y-m-d'))
                    ->where('end_date', '>=', $checkDate->format('Y-m-d'));
    }
}

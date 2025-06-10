<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rank extends Model
{
    use HasFactory;

    // Timestamps activés car la table a les colonnes created_at et updated_at
    public $timestamps = true;
    
    protected $table = 'ranks'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'level',
        'minimum_points',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'level' => 'integer',
        'minimum_points' => 'integer',
    ];

    /**
     * Relationship with users
     */
    public function users()
    {
        return $this->hasMany(User::class, 'rank_id');
    }

    /**
     * Relationship with scores
     */
    public function scores()
    {
        return $this->hasMany(Score::class, 'rang_id');
    }

    /**
     * Get the next rank
     */
    public function getNextRank()
    {
        return static::where('level', '>', $this->level)
                    ->orderBy('level')
                    ->first();
    }

    /**
     * Get the previous rank
     */
    public function getPreviousRank()
    {
        return static::where('level', '<', $this->level)
                    ->orderBy('level', 'desc')
                    ->first();
    }

    /**
     * Get rank by points
     */
    public static function getRankByPoints($points)
    {
        return static::where('minimum_points', '<=', $points)
                    ->orderBy('minimum_points', 'desc')
                    ->first();
    }

    /**
     * Check if user can reach this rank with given points
     */
    public function canReach($points)
    {
        return $points >= $this->minimum_points;
    }

    /**
     * Scope for ranks by level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for ranks above a certain level
     */
    public function scopeAboveLevel($query, $level)
    {
        return $query->where('level', '>', $level);
    }

    /**
     * Scope for ranks below a certain level
     */
    public function scopeBelowLevel($query, $level)
    {
        return $query->where('level', '<', $level);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    use HasFactory;
    
    protected $table = 'scores'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'total_points',
        'bonus_points',
        'rank_id'
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'total_points' => 'integer',
        'bonus_points' => 'integer',
    ];

    /**
     * Relationship with user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with rank
     */
    public function rank()
    {
        return $this->belongsTo(Rank::class, 'rank_id');
    }

    /**
     * Get total points including bonus
     */
    public function getTotalWithBonusAttribute()
    {
        return $this->total_points + $this->bonus_points;
    }

    /**
     * Add points to the score
     */
    public function addPoints($points, $bonus = 0)
    {
        $this->increment('total_points', $points);
        if ($bonus > 0) {
            $this->increment('bonus_points', $bonus);
        }
    }

    /**
     * Update rank based on total points
     */
    public function updateRank()
    {
        $totalPoints = $this->total_points + $this->bonus_points;
        $newRank = Rank::getRankByPoints($totalPoints);
        
        if ($newRank && $newRank->id !== $this->rank_id) {
            $this->update(['rank_id' => $newRank->id]);
        }
    }

    /**
     * Scope for top scores
     */
    public function scopeTopScores($query, $limit = 10)
    {
        return $query->orderByRaw('(total_points + bonus_points) DESC')
                    ->limit($limit);
    }

    /**
     * Scope for scores by rank
     */
    public function scopeByRank($query, $rankId)
    {
        return $query->where('rank_id', $rankId);
    }
}

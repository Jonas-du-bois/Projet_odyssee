<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WeeklySeries extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'weekly_series'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'count',
        'bonus_tickets',
        'last_participation',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'count' => 'integer',
        'bonus_tickets' => 'integer',
        'last_participation' => 'date',
    ];

    /**
     * Relationship with user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Check if series is still active (participated last week)
     */
    public function isActive($date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $lastWeek = $checkDate->copy()->subWeek()->startOfWeek();
        $participationWeek = Carbon::parse($this->last_participation)->startOfWeek();
        
        return $participationWeek->gte($lastWeek);
    }

    /**
     * Check if series is broken (missed last week)
     */
    public function isBroken($date = null)
    {
        return !$this->isActive($date);
    }

    /**
     * Increment series count
     */
    public function incrementSeries($date = null)
    {
        $currentDate = $date ? Carbon::parse($date) : Carbon::now();
        
        $this->increment('count');
        $this->update(['last_participation' => $currentDate->format('Y-m-d')]);
        
        // Award bonus ticket for every 3 consecutive weeks
        if ($this->count % 3 === 0) {
            $this->increment('bonus_tickets');
        }
    }

    /**
     * Reset series (when streak is broken)
     */
    public function resetSeries()
    {
        $this->update([
            'count' => 0,
            'bonus_tickets' => 0,
            'last_participation' => null
        ]);
    }

    /**
     * Get weeks since last participation
     */
    public function getWeeksSinceLastParticipation($date = null)
    {
        if (!$this->last_participation) {
            return null;
        }
        
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $lastParticipation = Carbon::parse($this->last_participation);
        
        return $lastParticipation->diffInWeeks($checkDate);
    }

    /**
     * Scope for active series
     */
    public function scopeActive($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $lastWeek = $checkDate->copy()->subWeek()->startOfWeek();
        
        return $query->where('last_participation', '>=', $lastWeek->format('Y-m-d'));
    }

    /**
     * Scope for broken series
     */
    public function scopeBroken($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $lastWeek = $checkDate->copy()->subWeek()->startOfWeek();
        
        return $query->where('last_participation', '<', $lastWeek->format('Y-m-d'))
                    ->orWhereNull('last_participation');
    }

    /**
     * Scope for series with minimum count
     */
    public function scopeMinimumCount($query, $minCount)
    {
        return $query->where('count', '>=', $minCount);
    }

    /**
     * Get top series by count
     */
    public function scopeTopSeries($query, $limit = 10)
    {
        return $query->orderBy('count', 'desc')->limit($limit);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserQuizScore extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'user_quiz_scores'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'quiz_instance_id',
        'total_points',
        'total_time',
        'ticket_obtained',
        'bonus_obtained',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'total_points' => 'integer',
        'total_time' => 'integer',
        'ticket_obtained' => 'boolean',
        'bonus_obtained' => 'integer',
    ];

    /**
     * Relationship with quiz instance
     */
    public function quizInstance()
    {
        return $this->belongsTo(QuizInstance::class, 'quiz_instance_id');
    }

    /**
     * Get user through quiz instance
     */
    public function user()
    {
        return $this->hasOneThrough(
            User::class,
            QuizInstance::class,
            'id',
            'id',
            'quiz_instance_id',
            'user_id'
        );
    }

    /**
     * Calculate points per minute
     */
    public function getPointsPerMinuteAttribute()
    {
        if ($this->total_time <= 0) {
            return 0;
        }
        
        return round($this->total_points / ($this->total_time / 60), 2);
    }

    /**
     * Check if quiz was completed successfully
     */
    public function isSuccessful()
    {
        return $this->total_points > 0;
    }

    /**
     * Scope for successful quiz scores
     */
    public function scopeSuccessful($query)
    {
        return $query->where('total_points', '>', 0);
    }

    /**
     * Scope for failed quiz scores
     */
    public function scopeFailed($query)
    {
        return $query->where('total_points', '=', 0);
    }

    /**
     * Scope for scores that earned tickets
     */
    public function scopeEarnedTickets($query)
    {
        return $query->where('ticket_obtained', true);
    }

    /**
     * Scope for scores with bonus points
     */
    public function scopeWithBonus($query)
    {
        return $query->where('bonus_obtained', '>', 0);
    }

    /**
     * Get average score for a user
     */
    public static function getAverageScoreForUser($userId)
    {
        return static::whereHas('quizInstance', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->avg('total_points');
    }

    /**
     * Get total points earned by a user
     */
    public static function getTotalPointsForUser($userId)
    {
        return static::whereHas('quizInstance', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->sum('total_points');
    }

    /**
     * Get total tickets earned by a user
     */
    public static function getTotalTicketsForUser($userId)
    {
        return static::whereHas('quizInstance', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })->where('ticket_obtained', true)->count();
    }
}

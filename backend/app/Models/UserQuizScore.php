<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Events\QuizCompleted;

class UserQuizScore extends Model
{
    use HasFactory;
    
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
     * The "booted" method of the model.
     * Déclenche l'événement QuizCompleted après la création d'un score
     */
    protected static function booted(): void
    {
        static::created(function (UserQuizScore $userQuizScore) {
            // Déclencher l'événement pour synchroniser les scores
            event(new QuizCompleted($userQuizScore));
        });
    }

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
     * Calculate percentage score based on quiz type base points
     */
    public function getPercentageAttribute()
    {
        if (!$this->quizInstance || !$this->quizInstance->quizType) {
            return 0;
        }
        
        $basePoints = $this->quizInstance->quizType->base_points;
        
        // Si les points de base sont 0 (comme pour Weekly Quiz), 
        // considérer que tout score > 0 vaut 100%
        if ($basePoints <= 0) {
            return $this->total_points > 0 ? 100 : 0;
        }
        
        // Calculer le pourcentage en fonction des points de base du type de quiz
        $percentage = ($this->total_points / $basePoints) * 100;
        return round(min($percentage, 100), 2); // Limiter à 100%
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

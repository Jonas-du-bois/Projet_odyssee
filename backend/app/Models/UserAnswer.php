<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    use HasFactory;

    // Timestamps activés car la table a les colonnes created_at et updated_at
    public $timestamps = true;
    
    protected $table = 'user_answers'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'quiz_instance_id',
        'question_id',
        'choice_id',
        'is_correct',
        'response_time',
        'points_obtained',
        'date',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'is_correct' => 'boolean',
        'response_time' => 'integer',
        'points_obtained' => 'integer',
        'date' => 'datetime',
    ];

    /**
     * Relationship with user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with quiz instance
     */
    public function quizInstance()
    {
        return $this->belongsTo(QuizInstance::class, 'quiz_instance_id');
    }

    /**
     * Relationship with question
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * Relationship with choice
     */
    public function choice()
    {
        return $this->belongsTo(Choice::class, 'choice_id');
    }

    /**
     * Scope for correct answers
     */
    public function scopeCorrect($query)
    {
        return $query->where('is_correct', true);
    }

    /**
     * Scope for incorrect answers
     */
    public function scopeIncorrect($query)
    {
        return $query->where('is_correct', false);
    }

    /**
     * Scope for answers by user
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for answers by question
     */
    public function scopeByQuestion($query, $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    /**
     * Calculate accuracy percentage for a user
     */
    public static function getUserAccuracy($userId)
    {
        $totalAnswers = static::where('user_id', $userId)->count();
        $correctAnswers = static::where('user_id', $userId)->where('is_correct', true)->count();
        
        return $totalAnswers > 0 ? ($correctAnswers / $totalAnswers) * 100 : 0;
    }

    /**
     * Get average response time for a user
     */
    public static function getUserAverageResponseTime($userId)
    {
        return static::where('user_id', $userId)->avg('response_time');
    }

    /**
     * Get total points earned by a user
     */
    public static function getUserTotalPoints($userId)
    {
        return static::where('user_id', $userId)->sum('points_obtained');
    }
}

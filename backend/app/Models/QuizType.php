<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizType extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'quiz_types'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'nom',
        'base_points',
        'speed_bonus',
        'gives_ticket',
        'bonus_multiplier',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'base_points' => 'integer',
        'speed_bonus' => 'integer',
        'gives_ticket' => 'boolean',
        'bonus_multiplier' => 'integer',
    ];

    /**
     * Relationship with quiz instances
     */
    public function quizInstances()
    {
        return $this->hasMany(QuizInstance::class, 'quiz_type_id');
    }

    /**
     * Calculate total points for a quiz result
     * 
     * @param int $correctAnswers Number of correct answers
     * @param int $timeBonus Time bonus points
     * @return int Total points
     */
    public function calculatePoints($correctAnswers, $timeBonus = 0)
    {
        $basePoints = $this->base_points * $correctAnswers;
        $rapidityBonus = min($timeBonus, $this->speed_bonus);
        
        return ($basePoints + $rapidityBonus) * $this->bonus_multiplier;
    }

    /**
     * Check if this quiz type gives lottery tickets
     * 
     * @return bool
     */
    public function givesTicket()
    {
        return $this->gives_ticket;
    }

    /**
     * Scope for quiz types that give tickets
     */
    public function scopeGivesTickets($query)
    {
        return $query->where('gives_ticket', true);
    }

    /**
     * Scope for quiz types by name
     */
    public function scopeByName($query, $name)
    {
        return $query->where('nom', $name);
    }
}

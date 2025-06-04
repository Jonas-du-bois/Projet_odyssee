<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuizScore extends Model
{
    protected $fillable = [
        'quiz_instance_id',
        'total_points',
        'total_time',
        'ticket_obtained',
        'bonus_obtained',
    ];

    protected $casts = [
        'quiz_instance_id' => 'integer',
        'total_points' => 'integer',
        'total_time' => 'integer',
        'ticket_obtained' => 'boolean',
        'bonus_obtained' => 'boolean',
    ];

    /**
     * Relations
     */
    public function quizInstance()
    {
        return $this->belongsTo(QuizInstance::class);
    }
}

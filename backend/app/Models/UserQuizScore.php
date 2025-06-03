<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserQuizScore extends Model
{
    protected $fillable = [
        'quiz_instance_id',
        'total_points',
        'temps_total',
        'ticket_obtenu',
        'bonus_obtenu',
    ];

    protected $casts = [
        'quiz_instance_id' => 'integer',
        'total_points' => 'integer',
        'temps_total' => 'integer',
        'ticket_obtenu' => 'boolean',
        'bonus_obtenu' => 'boolean',
    ];

    /**
     * Relations
     */
    public function quizInstance()
    {
        return $this->belongsTo(QuizInstance::class);
    }
}

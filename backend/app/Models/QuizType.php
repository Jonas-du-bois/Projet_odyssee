<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizType extends Model
{
    protected $fillable = [
        'name',
        'base_points',
        'speed_bonus',
        'gives_ticket',
        'bonus_multiplier',
    ];

    protected $casts = [
        'base_points' => 'integer',
        'speed_bonus' => 'boolean',
        'gives_ticket' => 'boolean',
        'bonus_multiplier' => 'integer',
    ];

    /**
     * Relations
     */
    public function quizInstances()
    {
        return $this->hasMany(QuizInstance::class);
    }
}

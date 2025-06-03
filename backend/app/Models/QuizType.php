<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizType extends Model
{
    protected $fillable = [
        'nom',
        'base_points',
        'bonus_rapidite',
        'donne_ticket',
        'multiplicateur_bonus',
    ];

    protected $casts = [
        'base_points' => 'integer',
        'bonus_rapidite' => 'boolean',
        'donne_ticket' => 'boolean',
        'multiplicateur_bonus' => 'integer',
    ];

    /**
     * Relations
     */
    public function quizInstances()
    {
        return $this->hasMany(QuizInstance::class);
    }
}

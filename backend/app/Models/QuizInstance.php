<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuizInstance extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_type_id',
        'module_type',
        'module_id',
        'date_lancement',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'quiz_type_id' => 'integer',
        'module_id' => 'integer',
        'date_lancement' => 'datetime',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function quizType()
    {
        return $this->belongsTo(QuizType::class);
    }

    public function userQuizScores()
    {
        return $this->hasMany(UserQuizScore::class);
    }

    /**
     * Relation polymorphe pour le module (Chapter, Unit, etc.)
     */
    public function module()
    {
        return $this->morphTo();
    }
}

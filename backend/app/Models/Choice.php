<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    protected $fillable = [
        'question_id',
        'texte',
        'est_correct',
    ];

    protected $casts = [
        'question_id' => 'integer',
        'est_correct' => 'boolean',
    ];

    /**
     * Relations
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class, 'choix_id');
    }
}

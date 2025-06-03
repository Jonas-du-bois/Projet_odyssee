<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'choix_id',
        'est_correct',
        'temps_reponse',
        'points_obtenus',
        'date',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'question_id' => 'integer',
        'choix_id' => 'integer',
        'est_correct' => 'boolean',
        'temps_reponse' => 'integer',
        'points_obtenus' => 'integer',
        'date' => 'datetime',
    ];

    /**
     * Relations
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    public function choice()
    {
        return $this->belongsTo(Choice::class, 'choix_id');
    }
}

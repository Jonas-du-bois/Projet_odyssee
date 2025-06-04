<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAnswer extends Model
{
    protected $fillable = [
        'user_id',
        'question_id',
        'choice_id',
        'is_correct',
        'response_time',
        'points_obtained',
        'date',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'question_id' => 'integer',
        'choice_id' => 'integer',
        'is_correct' => 'boolean',
        'response_time' => 'integer',
        'points_obtained' => 'integer',
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

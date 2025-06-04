<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Choice extends Model
{
    use HasFactory;

    protected $table = 'choices'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'question_id',
        'text',
        'is_correct',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'is_correct' => 'boolean',
    ];

    /**
     * Relationship with question
     */
    public function question()
    {
        return $this->belongsTo(Question::class, 'question_id');
    }

    /**
     * Relationship with user answers
     */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class, 'choix_id');
    }
}

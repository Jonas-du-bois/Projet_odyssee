<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'questions'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'quizable_type',
        'quizable_id', 
        'question_text',
        'options',
        'correct_answer',
        'timer_seconds',
        'type',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'timer_seconds' => 'integer',
        'options' => 'array',
    ];

    /**
     * Polymorphic relationship with quizable models (Unit, Discovery, etc.)
     */
    public function quizable()
    {
        return $this->morphTo();
    }

    /**
     * Legacy relationship with unit (for backwards compatibility)
     * @deprecated Use quizable() instead
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'quizable_id')->where('quizable_type', 'App\\Models\\Unit');
    }

    /**
     * Relationship with choices
     */
    public function choices()
    {
        return $this->hasMany(Choice::class, 'question_id');
    }

    /**
     * Relationship with user answers
     */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class, 'question_id');
    }

    /**
     * Get correct choices
     */
    public function correctChoices()
    {
        return $this->choices()->where('est_correct', true);
    }

    /**
     * Scope for questions by type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for QCM questions
     */
    public function scopeQcm($query)
    {
        return $query->where('type', 'qcm');
    }

    /**
     * Scope for multiple choice questions
     */
    public function scopeMultiple($query)
    {
        return $query->where('type', 'multiple');
    }
}

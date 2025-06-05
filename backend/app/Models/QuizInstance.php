<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuizInstance extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'quiz_instances'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'quiz_type_id',
        'module_type',
        'module_id',
        'launch_date',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'launch_date' => 'datetime',
        'module_id' => 'integer',
    ];

    /**
     * Relationship with user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with quiz type
     */
    public function quizType()
    {
        return $this->belongsTo(QuizType::class, 'quiz_type_id');
    }

    /**
     * Relationship with user quiz score
     */
    public function userQuizScore()
    {
        return $this->hasOne(UserQuizScore::class, 'quiz_instance_id');
    }

    /**
     * Relationship with user answers
     */
    public function userAnswers()
    {
        return $this->hasMany(UserAnswer::class, 'quiz_instance_id');
    }

    /**
     * Polymorphic relationship to get the module (Unit, Discovery, Event, Weekly, etc.)
     */
    public function module()
    {
        return $this->morphTo('module', 'module_type', 'module_id');
    }

    /**
     * Get the related module based on module_type
     */
    public function getModuleAttribute()
    {
        switch ($this->module_type) {
            case 'Unit':
                return Unit::find($this->module_id);
            case 'Discovery':
                return Discovery::find($this->module_id);
            case 'Event':
                return Event::find($this->module_id);
            case 'Weekly':
                return Weekly::find($this->module_id);
            case 'Novelty':
                return Novelty::find($this->module_id);
            case 'Reminder':
                return Reminder::find($this->module_id);
            default:
                return null;
        }
    }

    /**
     * Scope for quiz instances by module type
     */
    public function scopeByModuleType($query, $moduleType)
    {
        return $query->where('module_type', $moduleType);
    }

    /**
     * Scope for completed quiz instances (with scores)
     */
    public function scopeCompleted($query)
    {
        return $query->whereHas('userQuizScore');
    }

    /**
     * Scope for pending quiz instances (without scores)
     */
    public function scopePending($query)
    {
        return $query->whereDoesntHave('userQuizScore');
    }
}

<?php

namespace App\Models;

use App\Contracts\Quizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use App\Models\User;

class Unit extends Model implements Quizable
{
    use HasFactory;
    
    protected $table = 'units';

    protected $fillable = [
        'chapter_id',
        'title',
        'description',
        'theory_html',
    ];

    /**
     * Relationship with chapter
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    /**
     * Relationship with questions
     */
    public function questions()
    {
        return $this->hasMany(Question::class, 'unit_id');
    }

    /**
     * Relationship with progress
     */
    public function progress()
    {
        return $this->hasMany(Progress::class, 'unit_id');
    }

    /**
     * Relationship with events through event_units pivot table
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'event_units', 'unit_id', 'event_id');
    }

    /**
     * Get questions count
     */
    public function getQuestionsCountAttribute()
    {
        return $this->questions()->count();
    }

    /**
     * Check if unit is completed by user
     */
    public function isCompletedByUser($userId)
    {
        return $this->progress()
                    ->where('user_id', $userId)
                    ->where('termine', true)
                    ->exists();
    }

    /**
     * Get user progress percentage
     */
    public function getUserProgress($userId)
    {
        $progress = $this->progress()
                         ->where('user_id', $userId)
                         ->first();
        
        return $progress ? $progress->pourcentage : 0;
    }

    /**
     * Scope for units by chapter
     */
    public function scopeByChapter($query, $chapterId)
    {
        return $query->where('chapter_id', $chapterId);
    }

    /**
     * Relation polymorphique avec les instances de quiz
     */
    public function quizInstances()
    {
        return $this->morphMany(QuizInstance::class, 'quizable');
    }

    // Implémentation de l'interface Quizable

    /**
     * Obtenir les questions pour cette unité
     */
    public function getQuestions(array $options = []): Collection
    {
        $query = $this->questions();
        
        if (isset($options['limit'])) {
            $query = $query->limit($options['limit']);
        }
        
        return $query->get();
    }

    /**
     * Obtenir le titre du quiz Unit
     */
    public function getQuizTitle(): string
    {
        return $this->title ?: 'Quiz Unité';
    }

    /**
     * Obtenir la description du quiz Unit
     */
    public function getQuizDescription(): string
    {
        return $this->description ?: 'Quiz sur les concepts de cette unité';
    }

    /**
     * Vérifier si cette unité est disponible pour un utilisateur
     */
    public function isAvailable(User $user): bool
    {
        return true;
    }

    /**
     * Obtenir le mode de quiz par défaut
     */
    public function getDefaultQuizMode(): string
    {
        return 'unit';
    }

    /**
     * Vérifier si ce quiz peut être rejoué
     */
    public function isReplayable(): bool
    {
        return true;
    }
}

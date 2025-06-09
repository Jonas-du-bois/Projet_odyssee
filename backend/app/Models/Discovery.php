<?php

namespace App\Models;

use App\Contracts\Quizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

class Discovery extends Model implements Quizable
{
    use HasFactory;

    protected $table = 'discoveries'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'chapter_id',
        'available_date',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'available_date' => 'date',
    ];

    /**
     * Relationship with chapter
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
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
     * Obtenir les questions pour ce module Discovery
     */
    public function getQuestions(array $options = []): Collection
    {
        if (!$this->chapter) {
            return new Collection([]);
        }

        // Commencer avec une collection Eloquent vide
        $questions = new Collection([]);
        
        // Récupérer toutes les questions de toutes les unités du chapitre
        foreach ($this->chapter->units as $unit) {
            $questions = $questions->merge($unit->questions);
        }
        
        // Mélanger et limiter selon les options
        $limit = $options['limit'] ?? 5; // Discovery par défaut : 5 questions
        return $questions->shuffle()->take($limit);
    }

    /**
     * Obtenir le titre du quiz Discovery
     */
    public function getQuizTitle(): string
    {
        return $this->chapter 
            ? "Discovery : {$this->chapter->title}" 
            : 'Quiz Discovery';
    }

    /**
     * Obtenir la description du quiz Discovery
     */
    public function getQuizDescription(): string
    {
        return $this->chapter
            ? "Découvrez les concepts clés du chapitre : {$this->chapter->title}"
            : 'Quiz de découverte des concepts fondamentaux';
    }

    /**
     * Vérifier si ce Discovery est disponible pour un utilisateur
     */
    public function isAvailable(User $user): bool
    {
        // Discovery est disponible si la date de disponibilité est passée
        return $this->available_date <= now()->toDateString();
    }

    /**
     * Obtenir le mode de quiz par défaut
     */
    public function getDefaultQuizMode(): string
    {
        return 'discovery';
    }

    /**
     * Vérifier si ce quiz peut être rejoué
     */
    public function isReplayable(): bool
    {
        return true; // Les Discovery peuvent être rejoués
    }
}

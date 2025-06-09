<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Collection;

/**
 * Instance de quiz pour un utilisateur
 * Applique le principe DRY : une seule classe pour tous les types de quiz
 */
class QuizInstance extends Model
{
    use HasFactory;

    protected $table = 'quiz_instances';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'quiz_type_id',
        'quizable_type',
        'quizable_id',
        'quiz_mode',
        'launch_date',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'launch_date' => 'datetime',
    ];

    /**
     * Relation polymorphe vers le module quizable
     * Clean Code : utilise les conventions Laravel
     */
    public function quizable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec le type de quiz
     */
    public function quizType(): BelongsTo
    {
        return $this->belongsTo(QuizType::class);
    }

    /**
     * Relation avec le score du quiz
     */
    public function userQuizScore(): HasOne
    {
        return $this->hasOne(UserQuizScore::class, 'quiz_instance_id');
    }

    /**
     * Relation avec les réponses utilisateur
     */
    public function userAnswers(): HasMany
    {
        return $this->hasMany(UserAnswer::class, 'quiz_instance_id');
    }

    /**
     * Obtenir les questions pour cette instance de quiz
     * KISS : méthode simple et directe
     */
    public function getQuestions(array $options = []): Collection
    {
        if (!$this->quizable) {
            return collect();
        }

        $options['quiz_mode'] = $this->quiz_mode;
        $options['user'] = $this->user;

        return $this->quizable->getQuestions($options);
    }

    /**
     * Vérifier si le quiz est terminé
     */
    public function isCompleted(): bool
    {
        return $this->userQuizScore()->exists();
    }

    /**
     * Vérifier si le quiz peut être rejoué
     */
    public function canReplay(): bool
    {
        return $this->quizable && $this->quizable->isReplayable();
    }

    /**
     * Obtenir le titre du quiz
     */
    public function getTitle(): string
    {
        return $this->quizable ? $this->quizable->getQuizTitle() : 'Quiz';
    }

    /**
     * Obtenir la description du quiz
     */
    public function getDescription(): string
    {
        return $this->quizable ? $this->quizable->getQuizDescription() : '';
    }

    /**
     * Scopes pour filtrer par type de module
     */
    public function scopeForDiscoveries($query)
    {
        return $query->where('quizable_type', Discovery::class);
    }

    public function scopeForEvents($query)
    {
        return $query->where('quizable_type', Event::class);
    }

    public function scopeForWeeklies($query)
    {
        return $query->where('quizable_type', Weekly::class);
    }

    public function scopeForNovelties($query)
    {
        return $query->where('quizable_type', Novelty::class);
    }

    public function scopeForReminders($query)
    {
        return $query->where('quizable_type', Reminder::class);
    }

    /**
     * Scope pour les quiz complétés
     */
    public function scopeCompleted($query)
    {
        return $query->whereHas('userQuizScore');
    }

    /**
     * Scope pour les quiz en cours
     */
    public function scopePending($query)
    {
        return $query->whereDoesntHave('userQuizScore');
    }
}

<?php

namespace App\Models;

use App\Contracts\Quizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class Reminder extends Model implements Quizable
{
    use HasFactory;
    
    protected $table = 'reminders'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'chapter_id',
        'number_questions',
        'deadline_date',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'number_questions' => 'integer',
        'deadline_date' => 'date',
    ];

    /**
     * Relationship with chapter
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    /**
     * Check if reminder is still active (before deadline)
     */
    public function isActive($date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return Carbon::parse($this->deadline_date)->endOfDay()->gte($checkDate->startOfDay());
    }

    /**
     * Check if reminder is expired
     */
    public function isExpired($date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return Carbon::parse($this->deadline_date)->endOfDay()->lt($checkDate->startOfDay());
    }

    /**
     * Get days remaining until deadline
     */
    public function getRemainingDays($date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $deadline = Carbon::parse($this->deadline_date);
        
        if ($deadline->lt($checkDate->startOfDay())) {
            return 0; // Expired
        }
        
        return $checkDate->startOfDay()->diffInDays($deadline->startOfDay()) + 1;
    }

    /**
     * Scope for active reminders
     */
    public function scopeActive($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('deadline_date', '>=', $checkDate->format('Y-m-d'));
    }

    /**
     * Scope for expired reminders
     */
    public function scopeExpired($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('deadline_date', '<', $checkDate->format('Y-m-d'));
    }

    /**
     * Scope for reminders expiring soon
     */
    public function scopeExpiringSoon($query, $days = 7)
    {
        $today = Carbon::now();
        $limitDate = $today->copy()->addDays($days);
        
        return $query->where('deadline_date', '>=', $today->format('Y-m-d'))
                    ->where('deadline_date', '<=', $limitDate->format('Y-m-d'));
    }

    /**
     * Get questions from the chapter associated with this reminder
     * Limited by the number_questions attribute
     */
    public function getChapterQuestions()
    {
        if (!$this->chapter) {
            return collect([]);
        }

        $questions = collect([]);
        
        // Get all questions from all units in the chapter
        foreach ($this->chapter->units as $unit) {
            $questions = $questions->merge($unit->questions);
        }
        
        // Shuffle and limit to the number of questions specified for this reminder
        return $questions->shuffle()->take($this->number_questions);
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
     * Obtenir les questions pour ce rappel
     */
    public function getQuestions(array $options = []): Collection
    {
        if (!$this->chapter) {
            return collect([]);
        }

        $questions = collect([]);
        
        // Récupérer toutes les questions de toutes les unités du chapitre
        foreach ($this->chapter->units as $unit) {
            $questions = $questions->merge($unit->questions);
        }
        
        // Utiliser le nombre de questions défini ou la limite des options
        $limit = $options['limit'] ?? $this->number_questions ?? 5;
        return $questions->shuffle()->take($limit);
    }

    /**
     * Obtenir le titre du quiz Reminder
     */
    public function getQuizTitle(): string
    {
        return $this->chapter 
            ? "Rappel : {$this->chapter->title}" 
            : 'Quiz de Rappel';
    }

    /**
     * Obtenir la description du quiz Reminder
     */
    public function getQuizDescription(): string
    {
        return $this->chapter
            ? "Quiz de rappel sur le chapitre : {$this->chapter->title}"
            : 'Quiz de révision et rappel des concepts';
    }

    /**
     * Vérifier si ce rappel est disponible pour un utilisateur
     */
    public function isAvailable(User $user): bool
    {
        return $this->isActive();
    }

    /**
     * Obtenir le mode de quiz par défaut
     */
    public function getDefaultQuizMode(): string
    {
        return 'reminder';
    }

    /**
     * Vérifier si ce quiz peut être rejoué
     */
    public function isReplayable(): bool
    {
        return $this->isActive(); // Peut être rejoué tant qu'il est actif
    }
}

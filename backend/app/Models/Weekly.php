<?php

namespace App\Models;

use App\Contracts\Quizable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Carbon\Carbon;

class Weekly extends Model implements Quizable
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'weeklies'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'chapter_id',
        'week_start',
        'number_questions',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'week_start' => 'date',
        'number_questions' => 'integer',
    ];

    /**
     * Relationship with chapter
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    /**
     * Relationship with lottery tickets
     */
    public function lotteryTickets()
    {
        return $this->hasMany(LotteryTicket::class, 'weekly_id');
    }

    /**
     * Check if weekly quiz is for current week
     */
    public function isCurrentWeek($date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $weekStart = Carbon::parse($this->week_start)->startOfWeek();
        $weekEnd = Carbon::parse($this->week_start)->endOfWeek();
        
        return $checkDate->between($weekStart, $weekEnd);
    }

    /**
     * Check if weekly quiz is active (current or future week)
     */
    public function isActive($date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $weekEnd = Carbon::parse($this->week_start)->endOfWeek();
        
        return $weekEnd->gte($checkDate->startOfDay());
    }

    /**
     * Get week number of the year
     */
    public function getWeekNumberAttribute()
    {
        return Carbon::parse($this->week_start)->weekOfYear;
    }

    /**
     * Get formatted week range (Monday - Sunday)
     */
    public function getWeekRangeAttribute()
    {
        $start = Carbon::parse($this->week_start)->startOfWeek();
        $end = Carbon::parse($this->week_start)->endOfWeek();
        
        return $start->format('d/m/Y') . ' - ' . $end->format('d/m/Y');
    }

    /**
     * Scope for current week
     */
    public function scopeCurrentWeek($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $weekStart = $checkDate->startOfWeek();
        $weekEnd = $checkDate->endOfWeek();
        
        return $query->whereBetween('week_start', [
            $weekStart->format('Y-m-d'),
            $weekEnd->format('Y-m-d')
        ]);
    }

    /**
     * Scope for active weekly quizzes
     */
    public function scopeActive($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('week_start', '>=', $checkDate->startOfWeek()->format('Y-m-d'));
    }

    /**
     * Scope for past weekly quizzes
     */
    public function scopePast($query, $date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        return $query->where('week_start', '<', $checkDate->startOfWeek()->format('Y-m-d'));
    }

    /**
     * Scope for weekly quizzes by chapter
     */
    public function scopeByChapter($query, $chapterId)
    {
        return $query->where('chapter_id', $chapterId);
    }

    /**
     * Get weekly quiz for a specific week
     */
    public static function getByWeek($date)
    {
        $weekStart = Carbon::parse($date)->startOfWeek();
        return static::where('week_start', $weekStart->format('Y-m-d'))->first();
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
     * Obtenir les questions pour ce quiz hebdomadaire
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
        
        // Utiliser le nombre de questions défini ou la limite des options
        $limit = $options['limit'] ?? $this->number_questions ?? 7;
        return $questions->shuffle()->take($limit);
    }

    /**
     * Obtenir le titre du quiz Weekly
     */
    public function getQuizTitle(): string
    {
        $weekFormat = Carbon::parse($this->week_start)->format('W/Y');
        return $this->chapter 
            ? "Quiz Hebdomadaire S{$weekFormat} : {$this->chapter->title}" 
            : "Quiz Hebdomadaire S{$weekFormat}";
    }

    /**
     * Obtenir la description du quiz Weekly
     */
    public function getQuizDescription(): string
    {
        return $this->chapter
            ? "Quiz hebdomadaire sur le chapitre : {$this->chapter->title}"
            : 'Quiz hebdomadaire de révision';
    }

    /**
     * Check if weekly quiz is for past week
     */
    public function isPastWeek($date = null)
    {
        $checkDate = $date ? Carbon::parse($date) : Carbon::now();
        $weekEnd = Carbon::parse($this->week_start)->endOfWeek();
        
        return $weekEnd->lt($checkDate->startOfDay());
    }

    /**
     * Vérifier si ce Weekly est disponible pour un utilisateur
     */
    public function isAvailable(User $user): bool
    {
        return $this->isCurrentWeek() || $this->isPastWeek();
    }

    /**
     * Obtenir le mode de quiz par défaut
     */
    public function getDefaultQuizMode(): string
    {
        return 'weekly';
    }

    /**
     * Vérifier si ce quiz peut être rejoué
     */
    public function isReplayable(): bool
    {
        return $this->isCurrentWeek(); // Peut être rejoué seulement pendant la semaine courante
    }
}

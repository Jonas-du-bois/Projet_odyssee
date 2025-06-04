<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'units'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
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
     * Relationship with events through EventUnit pivot table
     */
    public function events()
    {
        return $this->belongsToMany(Event::class, 'EventUnit', 'unit_id', 'event_id');
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
}

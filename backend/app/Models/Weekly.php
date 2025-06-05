<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Weekly extends Model
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
}

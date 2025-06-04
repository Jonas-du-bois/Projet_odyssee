<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Reminder extends Model
{
    use HasFactory;

    public $timestamps = false;
    
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
    public function getDaysRemaining($date = null)
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
}

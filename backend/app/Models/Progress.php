<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    use HasFactory;

    public $timestamps = false;
    
    protected $table = 'progress'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'chapter_id',
        'unit_id',
        'percentage',
        'completed',
    ];

    /**
     * Cast attributes
     */
    protected $casts = [
        'percentage' => 'decimal:2',
        'completed' => 'boolean',
    ];

    /**
     * Relationship with user
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relationship with chapter
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class, 'chapter_id');
    }

    /**
     * Relationship with unit (optional)
     */
    public function unit()
    {
        return $this->belongsTo(Unit::class, 'unit_id');
    }

    /**
     * Scope for completed progress
     */
    public function scopeCompleted($query)
    {
        return $query->where('completed', true);
    }

    /**
     * Scope for incomplete progress
     */
    public function scopeIncomplete($query)
    {
        return $query->where('completed', false);
    }

    /**
     * Scope for chapter progress (unit_id is null)
     */
    public function scopeChapterLevel($query)
    {
        return $query->whereNull('unit_id');
    }

    /**
     * Scope for unit progress (unit_id is not null)
     */
    public function scopeUnitLevel($query)
    {
        return $query->whereNotNull('unit_id');
    }

    /**
     * Mark progress as complete
     */
    public function markAsComplete()
    {
        $this->update([
            'percentage' => 100.0,
            'completed' => true
        ]);
    }

    /**
     * Update progress percentage
     */
    public function updateProgress($percentage)
    {
        $this->update([
            'percentage' => $percentage,
            'completed' => $percentage >= 100
        ]);
    }
}

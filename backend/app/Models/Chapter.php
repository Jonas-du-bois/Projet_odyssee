<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    use HasFactory;

    protected $table = 'chapters'; // Match your database table name

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'description',
        'theory_content',
        'is_active',
    ];

    /**
     * Relationship with units
     */
    public function units()
    {
        return $this->hasMany(Unit::class, 'chapter_id');
    }

    /**
     * Relationship with discoveries
     */
    public function discoveries()
    {
        return $this->hasMany(Discovery::class, 'chapter_id');
    }

    /**
     * Relationship with novelties
     */
    public function novelties()
    {
        return $this->hasMany(Novelty::class, 'chapter_id');
    }

    /**
     * Relationship with events
     */
    public function events()
    {
        return $this->hasMany(Event::class, 'chapter_id');
    }

    /**
     * Relationship with reminders
     */
    public function reminders()
    {
        return $this->hasMany(Reminder::class, 'chapter_id');
    }

    /**
     * Relationship with weeklies
     */
    public function weeklies()
    {
        return $this->hasMany(Weekly::class, 'chapter_id');
    }

    /**
     * Relationship with progress
     */
    public function progress()
    {
        return $this->hasMany(Progress::class, 'chapter_id');
    }
}

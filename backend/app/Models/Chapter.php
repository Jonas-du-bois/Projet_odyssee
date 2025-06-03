<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chapter extends Model
{
    protected $fillable = [
        'titre',
        'description',
    ];

    /**
     * Relations
     */
    public function units()
    {
        return $this->hasMany(Unit::class);
    }

    public function discoveries()
    {
        return $this->hasMany(Discovery::class);
    }

    public function novelties()
    {
        return $this->hasMany(Novelty::class);
    }

    public function events()
    {
        return $this->hasMany(Event::class);
    }

    public function reminders()
    {
        return $this->hasMany(Reminder::class);
    }

    public function weeklies()
    {
        return $this->hasMany(Weekly::class);
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }
}

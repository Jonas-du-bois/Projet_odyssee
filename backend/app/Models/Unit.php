<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'chapter_id',
        'titre',
        'description',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
    ];

    /**
     * Relations
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }

    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    public function progress()
    {
        return $this->hasMany(Progress::class);
    }
}

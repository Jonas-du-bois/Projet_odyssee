<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = [
        'chapter_id',
        'theme',
        'date_debut',
        'date_fin',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'date_debut' => 'date',
        'date_fin' => 'date',
    ];

    /**
     * Relations
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}

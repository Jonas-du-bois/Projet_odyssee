<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'chapter_id',
        'nb_questions',
        'date_limite',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'nb_questions' => 'integer',
        'date_limite' => 'date',
    ];

    /**
     * Relations
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}

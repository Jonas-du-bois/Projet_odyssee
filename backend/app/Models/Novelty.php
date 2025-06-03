<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Novelty extends Model
{
    protected $fillable = [
        'chapter_id',
        'date_publication',
        'bonus_initial',
    ];

    protected $casts = [
        'chapter_id' => 'integer',
        'date_publication' => 'date',
        'bonus_initial' => 'boolean',
    ];

    /**
     * Relations
     */
    public function chapter()
    {
        return $this->belongsTo(Chapter::class);
    }
}
